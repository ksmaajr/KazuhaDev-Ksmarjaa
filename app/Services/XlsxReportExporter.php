<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class XlsxReportExporter
{
    private function xml(string $value): string
    {
        return htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $value), ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function cell(string $ref, mixed $value, int $style = 0): string
    {
        return is_int($value) ? '<c r="'.$ref.'" s="2"><v>'.$value.'</v></c>' : '<c r="'.$ref.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'.$this->xml((string) $value).'</t></is></c>';
    }

    private function sheet(array $rows, array $widths): string
    {
        $cols = '';
        foreach ($widths as $i => $width) {
            $cols .= '<col min="'.($i + 1).'" max="'.($i + 1).'" width="'.$width.'" customWidth="1"/>';
        }
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews><cols>'.$cols.'</cols><sheetData>';
        foreach ($rows as $r => $values) {
            $xml .= '<row r="'.($r + 1).'">';
            foreach ($values as $c => $value) {
                $xml .= $this->cell(chr(65 + $c).($r + 1), $value, $r === 0 ? 1 : 0);
            }$xml .= '</row>';
        }

        return $xml.'</sheetData><autoFilter ref="A1:'.chr(64 + count($widths)).count($rows).'"/></worksheet>';
    }

    public function export(array $report): string
    {
        $summary = [['KazuhaDev. · Laporan keuangan', 'IDR'], ['Periode', $report['from'].' s/d '.$report['to'].' (WIB)'], ['Pemasukan bruto', $report['income']], ['Biaya pembayaran', $report['fees']], ['Pengeluaran', $report['expenses']], ['Arus kas bersih', $report['net']], ['Pemasukan gateway bruto', $report['gateway']], ['Aturan', 'Hanya transaksi posted dihitung; void dikecualikan.'], ['Sumber', 'Ledger financial_transactions; waktu pembayaran WIB.']];
        $rows = [['Referensi', 'Waktu bayar (WIB)', 'Arah', 'Sumber', 'Status', 'Deskripsi', 'Bruto (IDR)', 'Biaya (IDR)', 'Kontribusi kas (IDR)']];
        foreach ($report['rows'] as $r) {
            $net = $r->status === 'posted' ? ($r->direction === 'income' ? $r->amount - $r->fee : -$r->amount) : 0;
            $rows[] = [$r->reference, $r->paid_at->timezone('Asia/Jakarta')->format('Y-m-d H:i:s'), $r->direction, $r->source, $r->status, $r->description, $r->amount, $r->fee, $net];
        }
        $sum = array_sum(array_column(array_slice($rows, 1), 8));
        if ($sum !== $report['net']) {
            throw new RuntimeException('Rekonsiliasi ekspor gagal.');
        }
        $file = tempnam(sys_get_temp_dir(), 'kazuha-report-');
        $zip = new ZipArchive;
        if ($zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Tidak dapat membuat laporan Excel.');
        }
        try {
            $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
            $zip->addFromString('_rels/.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
            $zip->addFromString('xl/workbook.xml', '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Ringkasan" sheetId="1" r:id="rId1"/><sheet name="Transaksi" sheetId="2" r:id="rId2"/></sheets></workbook>');
            $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet2.xml"/><Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
            $zip->addFromString('xl/styles.xml', '<?xml version="1.0"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FF653654"/><name val="Calibri"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF8DDEB"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="3"><xf fontId="0" fillId="0" borderId="0" xfId="0"/><xf fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/><xf fontId="0" fillId="0" borderId="0" numFmtId="3" xfId="0" applyNumberFormat="1"/></cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>');
            $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheet($summary, [34, 72]));
            $zip->addFromString('xl/worksheets/sheet2.xml', $this->sheet($rows, [31, 24, 14, 14, 12, 52, 22, 20, 25]));
        } finally {
            $zip->close();
        }

        return $file;
    }
}

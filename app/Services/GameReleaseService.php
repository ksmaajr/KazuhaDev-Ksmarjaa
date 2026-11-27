<?php

namespace App\Services;

use App\Models\Game;
use App\Models\LicenseEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class GameReleaseService
{
    public function save(?Game $game, array $data, int $actorId, ?string $ip): Game
    {
        $newPaths = [];
        $oldPaths = [];
        try {
            $result = DB::transaction(function () use ($game, $data, $actorId, $ip, &$newPaths, &$oldPaths): Game {
                $locked = $game ? Game::whereKey($game->id)->lockForUpdate()->firstOrFail() : new Game;
                $locked->fill(collect($data)->only(['name', 'release_version'])->all());
                if (! $game) {
                    $locked->code = $data['code'];
                }
                foreach (['injector', 'dll'] as $kind) {
                    if (isset($data[$kind.'_file'])) {
                        $file = $data[$kind.'_file'];
                        $path = $file->store('game-builds', 'local');
                        if (! $path) {
                            throw new \RuntimeException('Gagal menyimpan berkas.');
                        }
                        $newPaths[] = $path;
                        if ($locked->{$kind.'_path'}) {
                            $oldPaths[] = $locked->{$kind.'_path'};
                        }
                        $locked->{$kind.'_path'} = $path;
                        $extension = strtolower($file->getClientOriginalExtension());
                        $locked->{$kind.'_name'} = $locked->code.'-'.$kind.'.'.$extension;
                        $locked->{$kind.'_sha256'} = hash_file('sha256', $file->getRealPath());
                        $locked->{$kind.'_size'} = $file->getSize();
                    } elseif ($data['remove_'.$kind] ?? false) {
                        if ($locked->{$kind.'_path'}) {
                            $oldPaths[] = $locked->{$kind.'_path'};
                        }
                        foreach (['path', 'name', 'sha256', 'size'] as $field) {
                            $locked->{$kind.'_'.$field} = null;
                        }
                    }
                }
                foreach (['banner', 'logo'] as $kind) {
                    if (isset($data[$kind.'_file'])) {
                        $path = $data[$kind.'_file']->store('game-artwork', 'local');
                        if (! $path) {
                            throw new \RuntimeException('Gagal menyimpan gambar.');
                        }
                        $newPaths[] = $path;
                        if ($locked->{$kind.'_path'}) {
                            $oldPaths[] = $locked->{$kind.'_path'};
                        }
                        $locked->{$kind.'_path'} = $path;
                    } elseif ($data['remove_'.$kind] ?? false) {
                        if ($locked->{$kind.'_path'}) {
                            $oldPaths[] = $locked->{$kind.'_path'};
                        }
                        $locked->{$kind.'_path'} = null;
                    }
                }
                $locked->save();
                LicenseEvent::create(['user_id' => $actorId, 'action' => $game ? 'game_updated' : 'game_created', 'ip_address' => $ip, 'metadata' => ['game_id' => $locked->id, 'game_name' => $locked->name, 'game_code' => $locked->code, 'release_version' => $locked->release_version, 'injector_sha256' => $locked->injector_sha256, 'dll_sha256' => $locked->dll_sha256]]);

                return $locked;
            });
        } catch (Throwable $error) {
            Storage::disk('local')->delete($newPaths);
            throw $error;
        }
        Storage::disk('local')->delete($oldPaths);

        return $result;
    }
}

import {notify} from './feedback';

document.addEventListener('click', event => {
    const add = event.target.closest('[data-add-dll-version]');
    if (add) {
        const section = add.closest('[data-dll-versions]');
        const rows = section.querySelector('[data-dll-version-rows]');
        if (rows.children.length >= 20) {
            notify('Maksimal 20 versi per game.', {type: 'error'});
            return;
        }
        const used = [...rows.querySelectorAll('input[name]')].map(input => Number(input.name.match(/dll_variants\[(\d+)\]/)?.[1] ?? -1));
        const index = Math.max(-1, ...used) + 1;
        const row = section.querySelector('template').content.cloneNode(true);
        row.querySelectorAll('[data-version-field]').forEach(input => {
            input.name = `dll_variants[${index}][${input.dataset.versionField}]`;
        });
        rows.append(row);
        rows.lastElementChild.querySelector('input').focus();
    }
    event.target.closest('[data-remove-dll-version]')?.closest('[data-dll-version-row]').remove();
});

(() => {
    const inventoryRows = document.getElementById('inventory-rows');
    if (!inventoryRows) return;

    const listKey = inventoryRows.dataset.listKey;
    const emptyMessage = document.getElementById('empty-search');
    const errorMessage = document.getElementById('edit-error');
    const validUnits = [
        'Gramos (g)',
        'Kilogramos (kg)',
        'Libras (lb)',
        'Onzas (oz)',
        'Mililitros (ml)',
        'Litros (L)',
        'Piezas (pz)'
    ];

    function showError() {
        console.error('No se pudieron cargar los artículos agregados.');
        if (errorMessage) {
            errorMessage.textContent = 'No se pudieron cargar los artículos agregados. Revisa el almacenamiento del navegador.';
            errorMessage.style.display = 'block';
        }
    }

    function removeFromList(row) {
        const entryId = row.dataset.listId;
        if (!entryId) return;

        try {
            const storedIds = localStorage.getItem(listKey);
            if (storedIds === null) return;
            const ids = JSON.parse(storedIds);
            if (!Array.isArray(ids) || !ids.every((id) => typeof id === 'string')) {
                throw new Error('El listado guardado no tiene un formato válido.');
            }

            const remainingIds = ids.filter((id) => id !== entryId);
            if (remainingIds.length === 0) {
                localStorage.removeItem(listKey);
            } else {
                localStorage.setItem(listKey, JSON.stringify(remainingIds));
            }
        } catch (error) {
            showError();
            throw error;
        }
    }

    function makeCell(className, value) {
        const cell = document.createElement('td');
        cell.className = className;
        cell.textContent = value;
        return cell;
    }

    try {
        const storedIds = localStorage.getItem(listKey);
        if (storedIds === null) return;

        const ids = JSON.parse(storedIds);
        if (!Array.isArray(ids) || !ids.every((id) => typeof id === 'string')) {
            throw new Error('El listado guardado no tiene un formato válido.');
        }

        ids.forEach((id) => {
            const storageKey = `bakestock-articulo-${id}`;
            if (localStorage.getItem(`bakestock-eliminado-${id}`) === 'true') return;

            const serializedItem = localStorage.getItem(storageKey);
            if (serializedItem === null) return;

            const item = JSON.parse(serializedItem);
            if (
                item.id !== id
                || typeof item.name !== 'string'
                || (item.stock !== null && !Number.isInteger(item.stock))
                || (item.minimum !== null && !Number.isInteger(item.minimum))
                || typeof item.notes !== 'string'
                || !validUnits.includes(item.unit)
                || (item.fFecha_registro !== undefined && (typeof item.fFecha_registro !== 'string' || !/^\d{4}-\d{2}-\d{2}$/.test(item.fFecha_registro)))
            ) {
                throw new Error('Un artículo guardado tiene datos inválidos.');
            }

            const row = document.createElement('tr');
            row.className = 'inventory-row';
            row.dataset.listId = id;
            row.dataset.storageKey = storageKey;
            row.dataset.deletedKey = `bakestock-eliminado-${id}`;
            row.dataset.defaultName = item.name;

            const checkboxCell = document.createElement('td');
            checkboxCell.className = 'selection-column';
            checkboxCell.style.textAlign = 'center';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'row-checkbox';
            checkbox.setAttribute('aria-label', `Seleccionar ${item.name}`);
            checkboxCell.append(checkbox);
            row.append(checkboxCell);

            row.append(
                makeCell('item-name', item.name),
                makeCell('item-stock', item.stock === null ? '—' : String(item.stock)),
                makeCell('item-minimum', item.minimum === null ? '—' : String(item.minimum)),
                makeCell('item-unit', item.unit),
                makeCell('item-notes', item.notes || '—'),
                makeCell('item-date', item.fFecha_registro || '—')
            );
            inventoryRows.append(row);
        });

        if (inventoryRows.querySelector('.inventory-row') && emptyMessage) {
            emptyMessage.style.display = 'none';
        }
    } catch (error) {
        showError();
    }

    window.BakeStockAddedItems = { remove: removeFromList };
})();

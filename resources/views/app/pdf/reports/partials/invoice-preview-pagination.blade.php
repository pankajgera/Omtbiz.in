<script>
(() => {
    const original = document.querySelector('.invoice-preview-sheet').cloneNode(true);

    function paginate() {
        document.querySelectorAll('.invoice-preview-sheet').forEach(sheet => sheet.remove());
        const source = original.querySelector('.report-document').cloneNode(true);
        const heading = source.querySelector('.document-heading');
        const items = source.querySelector('.line-items');
        const rows = [...items.tBodies[0].rows];
        const footer = source.querySelector('.document-footer');
        let sheet, section, table;

        function newPage(first = false) {
            sheet = original.cloneNode(false);
            sheet.classList.add('is-paginated');
            if (!first) sheet.classList.add('is-continuation');
            section = source.cloneNode(false);
            sheet.append(section);
            document.body.append(sheet);
            if (first) section.append(heading);
            table = null;
        }

        function newTable() {
            table = items.cloneNode(false);
            table.append(items.tHead.cloneNode(true), document.createElement('tbody'));
            section.append(table);
        }

        function overflows() {
            const padding = parseFloat(getComputedStyle(sheet).paddingBottom);
            // A4 content area, measured in the same CSS units as the rendered rows.
            const bottom = sheet.getBoundingClientRect().top + 297 * 96 / 25.4 - padding;
            return section.getBoundingClientRect().bottom > bottom + 0.5;
        }

        newPage(true);
        for (const row of rows) {
            if (!table) newTable();
            table.tBodies[0].append(row);
            if (overflows()) {
                row.remove();
                if (table.tBodies[0].rows.length === 0) table.remove();
                newPage();
                newTable();
                table.tBodies[0].append(row);
            }
        }

        section.append(footer);
        if (overflows()) {
            footer.remove();
            newPage();
            section.append(footer);
        }

        const pages = [...document.querySelectorAll('.invoice-preview-sheet')];
        pages.forEach((page, index) => {
            const number = document.createElement('span');
            number.className = 'invoice-preview-page-number';
            number.textContent = `${index + 1}/${pages.length}`;
            page.append(number);
        });
    }

    paginate();
    document.fonts.ready.then(paginate);
    window.addEventListener('beforeprint', paginate);
})();
</script>

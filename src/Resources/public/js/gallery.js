(function () {
    'use strict';

    function initGrid(grid) {
        const model = grid.dataset.model;
        const modelId = grid.dataset.modelId;
        const modelName = grid.dataset.modelName;
        const base = `/admin/api/${model}/${modelId}/gallery/${modelName}/item`;
        const url = (itemId, action) => `${base}/${itemId}/${action}`;

        function updateCountBadge(delta) {
            const badge = document.getElementById('gallery-count-badge-' + modelName);
            if (!badge) return;
            const current = (parseInt(badge.textContent, 10) || 0) + delta;
            badge.textContent = current;
            badge.classList.toggle('d-none', current <= 0);
        }

        grid.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-gallery-delete');
            if (!btn) return;
            if (!confirm('Opravdu chcete odebrat tento obrázek?')) return;

            const itemId = btn.dataset.itemId;

            axios.post(url(itemId, 'delete'))
                .then(function (response) {
                    if (response.data.status === 'success') {
                        const col = document.getElementById('gallery-item-' + itemId);
                        if (col) col.remove();
                        updateCountBadge(-1);

                        if (grid.querySelectorAll('.gallery-item-col').length === 0) {
                            grid.querySelector('.gallery-grid-items').classList.add('d-none');
                            grid.querySelector('.gallery-grid-empty').classList.remove('d-none');
                        }

                        toastr.success('Obrázek byl odebrán.');
                    } else {
                        toastr.error(response.data.data?.message || 'Nepodařilo se odebrat obrázek.');
                    }
                })
                .catch(function () {
                    toastr.error('Chyba při odebírání obrázku.');
                });
        });

        grid.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-gallery-set-cover');
            if (!btn) return;

            const itemId = btn.dataset.itemId;

            axios.post(url(itemId, 'set-cover'))
                .then(function (response) {
                    if (response.data.status === 'success') {
                        grid.querySelectorAll('.js-gallery-set-cover').forEach(function (b) {
                            const id = b.dataset.itemId;
                            b.classList.remove('active');
                            b.closest('.card').classList.remove('border', 'border-primary');
                            document.getElementById('cover-badge-' + id)?.classList.add('d-none');
                            const icon = document.getElementById('cover-icon-' + id);
                            if (icon) {
                                icon.classList.replace('bi-star-fill', 'bi-star');
                            }
                        });

                        btn.classList.add('active');
                        btn.closest('.card').classList.add('border', 'border-primary');
                        document.getElementById('cover-badge-' + itemId)?.classList.remove('d-none');
                        const icon = document.getElementById('cover-icon-' + itemId);
                        if (icon) {
                            icon.classList.replace('bi-star', 'bi-star-fill');
                        }

                        toastr.success('Obálka byla nastavena.');
                    } else {
                        toastr.error(response.data.data?.message || 'Nepodařilo se nastavit obálku.');
                    }
                })
                .catch(function () {
                    toastr.error('Chyba při nastavení obálky.');
                });
        });

        grid.addEventListener('click', function (e) {
            const btnLeft = e.target.closest('.js-gallery-move-left');
            const btnRight = e.target.closest('.js-gallery-move-right');
            const btn = btnLeft || btnRight;
            if (!btn) return;

            const itemId = btn.dataset.itemId;
            const action = btnLeft ? 'move-left' : 'move-right';

            axios.post(url(itemId, action))
                .then(function (response) {
                    if (response.data.status === 'success') {
                        const col = document.getElementById('gallery-item-' + itemId);
                        const row = grid.querySelector('.gallery-grid-items');
                        if (btnLeft && col.previousElementSibling) {
                            row.insertBefore(col, col.previousElementSibling);
                        } else if (btnRight && col.nextElementSibling) {
                            row.insertBefore(col.nextElementSibling, col);
                        }
                    } else {
                        toastr.error(response.data.data?.message || 'Nepodařilo se přesunout obrázek.');
                    }
                })
                .catch(function () {
                    toastr.error('Chyba při přesouvání obrázku.');
                });
        });
    }

    function init() {
        document.querySelectorAll('.gallery-grid').forEach(initGrid);

        const containers = document.querySelectorAll('.gallery-grid-items');
        if (containers.length > 0) {
            new Sortable.default(containers, {
                draggable: '.gallery-item-col',
                handle: '.gallery-item-col img',
                mirror: {
                    appendTo: 'body',
                    constrainDimensions: true,
                },
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());

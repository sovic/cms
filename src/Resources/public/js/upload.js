(function () {
    'use strict';

    function buildGalleryItemHtml(item) {
        const isCover = !!item.is_cover;
        return `
<div class="col-6 col-md-4 col-lg-3 gallery-item-col" id="gallery-item-${item.id}">
    <div class="card card-flush shadow-sm h-100 ${isCover ? 'border border-primary' : ''}" data-item-id="${item.id}">
        <div class="card-body p-3 d-flex flex-column">
            <a class="d-block overlay" data-fslightbox="lightbox-basic" href="${item.big}">
                <div class="position-relative mb-3">
                    <img src="${item.small}" alt="${item.name}" class="img-fluid rounded w-100 overlay-wrapper" style="object-fit: cover; max-height: 160px;">
                    <span class="badge badge-primary position-absolute top-0 start-0 m-1 ${isCover ? '' : 'd-none'}" id="cover-badge-${item.id}">Titulní</span>
                    <button type="button" class="btn btn-icon btn-sm btn-color-white position-absolute top-0 end-0 m-1 js-gallery-delete" data-item-id="${item.id}" title="Odebrat">
                        <i class="bi bi-x-lg fs-6"></i>
                    </button>
                    <div class="overlay-layer card-rounded bg-dark bg-opacity-25 shadow">
                        <i class="bi bi-eye-fill text-white fs-3x"></i>
                    </div>
                </div>
            </a>
            <div class="fs-8 text-gray-600 mb-2 text-truncate" title="${item.name}">${item.name}${item.extension ? '.' + item.extension : ''}</div>
            ${item.width && item.height ? `<div class="fs-8 text-gray-500 mb-3">${item.width} × ${item.height} px</div>` : ''}
            <div class="mt-auto d-flex gap-1">
                <button type="button" class="btn btn-icon btn-sm btn-light-primary js-gallery-set-cover ${isCover ? 'active' : ''}" data-item-id="${item.id}" title="Nastavit jako titulní obrázek">
                    <i class="bi bi-star${isCover ? '-fill' : ''}" id="cover-icon-${item.id}"></i>
                </button>
                <button type="button" class="btn btn-icon btn-sm btn-light js-gallery-move-left" data-item-id="${item.id}" title="Posunout doleva">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-icon btn-sm btn-light js-gallery-move-right" data-item-id="${item.id}" title="Posunout doprava">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>`;
    }

    function initDropzone(container) {
        const model = container.dataset.model;
        const modelId = container.dataset.modelId;
        const galleryName = container.dataset.galleryName;
        const form = container.querySelector('.dropzone');

        if (!form || !model || !modelId || !galleryName) {
            return;
        }

        const grid = document.querySelector('.gallery-grid[data-model="' + model + '"][data-model-id="' + modelId + '"][data-model-name="' + galleryName + '"]');

        new Dropzone(form, {
            url: '/admin/api/upload',
            paramName: 'file',
            maxFiles: 10,
            maxFilesize: 32,
            parallelUploads: 2,
            addRemoveLinks: false,
            autoProcessQueue: true,
            params: {
                model: model,
                model_id: modelId,
                gallery_name: galleryName,
            },
            success: function (file, response) {
                if (response.status === 'success' && response.data && response.data.item) {
                    const item = response.data.item;

                    if (grid) {
                        const itemsRow = grid.querySelector('.gallery-grid-items');
                        const emptyState = grid.querySelector('.gallery-grid-empty');

                        if (itemsRow) {
                            itemsRow.insertAdjacentHTML('beforeend', buildGalleryItemHtml(item));
                            itemsRow.classList.remove('d-none');
                        }
                        if (emptyState) {
                            emptyState.classList.add('d-none');
                        }

                        const badge = document.getElementById('gallery-count-badge-' + galleryName);
                        if (badge) {
                            const current = (parseInt(badge.textContent, 10) || 0) + 1;
                            badge.textContent = current;
                            badge.classList.remove('d-none');
                        }
                    }

                    this.removeFile(file);
                } else {
                    const message = response.data && response.data.message ? response.data.message : 'Nahrání selhalo.';
                    toastr.error(message);
                }
            },
            error: function (file, message) {
                const text = typeof message === 'string' ? message : (message.data && message.data.message ? message.data.message : 'Chyba při nahrávání.');
                toastr.error(text);
                this.removeFile(file);
            },
        });
    }

    function init() {
        document.querySelectorAll('.gallery-upload').forEach(initDropzone);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());

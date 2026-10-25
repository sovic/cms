(function () {
    'use strict';

    /**
     * @param {string} entity
     * @param {string|number} entityId
     * @returns {string}
     */
    function buildApiUrl(entity, entityId) {
        return `/admin/api/web/${entity}/${entityId}/toggle-state`;
    }

    /**
     * Switch state dependent tooltip of check button (form macro check_button).
     * @param {HTMLInputElement} checkbox
     */
    function syncTooltip(checkbox) {
        const label = checkbox.closest('label');
        if (!label || label.dataset.tooltipChecked === undefined) {
            return;
        }
        const text = checkbox.checked ? label.dataset.tooltipChecked : label.dataset.tooltipUnchecked;
        const tooltip = window.bootstrap && bootstrap.Tooltip.getInstance(label);
        if (tooltip) {
            // initialized tooltip moves title to data-bs-original-title, keep native title empty
            label.setAttribute('data-bs-original-title', text);
            tooltip.setContent({'.tooltip-inner': text});
        } else {
            label.setAttribute('title', text);
        }
    }

    /**
     * @param {HTMLInputElement} checkbox
     * @param {string} url
     */
    function handleToggle(checkbox, url) {
        const fieldName = checkbox.dataset.field;
        if (!fieldName) {
            console.warn('[toggle-switch] Missing data-field on checkbox:', checkbox.id);
            return;
        }

        const prevState = checkbox.checked;
        checkbox.disabled = true;

        axios.post(url, {
            field: fieldName,
            state: prevState,
        }).then(function (response) {
            const payload = response.data;
            if (payload && payload.status === 'success') {
                const newState = payload.data && payload.data.value !== undefined ? payload.data.value : !prevState;
                checkbox.checked = newState;
                const icon = checkbox.closest('label').querySelector('.form-check-label i');
                if (icon) {
                    icon.classList.toggle('text-success', newState);
                    icon.classList.toggle('text-warning', !newState);
                }
            } else {
                console.error('[toggle-switch] Toggle failed', payload);
                checkbox.checked = !prevState;
            }
        }).catch(function (error) {
            console.error('[toggle-switch] Request error', error);
            checkbox.checked = !prevState;
        }).finally(function () {
            checkbox.disabled = false;
            syncTooltip(checkbox);
        });
    }

    /**
     * Attach change listeners to all toggle switches inside containers
     * marked with the .js-toggle-switch class and data-entity / data-entity-id attributes,
     * or with data-url attribute pointing to a custom toggle endpoint.
     */
    function init() {
        document.querySelectorAll('.js-toggle-switch').forEach(function (container) {
            const entity = container.dataset.entity;
            const entityId = container.dataset.entityId;
            let url = container.dataset.url;

            if (!url) {
                if (!entity || !entityId) {
                    console.warn('[toggle-switch] Missing data-url or data-entity / data-entity-id on container:', container);
                    return;
                }
                url = buildApiUrl(entity, entityId);
            }

            container.querySelectorAll('input.form-check-input[type="checkbox"]').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    handleToggle(checkbox, url);
                });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

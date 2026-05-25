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
     * @param {HTMLInputElement} checkbox
     * @param {string} entity
     * @param {string} entityId
     */
    function handleToggle(checkbox, entity, entityId) {
        const fieldName = checkbox.dataset.field;
        if (!fieldName) {
            console.warn('[toggle-switch] Missing data-field on checkbox:', checkbox.id);
            return;
        }

        const prevState = checkbox.checked;
        checkbox.disabled = true;

        axios.post(buildApiUrl(entity, entityId), {
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
        });
    }

    /**
     * Attach change listeners to all toggle switches inside containers
     * marked with the .js-toggle-switch class and data-entity / data-entity-id attributes.
     */
    function init() {
        document.querySelectorAll('.js-toggle-switch').forEach(function (container) {
            const entity = container.dataset.entity;
            const entityId = container.dataset.entityId;

            if (!entity || !entityId) {
                console.warn('[toggle-switch] Missing data-entity or data-entity-id on container:', container);
                return;
            }

            container.querySelectorAll('input.form-check-input[type="checkbox"]').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    handleToggle(checkbox, entity, entityId);
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

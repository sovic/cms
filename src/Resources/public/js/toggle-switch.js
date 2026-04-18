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

        const newState = checkbox.checked;
        checkbox.disabled = true;

        axios.post(buildApiUrl(entity, entityId), {
            field: fieldName,
            state: newState,
        }).then(function (response) {
            const payload = response.data;
            if (payload && payload.status === 'success') {
                if (payload.data && payload.data.value !== undefined) {
                    checkbox.checked = payload.data.value;
                }
            } else {
                console.error('[toggle-switch] Toggle failed', payload);
                checkbox.checked = !newState;
            }
        }).catch(function (error) {
            console.error('[toggle-switch] Request error', error);
            checkbox.checked = !newState;
        }).finally(function () {
            checkbox.disabled = false;
        });
    }

    /**
     * Attach change listeners to all toggle switches inside containers
     * that carry a data-{entity}-id attribute.
     */
    function init() {
        document.querySelectorAll('[data-user-id], [data-file-id]').forEach(function (container) {
            const userId = container.dataset.userId;
            const fileId = container.dataset.fileId;
            const entity = userId !== undefined ? 'user' : 'file';
            const entityId = userId !== undefined ? userId : fileId;

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

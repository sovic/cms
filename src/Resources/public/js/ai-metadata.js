(function () {
    'use strict';

    /** Client side timeout, a metadata suggestion is a short generation. */
    const REQUEST_TIMEOUT = 60000;

    /**
     * Button attributes:
     * data-url             metadata API endpoint
     * data-collapse        collapsible metadata section selector, opened on click
     * data-source-heading  heading input selector
     * data-source-content  content textarea selector (TinyMCE editor id is taken from it)
     * data-source-tags     tags input selector (comma separated values)
     * data-target-*        meta title / description / keywords input selectors
     *
     * @param {HTMLButtonElement} button
     */
    function init(button) {
        const value = function (selector) {
            const input = selector ? document.querySelector(selector) : null;
            return input ? input.value.trim() : '';
        };

        const targets = {
            title: document.querySelector(button.dataset.targetTitle),
            description: document.querySelector(button.dataset.targetDescription),
            keywords: document.querySelector(button.dataset.targetKeywords),
        };

        button.addEventListener('click', function () {
            const collapse = button.dataset.collapse ? document.querySelector(button.dataset.collapse) : null;
            if (collapse) {
                bootstrap.Collapse.getOrCreateInstance(collapse, {toggle: false}).show();
            }

            // only empty fields are generated, values filled manually are kept
            const fields = Object.keys(targets).filter(function (field) {
                return targets[field] && targets[field].value.trim() === '';
            });
            if (fields.length === 0) {
                toastr.info('Metadata jsou již vyplněná, smažte pole, která chcete vygenerovat.');
                return;
            }

            const contentSelector = button.dataset.sourceContent;
            const editor = window.tinymce && contentSelector ? tinymce.get(contentSelector.replace(/^#/, '')) : null;

            const icon = button.querySelector('i');
            const iconClass = icon ? icon.className : '';
            button.disabled = true;
            if (icon) {
                icon.className = 'spinner-border spinner-border-sm';
            }

            axios.post(button.dataset.url, {
                fields: fields,
                heading: value(button.dataset.sourceHeading),
                content: editor ? editor.getContent() : value(contentSelector),
                tags: value(button.dataset.sourceTags),
            }, {
                timeout: REQUEST_TIMEOUT,
            }).then(function (response) {
                const payload = response.data;
                if (!payload || payload.status !== 'success') {
                    throw {response: {data: payload}};
                }

                fields.forEach(function (field) {
                    const suggested = payload.data.values[field];
                    // the user may have typed into the field while waiting
                    if (suggested && targets[field].value.trim() === '') {
                        targets[field].value = suggested;
                        targets[field].dispatchEvent(new Event('input', {bubbles: true}));
                    }
                });
            }).catch(function (error) {
                const data = error.response ? error.response.data : null;
                toastr.error(data && data.data && data.data.message
                    ? data.data.message
                    : 'Metadata se nepodařilo vygenerovat, zkuste to prosím znovu.');
            }).finally(function () {
                button.disabled = false;
                if (icon) {
                    icon.className = iconClass;
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-ai-metadata]').forEach(init);
    });
})();

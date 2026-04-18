(function () {
    'use strict';

    function updateHideExceptTab(tabId) {
        document.querySelectorAll('[data-hide-except-tab]').forEach(function (el) {
            el.classList.toggle('d-none', el.dataset.hideExceptTab !== tabId);
        });
    }

    function init() {
        document.addEventListener('shown.bs.tab', function (e) {
            const target = e.target.dataset.bsTarget;
            if (!target) return;
            const tabId = target.replace('#', '');
            history.pushState(null, '', '#' + tabId);
            updateHideExceptTab(tabId);
            window.scrollTo({top: 0, behavior: 'instant'});
        });

        function activateTabFromHash(hash) {
            if (!hash) return false;
            const btn = document.querySelector('[data-bs-toggle="tab"][data-bs-target="' + hash + '"]');
            if (btn) {
                bootstrap.Tab.getOrCreateInstance(btn).show();
                return true;
            }
            return false;
        }

        window.addEventListener('hashchange', function (e) {
            activateTabFromHash(window.location.hash);
        });

        if (!activateTabFromHash(window.location.hash)) {
            // No hash — initialise hide state for the default active tab
            const activeBtn = document.querySelector('[data-bs-toggle="tab"].active');
            if (activeBtn && activeBtn.dataset.bsTarget) {
                updateHideExceptTab(activeBtn.dataset.bsTarget.replace('#', ''));
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());

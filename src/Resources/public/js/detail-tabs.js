/*
 * Detail navigation tabs: the tabs that do not fit are moved to a dropdown, the current tab always stays visible.
 * Loaded on every admin page, works only on the opt-in markup:
 *
 * <nav class="detail-tabs mb-8" aria-label="Sekce ...">
 *     <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold">
 *         {{ detail.tab(label, url, current_path) }}
 *     </ul>
 * </nav>
 *
 * Only for links to other pages, not for data-bs-toggle="tab" tabs within a page (see tabs.js).
 */
document.querySelectorAll('.detail-tabs').forEach(function ($nav) {
    const $list = $nav.querySelector(':scope > .nav');
    if (!$list) {
        return;
    }

    const $more = document.createElement('div');
    $more.className = 'dropdown detail-tabs-more';
    $more.hidden = true;
    $more.innerHTML = `
        <button type="button"
                class="btn btn-sm btn-icon bg-body border btn-color-gray-700 btn-active-color-primary"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Další sekce">
            <i class="bi bi-three-dots fs-3" aria-hidden="true"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end"></ul>
    `;
    const $toggle = $more.querySelector('button');
    const $menu = $more.querySelector('ul');

    const tabs = Array.from($list.querySelectorAll(':scope > .nav-item > .nav-link'), function ($link) {
        const isCurrent = $link.classList.contains('active');

        // links to other pages, not ARIA tabs (the roles come from the CMS macro)
        $link.removeAttribute('role');
        $link.parentElement.removeAttribute('role');
        if (isCurrent) {
            $link.setAttribute('aria-current', 'page');
        }

        const $menuItem = document.createElement('li');
        const $menuLink = document.createElement('a');
        $menuLink.className = 'dropdown-item py-3 px-5';
        $menuLink.href = $link.href;
        $menuLink.textContent = $link.textContent.trim();
        $menuItem.hidden = true;
        $menuItem.append($menuLink);
        $menu.append($menuItem);

        return {$item: $link.parentElement, $link, $menuItem, isCurrent};
    });

    function setCollapsed(tab, isCollapsed) {
        tab.$item.hidden = isCollapsed;
        tab.$menuItem.hidden = !isCollapsed;
    }

    function fits() {
        return $list.scrollWidth <= $list.clientWidth + 1; // both are rounded
    }

    function update() {
        const $focused = $nav.contains(document.activeElement) ? document.activeElement : null;

        // measured from the expanded state, the browser does not paint in between
        $nav.classList.remove('is-truncated');
        tabs.forEach(function (tab) {
            setCollapsed(tab, false);
        });
        $more.hidden = true;

        if (!fits()) {
            $more.hidden = false;
            for (let i = tabs.length - 1; i >= 0 && !fits(); i--) {
                if (!tabs[i].isCurrent) {
                    setCollapsed(tabs[i], true);
                }
            }
            $nav.classList.toggle('is-truncated', !fits());
        } else if ($toggle.getAttribute('aria-expanded') === 'true') {
            bootstrap.Dropdown.getOrCreateInstance($toggle).hide();
        }

        // a resize must not lose the keyboard focus
        if ($focused) {
            const isVisible = $focused.getClientRects().length > 0;
            const $fallback = $more.hidden ? (tabs.find((tab) => tab.isCurrent) || tabs[0]).$link : $toggle;
            (isVisible ? $focused : $fallback).focus({preventScroll: true});
        }
    }

    $nav.append($more);
    $nav.classList.add('is-ready');
    update();

    // in the next frame, changing the size inside the callback raises "ResizeObserver loop" errors
    let frame = 0;
    new ResizeObserver(function () {
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(update);
    }).observe($nav);

    // a loaded web font changes the width of the labels, not of the nav
    document.fonts.ready.then(update);
    document.fonts.addEventListener('loadingdone', update);
});

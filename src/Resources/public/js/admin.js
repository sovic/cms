function createLoader() {
    const loadingEl = document.createElement("div");
    document.body.append(loadingEl);
    loadingEl.classList.add("page-loader");
    loadingEl.innerHTML = `
        <span class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Probíhá zpracování…</span>
        </span>
    `;
}

function initTooltip() {
    let $tooltipEl = document.querySelectorAll("[data-bs-toggle='tooltip']");
    $tooltipEl.forEach((el) => {
        new bootstrap.Tooltip(el);
    });
}

function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        let date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}

function toggleScrollTop() {
    const btn = document.getElementById('back-to-top');
    if (!btn) {
        return;
    }

    const threshold = Number(btn.dataset.threshold) || 300;

    if (window.scrollY > threshold) {
        btn.classList.remove('d-none');
    } else {
        btn.classList.add('d-none');
    }
}

// Initialize binds on DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {
    createLoader();
    initTooltip();
    toggleScrollTop();

    // Copy to clipboard functionality
    let $copyEl = document.getElementsByClassName('copy-to-clipboard');
    for (let i = 0; i < $copyEl.length; i++) {
        const target = $copyEl[i].querySelector('.copy-to-clipboard-target');
        const button = $copyEl[i].querySelector('.copy-to-clipboard-source');
        let clipboard = new ClipboardJS(button, {
            target: target,
            text: function () {
                return target.innerHTML;
            }
        });

        clipboard.on('success', function () {
            const currentLabel = button.innerHTML;
            if (button.innerHTML === 'Zkopírováno!') {
                return;
            }
            button.innerHTML = 'Zkopírováno!';
            setTimeout(function () {
                button.innerHTML = currentLabel;
            }, 3000)
        });
    }
});

window.addEventListener('scroll', toggleScrollTop, {passive: true});
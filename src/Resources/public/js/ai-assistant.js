(function () {
    'use strict';

    const EDITOR_ID = 'page_content';

    /**
     * @param {HTMLElement} drawer
     */
    function AiAssistant(drawer) {
        this.drawer = drawer;
        this.apiBase = drawer.dataset.apiBase;
        this.contentCss = drawer.dataset.contentCss || '';
        this.settingsUrl = drawer.dataset.settingsUrl;
        this.messages = drawer.querySelector('#ai-messages');
        this.empty = drawer.querySelector('#ai-empty');
        this.input = drawer.querySelector('#ai-input');
        this.sendButton = drawer.querySelector('#ai-send');
        this.resetButton = drawer.querySelector('#ai-reset');
        this.template = drawer.querySelector('#ai-message-tpl');
        /** @type {Object<string, string>} */
        this.htmlByMessage = {};
        this.loaded = false;
        this.busy = false;
        this.tempId = 0;
    }

    AiAssistant.prototype.init = function () {
        const self = this;
        const toggle = document.getElementById('ai-assistant-toggle');
        if (toggle) {
            toggle.addEventListener('click', function () {
                self.loadHistory();
                setTimeout(function () {
                    self.input.focus();
                }, 300);
            });
        }

        this.sendButton.addEventListener('click', function () {
            self.send();
        });
        this.input.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' && (event.ctrlKey || event.metaKey)) {
                event.preventDefault();
                self.send();
            }
        });
        this.resetButton.addEventListener('click', function () {
            self.reset();
        });
        this.messages.addEventListener('click', function (event) {
            const button = event.target.closest('[data-ai-action]');
            if (button) {
                const message = button.closest('.ai-message');
                self.apply(button.dataset.aiAction, self.htmlByMessage[message.dataset.messageId]);
            }
        });
    };

    AiAssistant.prototype.loadHistory = function () {
        if (this.loaded) {
            return;
        }
        this.loaded = true;

        const self = this;
        axios.get(this.apiBase + '/history').then(function (response) {
            const payload = response.data;
            if (!payload || payload.status !== 'success') {
                return;
            }
            payload.data.messages.forEach(function (message) {
                self.renderMessage(message);
            });
            self.scrollDown();
        }).catch(function (error) {
            self.loaded = false;
            self.showError(error.response ? error.response.data : null);
        });
    };

    AiAssistant.prototype.send = function () {
        const text = this.input.value.trim();
        if (text === '' || this.busy) {
            return;
        }

        const editor = window.tinymce ? tinymce.get(EDITOR_ID) : null;
        const self = this;

        this.setBusy(true);
        const pending = this.renderMessage({id: 'pending-' + (++this.tempId), role: 'user', content: text, html: null});
        this.scrollDown();

        axios.post(this.apiBase + '/message', {
            message: text,
            current_content: editor ? editor.getContent() : null,
        }).then(function (response) {
            const payload = response.data;
            if (!payload || payload.status !== 'success') {
                throw {response: {data: payload}};
            }
            self.input.value = '';
            self.renderMessage({
                id: payload.data.message_id,
                role: 'assistant',
                content: payload.data.reply,
                html: payload.data.html,
            });
            self.scrollDown();
        }).catch(function (error) {
            // failed messages are not stored, keep the text in the input for another try
            pending.remove();
            self.showError(error && error.response ? error.response.data : null);
        }).finally(function () {
            self.setBusy(false);
        });
    };

    AiAssistant.prototype.reset = function () {
        if (this.busy) {
            return;
        }
        const self = this;
        axios.post(this.apiBase + '/reset').then(function () {
            self.messages.querySelectorAll('.ai-message').forEach(function (element) {
                element.remove();
            });
            self.htmlByMessage = {};
            self.empty.hidden = false;
            self.input.focus();
        }).catch(function (error) {
            self.showError(error.response ? error.response.data : null);
        });
    };

    /**
     * @param {{id: (number|string), role: string, content: string, html: (string|null)}} message
     * @returns {HTMLElement}
     */
    AiAssistant.prototype.renderMessage = function (message) {
        const fragment = this.template.content.cloneNode(true);
        const element = fragment.querySelector('.ai-message');
        const isUser = message.role === 'user';

        element.dataset.messageId = String(message.id);

        const role = element.querySelector('.js-ai-role');
        role.textContent = isUser ? 'Vy' : 'Asistent';
        role.classList.add(isUser ? 'badge-light-primary' : 'badge-light-success');

        const text = element.querySelector('.js-ai-text');
        text.textContent = message.content;
        text.classList.add(isUser ? 'bg-light-primary' : 'bg-light');

        if (!isUser && message.html) {
            this.htmlByMessage[String(message.id)] = message.html;
            const preview = element.querySelector('.js-ai-preview');
            preview.querySelector('iframe').srcdoc = this.buildPreviewDocument(message.html);
            preview.hidden = false;
        }

        this.empty.hidden = true;
        this.messages.appendChild(fragment);

        return element;
    };

    /**
     * @param {string} html
     * @returns {string}
     */
    AiAssistant.prototype.buildPreviewDocument = function (html) {
        let head = '<meta charset="utf-8"><base target="_blank">';
        if (this.contentCss) {
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = this.contentCss;
            head += link.outerHTML;
        }

        return '<!doctype html><html><head>' + head + '</head><body style="padding: 12px;">' + html + '</body></html>';
    };

    /**
     * @param {string} action
     * @param {string|undefined} html
     */
    AiAssistant.prototype.apply = function (action, html) {
        if (!html) {
            return;
        }

        if (action === 'copy') {
            navigator.clipboard.writeText(html).then(function () {
                toastr.success('HTML zkopírováno do schránky.');
            });
            return;
        }

        const editor = window.tinymce ? tinymce.get(EDITOR_ID) : null;
        if (!editor) {
            toastr.error('Editor obsahu nebyl nalezen.');
            return;
        }

        if (action === 'replace') {
            if (editor.getContent().trim() !== '' && !confirm('Nahradit celý obsah stránky? Změnu lze vrátit tlačítkem Zpět v editoru.')) {
                return;
            }
            editor.undoManager.transact(function () {
                editor.setContent(html);
            });
        } else if (action === 'insert') {
            editor.undoManager.transact(function () {
                editor.insertContent(html);
            });
        } else {
            return;
        }

        editor.setDirty(true);
        editor.save();
        toastr.success('Obsah vložen do editoru, nezapomeňte stránku uložit.');
    };

    /**
     * @param {object|null} payload JSend fail/error payload
     */
    AiAssistant.prototype.showError = function (payload) {
        let message = 'Chyba AI asistenta, zkuste to prosím znovu.';
        if (payload && payload.data && payload.data.message) {
            message = payload.data.message;
        } else if (payload && payload.message) {
            message = payload.message;
        }

        if (payload && payload.data && payload.data.settings_url) {
            toastr.error(message + ' <a href="' + this.settingsUrl + '" class="fw-bold text-white text-decoration-underline">Otevřít nastavení</a>', '', {
                escapeHtml: false,
                timeOut: 10000,
            });
            return;
        }

        toastr.error(message);
    };

    AiAssistant.prototype.setBusy = function (busy) {
        this.busy = busy;
        this.input.disabled = busy;
        this.sendButton.disabled = busy;
        this.resetButton.disabled = busy;
        if (busy) {
            this.sendButton.setAttribute('data-kt-indicator', 'on');
        } else {
            this.sendButton.removeAttribute('data-kt-indicator');
            this.input.focus();
        }
    };

    AiAssistant.prototype.scrollDown = function () {
        this.messages.scrollTop = this.messages.scrollHeight;
    };

    function init() {
        const drawer = document.getElementById('kt-drawer-ai');
        if (drawer) {
            new AiAssistant(drawer).init();
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

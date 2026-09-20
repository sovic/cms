(function () {
    'use strict';

    const EDITOR_ID = 'page_content';

    /** Client side timeout, must be longer than the nginx/PHP chain so the server error arrives first. */
    const REQUEST_TIMEOUT = 250000;

    /** Recovery polling: an answer is stored even when the browser or a proxy gave up on the request. */
    const POLL_INTERVAL = 3000;
    const POLL_MAX_TRIES = 40;

    /**
     * @param {string|undefined} value JSON encoded list of strings
     * @returns {string[]}
     */
    function parseList(value) {
        if (!value) {
            return [];
        }
        try {
            const parsed = JSON.parse(value);
            return Array.isArray(parsed) ? parsed.filter(Boolean) : [];
        } catch (e) {
            return [];
        }
    }

    /**
     * @param {HTMLElement} drawer
     */
    function AiAssistant(drawer) {
        this.drawer = drawer;
        this.apiBase = drawer.dataset.apiBase;
        this.contentCss = drawer.dataset.contentCss || '';
        this.previewCss = parseList(drawer.dataset.previewCss);
        this.previewBase = drawer.dataset.previewBase || '';
        this.previewBodyClass = drawer.dataset.previewBodyClass || '';
        this.previewWrapperClass = drawer.dataset.previewWrapperClass || '';
        if (this.previewCss.length === 0 && this.contentCss) {
            this.previewCss = [this.contentCss];
        }
        this.settingsUrl = drawer.dataset.settingsUrl;
        this.messages = drawer.querySelector('#ai-messages');
        this.empty = drawer.querySelector('#ai-empty');
        this.input = drawer.querySelector('#ai-input');
        this.sendButton = drawer.querySelector('#ai-send');
        this.resetButton = drawer.querySelector('#ai-reset');
        this.template = drawer.querySelector('#ai-message-tpl');
        /** @type {Object<string, string>} */
        this.htmlByMessage = {};
        this.lastMessageId = 0;
        this.loaded = false;
        this.busy = false;
        this.recovering = false;
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

            // a generation started before a reload is still running, wait for its answer
            if (payload.data.in_progress) {
                self.recover(self.lastMessageId, 'Předchozí požadavek se ještě zpracovává, čekám na odpověď…');
            }
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
        const since = this.lastMessageId;

        this.setBusy(true);
        const pending = this.renderMessage({id: 'pending-' + (++this.tempId), role: 'user', content: text, html: null});
        this.scrollDown();

        axios.post(this.apiBase + '/message', {
            message: text,
            current_content: editor ? editor.getContent() : null,
        }, {
            timeout: REQUEST_TIMEOUT,
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
            self.setBusy(false);
        }).catch(function (error) {
            const payload = error && error.response ? error.response.data : null;

            if (self.isRecoverable(error, payload)) {
                // the answer is generated and stored server side, pick it up from the conversation history
                self.input.value = '';
                self.recover(since, 'Odpověď trvá déle než obvykle, čekám na její dokončení…');
                return;
            }

            // failed messages are not stored, keep the text in the input for another try
            pending.remove();
            self.setBusy(false);
            self.showError(payload);
        });
    };

    /**
     * True when the request failed in a way that does not mean the generation itself failed:
     * client/proxy timeout, dropped connection, gateway error, or a generation already running.
     *
     * @param {object} error
     * @param {object|null} payload
     * @returns {boolean}
     */
    AiAssistant.prototype.isRecoverable = function (error, payload) {
        if (payload && payload.data && payload.data.errors && payload.data.errors.indexOf('in_progress') !== -1) {
            return true;
        }
        if (!error || !error.response) {
            // timeout or network error, there is no server answer at all
            return true;
        }

        return [502, 503, 504].indexOf(error.response.status) !== -1 && !(payload && payload.status);
    };

    /**
     * Polls the conversation until the answer of a request we lost the response of appears.
     *
     * @param {number} sinceId
     * @param {string} notice
     */
    AiAssistant.prototype.recover = function (sinceId, notice) {
        if (this.recovering) {
            return;
        }
        this.recovering = true;
        this.setBusy(true);
        toastr.info(notice);

        const self = this;
        let tries = 0;

        const poll = function () {
            tries += 1;
            axios.get(self.apiBase + '/history', {params: {after_id: sinceId}}).then(function (response) {
                const payload = response.data;
                if (!payload || payload.status !== 'success') {
                    return schedule();
                }

                const messages = payload.data.messages || [];
                const answered = messages.some(function (message) {
                    return message.role === 'assistant';
                });

                if (answered) {
                    // replace the optimistic user message with what is really stored
                    self.messages.querySelectorAll('.ai-message[data-message-id^="pending-"]').forEach(function (element) {
                        element.remove();
                    });
                    messages.forEach(function (message) {
                        self.renderMessage(message);
                    });
                    self.scrollDown();
                    self.finishRecovery();
                    toastr.success('Odpověď byla načtena.');
                    return;
                }

                if (!payload.data.in_progress && tries > 2) {
                    // nothing is running any more and no answer was stored, the generation failed
                    self.failRecovery();
                    return;
                }

                schedule();
            }).catch(function () {
                schedule();
            });
        };

        const schedule = function () {
            if (tries >= POLL_MAX_TRIES) {
                self.failRecovery();
                return;
            }
            setTimeout(poll, POLL_INTERVAL);
        };

        setTimeout(poll, POLL_INTERVAL);
    };

    AiAssistant.prototype.finishRecovery = function () {
        this.recovering = false;
        this.setBusy(false);
    };

    AiAssistant.prototype.failRecovery = function () {
        this.messages.querySelectorAll('.ai-message[data-message-id^="pending-"]').forEach(function (element) {
            element.remove();
        });
        this.recovering = false;
        this.setBusy(false);
        toastr.error('Odpověď se nepodařilo načíst. Zkuste to prosím znovu, nebo za chvíli obnovte stránku.');
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
        if (typeof message.id === 'number' && message.id > this.lastMessageId) {
            this.lastMessageId = message.id;
        }

        const role = element.querySelector('.js-ai-role');
        role.textContent = isUser ? 'Vy' : 'Asistent';
        role.classList.add(isUser ? 'badge-light-primary' : 'badge-light-success');

        const text = element.querySelector('.js-ai-text');
        text.textContent = message.content;
        text.classList.add(isUser ? 'bg-light-primary' : 'bg-light');

        if (!isUser && message.html) {
            this.htmlByMessage[String(message.id)] = message.html;
            const preview = element.querySelector('.js-ai-preview');
            const frame = preview.querySelector('iframe');
            this.autoSize(frame);
            frame.srcdoc = this.buildPreviewDocument(message.html);
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
        const doc = document.implementation.createHTMLDocument('');

        const meta = doc.createElement('meta');
        meta.setAttribute('charset', 'utf-8');
        doc.head.appendChild(meta);

        const base = doc.createElement('base');
        if (this.previewBase) {
            // relative stylesheet, image and link paths resolve against the public website
            base.href = this.previewBase.replace(/\/+$/, '') + '/';
        }
        base.target = '_blank';
        doc.head.appendChild(base);

        this.previewCss.forEach(function (href) {
            const link = doc.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            doc.head.appendChild(link);
        });

        if (this.previewBodyClass) {
            doc.body.className = this.previewBodyClass;
        }

        let target = doc.body;
        if (this.previewWrapperClass) {
            const wrapper = doc.createElement('div');
            wrapper.className = this.previewWrapperClass;
            doc.body.appendChild(wrapper);
            target = wrapper;
        }
        target.innerHTML = html;

        return '<!doctype html>' + doc.documentElement.outerHTML;
    };

    /**
     * Grows the preview to the height of its content, so the page is not scrolled inside a small box.
     *
     * @param {HTMLIFrameElement} frame
     */
    AiAssistant.prototype.autoSize = function (frame) {
        const resize = function () {
            try {
                const body = frame.contentDocument ? frame.contentDocument.body : null;
                if (!body) {
                    return;
                }
                const height = Math.max(body.scrollHeight, body.offsetHeight);
                frame.style.height = Math.min(Math.max(height + 24, 200), 900) + 'px';
            } catch (e) {
                // preview is not readable, keep the default height
            }
        };

        frame.addEventListener('load', function () {
            resize();
            // stylesheets and images may still be loading and change the height
            setTimeout(resize, 400);
            setTimeout(resize, 1500);
        });
    };

    /**
     * @param {string} action
     * @param {string|undefined} html
     */
    AiAssistant.prototype.apply = function (action, html) {
        if (!html) {
            return;
        }

        if (action === 'fullscreen') {
            const modal = document.getElementById('ai-preview-modal');
            if (modal) {
                document.getElementById('ai-preview-modal-frame').srcdoc = this.buildPreviewDocument(html);
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
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

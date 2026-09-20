# AI page assistant

The page edit form can show an AI assistant (Anthropic Claude) that prepares page HTML from chat instructions,
using the website's own HTML components and styleguide, so editors without HTML knowledge produce content in the
same visual style as the rest of the website.

## Configuration

Add to `config/packages/sovic_cms.yaml` (the assistant is enabled when `components_dir` is set):

```yaml
sovic_cms:
    ai:
        components_dir: '%kernel.project_dir%/templates/components'
        styleguide_file: '%kernel.project_dir%/templates/components/STYLEGUIDE.md'
        model: 'claude-opus-5'   # default, users can override it in their AI settings
        effort: 'medium'         # low | medium | high | xhigh | max
        max_tokens: 16000        # max output tokens of one response
        history_limit: 20        # previous messages sent to the model
```

- `components_dir` contains one `*.html` file per component, the file name is the component name
  (e.g. `hero.html`, `feature-cards.html`, `cta.html`). Each file holds example markup with the real CSS classes
  and placeholder texts. Max 100 files, 64 KB per file, 512 KB in total.
- `styleguide_file` (Markdown or HTML) describes how to use the components: which component to use when, tone of
  voice, allowed classes, heading levels, etc.
- The whole library is sent with every request (prompt cached), keep the snippets short.

## Database

New entities `UserAiSetting`, `AiConversation`, `AiMessage` (tables `user_ai_setting`, `ai_conversation`,
`ai_message`) need a migration in the host application.

## API keys

API keys are per user. Each admin opens *AI asistent* in the user menu (`/admin/ai/settings`) and enters their own
Anthropic API key. Keys are stored encrypted with a key derived from `kernel.secret`; changing the secret makes
stored keys unreadable and users have to enter them again.

## Preview styles

The assistant shows a preview of the generated content in a sandboxed iframe. To make it look like the real page,
point `preview_css` at the stylesheets of the public website:

```yaml
sovic_cms:
    base_public_url: 'https://www.example.com'
    ai:
        preview_css:
            - '/build/app.css'
        preview_body_class: ''
        preview_wrapper_class: 'container'
```

- `preview_css` takes one path or a list of paths. Relative paths resolve against `base_public_url`, so the
  stylesheet of the public site can be reused as it is, including the fonts and images it references. When left
  empty, the preview falls back to `tinymce_content_css`.
- `preview_body_class` and `preview_wrapper_class` reproduce the wrapper markup of the public page template, for
  styles that depend on it (a container class, a page modifier class on the body).
- The preview grows to the height of its content and every answer has a *Velký náhled* button that opens the
  content full screen in the same styles.
- Scripts never run in the preview, so components that need JavaScript (sliders, accordions) show their static
  markup only.

The stylesheet is used for rendering the preview only, it is not sent to the model. What the model may use is
defined by the component snippets and the styleguide file. Describe the available classes and when to use them in
the styleguide rather than attaching the whole CSS, which would be large and expensive on every request.

## Speed and cost

`effort` controls how much the model thinks before answering and is the main speed / cost knob:
`low` and `medium` are fast and cheap and are usually enough for assembling a page from components, `high` and above
are slower and more expensive. The component library and styleguide are sent with every request but are prompt
cached, so only the first request of a conversation pays for them in full.

## Timeouts

Generating a whole page takes tens of seconds. Each layer must allow more time than the one below it, otherwise the
browser gets a gateway error page instead of a readable error message:

| Layer              | Setting                                        | Value |
|--------------------|------------------------------------------------|-------|
| Anthropic API call | `AnthropicClientFactory::Timeout` (no retries) | 150 s |
| PHP script         | `set_time_limit()` in the API controller       | 180 s |
| PHP-FPM pool       | `request_terminate_timeout`                    | 200 s |
| nginx              | `fastcgi_read_timeout`                         | 240 s |
| Browser            | client timeout in `ai-assistant.js`            | 250 s |

```nginx
location /admin/api/web/ai/ {
    fastcgi_read_timeout 240s;
    fastcgi_send_timeout 240s;
    include fastcgi_params;
    fastcgi_pass php-fpm;
}
```

If a proxy in front of nginx enforces a shorter limit (Cloudflare cuts at 100 s), the request is still not lost,
see below.

## Failsafe

The answer is written to the database at the end of the call and the request finishes even when the browser or a
proxy already gave up (`ignore_user_abort`). When the client loses the response, it polls the conversation history
and picks the answer up as soon as it is stored, so a gateway timeout, a page reload or a closed tab do not waste a
generation. While a generation runs, another request for the same page and user is rejected with HTTP 409, so a
retry after a timeout cannot start a second paid call.

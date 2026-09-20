# Symfony web applications SDK

## Installation

```shell
composer require sovic/cms
```

Add to .env

```shell
####> project ###
PROJECT=
####< project ###
```

Add to service.yaml

```shell
    app.project:
        class: Sovic\Cms\Project\Project
        factory: [ '@Sovic\Cms\Project\ProjectFactory', loadByRequest ]
        arguments: [ '@=service("request_stack").getCurrentRequest()' ]

    app.settings:
        class: Sovic\Cms\Project\Settings
        arguments:
            $cache: '@cache.app'
            $em: '@doctrine.orm.entity_manager'
            $ttl: 3600
            $project: '@app.project'

    _instanceof:
        Sovic\Cms\Controller\ProjectControllerInterface:
            tags: [ 'controller.service_arguments' ]
            calls:
                -   setLocale: [ '@=service("request_stack").getCurrentRequest().getLocale()' ]
                -   setProject: [ '@app.project' ]
                -   setProjectTwig: [ '@twig' ]
                -   assignProjectData: [ ]
                -   setSettings: [ '@app.settings' ]

    Sovic\Cms\Post\PostFactory:
        calls:
            - [ setProject, [ '@app.project' ] ]

    Sovic\Cms\Page\PageFactory:
        calls:
            - [ setProject, [ '@app.project' ] ]
```

Add to routes.yaml

```shell
cms_controllers:
    resource: '../../vendor/sovic/cms/src/Controller'
    type: attribute
```

Add to twig.yaml

```shell
paths:
    '%kernel.project_dir%/vendor/sovic/cms/templates': Cms
```

Publish admin assets

```shell
bin/console assets:install
```

## AI page assistant

The page edit form can show an AI assistant (Anthropic Claude) that prepares page HTML from chat instructions,
using the website's own HTML components and styleguide, so editors without HTML knowledge produce content in the
same visual style as the rest of the website.

### Configuration

Add to `config/packages/sovic_cms.yaml` (the assistant is enabled when `components_dir` is set):

```yaml
sovic_cms:
    ai:
        components_dir: '%kernel.project_dir%/templates/components'
        styleguide_file: '%kernel.project_dir%/templates/components/STYLEGUIDE.md'
        model: 'claude-opus-5'   # default, users can override it in their AI settings
        max_tokens: 16000        # max output tokens of one response
        history_limit: 20        # previous messages sent to the model
```

- `components_dir` contains one `*.html` file per component, the file name is the component name
  (e.g. `hero.html`, `feature-cards.html`, `cta.html`). Each file holds example markup with the real CSS classes
  and placeholder texts. Max 100 files, 64 KB per file, 512 KB in total.
- `styleguide_file` (Markdown or HTML) describes how to use the components: which component to use when, tone of
  voice, allowed classes, heading levels, etc.
- The whole library is sent with every request (prompt cached), keep the snippets short.

### Database

New entities `UserAiSetting`, `AiConversation`, `AiMessage` (tables `user_ai_setting`, `ai_conversation`,
`ai_message`) need a migration in the host application.

### API keys

API keys are per user. Each admin opens *AI asistent* in the user menu (`/admin/ai/settings`) and enters their own
Anthropic API key. Keys are stored encrypted with a key derived from `kernel.secret`; changing the secret makes
stored keys unreadable and users have to enter them again.

### Server

Generating a whole page can take tens of seconds, allow at least 180 s for PHP-FPM `max_execution_time` /
`request_terminate_timeout` and nginx `fastcgi_read_timeout` on `/admin/api/web/ai/` routes.

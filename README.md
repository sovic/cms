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

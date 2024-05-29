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

    _instanceof:
        Sovic\Cms\Controller\ProjectControllerInterface:
            tags: [ 'controller.service_arguments' ]
            calls:
                -   setLocale: [ '@=service("request_stack").getCurrentRequest().getLocale()' ]
                -   setProject: [ '@app.project' ]
                -   setProjectTwig: [ '@twig' ]
                -   assignProjectData: [ ]
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

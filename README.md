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
_instanceof:
    Sovic\Cms\Controller\ProjectControllerInterface:
        tags: [ 'controller.service_arguments' ]
        calls:
            -   initializeProjectController: [ '@Sovic\Cms\Project\ProjectFactory', '@=service("request_stack").getCurrentRequest()', '@twig' ] ]
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

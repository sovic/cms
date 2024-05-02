# Symfony web applications SDK

## Installation

```shell
composer require sovic/symfony-cms
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
            -   initializeProjectController: [ '@Sovic\Cms\Project\ProjectFactory', '@=service("request_stack").getCurrentRequest()' ] ]
```

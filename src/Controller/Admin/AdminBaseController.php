<?php

namespace Sovic\Cms\Controller\Admin;

use Sovic\Cms\Controller\BaseController;
use Sovic\Common\Controller\Trait\BaseControllerTrait;
use Symfony\Component\HttpFoundation\Response;

class AdminBaseController extends BaseController
{
    use BaseControllerTrait;

    protected function render(string $view, array $parameters = [], Response $response = null): Response
    {
        $user = $this->getUser();
        $parameters = $this->getRenderParameters($parameters);

        // ui preferences
        $themeMode = 'light';
        $sidebar = 'open';
        if ($user !== null) {
            // access cookie only if the user is logged in
            $themeMode = $_COOKIE['theme'] ?? 'light';
            if (!in_array($themeMode, ['light', 'dark', 'system'])) {
                $themeMode = 'light';
            }
            $sidebar = $_COOKIE['sidebar'] ?? 'open';
            if (!in_array($sidebar, ['open', 'closed'])) {
                $sidebar = 'open';
            }
        }

        $parameters['sidebar'] = $sidebar;
        $parameters['theme_mode'] = $themeMode;

        // user bundle
        $parameters['is_authorized'] = $user !== null;
        $parameters['auth_user'] = $user;

        // routing
        /** @noinspection PhpUnhandledExceptionInspection */
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $parameters['current_route'] = $request?->attributes->get('_route');

        // misc
        $parameters['local_debug'] = !empty($_ENV['APP_LOCAL_DEBUG']);

        return parent::render($view, $parameters, $response);
    }
}

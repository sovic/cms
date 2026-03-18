<?php

namespace Sovic\Cms\Controller\Admin;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AdminBaseController
{
    #[Route(
        '/admin/dashboard',
        name: 'admin:dashboard',
    )]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('@CmsBundle/admin/dashboard/index.html.twig');
    }
}

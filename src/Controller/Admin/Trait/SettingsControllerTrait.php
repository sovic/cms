<?php

namespace Sovic\Cms\Controller\Admin\Trait;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Trait\ControllerAccessTrait;
use Sovic\Cms\Form\Admin\Settings;
use Sovic\Common\Entity\Setting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

trait SettingsControllerTrait
{
    use ControllerAccessTrait;

    protected function isAttributeGranted(string $attribute): bool
    {
        if (in_array($attribute, ['admin:settings:list', 'admin:settings:edit'])) {
            return $this->isGranted('ROLE_ADMIN');
        }

        return false;
    }

    #[Route(
        '/admin/settings/list',
        name: 'admin:settings:list',
        methods: ['GET'],
        priority: 1,
    )]
    public function list(
        EntityManagerInterface $em,
    ): Response {
        $this->getRouteAccessDecision('admin:settings:list');

        $settings = $em
            ->getRepository(Setting::class)
            ->findBy(
                [],
                ['groupId' => 'ASC', 'description' => 'ASC']
            );

        $this->assign('settings', $settings);

        return $this->render('@CmsBundle/admin/settings/list.html.twig');
    }

    #[Route(
        '/admin/settings/edit/{id}',
        name: 'admin:settings:edit',
        methods: ['GET', 'POST'],
        priority: 1,
    )]
    public function edit(
        int                    $id,
        EntityManagerInterface $em,
        Request                $request,
    ): Response {
        $this->getRouteAccessDecision('admin:settings:list');

        $settings = $em
            ->getRepository(Setting::class)
            ->find($id);

        if (!$settings) {
            return $this->render404();
        }

        $form = $this->createForm(Settings::class, $settings);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->persist($settings);
                $em->flush();

                try {
                    $this->addFlash('success', 'Nastavení uloženo.');
                } catch (Throwable) {
                }

                return $this->redirectToRoute('admin:settings:edit', ['id' => $settings->getId()]);
            }

            try {
                $this->addFlash('error', 'Formulář obsahuje chyby, opravte je prosím a odešlete znovu.');
            } catch (Throwable) {
            }
        }

        $this->assign('form', $form->createView());
        $this->assign('settings', $settings);

        return $this->render('@CmsBundle/admin/settings/edit.html.twig');
    }
}

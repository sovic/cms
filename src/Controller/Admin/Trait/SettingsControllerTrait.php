<?php

namespace Sovic\Cms\Controller\Admin\Trait;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Form\Admin\Settings;
use Sovic\Common\Entity\Setting;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

trait SettingsControllerTrait
{
    protected function isAttributeGranted(string $attribute): bool
    {
        if (in_array($attribute, ['admin:settings:list', 'admin:settings:edit'])) {
            return $this->isGranted('ROLE_ADMIN');
        }

        return false;
    }

    #[Route(
        '/settings/list',
        name: 'admin:settings:list',
        methods: ['GET'],
        priority: 1,
    )]
    public function list(
        EntityManagerInterface $em,
    ): Response {
        $this->getEmailAccessDecision('admin:email:list');

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
        '/settings/edit/{id}',
        name: 'admin:settings:edit',
        methods: ['GET', 'POST'],
        priority: 1,
    )]
    public function edit(
        int                    $id,
        EntityManagerInterface $em,
        Request                $request,
    ): Response {
        $this->getEmailAccessDecision('admin:email:list');

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

                $this->addFlash('success', 'Nastavení uloženo.');

                return $this->redirectToRoute('admin:settings:edit', ['id' => $settings->getId()]);
            }

            $this->addFlash('error', 'Formulář obsahuje chyby, opravte je prosím a odešlete znovu.');
        }

        $this->assign('form', $form->createView());
        $this->assign('settings', $settings);

        return $this->render('@CmsBundle/admin/settings/edit.html.twig');
    }
}

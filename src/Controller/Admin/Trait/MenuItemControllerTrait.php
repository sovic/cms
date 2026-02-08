<?php

namespace Sovic\Cms\Controller\Admin\Trait;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\MenuItem;
use Sovic\Cms\Entity\Page;
use Sovic\Cms\Repository\MenuItemRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

trait MenuItemControllerTrait
{
    #[Route(
        '/admin/menu/list',
        name: 'admin:menu:list',
    )]
    public function menuList(
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var MenuItemRepository $repo */
        $repo = $em->getRepository(MenuItem::class);
        $items = $repo->findAllOrdered();
        $tree = $repo->buildTree($items);

        $this->assign('tree', $tree);

        return $this->render('@CmsBundle/admin/menu/list.html.twig');
    }

    #[Route(
        '/admin/menu/edit/{id}',
        name: 'admin:menu:edit',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0],
    )]
    public function menuEdit(
        ?int                   $id,
        EntityManagerInterface $em,
        Request                $request,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $repo = $em->getRepository(MenuItem::class);
        $menuItem = $repo->find($id);

        if ($menuItem === null) {
            $menuItem = new MenuItem();
        }

        // pages for select
        $pages = $em->getRepository(Page::class)->findBy([], ['name' => 'ASC']);

        // parent choices (exclude current item)
        $allItems = $repo->findAllOrdered();
        $parentChoices = [];
        foreach ($allItems as $item) {
            if ($id > 0 && $item->getId() === $id) {
                continue;
            }
            $parentChoices[$item->getName()] = $item->getId();
        }

        $form = $this->createForm(\Sovic\Cms\Form\Admin\MenuItem::class, $menuItem, [
            'pages' => $pages,
            'parent_choices' => $parentChoices,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->persist($menuItem);
                $em->flush();

                return $this->redirectToRoute('admin:menu:edit', ['id' => $menuItem->getId()]);
            }

            $this->addFlash('error', 'Formulář obsahuje chyby, opravte je prosím a odešlete znovu.');
        }

        $this->assign('menu_item', $menuItem);
        $this->assign('form', $form->createView());

        return $this->render('@CmsBundle/admin/menu/edit.html.twig');
    }
}

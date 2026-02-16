<?php

namespace Sovic\Cms\Controller\Admin\Trait;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Entity\MenuItem;
use Sovic\Cms\Entity\Page;
use Sovic\Cms\Menu\MenuCacheableData;
use Sovic\Cms\Repository\MenuItemRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\CacheInterface;

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
        $items = $repo->findRootWithPosition();

        $this->assign('items', $items);

        return $this->render('@CmsBundle/admin/menu/list.html.twig');
    }

    #[Route(
        '/admin/menu/list/{position}',
        name: 'admin:menu:list:position',
    )]
    public function menuListByPosition(
        string                 $position,
        EntityManagerInterface $em,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var MenuItemRepository $repo */
        $repo = $em->getRepository(MenuItem::class);
        $allItems = $repo->findAllOrdered();
        $tree = $repo->buildTreeForPosition($allItems, $position);

        $this->assign('position', $position);
        $this->assign('tree', $tree);

        return $this->render('@CmsBundle/admin/menu/list-position.html.twig');
    }

    #[Route(
        '/admin/menu/edit/{id}',
        name: 'admin:menu:edit',
        requirements: ['id' => '\d+'],
        defaults: ['id' => 0],
    )]
    public function menuEdit(
        ?int                   $id,
        CacheInterface         $cache,
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

        // parent choices (exclude current item), structured as tree
        $allItems = $repo->findAllOrdered();
        $tree = $repo->buildTree($allItems);
        $parentChoices = [];
        $buildChoices = static function (array $nodes, int $depth) use (&$buildChoices, &$parentChoices, $id): void {
            foreach ($nodes as $node) {
                $item = $node['item'];
                $prefix = $depth > 0 ? str_repeat('– ', $depth) . ' ' : '';
                $key = $prefix . $item->getName();
                if ($item->getId() !== $id) {
                    if (isset($parentChoices[$key])) {
                        $parentChoices[$key . ' (ID ' . $item->getId() . ')'] = $item->getId();
                    } else {
                        $parentChoices[$key] = $item->getId();
                    }
                }
                $buildChoices($node['children'], $depth + 1);
            }
        };
        $buildChoices($tree, 0);

        $form = $this->createForm(\Sovic\Cms\Form\Admin\MenuItem::class, $menuItem, [
            'pages' => $pages,
            'parent_choices' => $parentChoices,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            /** @noinspection NestedPositiveIfStatementsInspection */
            if ($form->isValid()) {
                $em->persist($menuItem);
                $em->flush();

                (new MenuCacheableData($cache, $em))->warmUp();

                return $this->redirectToRoute('admin:menu:edit', ['id' => $menuItem->getId()]);
            }

            // $this->addFlash('error', 'Formulář obsahuje chyby, opravte je prosím a odešlete znovu.');
        }

        $this->assign('menu_item', $menuItem);
        $this->assign('form', $form->createView());

        return $this->render('@CmsBundle/admin/menu/edit.html.twig');
    }
}

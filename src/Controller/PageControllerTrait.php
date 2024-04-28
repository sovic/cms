<?php

namespace Sovic\Cms\Controller;

use RuntimeException;
use Sovic\Cms\Page\Page;
use Sovic\Cms\Page\PageFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

trait PageControllerTrait
{
    private ?string $galleryBaseUrl = null;
    protected ?Page $page = null;

    public function setGalleryBaseUrl(?string $galleryBaseUrl): void
    {
        $this->galleryBaseUrl = $galleryBaseUrl;
    }

    /**
     * # keep priority low, only if no other route found
     *
     * @Route(
     *     "/{urlId}",
     *     name="page_show",
     *     requirements={"urlId"="[a-zA-Z0-9\-]+"},
     *     priority="-10"
     * )
     */
    public function show(string $urlId, PageFactory $pageFactory): Response
    {
        $this->loadPage($pageFactory, $urlId);
        if (null === $this->page) {
            return $this->show404();
        }

        return $this->render('page/show.html.twig');
    }

    protected function loadPage(PageFactory $pageFactory, string $urlId): void
    {
        $this->page = $pageFactory->loadByUrlId($urlId);
        if (null === $this->page) {
            return;
        }

        $this->assign('page', $this->page);
        $this->assignArray($this->page->toArray());
        $this->assign('show_toc', $this->page->getEntity()->hasToc());

        if (!isset($this->variables['heading'])) {
            $this->assign('heading', $this->page->getHeading());
        }
        if (!isset($this->variables['side_menu_id'])) {
            $this->variables['side_menu_id'] = $this->page->getEntity()->getSideMenuId();
        }
    }

    protected function loadGallery(string $galleryName = 'page'): void
    {
        if (null === $this->page) {
            throw new RuntimeException('page not loaded');
        }

        // galleries
        $galleryManager = $this->page->getGalleryManager();
        $gallery = $galleryManager->loadGallery($galleryName);
        $resultSet = $gallery->getItemsResultSet();
        if ($this->galleryBaseUrl) {
            $resultSet->setBaseUrl($this->galleryBaseUrl);
        }

        $this->assign('gallery_' . $galleryName, $resultSet->toArray());
    }
}

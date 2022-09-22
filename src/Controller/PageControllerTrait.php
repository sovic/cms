<?php

namespace SovicCms\Controller;

use RuntimeException;
use SovicCms\Page\Page;
use SovicCms\Page\PageFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

trait PageControllerTrait
{
    private ?string $mediaBaseUrl = null;
    protected ?Page $page = null;

    public function setMediaBaseUrl(?string $mediaBaseUrl): void
    {
        $this->mediaBaseUrl = $mediaBaseUrl;
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
     *
     * @param string $urlId
     * @param PageFactory $pageFactory
     * @return Response
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

        $galleryManager = $this->page->getGalleryManager();
        if (null !== $this->mediaBaseUrl) {
            $galleryManager->setBaseUrl($this->mediaBaseUrl);
        }
        $media = $galleryManager->getGallery($galleryName);

        $this->assign('media_' . $galleryName, $media);
    }
}

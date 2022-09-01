<?php

namespace SovicCms\Controller;

use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use SovicCms\Entity\Page;
use SovicCms\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Routing\Annotation\Route;

abstract class SitemapController extends AbstractController
{
    private array $urls = [];

    /**
     * @Route("/sitemap", name="sitemap")
     * @Route("/sitemap.xml", name="sitemap_xml")
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param UrlHelper $urlHelper
     * @return Response
     */
    public function index(EntityManagerInterface $entityManager, Request $request, UrlHelper $urlHelper): Response
    {
        $hostname = $request->getSchemeAndHttpHost();
        $baseUrl = $urlHelper->getAbsoluteUrl('/');
        $this->addUrl($baseUrl);

        /** @var PageRepository $pagesRepo */
        $pagesRepo = $entityManager->getRepository(Page::class);
        $pages = $pagesRepo->findPublic();
        foreach ($pages as $page) {
            $this->addUrl($baseUrl . $page->getUrlId(), $page->getLastUpdateDate());
        }

        $this->addSitemapUrls();

        // return response in XML format
        $response = new Response(
            $this->renderView('@SovicCms/sitemap/sitemap.html.twig', [
                'urls' => $this->urls,
                'hostname' => $hostname,
            ]),
            200
        );
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    protected function addUrl(string $url, ?DateTimeInterface $lastModified = null): void
    {
        $item = [
            'loc' => $url,
        ];
        if (null !== $lastModified) {
            $item['lastmod'] = date('c', $lastModified->getTimestamp());
        }

        $this->urls[] = $item;
    }

    // override to add custom urls
    abstract protected function addSitemapUrls();
}

<?php

namespace Sovic\Cms\Controller;

use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Controller\Trait\ProjectControllerTrait;
use Sovic\Cms\Entity\Page;
use Sovic\Cms\Entity\Post;
use Sovic\Cms\Repository\PageRepository;
use Sovic\Cms\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class SitemapController extends BaseController implements ProjectControllerInterface
{
    use ProjectControllerTrait;

    private array $urls = [];
    protected bool $addPages = true;
    protected bool $addPosts = true;
    protected string $postsRoute = 'posts_detail';

    #[Route('/sitemap', name: 'sitemap_index')]
    #[Route('/sitemap.xml', name: 'sitemap_xml')]
    public function sitemap(
        EntityManagerInterface $em,
        Request                $request,
        RouterInterface        $router,
        UrlHelper              $urlHelper
    ): Response {
        $hostname = $request->getSchemeAndHttpHost();
        $baseUrl = $urlHelper->getAbsoluteUrl('/');

        if ($this->addPages) {
            /** @var PageRepository $repo */
            $repo = $em->getRepository(Page::class);
            $pages = $repo->findPublic($this->project);
            foreach ($pages as $page) {
                if (!$page->isInSitemap()) {
                    continue;
                }
                $this->addUrl($baseUrl . $page->getUrlId(), $page->getLastUpdateDate());
            }
        }

        if ($this->addPosts) {
            /** @var PostRepository $repo */
            $repo = $em->getRepository(Post::class);
            $posts = $repo->findPublic($this->project);
            foreach ($posts as $post) {
                $url = $router->generate($this->postsRoute, ['urlId' => $post->getUrlId()]);
                $this->addUrl($urlHelper->getAbsoluteUrl($url), $post->getLastModifiedDate());
            }
        }

        $this->addSitemapUrls();

        // return response in XML format
        $response = new Response(
            $this->renderView('@Cms/sitemap/sitemap.html.twig', [
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
    protected function addSitemapUrls(): void
    {
    }
}

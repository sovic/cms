<?php

/** @noinspection SqlResolve */

namespace Sovic\Cms\Command;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sovic\Cms\Entity\Post;
use Sovic\Cms\Entity\Project;
use Sovic\Cms\Entity\Tag;
use Sovic\Cms\Post\PostFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to migrate posts from old database to new one.
 * requires default and import entity managers to be defined in doctrine.yaml
 *
 * TODO need more work
 */
#[AsCommand(name: 'migrate:posts')]
class MigratePostsCommand extends Command
{
    public function __construct(
        private readonly ManagerRegistry $registry,
        private readonly PostFactory     $postFactory,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var EntityManagerInterface $importEm */
        $importEm = $this->registry->getManager('import');
        /** @var EntityManagerInterface $em */
        $em = $this->registry->getManager('default');
        $project = $em->getRepository(Project::class)->findOneBy(['slug' => 'jana-cernochova']);
        if (!$project) {
            return Command::FAILURE;
        }

        $this->importPosts($importEm, $em, $project);
        $this->importTags($importEm, $em, $project);

        return Command::SUCCESS;
    }

    /**
     * @throws Exception
     */
    private function importPosts(EntityManagerInterface $importEm, EntityManagerInterface $em, Project $project): void
    {
        $batch = 100;
        $lastId = 0;
        do {
            $sql = "
                SELECT * FROM posts
                WHERE id > $lastId
                LIMIT $batch
            ";
            $stmt = $importEm->getConnection()->prepare($sql);
            $result = $stmt->executeQuery()->fetchAllAssociative();

            $repo = $em->getRepository(Post::class);
            foreach ($result as $item) {
                $lastId = $item['id'];
                $post = $repo->findOneBy(['importService' => 'janacernochova', 'importId' => $item['id']]);
                if ($post) {
                    $this->setDateTime($post, $item);
                    $post->setPerex($item['perex']);
                    $post->setContent($item['content']);
                    $em->persist($post);

                    continue;
                }
                $post = new Post();
                $post->setProject($project);
                $post->setImportService('janacernochova');
                $post->setImportId($item['id']);
                $post->setName($item['name']);
                $post->setUrlId($item['raw_id']);
                if (!empty($item['head_title'])) {
                    $post->setMetaTitle($item['head_title']);
                }
                if (!empty($item['meta_description'])) {
                    $post->setMetaDescription($item['meta_description']);
                }
                if (!empty($item['meta_keywords'])) {
                    $post->setMetaKeywords($item['meta_keywords']);
                }
                $post->setHeading($item['heading']);
                if (!empty($item['subtitle'])) {
                    $post->setSubtitle($item['subtitle']);
                }
                $post->setPerex($item['perex']);
                $post->setContent($item['content']);
                $post->setLang($item['lang']);
                $post->setPublic((bool) $item['public']);
                $this->setDateTime($post, $item);

                $em->persist($post);
            }
            $em->flush();
        } while (count($result) > 0);
    }

    /**
     * @throws Exception
     */
    private function importTags(EntityManagerInterface $importEm, EntityManagerInterface $em, Project $project): void
    {
        $sql = "
            SELECT * FROM tags
        ";
        $stmt = $importEm->getConnection()->prepare($sql);
        $result = $stmt->executeQuery()->fetchAllAssociative();
        $tagRepo = $em->getRepository(Tag::class);
        foreach ($result as $item) {
            $tag = $tagRepo->findOneBy(['project' => $project, 'name' => $item['name']]);
            if ($tag) {
                continue;
            }
            $tag = new Tag();
            $tag->setProject($project);
            $tag->setName($item['name']);
            $tag->setUrlId($item['raw_id']);
            $tag->setPublic((bool) $item['public']);
            $em->persist($tag);
        }

        $sql = "
            SELECT * FROM tags_posts
            LEFT JOIN tags ON tags.id = tags_posts.tags_id
        ";
        $stmt = $importEm->getConnection()->prepare($sql);
        $result = $stmt->executeQuery()->fetchAllAssociative();
        $repo = $em->getRepository(Post::class);
        foreach ($result as $item) {
            $post = $repo->findOneBy(['importService' => 'janacernochova', 'importId' => $item['posts_id']]);
            if (!$post) {
                continue;
            }
            $postModel = $this->postFactory->loadByEntity($post);
            if (!$postModel) {
                continue;
            }
            $postModel->addTag($tagRepo->findOneBy(['project' => $project, 'name' => $item['name']]));
            $em->persist($post);
        }
        $em->flush();
    }

    private function setDateTime(Post $post, array $item): void
    {
        if (!empty($item['published'])) {
            $date = (new DateTime())
                ->setTimestamp($item['published'])
                ->setTimezone(new DateTimeZone('Europe/Prague'));
            $immutable = DateTimeImmutable::createFromMutable($date);
            $post->setCreated($immutable);
            $post->setCreateDate($immutable);
            $post->setPublishDate($immutable);
        }
    }
}

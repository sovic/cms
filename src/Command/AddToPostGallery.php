<?php

namespace Sovic\Cms\Command;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Command\Trait\GalleryCommandTrait;
use Sovic\Cms\Command\Trait\PostCommandTrait;
use Sovic\Cms\Entity\Post;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Post\PostSearchRequest;
use Sovic\Cms\Repository\PostRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'post:add-to-gallery', description: 'Add items to post gallery')]
class AddToPostGallery extends Command
{
    use GalleryCommandTrait;
    use PostCommandTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PostFactory            $postFactory,
        private readonly FilesystemOperator     $galleryStorage
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->entityManager;
        /** @var PostRepository $repo */
        $repo = $em->getRepository(Post::class);
        $search = new PostSearchRequest();
        $search->includePrivate = true;
        $latestPosts = $repo->findByRequest($search, 10, 0);

        $output->writeln('Latest published posts:');
        foreach ($latestPosts as $post) {
            $output->writeln("{$post->getId()} {$post->getName()}");
        }
        $output->writeln('');

        $post = $this->loadPostById($input, $output);
        if (!$post) {
            return Command::FAILURE;
        }

        $this->addToGallery($input, $output, $post, 'post');

        return Command::SUCCESS;
    }
}

<?php

namespace Sovic\Cms\Command;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Command\Trait\GalleryCommandTrait;
use Sovic\Cms\Command\Trait\PostCommandTrait;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Tag\TagFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'post:add-tag', description: 'Add tag to post')]
class AddPostTag extends Command
{
    use GalleryCommandTrait;
    use PostCommandTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PostFactory            $postFactory,
        private readonly FilesystemOperator     $galleryStorage,
        private readonly TagFactory             $tagFactory,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $post = $this->loadPostById($input, $output);
        if (!$post) {
            return Command::FAILURE;
        }

        $tagName = $input->getArgument('tag');
        if (empty($tagName)) {
            $output->writeln('<error>Tag name cannot be empty.</error>');

            return Command::FAILURE;
        }


        $tag = $this->tagFactory->loadByName($tagName);
        if (!$tag) {
            $output->writeln("<error>Tag '$tagName' not found.</error>");

            return Command::FAILURE;
        }

        $post->addTag($tag->getEntity());

        return Command::SUCCESS;
    }
}

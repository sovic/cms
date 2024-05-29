<?php

namespace Sovic\Cms\Command;

use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Command\Trait\GalleryCommandTrait;
use Sovic\Cms\Command\Trait\PostCommandTrait;
use Sovic\Cms\Post\PostFactory;
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
        private readonly PostFactory        $postFactory,
        private readonly FilesystemOperator $galleryStorage
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $post = $this->loadPostById($input, $output);
        if (!$post) {
            return Command::FAILURE;
        }

        $this->addToGallery($input, $output, $post);

        return Command::SUCCESS;
    }
}

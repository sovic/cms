<?php

namespace Sovic\Cms\Command;

use ImagickException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Command\Trait\GalleryCommandTrait;
use Sovic\Cms\Command\Trait\PageCommandTrait;
use Sovic\Cms\Page\PageFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'page:add-to-gallery', description: 'Add items to page gallery')]
class AddToPageGallery extends Command
{
    use GalleryCommandTrait;
    use PageCommandTrait;

    public function __construct(
        private readonly PageFactory        $pageFactory,
        private readonly FilesystemOperator $galleryStorage
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $page = $this->loadPageById($input, $output);
        if (!$page) {
            return Command::FAILURE;
        }

        $this->addToGallery($input, $output, $page);

        return Command::SUCCESS;
    }
}

<?php

namespace Sovic\Cms\Command;

use InvalidArgumentException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Sovic\Gallery\Gallery\GalleryFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'gallery:delete-item')]
class DeleteGalleryItemCommand extends Command
{
    public function __construct(
        private readonly GalleryFactory     $galleryFactory,
        private readonly FilesystemOperator $galleryStorage
    ) {
        parent::__construct();
    }

    /**
     * @throws FilesystemException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $question = new Question('Gallery item ID: ');
        $id = $helper->ask($input, $output, $question);
        if (empty($id)) {
            return Command::FAILURE;
        }

        $gallery = $this->galleryFactory->loadByGalleryItemId($id);
        if (!$gallery) {
            throw new InvalidArgumentException('Gallery not found');
        }

        $gallery->setFilesystemOperator($this->galleryStorage);
        $gallery->deleteItem($id);

        $output->writeln('Gallery item deleted with ID: ' . $id);

        return Command::SUCCESS;
    }
}

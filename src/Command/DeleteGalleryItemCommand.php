<?php

namespace Sovic\Cms\Command;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Sovic\Gallery\Gallery\GalleryFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
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
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question('Gallery item ID (or multiple IDs separate by spaces): ');
        $id = $helper->ask($input, $output, $question);
        if (empty($id)) {
            return Command::FAILURE;
        }

        $ids = explode(' ', $id);
        foreach ($ids as $id) {
            $gallery = $this->galleryFactory->loadByGalleryItemId($id);
            if (!$gallery) {
                $output->writeln('Gallery item with ID: ' . $id . ' not found');
                continue;
            }

            $gallery->setFilesystemOperator($this->galleryStorage);
            $gallery->deleteItem($id);

            $output->writeln('Gallery item deleted with ID: ' . $id);
        }


        return Command::SUCCESS;
    }
}

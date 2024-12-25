<?php

namespace Sovic\Cms\Command;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Sovic\Gallery\Gallery\GalleryFactory;
use Sovic\Gallery\Migration\StorageMigration;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'galleries:migrate-storage')]
class MigrateGalleriesStorageCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GalleryFactory         $galleryFactory,
        private readonly FilesystemOperator     $galleryStorage
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
        $question = new Question('Gallery ID: ');
        $id = $helper->ask($input, $output, $question);
        $gallery = null;
        if (!empty($id)) {
            $gallery = $this->galleryFactory->loadById($id);
        }

        $pathMigration = new StorageMigration($this->entityManager, $this->galleryStorage);
        $pathMigration->migrate([
            'gallery' => $gallery,
            'variants' => ['full', 'thumb', 'small', 'hp', 'big', 'cms', 'post', 'cms_block'],
            'limit' => 10,
        ]);

        $output->writeln('Gallery items moved');

        return Command::SUCCESS;
    }
}

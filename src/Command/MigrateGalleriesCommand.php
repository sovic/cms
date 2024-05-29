<?php

namespace Sovic\Cms\Command;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Sovic\Gallery\Migration\GalleryMigration;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'galleries:migrate')]
class MigrateGalleriesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $galleryMigration = new GalleryMigration($this->entityManager);
        $galleryMigration->migrate();

        $output->writeln('Galleries migrated');

        return Command::SUCCESS;
    }
}

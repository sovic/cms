<?php

namespace Sovic\Cms\Command;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Command\Trait\PostCommandTrait;
use Sovic\Cms\Post\PostFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'post:delete-gallery')]
class DeletePostGalleryCommand extends Command
{
    use PostCommandTrait;

    public function __construct(
        private readonly PostFactory        $postFactory,
        private readonly FilesystemOperator $galleryStorage
    ) {
        parent::__construct();
    }

    /**
     * @throws FilesystemException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $post = $this->loadPostById($input, $output);
        if (!$post) {
            return Command::FAILURE;
        }

        $helper = $this->getHelper('question');
        $question = new Question('Delete which post gallery? (default: post)', 'post');
        $galleryName = $helper->ask($input, $output, $question);

        $post->getGalleryManager()->setFilesystemOperator($this->galleryStorage);
        $post->getGalleryManager()->getGallery($galleryName)?->delete();

        $output->writeln('Post gallery deleted');

        return Command::SUCCESS;
    }
}

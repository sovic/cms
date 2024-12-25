<?php

namespace Sovic\Cms\Command;

use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Command\Trait\PostCommandTrait;
use Sovic\Cms\Post\PostFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

#[AsCommand(name: 'post:delete')]
class DeletePostCommand extends Command
{
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

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('Delete post? (y/n)', true);
        if (!$helper->ask($input, $output, $question)) {
            return Command::FAILURE;
        }

        $id = $post->getId();
        $post->getGalleryManager()->setFilesystemOperator($this->galleryStorage);
        $post->delete();
        $output->writeln('Post deleted with ID: ' . $id);

        return Command::SUCCESS;
    }
}

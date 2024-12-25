<?php

namespace Sovic\Cms\Command;

use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Sovic\Cms\Command\Trait\PostCommandTrait;
use Sovic\Cms\Post\PostFactory;
use Sovic\Gallery\Entity\GalleryItem;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'post:set-cover', description: 'Set post cover')]
class SetPostCoverCommand extends Command
{
    use PostCommandTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PostFactory            $postFactory,
        private readonly FilesystemOperator     $galleryStorage
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
        $question = new Question('Gallery item ID: ');
        $id = $helper->ask($input, $output, $question);
        if (empty($id)) {
            return Command::FAILURE;
        }

        $item = $this->em->getRepository(GalleryItem::class)->find($id);
        if (!$item) {
            return Command::FAILURE;
        }
        $post->getGallery()->setCoverImage($item);

        $output->writeln('Post cover set to ID: ' . $id);

        return Command::SUCCESS;
    }
}

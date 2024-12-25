<?php

namespace Sovic\Cms\Command;

use ImagickException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Random\RandomException;
use RuntimeException;
use Sovic\Cms\Entity\Post;
use Sovic\Cms\Post\PostFactory;
use Sovic\Cms\Project\ProjectFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

#[AsCommand(name: 'post:create')]
class CreatePostCommand extends Command
{
    public function __construct(
        private readonly PostFactory        $postFactory,
        private readonly ProjectFactory     $projectFactory,
        private readonly FilesystemOperator $galleryStorage
    ) {
        parent::__construct();
    }

    /**
     * @throws RandomException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $question = new Question('Project slug: ');
        $slug = $helper->ask($input, $output, $question);
        if (empty($slug)) {
            return Command::FAILURE;
        }
        $project = $this->projectFactory->loadBySlug($slug);
        if (!$project) {
            $output->writeln('Project not found');

            return Command::FAILURE;
        }

        $question = new Question('Post name: ');
        $name = $helper->ask($input, $output, $question);
        if (empty($name)) {
            return Command::FAILURE;
        }

        $entity = new Post();
        $entity->setProject($project->entity);
        $entity->setName($name);

        $question = new Question('Post heading (default: ' . $name . '): ', $name);
        $heading = $helper->ask($input, $output, $question);
        $entity->setHeading($heading);

        $question = new Question('Perex: ', null);
        $perex = $helper->ask($input, $output, $question);
        $entity->setPerex($perex);

        $question = new Question('Content: ', null);
        $content = $helper->ask($input, $output, $question);
        $entity->setContent($content);

        $entity->setSecret(substr(md5(random_bytes(10)), 0, 10));

        $post = $this->postFactory->loadByEntity($entity);
        if (!$post) {
            throw new RuntimeException('Unable to create post');
        }
        $post->save();

        $gm = $post->getGalleryManager();
        $gm->setFilesystemOperator($this->galleryStorage);

        $question = new Question('Gallery items path: ', null);
        $galleryPath = $helper->ask($input, $output, $question);

        if ($galleryPath !== null && is_dir($galleryPath)) {
            $gallery = $gm->createGallery();
            try {
                $gallery->uploadPath($galleryPath);
                $gallery->setDefaultCoverImage();
            } catch (FilesystemException|ImagickException $e) {
                echo $e->getMessage();
            }
        }

        $question = new ConfirmationQuestion('Publish post? (y/n)', true);
        if ($helper->ask($input, $output, $question)) {
            $post->save(true);
        }

        $output->writeln('Post created with ID: ' . $post->getId());
        $url = '/posts/' . $entity->getUrlId();
        $output->writeln('Post URL: ' . $url);
        $output->writeln('Post URL with download: ' . $url . '?secret=' . $entity->getSecret());

        return Command::SUCCESS;
    }
}

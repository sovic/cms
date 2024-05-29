<?php

namespace Sovic\Cms\Command\Trait;

use Sovic\Cms\Post\Post;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

trait PostCommandTrait
{
    private function loadPostById(InputInterface $input, OutputInterface $output): ?Post
    {
        $helper = $this->getHelper('question');
        $question = new Question('Post ID: ');
        $id = $helper->ask($input, $output, $question);
        if (empty($id)) {
            return null;
        }
        $post = $this->postFactory->loadById($id);
        if (!$post) {
            $output->writeln('Post not found with ID: ' . $id);

            return null;
        }

        return $post;
    }
}

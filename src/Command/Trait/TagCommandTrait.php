<?php

namespace Sovic\Cms\Command\Trait;

use Sovic\Cms\Tag\Tag;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

trait TagCommandTrait
{
    private function loadTagById(InputInterface $input, OutputInterface $output): ?Tag
    {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $question = new Question('Tag ID: ');
        $id = $helper->ask($input, $output, $question);
        if (empty($id)) {
            return null;
        }
        $tag = $this->tagFactory->loadById($id);
        if (!$tag) {
            $output->writeln('Post not found with ID: ' . $id);

            return null;
        }

        return $tag;
    }
}

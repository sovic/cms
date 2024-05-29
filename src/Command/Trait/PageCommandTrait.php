<?php

namespace Sovic\Cms\Command\Trait;

use Sovic\Cms\Page\Page;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

trait PageCommandTrait
{
    private function loadPageById(InputInterface $input, OutputInterface $output): ?Page
    {
        $helper = $this->getHelper('question');
        $question = new Question('Page ID: ');
        $id = $helper->ask($input, $output, $question);
        if (empty($id)) {
            return null;
        }
        $page = $this->pageFactory->loadById($id);
        if (!$page) {
            $output->writeln('Page not found with ID: ' . $id);

            return null;
        }

        return $page;
    }
}

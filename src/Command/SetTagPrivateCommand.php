<?php

namespace Sovic\Cms\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Command\Trait\TagCommandTrait;
use Sovic\Cms\Project\ProjectFactory;
use Sovic\Cms\Tag\TagFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(name: 'tag:set-private', description: 'Set tag private + slug')]
class SetTagPrivateCommand extends Command
{
    use TagCommandTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ProjectFactory         $projectFactory,
        private readonly RouterInterface        $router,
        private readonly TagFactory             $tagFactory,

    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $tag = $this->loadTagById($input, $output);
        if (!$tag) {
            return Command::FAILURE;
        }

        if ($tag->entity->getPrivateSlug() !== null) {
            $output->writeln('Tag already has a private slug');

            return Command::FAILURE;
        }

        $slug = $tag->generateUniqueSlug(32);
        $tag->entity->setPrivateSlug($slug);
        $tag->entity->setIsPublic(0);
        $tag->flush();

        $project = $this->projectFactory->loadByEntity($tag->entity->getProject());
        $domains = $project->entity->getDomains();
        $mainDomain = explode("\n", $domains)[0] ?? null;

        $url = $this->router->generate('posts_tag', ['tagName' => $slug]);
        if ($mainDomain) {
            $url = $mainDomain . $url;
        }

        $output->writeln('Tag private url: ' . $url);

        return Command::SUCCESS;
    }
}

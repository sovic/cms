<?php

namespace Sovic\Cms\Command;

use Doctrine\ORM\EntityManagerInterface;
use Sovic\Cms\Command\Trait\PostCommandTrait;
use Sovic\Cms\Post\PostFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

#[AsCommand(name: 'post:set-private', description: 'Set post private + slug')]
class SetPostPrivateCommand extends Command
{
    use PostCommandTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PostFactory            $postFactory,
        private readonly RouterInterface        $router
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $post = $this->loadPostById($input, $output);
        if (!$post) {
            return Command::FAILURE;
        }

        $slug = $post->generateUniqueSlug(32);
        $post->entity->setPrivateSlug($slug);
        $post->entity->setPublic(false);
        $post->save();

        $url = $this->router->generate(
            'posts_detail',
            ['urlId' => $slug],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $output->writeln('Post private url: ' . $url);

        return Command::SUCCESS;
    }
}

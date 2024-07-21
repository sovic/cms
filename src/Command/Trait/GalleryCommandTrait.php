<?php

namespace Sovic\Cms\Command\Trait;

use ImagickException;
use League\Flysystem\FilesystemException;
use Sovic\Gallery\Entity\GalleryModelInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

trait GalleryCommandTrait
{
    private function addToGallery(
        InputInterface        $input,
        OutputInterface       $output,
        GalleryModelInterface $model,
        ?string               $defaultGalleryName = null,
    ): void {
        $gm = $model->getGalleryManager();
        $gm->setFilesystemOperator($this->galleryStorage);

        $helper = $this->getHelper('question');
        $question = new Question('Gallery items path: ');
        $galleryPath = $helper->ask($input, $output, $question);

        if ($galleryPath !== null) {
            $question = new Question('Gallery name [default=' . $defaultGalleryName . ']: ');
            $galleryName = $helper->ask($input, $output, $question);
            if (empty($galleryName)) {
                $galleryName = $defaultGalleryName;
            }
            $gallery = $gm->loadGallery($galleryName);
            try {
                $gallery->uploadPath($galleryPath);
            } catch (FilesystemException|ImagickException $e) {
                $output->writeln('Error: ' . $e->getMessage());
            }
        }
    }
}

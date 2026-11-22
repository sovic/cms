<?php

namespace Sovic\Cms\Gallery;

use Sovic\Cms\Entity\GalleryModelInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class GalleryModelResolver
{
    /**
     * @param iterable<GalleryModelProviderInterface> $providers
     */
    public function __construct(
        #[AutowireIterator(GalleryModelProviderInterface::Tag)]
        private iterable $providers,
    ) {
    }

    public function resolve(string $modelName, int $modelId): ?GalleryModelInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider->getGalleryModelName() === $modelName) {
                return $provider->loadGalleryModel($modelId);
            }
        }

        return null;
    }
}

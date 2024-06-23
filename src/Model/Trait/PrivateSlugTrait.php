<?php

namespace Sovic\Cms\Model\Trait;

trait PrivateSlugTrait
{
    public function existsSlug(string $slug): bool
    {
        $repo = $this->getEntityManager()->getRepository($this->entity::class);

        return $repo->findOneBy(['privateSlug' => $slug]) !== null;
    }

    public function generateSlug(int $length): string
    {
        /** @noinspection SpellCheckingInspection */
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charsLength = strlen($chars);

        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $pos = random_int(1, $charsLength);
            $result .= $chars[$pos - 1];
        }

        return $result;
    }

    public function generateUniqueSlug(int $length): string
    {
        do {
            $slug = $this->generateSlug($length);
        } while ($this->existsSlug($slug));

        return $slug;
    }
}

<?php

namespace Sovic\Cms\Ai;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Sovic\Cms\Entity\Page;
use UserBundle\User\UserEntityInterface;

/**
 * Marks a page + user assistant request as running, so a retry after a browser or proxy timeout
 * does not start a second (paid) generation while the first one is still in progress.
 */
class AiRequestLock
{
    /**
     * Seconds, must outlive the longest possible request (see AnthropicClientFactory::Timeout).
     */
    public const Ttl = 200;

    public function __construct(
        private readonly ?CacheItemPoolInterface $cache = null,
    ) {
    }

    public function isLocked(Page $page, UserEntityInterface $user): bool
    {
        if ($this->cache === null) {
            return false;
        }

        try {
            return $this->cache->getItem($this->key($page, $user))->isHit();
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function acquire(Page $page, UserEntityInterface $user): void
    {
        if ($this->cache === null) {
            return;
        }

        try {
            $item = $this->cache->getItem($this->key($page, $user));
        } catch (InvalidArgumentException) {
            return;
        }
        $item->set(true);
        $item->expiresAfter(self::Ttl);
        $this->cache->save($item);
    }

    public function release(Page $page, UserEntityInterface $user): void
    {
        if ($this->cache === null) {
            return;
        }

        try {
            $this->cache->deleteItem($this->key($page, $user));
        } catch (InvalidArgumentException) {
            // nothing to release
        }
    }

    private function key(Page $page, UserEntityInterface $user): string
    {
        // the identifier is an e-mail or username, hash it to keep the cache key valid
        return 'sovic_cms_ai_running_' . $page->getId() . '_' . md5($user->getUserIdentifier());
    }
}

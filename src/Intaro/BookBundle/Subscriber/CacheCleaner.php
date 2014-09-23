<?php

namespace Intaro\BookBundle\Subscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Intaro\BookBundle\Entity\Book;
use Doctrine\Common\Cache\MemcacheCache;

/**
 * Сбрасывает кеш при добавлении или редактировании книги
 */
class CacheCleaner
{
    /**
     * @var MemcacheCache 
     */
    private $cacheDriver;

    /**
     * @var string
     */
    private $cacheId;

    /**
     * @param MemcacheCache $cacheDriver
     * @param string        $cacheId
     */
    public function __construct(MemcacheCache $cacheDriver, $cacheId)
    {
        $this->cacheDriver = $cacheDriver;
        $this->cacheId = $cacheId;
    }

    /**
     * @return MemcacheCache
     */
    public function getCacheDriver()
    {
        return $this->cacheDriver;
    }

    /**
     * @return string
     */
    public function getCacheId()
    {
        return $this->cacheId;
    }

    /**
     * Очищает кеш
     * 
     * @param LifecycleEventArgs $args
     */
    public function clearCache(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if (!$entity instanceof Book) {
            return;
        }

        $this->deleteCache();
    }

    /**
     * Удалет из кеша кеш с идентификатором $this->cacheId, если таковой установлен
     */
    private function deleteCache()
    {
        if ($this->getCacheDriver()->contains($this->getCacheId())) {
            $this->getCacheDriver()->delete($this->getCacheId());
        }
    }
}
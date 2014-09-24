<?php

namespace Intaro\BookBundle\Subscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Intaro\BookBundle\Entity\Book;
use Doctrine\Common\Cache\Cache;

/**
 * Сбрасывает кеш при добавлении или редактировании книги
 */
class CacheCleaner
{
    /**
     * @var string
     */
    private $cacheId;

    /**
     * @param string        $cacheId
     */
    public function __construct($cacheId)
    {
        $this->cacheId = $cacheId;
    }

    /**
     * Срабатывает при добавлении
     * 
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        if (!($args->getEntity() instanceof Book)) {
            return;
        }

        $this->clearCache($this->getCacheDriver($args));
    }

    /**
     * Срабатывает при обновлении
     * 
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        if (!($args->getEntity() instanceof Book)) {
            return;
        }

        $this->clearCache($this->getCacheDriver($args));
    }

    /**
     * Срабатывает при удалении
     * 
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        if (!($args->getEntity() instanceof Book)) {
            return;
        }

        $this->clearCache($this->getCacheDriver($args));
    }

    /**
     * Удалет из кеша кеш с идентификатором $this->cacheId, если таковой установлен
     * 
     * @param Cache $cd кеш драйвер
     */
    private function clearCache(Cache $cd)
    {
        if ($cd->contains($this->cacheId)) {
            $cd->delete($this->cacheId);
        }
    }

    /**
     * Упрощает получение кеш дайвера
     * 
     * @param LifecycleEventArgs $args
     * 
     * @return Cache
     */
    private function getCacheDriver(LifecycleEventArgs $args)
    {
        return $args->getEntityManager()->getConfiguration()->getQueryCacheImpl();
    }
}
<?php

namespace Intaro\BookBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
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
     * Возвращает массив соббытий, на которые подписан данный Listener
     * 
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array('onFlush');
    }

    /**
     * Срабатывает при удалении
     * 
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
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
     * @param OnFlushEventArgs $args
     * 
     * @return Cache
     */
    private function getCacheDriver(OnFlushEventArgs $args)
    {
        return $args->getEntityManager()->getConfiguration()->getQueryCacheImpl();
    }
}
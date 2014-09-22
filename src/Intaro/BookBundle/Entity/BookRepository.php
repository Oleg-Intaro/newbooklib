<?php

namespace Intaro\BookBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Book Repository
 */
class BookRepository extends EntityRepository
{
    /**
     * Возвращает книги, упорядоченные по дате прочтения использует кеш
     * 
     * @param \Doctrine\Common\Cache\MemcacheCache() $cacheDriver
     * 
     * @return type
     */
    public function findAllOrderedByDateCached($cacheDriver)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT b FROM IntaroBookBundle:Book b ORDER BY b.lastRead DESC');
        $query->setResultCacheDriver($cacheDriver);
        $query->useResultCache(true, 24*60*60, 'book_entities');

        return $query->getResult();
    }

}

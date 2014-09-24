<?php

namespace Intaro\BookBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Book Repository
 */
class BookRepository extends EntityRepository
{
    /**
     * @var int время жизни кеша 
     */
    private $cacheExp;

    /**
     * @var string id кеша для книг
     */
    private $caheId;

    /**
     * Инициализирует время кеша и его id
     * ! вынести в конфигурацию
     * 
     * @param EntityManager         $em
     * @param Mapping\ClassMetadata $class
     */
    public function __construct($em, $class)
    {
        parent::__construct($em, $class);
        $this->cacheExp = 24*60*60;
        $this->caheId = 'book_entities';
    }
    /**
     * Возвращает книги, упорядоченные по дате прочтения использует кеш
     * 
     * @return array The entities
     */
    public function findAllOrderedByDate()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'SELECT b
            FROM IntaroBookBundle:Book b 
            ORDER BY b.lastRead DESC'
        );

        $cd = $em->getConfiguration()->getQueryCacheImpl();

        $query->setResultCacheDriver($cd);
        $query->useResultCache(true, $this->cacheExp, $this->caheId);

        return $query->getResult();
    }
}

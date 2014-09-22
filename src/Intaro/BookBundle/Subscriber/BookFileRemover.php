<?php

namespace Intaro\BookBundle\Subscriber;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Intaro\BookBundle\Entity\Book;

/**
 * Удаляет файлы с книгами и их обложками после удаления книги
 */
class BookFileRemover
{
    /**
     * Вызывается при удалении книги
     * 
     * @param LifecycleEventArgs $args
     * 
     * @return type
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$entity instanceof Book) {
            return;
        }
        $this->deleteFile($entity->getAbsolutePath());
        $this->deleteFile($entity->getAbsoluteCoverPath());
    }

    /**
     * Удаляет файл с $filename, если тот существует
     * 
     * @param type $filename
     */
    private function deleteFile($filename)
    {
        if (file_exists($filename)) {
            unlink($filename);
        }
    }
}

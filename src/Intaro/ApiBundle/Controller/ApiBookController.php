<?php 

namespace Intaro\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Intaro\BookBundle\Entity\Book;
//use Intaro\BookBundle\Form\BookType;
//use Symfony\Component\HttpFoundation\Request;
//use FOS\RestBundle\View\View

class ApiBookController extends FOSRestController
{
   /**
    * Получаем список всех книг
    * 
    * @ApiDoc(
    *     description="Возвращает список всех книг",
    *     output = "Intaro\BookBundle\Entity\Book",
    *     statusCodes={
    *         200="В случае успеха",
    *         404="Если книги не найдены"
    *     }
    * )
    * 
    * @return array
    * @throws NotFoundHttpException
    * 
    */
    public function getAllAction()
    {
        $entities = $this->getDoctrine()->getEntityManager()
            ->getRepository('IntaroBookBundle:Book')
            ->findAll()
        ;

        if (!$entities) {
             throw new NotFoundHttpException('Books not found');
        }
        
        return array('books' => $entities);
    }
    
    /**
     * Получаем книгу по идентификатору
     * 
     * @ApiDoc( 
     *     description="Возвращает книгу",
     *     output = "Intaro\BookBundle\Entity\Book",
     *     statusCodes={
     *         200="В случае успеха",
     *         404="Если книга не найдена"
     *     }
     * )
     * 
     * @param int $id
     * 
     * @return array
     * @throws NotFoundHttpException
     */
    public function getSingleAction($id)
    {
       $entity = $this->getDoctrine()->getEntityManager()
            ->getRepository('IntaroBookBundle:Book')
            ->find($id)
        ;

        if (!$entity) {
             throw new NotFoundHttpException('Book not found');
        }
        
        return array('book' => $entity);
    }
    
    public function addAction(Book $entity, ConstraintViolationListInterface $validationErrors)
    {
        if (count($validationErrors) > 0) {
            return array('errors' => $validationErrors);
        }
            
        return $this->routeRedirectView(
            'intaro_api_book_get_single',
            array('id' => $entity->id)
        );
    }
}

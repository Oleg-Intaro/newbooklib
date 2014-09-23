<?php 

namespace Intaro\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Intaro\BookBundle\Entity\Book;
use Intaro\ApiBundle\Form\BookType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;

/**
 * Контролер реализует API для работы с книгами
 */
class ApiBookController extends FOSRestController
{
    /**
     * Получаем список всех книг
     * 
     * **Формат ответа**
     *
     *     {
     *       "books":[
     *         {
     *           "id":4,
     *           "title":"New book",
     *           "author":"Oleg",
     *           "last_read":"2014-09-19T16:02:00+0400",
     *           "allow_download":false
     *         },
     *         {
     *           "id":3,
     *           "title":"Книга",
     *           "author":"Олег",
     *           "last_read":"2014-09-19T16:00:00+0400",
     *           "allow_download":false,
     *           "path":"/home/oleg/development/public_html/newbooklib/src/Intaro/BookBundle/Entity/../../../../web/uploads/books/2014/09/19/5a61df9a7ab2879891df1bb06244d92ee2444197.txt",
     *           "cover_path":"/home/oleg/development/public_html/newbooklib/src/Intaro/BookBundle/Entity/../../../../web/uploads/books/2014/09/19/covers/01dc685fff259988b411ccbb5aafc492e89ff105.png"
     *         }
     *      ]
     *     }
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
     * @return array|View
     * 
     */
    public function getAllAction()
    {
        $entities = $this->getDoctrine()->getEntityManager()
            ->getRepository('IntaroBookBundle:Book')
            ->findAll();

        if (!$entities) {
            return View::create(array('errors' => 'Книги не найдены'), 404);
        }

        return array('books' => $entities);
    }

    /**
     * Получаем книгу по идентификатору
     * 
     * **Формат ответа**
     * 
     *     {
     *       "book":{
     *         "id":13,
     *         "title":"Json Book",
     *         "author":"Json Author",
     *         "last_read":"2014-10-22T00:00:00+0400",
     *         "allow_download":false
     *       }
     *     }
     * 
     * @param int $id  
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
     * @return array|View
     */
    public function getSingleAction($id)
    {
        $entity = $this->getDoctrine()->getEntityManager()
            ->getRepository('IntaroBookBundle:Book')
            ->find($id);

        if (!$entity) {
            return View::create(array('errors' => 'Книга не найдена c id: '.$id ), 404);
        }

        return array('book' => $entity);
    }

    /**
     * Добавляет новую книгу, правда без файла самой книги
     * 
     * **Формат запроса**
     * 
     *     {
     *       "book":{
     *         "title":"Json Book",
     *         "author":"Json Author",
     *         "lastRead":"2014-10-22T11:50:49+0400",
     *         "allowDownload":true
     *       }
     *     }
     * 
     * @param Request $request 
     * 
     * @ApiDoc( 
     *     description="Добавляет книгу, правда без файла самой книги",
     *     output = "Intaro\BookBundle\Entity\Book",
     *     statusCodes={
     *         201="Если книга успешно добавлена",
     *         400="Если есть ошибки валидации"
     *     }
     * )
     * 
     * @return View
     */
    public function addAction(Request $request)
    {
        $entity = new Book();
        $form = $this->createForm(new BookType(), $entity);
        $form->submit($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->routeRedirectView(
                'intaro_api_book_get_single',
                array('id' => $entity->getId())
            );
        }

        return View::create($form, 400);
    }
}

<?php 

namespace Intaro\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Intaro\BookBundle\Entity\Book;
use Intaro\ApiBundle\Form\BookType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Util\Codes;

/**
 * Контролер реализует API для работы с книгами
 */
class ApiBookController extends FOSRestController
{
    /**
     * Получает список всех книг. Если книжка не доступна для скачивания, то 
     * свойство "path" переданно не будет.
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
        $entities = $this->getEm()
            ->getRepository('IntaroBookBundle:Book')
            ->findAll();

        if (!$entities) {
            return $this->notFoundView('Книги не найдены');
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
        $entity = $this->getEm()
            ->getRepository('IntaroBookBundle:Book')
            ->find($id);

        if (!$entity) {
            return $this->notFoundView('Книга не найдена c id: '.$id);
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
          return $this->processForm(new Book(), $request);
    }

    /**
     * Редактирует книгу
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
     * @param int     $id
     * 
     * @ApiDoc( 
     *     description="Редактирует книгу",
     *     output = "Intaro\BookBundle\Entity\Book",
     *     statusCodes={
     *         204="Если книга успешно обновлена",
     *         400="Если есть ошибки валидации"
     *     }
     * )
     * 
     * @return View
     */
    public function editAction(Request $request, $id)
    {
        $entity = $this->getEm()
            ->getRepository('IntaroBookBundle:Book')
            ->find($id);

        if (!$entity) {
            return $this->notFoundView('Книга не найдена c id: '.$id);
        }

        return $this->processForm($entity, $request);
    }

    /**
     * Создаёт форму для приёма данных для редактирования и добавления книги
     * 
     * @param Book    $entity
     * @param Request $request
     * 
     * @return View
     */
    private function processForm(Book $entity, Request $request)
    {
        $statusCode = $this->getEntityStatusCode($entity);
        $form = $this->createForm(new BookType(), $entity);
        $form->submit($request);
        if ($form->isValid()) {
            $this->saveEntity($entity);

            return $this->routeRedirectView(
                'intaro_api_book_get_single',
                array('id' => $entity->getId()),
                $statusCode
            );
        }

        return View::create($form, Codes::HTTP_BAD_REQUEST);
    }

    /**
     * Возвращает Entity Manager'а
     * 
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    private function getEm()
    {
        return $this->getDoctrine()->getEntityManager();
    }

    /**
     * Сохраняет книгу
     * 
     * @param Book $entity
     */
    private function saveEntity($entity)
    {
        $em = $this->getEm();
        $em->persist($entity);
        $em->flush();
    }

    /**
     * Генерирует View 404
     * 
     * @param string $message
     * 
     * @return View
     */
    private function notFoundView($message)
    {
        return View::create(array('errors' => $message), Codes::HTTP_NOT_FOUND);
    }

    /**
     * Определяет, какой статус код установить при сохранении книги - создана или отредактированна
     * 
     * @param Book $entity
     * 
     * @return int
     */
    private function getEntityStatusCode($entity)
    {
        return $entity->isNew() ? Codes::HTTP_CREATED : Codes::HTTP_NO_CONTENT;
    }
}
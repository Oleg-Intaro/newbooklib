<?php

namespace Intaro\BookBundle\Controller;

use Intaro\BookBundle\Entity\Book;
use Intaro\BookBundle\Form\BookType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * Контроллер книг.
 */
class BookController extends Controller
{

    /**
     * Получаем список всех книг.
     *
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $cacheDriver = $this->get('memcache_driver');
        $entities = $em->getRepository('IntaroBookBundle:Book')
            ->findAllOrderedByDateCached($cacheDriver);

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new Book entity.
     * 
     * @param Request $request
     *
     * @Template("IntaroBookBundle:Book:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Book();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $entity->upload();
            $entity->uploadCover();
            $em->flush();
            $this->clearCache();

            return $this->redirect($this->generateUrl(
                'book_show',
                array('id' => $entity->getId()
            )));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Book entity.
     *
     * @param Book $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Book $entity)
    {
        $form = $this->createForm(new BookType(), $entity, array(
            'action' => $this->generateUrl('book_create'),
            'method' => 'POST',
            'validation_groups' => array('Book', 'create'),
        ));
        $form->add('file');
        $form->add('coverFile');
        $form->add('submit', 'submit');

        return $form;
    }

    /**
     * Displays a form to create a new Book entity.
     *
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function newAction()
    {
        $entity = new Book();
        $entity->setLastRead(new \DateTime());
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Book entity.
     * 
     * @param int $id
     *
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('IntaroBookBundle:Book')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Невозможно найти данную книгу..');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Book entity.
     * 
     * @param int $id
     *
     * @Template()
     * @Security("has_role('ROLE_USER')")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('IntaroBookBundle:Book')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Невозможно найти данную книгу..');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Book entity.
    *
    * @param Book $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Book $entity)
    {
        $form = $this->createForm(new BookType(), $entity, array(
            'action' => $this->generateUrl(
                'book_update',
                array('id' => $entity->getId()
            )),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit');

        return $form;
    }
    /**
     * Edits an existing Book entity.
     *
     * @param Request $request
     * @param int     $id
     * 
     * @Template("IntaroBookBundle:Book:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('IntaroBookBundle:Book')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Невозможно найти данную книгу..');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $this->clearCache();

            return $this->redirect($this->generateUrl(
                'book_edit',
                array('id' => $id)
            ));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Book entity.
     *
     * @param Request $request
     * @param int     $id
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('IntaroBookBundle:Book')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Невозможно найти данную книгу..');
            }

            $em->remove($entity);
            $em->flush();
            $this->clearCache();
        }

        return $this->redirect($this->generateUrl('book'));
    }

    /**
     * Creates a form to delete a Book entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('book_delete', array('id' => $id)))
            ->setMethod('GET')
            ->add('submit', 'submit')
            ->getForm();
    }

    /**
     * Загрузка файла
     * 
     * @param int $id
     * 
     * @return BinaryFileResponse
     */
    public function downloadAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('IntaroBookBundle:Book')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Невозможно найти данную книгу.');
        }
        $file = $entity->getAbsolutePath();
        if (!file_exists($file)) {
            throw $this->createNotFoundException('Невозможно найти файл книги.');
        }
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            // если здесь использовть название книги на русском, 
            // то вылетает ошибка, что имя файла может содержать только ASCII
            $entity->getPath()
        );

        return $response;
    }

    /**
     * Очищает кеш книг
     */
    private function clearCache()
    {
        $cacheDriver = $this->get('memcache_driver');
        if ($cacheDriver->contains('book_entities')) {
            $cacheDriver->delete('book_entities');
        }
    }
}

<?php

namespace Raddit\AppBundle\Controller;

use Doctrine\ORM\EntityManager;
use Raddit\AppBundle\Entity\MessageThread;
use Raddit\AppBundle\Entity\User;
use Raddit\AppBundle\Form\MessageReplyType;
use Raddit\AppBundle\Form\MessageThreadType;
use Raddit\AppBundle\Form\Model\MessageData;
use Raddit\AppBundle\Repository\MessageThreadRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MessageController extends Controller {
    /**
     * @Security("is_granted('ROLE_USER')")
     *
     * @param MessageThreadRepository $repository
     * @param int                     $page
     *
     * @return Response
     */
    public function listAction(MessageThreadRepository $repository, int $page) {
        $messages = $repository->findUserMessages($this->getUser(), $page);

        return $this->render('@RadditApp/message_list.html.twig', [
            'messages' => $messages,
        ]);
    }

    /**
     * Start a new message thread.
     *
     * @Security("is_granted('ROLE_USER')")
     *
     * @param Request       $request
     * @param EntityManager $em
     * @param User          $receiver
     *
     * @return Response
     */
    public function composeAction(Request $request, EntityManager $em, User $receiver) {
        $data = new MessageData($this->getUser(), $request->getClientIp());

        $form = $this->createForm(MessageThreadType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thread = $data->toThread($receiver);

            $em->persist($thread);
            $em->flush();

            return $this->redirectToRoute('raddit_app_message', [
                'id' => $thread->getId(),
            ]);
        }

        return $this->render('@RadditApp/message_compose.html.twig', [
            'form' => $form->createView(),
            'receiver' => $receiver,
        ]);
    }

    /**
     * View and reply to a message thread.
     *
     * @Security("is_granted('access', thread)")
     *
     * @param Request       $request
     * @param EntityManager $em
     * @param MessageThread $thread
     *
     * @return Response
     */
    public function messageAction(Request $request, EntityManager $em, MessageThread $thread) {
        $data = new MessageData($this->getUser(), $request->getClientIp());

        $form = $this->createForm(MessageReplyType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $thread->addReply($data->toReply($thread));

            $em->flush();

            return $this->redirectToRoute('raddit_app_message', [
                'id' => $thread->getId(),
            ]);
        }

        return $this->render('@RadditApp/message.html.twig', [
            'form' => $form->createView(),
            'thread' => $thread,
        ]);
    }
}

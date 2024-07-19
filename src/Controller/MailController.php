<?php

namespace App\Controller;

use App\Entity\Mail;
use App\Form\MailType;
use App\Repository\MailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class MailController extends AbstractController
{
    #[Route('/mail/', name: 'app_mail_index', methods: ['GET'])]
    public function index(Request $request, MailRepository $mailRepository): Response
    {

        $options = ['Pending Addition' => 'Pendente de Adição' , 'Pending Removal' => 'Pendente de Remoção', 'Added' => 'Adicionado', 'Removed' => 'Removido'];

        $pagination = $mailRepository->findAllPagination();

        if ($request->isXmlHttpRequest()) {
            $html = $this->renderView('mail/_email_table.html.twig', [
                'mails' => $pagination,
                'options' => $options
            ]);

            return new JsonResponse(['html' => $html]);
        }

        return $this->render('mail/index.html.twig', [
            'mails' => $pagination,
            'options' => $options
        ]);
    }

    #[Route('/mail/new', name: 'app_mail_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mail = new Mail();
        $form = $this->createForm(MailType::class, $mail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($mail);
            $entityManager->flush();

            return $this->redirectToRoute('app_mail_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mail/new.html.twig', [
            'mail' => $mail,
            'form' => $form,
        ]);
    }

    #[Route('/mail/{id}', name: 'app_mail_show', methods: ['GET'])]
    public function show(Mail $mail): Response
    {
        return $this->render('mail/show.html.twig', [
            'mail' => $mail,
        ]);
    }

    #[Route('/mail/{id}/edit', name: 'app_mail_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Mail $mail, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MailType::class, $mail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_mail_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('mail/edit.html.twig', [
            'mail' => $mail,
            'form' => $form,
        ]);
    }

    #[Route('/mail/{id}', name: 'app_mail_delete', methods: ['POST'])]
    public function delete(Request $request, Mail $mail, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mail->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($mail);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_mail_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/unsubscribe', name: 'unsubscribe', methods: ['GET'])]
    public function unsubscribe(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $id = $request->query->get('id');

        if ($id) {
            $mail = new Mail();
            $mail->setEmail($id);
            $mail->setStatus('pendent block');

            $entityManager->persist($mail);
            $entityManager->flush();
            return $this->json(['message' => 'Email salvo com sucesso!', 'id' => $id]);
        }

        return $this->json(['message' => 'Parâmetro "id" não encontrado na query string.', 'id' => $id]);
    }

    #[Route('/subscribe', name: 'subscribe-get', methods: ['GET'])]
    public function subscribeGet(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $id = $request->query->get('id');

        if ($id) {
            $mail = new Mail();
            $mail->setEmail($id);
            $mail->setStatus('Pending Addition');
            $entityManager->persist($mail);
            $entityManager->flush();

            return $this->json(['message' => 'Email salvo com sucesso!', 'id' => $id]);
        }

        return $this->json(['message' => 'Parâmetro "id" não encontrado na query string.', 'id' => $id]);
    }

    #[Route('/subscribe', name: 'subscribe-post', methods: ['POST'])]
    public function subscribePost(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = null;

        // Verifica o tipo de conteúdo da requisição
        if ($request->headers->get('Content-Type') === 'application/json') {
            // Decodificar o JSON do corpo da requisição
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new Response('Invalid JSON', 400);
            }
        } else {
            // Pegar os dados do form-data
            $data = $request->request->all();
        }


        if (isset($data['id'])) {
            $mail = new Mail();
            $mail->setEmail($data['id']);
            $mail->setStatus('Pending Addition');
            $entityManager->persist($mail);
            $entityManager->flush();

            return $this->json(['message' => 'Email salvo com sucesso!', 'id' => $data['id']]);
        }

        return $this->json(['message' => 'Parâmetro "id" não encontrado na query string.', 'id' => $data['id']]);
    }

    #[Route('/mail/status/{id}', name: 'app_mail_update_status', methods: ['PATCH'])]
    public function updateStatus(Request $request, Mail $mail, EntityManagerInterface $entityManager, SerializerInterface $serializer): Response
    {
        $data = null;

        // Verifica o tipo de conteúdo da requisição
        if ($request->headers->get('Content-Type') === 'application/json') {
            // Decodificar o JSON do corpo da requisição
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new Response('Invalid JSON', 400);
            }
        } else {
            // Pegar os dados do form-data
            $data = $request->request->all();
        }

        if ($mail) {
            $mail->setStatus($data['status']);
            //$entityManager->persist($mail);
            $entityManager->flush();

            $jsonData = $serializer->serialize($mail, 'json');

            $response = [
                'message' => 'Status alterado com sucesso!',
                'email' => json_decode($jsonData, true) // Decoding to array to include in the response
            ];

            return new JsonResponse($response);
        }

        return $this->json(['message' => 'Parâmetro "id" não encontrado na query string.', 'email' => $mail]);
    }
}

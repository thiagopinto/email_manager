<?php

namespace App\Controller;

use App\Entity\BouncedEmail;
use App\Form\BouncedEmailType;
use App\Repository\BouncedEmailRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bounced/email')]
class BouncedEmailController extends AbstractController
{
    #[Route('/', name: 'app_bounced_email_index', methods: ['GET'])]
    public function index(BouncedEmailRepository $bouncedEmailRepository): Response
    {
        return $this->render('bounced_email/index.html.twig', [
            'bounced_emails' => $bouncedEmailRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_bounced_email_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $bouncedEmail = new BouncedEmail();
        $form = $this->createForm(BouncedEmailType::class, $bouncedEmail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($bouncedEmail);
            $entityManager->flush();

            return $this->redirectToRoute('app_bounced_email_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bounced_email/new.html.twig', [
            'bounced_email' => $bouncedEmail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bounced_email_show', methods: ['GET'])]
    public function show(BouncedEmail $bouncedEmail): Response
    {
        return $this->render('bounced_email/show.html.twig', [
            'bounced_email' => $bouncedEmail,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bounced_email_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BouncedEmail $bouncedEmail, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BouncedEmailType::class, $bouncedEmail);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_bounced_email_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bounced_email/edit.html.twig', [
            'bounced_email' => $bouncedEmail,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bounced_email_delete', methods: ['POST'])]
    public function delete(Request $request, BouncedEmail $bouncedEmail, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bouncedEmail->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($bouncedEmail);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_bounced_email_index', [], Response::HTTP_SEE_OTHER);
    }
}

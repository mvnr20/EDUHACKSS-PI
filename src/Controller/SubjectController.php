<?php

namespace App\Controller;

use App\Entity\Subject;
use App\Form\SubjectType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/subject')]
class SubjectController extends AbstractController
{
    #[Route('/back', name: 'app_subject_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $subjects = $entityManager
            ->getRepository(Subject::class)
            ->findAll();

        return $this->render('Back_office/back/subject.html.twig', [
            'subjects' => $subjects,
        ]);
    }

    #[Route('/new', name: 'app_subject_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subject = new Subject();
        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subject);
            $entityManager->flush();

            return $this->redirectToRoute('app_subject_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Back_office/back/newsub.html.twig', [
            'subject' => $subject,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_subject_show', methods: ['GET'])]
    public function show(Subject $subject): Response
    {
        return $this->render('subject/show.html.twig', [
            'subject' => $subject,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_subject_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $subject = $entityManager->getRepository(Subject::class)->find($id);

        if (!$subject) {
            throw $this->createNotFoundException('Subject not found.');
        }

        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('app_subject_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Back_office/back/updsub.html.twig', [
            'subject' => $subject,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_subject_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $subject = $entityManager->getRepository(Subject::class)->find($id);

        if (!$subject) {
            throw $this->createNotFoundException('Subject not found.');
        }

        if ($this->isCsrfTokenValid('delete' . $subject->getId(), $request->request->get('_token'))) {
            $entityManager->remove($subject);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_subject_index', [], Response::HTTP_SEE_OTHER);
    }
}

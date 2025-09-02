<?php

namespace App\Controller;

use App\Entity\BadWords; // Corrected entity class name
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\BadWordsRepository; // Corrected repository class
use App\Form\BadWordsType;
use App\Repository\CategoriesRepository;


class BadWordsController extends AbstractController
{
    #[Route('/bad_words', name: 'bad_words_index', methods: ['GET'])]
    public function index(BadWordsRepository $badWordsRepository, CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findAll();

        $badWords = $badWordsRepository->findAll();
        return $this->render('Back_office/User/bad_words/index.html.twig', [
            'badWords' => $badWords,
            'categories' => $categories, // Pass the $categories variable to the Twig template
        ]);
    }

    #[Route('/bad_word/new', name: 'bad_word_new', methods: ['GET', 'POST'])]
    public function newBadWord(Request $request, CategoriesRepository $categoriesRepository): Response
    {
        $categories = $categoriesRepository->findAll();
    
        $badWord = new BadWords();
        $form = $this->createForm(BadWordsType::class, $badWord);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($badWord);
            $entityManager->flush();
    
            $this->addFlash('success', 'Bad word added successfully.');
            return $this->redirectToRoute('bad_words_index');
        }
    
        return $this->render('Back_office/User/bad_words/new.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories, // Pass the $categories variable to the Twig template
        ]);
    }
    

    #[Route('/bad_word/{id}', name: 'bad_word_show', methods: ['GET'])]
public function show($id, BadWordsRepository $badWordsRepository, CategoriesRepository $categoriesRepository): Response
{
    $badWord = $badWordsRepository->find($id); // Fetch the BadWords entity using the repository

    if (!$badWord) {
        throw $this->createNotFoundException('Bad word not found');
    }

    $categories = $categoriesRepository->findAll();

    return $this->render('Back_office/User/bad_words/show.html.twig', [
        'badWord' => $badWord,
        'categories' => $categories,
    ]);
}

#[Route('/bad_word/{id}/edit', name: 'bad_word_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, $id, BadWordsRepository $badWordsRepository, CategoriesRepository $categoriesRepository): Response
{
    $badWord = $badWordsRepository->find($id); // Fetch the BadWords entity using the repository

    if (!$badWord) {
        throw $this->createNotFoundException('Bad word not found');
    }

    $form = $this->createForm(BadWordsType::class, $badWord);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        $this->addFlash('success', 'Bad word updated successfully.');
        return $this->redirectToRoute('bad_words_index');
    }

    $categories = $categoriesRepository->findAll();

    return $this->render('Back_office/User/bad_words/edit.html.twig', [
        'form' => $form->createView(),
        'categories' => $categories,
    ]);
}

#[Route('/bad_word/delete/{id}', name: 'bad_word_delete', methods: ['GET'])]
public function deleteBadWord($id, BadWordsRepository $badWordsRepository): Response
{
    $badWord = $badWordsRepository->find($id); // Fetch the BadWords entity using the repository

    if (!$badWord) {
        throw $this->createNotFoundException('Bad word not found');
    }

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->remove($badWord);
    $entityManager->flush();

    $this->addFlash('success', 'Bad word deleted successfully.');
    return $this->redirectToRoute('bad_words_index');
}
    

    
}
<?php

namespace App\Controller;

use App\Entity\Question;
use App\Repository\QuestionRepository;
use App\Entity\Form;
use App\Form\QuestionFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoriesRepository;


class QuestionController extends AbstractController
{

  
      #[Route("/question/{formId}/{id}/delete", name:"delete_question", methods:["GET"] ) ]
    
    public function deleteQuestion(Request $request, EntityManagerInterface $entityManager, int $formId, int $id): Response
    {
        // Fetch the question entity by ID and ensure it belongs to the specified form
        $question = $entityManager->getRepository(Question::class)->findOneBy(['form' => $formId, 'id' => $id]);


        // Delete the question
        $entityManager->remove($question);
        $entityManager->flush();

        // Redirect to the previous page
        return $this->redirect($request->headers->get('referer'));
    }
  
    #[Route("/question/{formId}/create", name: "create_question", methods: ["GET", "POST"])]
    public function createQuestion(Request $request, EntityManagerInterface $entityManager, int $formId, CategoriesRepository $categoriesRepository): Response
    {
        $formEntity = $entityManager->getRepository(Form::class)->find($formId);
    
        if (!$formEntity) {
            throw $this->createNotFoundException('Form not found');
        }
    
        $question = new Question();
        $form = $this->createForm(QuestionFormType::class, $question);
    
        // Fetch categories from the repository
        $categories = $categoriesRepository->findAll();
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $question->setForm($formEntity);
            $entityManager->persist($question);
            $entityManager->flush();
    
            return $this->redirectToRoute('view_questions', ['formId' => $formId]);
        }
    
        return $this->render('Back_office/form_management/create_question.html.twig', [
            'form' => $form->createView(),
            'formId' => $formId,
            'categories' => $categories, // Pass the categories variable to the Twig template
        ]);
    }
    

    #[Route('/question/{formId}/edit/{id}', name: 'edit_question', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $formId, int $id, QuestionRepository $questionRepository, CategoriesRepository $categoriesRepository): Response
    {
        $question = $questionRepository->find($id);
    
        if (!$question) {
            throw $this->createNotFoundException('Question not found');
        }
    
        $form = $this->createForm(QuestionFormType::class, $question);
    
        // Fetch categories from the repository
        $categories = $categoriesRepository->findAll();
    
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $questionRepository->update($question);
    
            return $this->redirectToRoute('view_questions', ['formId' => $formId]);
        }
    
        return $this->render('Back_office/form_management/edit_question.html.twig', [
            'form' => $form->createView(),
            'question' => $question,
            'formId' => $formId,
            'categories' => $categories, // Pass the categories variable to the Twig template
        ]);
    }
    


    #[Route('/form/{formId}/question/{questionId}/answers', name: 'view_answers')]
public function viewAnswers(int $formId, int $questionId, EntityManagerInterface $entityManager, CategoriesRepository $categoriesRepository): Response
{
    // Fetch the question entity
    $question = $entityManager->getRepository(Question::class)->find($questionId);

    // Fetch the answers associated with the question
    $answers = $question->getAnswer();

    // Fetch categories from the repository
    $categories = $categoriesRepository->findAll();

    return $this->render('Back_office/form_management/answers.html.twig', [
        'formId' => $formId,
        'questionId' => $questionId,
        'question' => $question,
        'answers' => $answers,
        'categories' => $categories, // Pass the categories variable to the Twig template
    ]);
}

}

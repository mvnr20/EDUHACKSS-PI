<?php
namespace App\Controller;
use App\Form\AnswerFormType;
use App\Repository\AnswerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Question;
use App\Entity\Answer;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoriesRepository;



class AnswerController extends AbstractController
{
    private $answerRepository;
    private $entityManager;

    public function __construct(AnswerRepository $answerRepository, EntityManagerInterface $entityManager)
    {
        $this->answerRepository = $answerRepository;
        $this->entityManager = $entityManager;
    }

   
    #[Route('/answer/{questionId}/{id}/delete', name: 'delete_answer')]
    public function delete(int $questionId, int $id, AnswerRepository $answerRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Get the formId from the request query parameters
        $formId = $request->query->get('formId');

        // Find the answer by its id
        $answer = $answerRepository->find($id);

        // Check if the answer exists
        if (!$answer) {
            throw $this->createNotFoundException('Answer not found');
        }
        
        // Delete the answer from the database
        $entityManager->remove($answer);
        $entityManager->flush();
        
        return $this->redirect($request->headers->get('referer'));
    }

#[Route('/answers/{formId}/edit/{questionId}', name: 'edit_answer')]
public function edit(int $formId, int $questionId, Request $request, CategoriesRepository $categoriesRepository): Response
{
    // Retrieve the answer object based on the provided question and form IDs
    $answer = $this->answerRepository->findOneBy(['question' => $questionId]);

    // Assuming you have created a form type for editing answers, create an instance of the form
    $form = $this->createForm(AnswerFormType::class, $answer);

    // Handle form submission
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        // Process form submission and update the answer
        $this->entityManager->flush();

        // Redirect to the view answers page
        return $this->redirectToRoute('view_answers', ['formId' => $formId, 'questionId' => $questionId]);
    }

    // Fetch categories from the repository
    $categories = $categoriesRepository->findAll();

    // Render the template and pass the form object and categories to it
    return $this->render('Back_office/form_management/edit_answer.html.twig', [
        'formId' => $formId,
        'questionId' => $questionId,
        'form' => $form->createView(), // Pass the form view to the template
        'answer' => $answer, // Pass the answer object to the template
        'categories' => $categories, // Pass the categories variable to the Twig template
    ]);
}

    
#[Route('/answers/{formId}/create/{questionId}', name: 'create_answer', methods: ['GET', 'POST'])]
public function createAnswer(Request $request, EntityManagerInterface $entityManager, int $formId, int $questionId, CategoriesRepository $categoriesRepository): Response
{
    // Fetch the Question entity corresponding to $questionId
    $question = $entityManager->getRepository(Question::class)->find($questionId);

    // Check if the question entity exists
    if (!$question) {
        throw $this->createNotFoundException('Question not found');
    }

    // Fetch categories from the repository
    $categories = $categoriesRepository->findAll();

    $answer = new Answer();
    $form = $this->createForm(AnswerFormType::class, $answer);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Set the question for the answer
        $answer->setQuestion($question);

        // Persist the answer entity
        $entityManager->persist($answer);
        $entityManager->flush();

        // Redirect to the view answers page
        return $this->redirectToRoute('view_answers', ['formId' => $formId, 'questionId' => $questionId]);
    }

    // Render the create answer form
    return $this->render('Back_office/form_management/create_answer.html.twig', [
        'form' => $form->createView(),
        'formId' => $formId,
        'questionId' => $questionId,
        'categories' => $categories, // Pass the categories variable to the Twig template
    ]);
}

}

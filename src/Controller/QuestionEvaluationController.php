<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\QuestionEvaluationRepository ;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\QuizRepository;
use App\Entity\QuestionEvaluation;
use App\Form\QuestionEvaluationType; 
use App\Entity\Quiz;
use App\Form\QuizType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Log\LoggerInterface;
use Shuchkin\SimpleXLSX;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
class QuestionEvaluationController extends AbstractController
{



    #[Route('/question/{idquiz}', name:"app_question_evaluation")]
    
    public function showquestion ($idquiz,Request $req, QuestionEvaluationRepository $questionevaluationRepository): Response
    {    
        //$questionEvaluation=$req->findAll();
        // Fetch all forms using the repository method
        $questionEvaluation = $questionevaluationRepository->findAll(['quiz' => $idquiz]);

        // Render the template and pass the list of forms
        return $this->render('Back_office/exam_management/questionevaluation/showquestion.html.twig', [
            'questionEvaluation' => $questionEvaluation,
            'idquiz' => $idquiz, // Pass the idquiz parameter to the template

        ]);
    }

    #[Route("/question/{idquestion}/edit'", name:"app_question_edit")]

    public function editQuestion(Request $request, QuestionEvaluationRepository $QuestionEvaluationRepository, EntityManagerInterface $entityManager, int $idquestion): Response
    {
        // Fetch the form entity by ID from the repository
        $question = $QuestionEvaluationRepository->find($idquestion);
    
        // Create the form using the FormType and pass the form entity
        $form = $this->createForm(QuestionEvaluationType::class, $question);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
    
            // Redirect to form management index page after form update
            return $this->redirectToRoute('view_questions', ['idquiz' => $question->getQuiz()->getIdquiz()]); // Assuming 'view_questions' is the route name for 'showquestion' action
        }
         // Fetch the quiz associated with the question
    $quiz = $question->getQuiz();
    
        return $this->render('Back_office/exam_management/questionevaluation/editquestion.html.twig', [
            'form' => $form->createView(),
            'question' => $question, // Pass the quiz entity to the template
            'quiz' => $quiz, // Pass the quiz entity to the template

        ]);
    }


    #[Route("/question/{idquestion}/delete'", name:"app_question_delete", methods:["GET"])]
     
    public function deleteQuestion(Request $request, QuestionEvaluationRepository $questionevaluationrepository, EntityManagerInterface $entityManager): Response
    {
        $questionid = $request->get('idquestion');

        // Fetch the form entity by ID from the repository
        $question = $questionevaluationrepository->find($questionid);

        // Check if the form can be deleted
        // For example, you may have some validation logic here
        // If the form can be deleted, proceed with deletion
        $entityManager->remove($question);
        $entityManager->flush();

        // Redirect back to the same page after deletion
        return $this->redirectToRoute('view_questions', ['idquiz' => $question->getQuiz()->getIdquiz()]); // Assuming 'view_questions' is the route name for 'showquestion' action
    }
    


    

#[Route("/question/{idquiz}/add-question", name: "app_quiz_add_question")]
public function addQuestion(Request $request, EntityManagerInterface $entityManager, QuizRepository $quizRepository, int $idquiz): Response
{
    // Fetch the quiz entity by ID
    $quiz = $quizRepository->find($idquiz);

    // Check if the quiz exists
    if (!$quiz) {
        throw $this->createNotFoundException('The quiz does not exist');
    }

    $questionEvaluation = new QuestionEvaluation();
    $form = $this->createForm(QuestionEvaluationType::class, $questionEvaluation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
   
        // Set the quiz for the question
        $questionEvaluation->setQuiz($quiz);

        // Persist and flush the question
        $entityManager->persist($questionEvaluation);
        $entityManager->flush();
        
        // Redirect back to the quiz details page or wherever you want
        return $this->redirectToRoute('view_questions', ['idquiz' => $idquiz]);
    }

    return $this->render('Back_office/exam_management/questionevaluation/add.html.twig', [
        'form' => $form->createView(),
        'idquiz' => $idquiz,
    ]);
}

////////////////////////////////////////////////// #[Route('/question/{idquiz}', name:"app_question_evaluation")]
#[Route('/question/{idquiz}', name:"app_question_evaluationFront")]

    public function showquestionFront ($idquiz,Request $req, QuestionEvaluationRepository $questionevaluationRepository): Response
    {    
        //$questionEvaluation=$req->findAll();
        // Fetch all forms using the repository method
        $questionEvaluation = $questionevaluationRepository->findAll(['quiz' => $idquiz]);

        // Render the template and pass the list of forms
        return $this->render('Front_office/exam_management/questionevaluation/showquestion.html.twig', [
            'questionEvaluation' => $questionEvaluation,
            'idquiz' => $idquiz, // Pass the idquiz parameter to the template

        ]);
    }

    #[Route("/question/{idquestion}/editFront", name:"app_question_editFront")]

    public function editQuestionFront(Request $request, QuestionEvaluationRepository $QuestionEvaluationRepository, EntityManagerInterface $entityManager, int $idquestion): Response
    {
        // Fetch the form entity by ID from the repository
        $question = $QuestionEvaluationRepository->find($idquestion);
    
        // Create the form using the FormType and pass the form entity
        $form = $this->createForm(QuestionEvaluationType::class, $question);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
    
            // Redirect to form management index page after form update
            return $this->redirectToRoute('view_questionsFront', ['idquiz' => $question->getQuiz()->getIdquiz()]); // Assuming 'view_questions' is the route name for 'showquestion' action
        }
         // Fetch the quiz associated with the question
    $quiz = $question->getQuiz();
    
        return $this->render('Front_office/exam_management/questionevaluation/editquestion.html.twig', [
            'form' => $form->createView(),
            'question' => $question, // Pass the quiz entity to the template
            'quiz' => $quiz, // Pass the quiz entity to the template

        ]);
    }


    #[Route("/question/{idquestion}/deleteFront'", name:"app_question_deleteFront", methods:["GET"])]
     
    public function deleteQuestionFront(Request $request, QuestionEvaluationRepository $questionevaluationrepository, EntityManagerInterface $entityManager): Response
    {
        $questionid = $request->get('idquestion');

        // Fetch the form entity by ID from the repository
        $question = $questionevaluationrepository->find($questionid);

        // Check if the form can be deleted
        // For example, you may have some validation logic here
        // If the form can be deleted, proceed with deletion
        $entityManager->remove($question);
        $entityManager->flush();

        // Redirect back to the same page after deletion
        return $this->redirectToRoute('view_questionsFront', ['idquiz' => $question->getQuiz()->getIdquiz()]); // Assuming 'view_questions' is the route name for 'showquestion' action
    }
    


    

#[Route("/question/{idquiz}/add-questionFront", name: "app_quiz_add_questionFront")]
public function addQuestionFront(Request $request, EntityManagerInterface $entityManager, QuizRepository $quizRepository, int $idquiz): Response
{
    // Fetch the quiz entity by ID
    $quiz = $quizRepository->find($idquiz);

    // Check if the quiz exists
    if (!$quiz) {
        throw $this->createNotFoundException('The quiz does not exist');
    }

    $questionEvaluation = new QuestionEvaluation();
    $form = $this->createForm(QuestionEvaluationType::class, $questionEvaluation);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
   
        // Set the quiz for the question
        $questionEvaluation->setQuiz($quiz);

        // Persist and flush the question
        $entityManager->persist($questionEvaluation);
        $entityManager->flush();
        
        // Redirect back to the quiz details page or wherever you want
        return $this->redirectToRoute('view_questionsFront', ['idquiz' => $idquiz]);
    }

    return $this->render('Front_office/exam_management/questionevaluation/add.html.twig', [
        'form' => $form->createView(),
        'idquiz' => $idquiz,
    ]);
}



}

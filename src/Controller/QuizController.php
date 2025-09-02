<?php

namespace App\Controller;

use App\Repository\QuizRepository ;

use App\Repository\UserRepository ;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Quiz;
use App\Entity\User;

use App\Form\QuizType;
use App\Entity\QuestionEvaluation;
use App\Form\QuestionEvaluationType; 
use App\Entity\QuizResult;
use App\Repository\QuizResultRepository;
use App\Repository\QuestionEvaluationRepository ;
use App\Form\QuizResultType ;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Log\LoggerInterface;
use Shuchkin\SimpleXLSX;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use DateTimeImmutable;


use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\Alignment\LabelAlignmentLeft;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\ErrorCorrectionLevel;

use App\Controller\ExportController;
use Symfony\Component\Form\FormBuilderInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Form\Extension\Core\Type\FileType;

use App\Repository\CategoriesRepository;
use App\Entity\Categories;


use Symfony\Component\VarDumper\VarDumper;

class QuizController extends AbstractController
{
    #[Route("/quiz-management", name:"quiz_management_index")]
    
    public function index(QuizRepository $quizRepository): Response
    {
        // Fetch all forms using the repository method
        $quizzes = $quizRepository->findAll();
        $categories = $this->getDoctrine()->getRepository(Categories::class)->findAll();

        // Render the template and pass the list of forms
        return $this->render('Back_office/exam_management/quiz/show.html.twig', [
            'quizzes' => $quizzes,
            'categories' => $categories,   // Pass the categories to the template

        ]);
    }
    #[Route("/quiz/{idquiz}/delete'", name:"app_quiz_delete", methods:["GET"])]
     
    public function deleteQuiz(Request $request, QuizRepository $quizrepository, EntityManagerInterface $entityManager): Response
    {
        $quizid = $request->get('idquiz');

        // Fetch the form entity by ID from the repository
        $quiz = $quizrepository->find($quizid);

        // Check if the form can be deleted
        // For example, you may have some validation logic here
        // If the form can be deleted, proceed with deletion
        $entityManager->remove($quiz);
        $entityManager->flush();

        // Redirect back to the same page after deletion
        return $this->redirectToRoute('quiz_management_index');
    }


    #[Route("/quiz/{idquiz}/edit'", name:"app_quiz_edit")]

public function editForm(Request $request, QuizRepository $quizRepository, EntityManagerInterface $entityManager, int $idquiz, CategoriesRepository $categoriesRepository): Response
{
    // Fetch the form entity by ID from the repository
    $quiz = $quizRepository->find($idquiz);
   // Fetch categories from the repository
   $categories = $categoriesRepository->findAll();
    // Create the form using the FormType and pass the form entity
    $form = $this->createForm(QuizType::class, $quiz);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        // Redirect to form management index page after form update
        return $this->redirectToRoute('quiz_management_index');
    }

    return $this->render('Back_office/exam_management/quiz/edit.html.twig', [
        'form' => $form->createView(),
        'quiz' => $quiz, // Pass the quiz entity to the template
        'categories' => $categories,   // Pass the categories to the template


    ]);
}


#[Route("/quiz-management/create", name: "app_quiz_create")]
public function createQuiz(Request $request, EntityManagerInterface $entityManager, CategoriesRepository $categoriesRepository): Response
{
    $quiz = new Quiz();
    $categories = $categoriesRepository->findAll();

    $form = $this->createForm(QuizType::class, $quiz);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($quiz);
        $entityManager->flush();

        return $this->redirectToRoute('quiz_management_index');
    }

    return $this->render('Back_office/exam_management/quiz/new.html.twig', [
        'form' => $form->createView(),
        'categories' => $categories, // Pass the $categories variable to the Twig template

    ]);
}


#[Route("/quiz-management/{idquiz}/add-question", name: "app_quiz_add_question")]
public function addQuestion(Request $request, EntityManagerInterface $entityManager, QuizRepository $quizRepository, int $idquiz, CategoriesRepository $categoriesRepository): Response
{
    // Fetch the quiz entity by ID
    $quiz = $quizRepository->find($idquiz);
  // Fetch categories from the repository
  $categories = $categoriesRepository->findAll();
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
        return $this->redirectToRoute('quiz_management_index', ['idquiz' => $idquiz]);
    }

    return $this->render('Back_office/exam_management/questionevaluation/add.html.twig', [
        'form' => $form->createView(),
        'quiz' => $quiz,
        'categories' => $categories,   // Pass the categories to the template

    ]);
    
}

#[Route("/QuizManagement/{idquiz}/questions", name:"view_questions")]
public function viewQuestions(int $idquiz, EntityManagerInterface $entityManager, CategoriesRepository $categoriesRepository): Response
{
    // Get the question entities associated with the given form ID using DQL
    $query = $entityManager->createQuery(
        'SELECT q FROM App\Entity\QuestionEvaluation q WHERE q.quiz= :idquiz'
    )->setParameter('idquiz', $idquiz);

    $questions = $query->getResult();
    $categories = $categoriesRepository->findAll();

    return $this->render('Back_office/exam_management/questionevaluation/showquestion.html.twig', [
        'questions' => $questions,
        'idquiz' => $idquiz,
        'categories' => $categories, // Pass the $categories variable to the Twig template

    ]);
}


#[Route("/QuizManagement/{idquiz}/export-excel", name: "export_questions_excel", methods:["GET","POST"])]
public function generateExcel1(int $idquiz,EntityManagerInterface $entityManager): Response
{
    // $filename = $excelController->generateQuestionsExcel();

    // // Generate the full path to the Excel file
    // $filePath = $this->getParameter('kernel.project_dir') . '/public/excel/' . $filename;

    // // Check if the file exists
    // if (!file_exists($filePath)) {
    //     throw $this->createNotFoundException('The file does not exist.');
    // }

    // // Return the file as a response
    // return  $this->file($filePath);
    //     //return $idquiz;


    // Create a new Spreadsheet
     $spreadsheet = new Spreadsheet();
     $sheet = $spreadsheet->getActiveSheet();

     // Add headers
     $sheet->setCellValue('A1', 'ID');
     $sheet->setCellValue('B1', 'Subject');
     $sheet->setCellValue('C1', 'Question');
     $sheet->setCellValue('D1', 'Answer');
     $sheet->setCellValue('E1', 'Description');
     $sheet->setCellValue('F1', 'Option 1');
     $sheet->setCellValue('G1', 'Option 2');
     $sheet->setCellValue('H1', 'Option 3');

     // Fetch questions from repository
     $query = $entityManager->createQuery(
        'SELECT q FROM App\Entity\QuestionEvaluation q WHERE q.quiz= :idquiz'
    )->setParameter('idquiz', $idquiz);

    $questions = $query->getResult();

     // Populate data
     $row = 2;
     foreach ($questions as $question) {
         $sheet->setCellValue('A'.$row, $question->getIdQuestion());
         $sheet->setCellValue('B'.$row, $question->getSubject());
         $sheet->setCellValue('C'.$row, $question->getQuestion());
         $sheet->setCellValue('D'.$row, $question->getAnswer());
         $sheet->setCellValue('E'.$row, $question->getDescription());
         $sheet->setCellValue('F'.$row, $question->getOption1());
         $sheet->setCellValue('G'.$row, $question->getOption2());
         $sheet->setCellValue('H'.$row, $question->getOption3());

         // Add more columns as needed
         $row++;
     }

     // Save Excel file
     $filename = 'questions_' . date('Ymd_His') . '.xlsx';
     $excelDirectory = $this->getParameter('kernel.project_dir') . '/public/excel';
     if (!is_dir($excelDirectory)) {
         mkdir($excelDirectory, 0777, true);
     }
     $writer = new Xlsx($spreadsheet);
     $writer->save($excelDirectory . '/' . $filename);

     // Return a response with the Excel file
     return $this->file($excelDirectory . '/' . $filename);
  
   
    }

    

 
   
    
  /////////////  , CategoriesRepository $categoriesRepository

#[Route("/QuizManagement/{idquiz}/details", name: "quiz_details")]
public function viewQuizDetails(int $idquiz, QuizRepository $QuizRepository, EntityManagerInterface $entityManager): Response
{
    // Fetch the form entity by ID
    $quiz = $QuizRepository->find($idquiz);

    // Fetch the questions associated with the form
    $questions = $quiz->getQuestionEvaluation();

    // Render the form details view and pass the form, questions, and form ID
    return $this->render('Back_office/exam_management/quiz/quiz_details.html.twig', [
        'quiz' => $quiz,
        'questions' => $questions,
        'idquiz' => $idquiz,
       // 'categories' => $categories, // Pass the $categories variable to the Twig template

    ]);
}

#[Route("/QuizManagement/{idquiz}/details_student", name: "quiz_details_student")]
public function viewQuizDetails_student(int $idquiz, QuizRepository $QuizRepository, EntityManagerInterface $entityManager): Response
{
    // Fetch the form entity by ID
    $quiz = $QuizRepository->find($idquiz);

    // Fetch the questions associated with the form
    $questions = $quiz->getQuestionEvaluation();

    // Render the form details view and pass the form, questions, and form ID
    return $this->render('Back_office/exam_management/quizstudent/quizdetailsstudent.html.twig', [
        'quiz' => $quiz,
        'questions' => $questions,
        'idquiz' => $idquiz,
    ]);
}

#[Route("/quiz-management/consult", name:"quiz_consult")]
    
    public function listquiz(QuizRepository $quizRepository): Response
    {
        // Fetch all forms using the repository method
        $quizzes = $quizRepository->findAll();

        // Render the template and pass the list of forms
        return $this->render('Back_office/exam_management/quizstudent/quizlist.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }



    #[Route("/QuizManagement/{idquiz}/submit", name: "submit_quiz")]
public function submitQuiz(
    Request $request,
    int $idquiz,
    QuestionEvaluationRepository $questionEvaluationRepository,
    QuizResultRepository $quizResultRepository,
    QuizRepository $quizRepository
): Response {
    // Retrieve the submitted answers from the form data
    $submittedAnswers = $request->request->get('answer');

    // Retrieve the correct answers and descriptions for the questions in this quiz
    $quiz = $quizRepository->find($idquiz);
    $questionEvaluations = $questionEvaluationRepository->findBy(['quiz' => $quiz]);

    $quizResultData = [];
    foreach ($questionEvaluations as $questionEvaluation) {
        $questionId = $questionEvaluation->getIdQuestion();
        $selectedOption = $submittedAnswers[$questionId] ?? null;
        $correctOption = $questionEvaluation->getAnswer();
        $description = $questionEvaluation->getDescription();
        $questionText = $questionEvaluation->getQuestion(); // Retrieve question text

        $quizResultData[] = [
            'questionId' => $questionId,
            'questionText' => $questionText,
            'selectedOption' => $selectedOption,
            'correctOption' => $correctOption,
            'description' => $description,
        ];
    }

    // Calculate the score based on the submitted answers and correct answers
    $score = 0;
    foreach ($submittedAnswers as $questionId => $selectedOption) {
        $questionEvaluation = $questionEvaluationRepository->findOneBy(['idQuestion' => $questionId]);
        if ($questionEvaluation && $selectedOption === $questionEvaluation->getAnswer()) {
            // Increment the score if the selected option matches the correct answer
            $score += 1;
        }
    }

    // Create a new QuizResult entity and save the result
    $quizResult = new QuizResult();
    $quizResult->setQuiz($quiz); // Set the Quiz entity
    $quizResult->setScore($score);
    $quizResult->setUserId(1); // Set the user ID to null or any default value
    $quizResult->setQuestionnumber(count($submittedAnswers)); // Set the number of questions submitted

    // Ensure that the quizsubmitted property is not null
    // Assuming $quiz is the Quiz entity associated with the quiz submission
    $quizResult->setQuizSubmitted($quiz);

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($quizResult);
    $entityManager->flush();

    // Pass the quiz result data to the template
    return $this->render('Back_office/exam_management/quizstudent/quizresult.html.twig', [
        'score' => $score,
        'quizResultData' => $quizResultData,
    ]);
}




private function calculateQuizResult(string $submittedAnswer, string $correctAnswer): float
{
    // Initialize score
    $score = 0;

    // Check if the submitted answer matches the correct answer
    if ($submittedAnswer === $correctAnswer) {
        // Increment score if the answer is correct
        $score = 1.0; // Ensure a float value
    }

    return $score;
}


 
/////////////////////////////////////////////////////////////////////////////FrontOffice

#[Route("/quiz-managementfront", name:"quiz_management_frontindex")]    
public function frontindex(Request $request, QuizRepository $quizRepository): Response
{ 
    // Dump the entire request data to inspect
    dump($request->request->all());
    
    // Fetch all forms using the repository method
    $quizzes = $quizRepository->findAll();

    // Render the template and pass the list of forms
    return $this->render('Front_office/exam_management/quiz/show.html.twig', [
        'quizzes' => $quizzes,
    ]);
}

#[Route("/quiz/{idquiz}/details", name: "quiz_detailsFrontOffice")]
public function viewQuizDetailsFront(int $idquiz, QuizRepository $QuizRepository, EntityManagerInterface $entityManager): Response
{
    // Fetch the form entity by ID
    $quiz = $QuizRepository->find($idquiz);

    // Fetch the questions associated with the form
    $questions = $quiz->getQuestionEvaluation();

    // Render the form details view and pass the form, questions, and form ID
    return $this->render('Front_office/exam_management/quiz/quiz_details.html.twig', [
        'quiz' => $quiz,
        'questions' => $questions,
        'idquiz' => $idquiz,
    ]);
}

#[Route("/quiz-managementFront", name:"quiz_management_indexFront")]
    
    public function indexFront(QuizRepository $quizRepository): Response
    {
        // Fetch all forms using the repository method
        $quizzes = $quizRepository->findAll();

        // Render the template and pass the list of forms
        return $this->render('Front_office/exam_management/quiz/show.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }
    #[Route("/quiz-managementFront/search", name: "search_quiz")]
    public function searchQuiz(Request $request, QuizRepository $quizRepository): Response
    {
        $searchInput = $request->request->get('searchInput');

        // Fetch quizzes based on search input or retrieve all quizzes
        if ($searchInput !== null) {
            $quizzes = $quizRepository->findBySearchInput($searchInput); // Calling the findBySearchInput method
        } else {
            $quizzes = $quizRepository->findAll();
        }

        // Render the partial quiz list template and pass the list of quizzes
        return $this->render('Front_office/exam_management/quiz/_quiz_list.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }
    #[Route("/quiz/{idquiz}/deleteFront'", name:"app_quiz_deleteFront", methods:["GET"])]
     
    public function deleteQuizFront(Request $request, QuizRepository $quizrepository, EntityManagerInterface $entityManager): Response
    {
        $quizid = $request->get('idquiz');

        // Fetch the form entity by ID from the repository
        $quiz = $quizrepository->find($quizid);

        // Check if the form can be deleted
        // For example, you may have some validation logic here
        // If the form can be deleted, proceed with deletion
        $entityManager->remove($quiz);
        $entityManager->flush();

        // Redirect back to the same page after deletion
        return $this->redirectToRoute('quiz_management_indexFront');
    }


    #[Route("/quiz/{idquiz}/editFront'", name:"app_quiz_editFront")]

public function editFormFront(Request $request, QuizRepository $quizRepository, EntityManagerInterface $entityManager, int $idquiz): Response
{
    // Fetch the form entity by ID from the repository
    $quiz = $quizRepository->find($idquiz);

    // Create the form using the FormType and pass the form entity
    $form = $this->createForm(QuizType::class, $quiz);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->flush();

        // Redirect to form management index page after form update
        return $this->redirectToRoute('quiz_management_indexFront');
    }

    return $this->render('Front_office/exam_management/quiz/edit.html.twig', [
        'form' => $form->createView(),
        'quiz' => $quiz, // Pass the quiz entity to the template

    ]);
}
#[Route("/quiz-management/createFront", name: "app_quiz_createFront")]
public function createQuizFront(Request $request, EntityManagerInterface $entityManager): Response
{
    $quiz = new Quiz();

    $form = $this->createForm(QuizType::class, $quiz);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Persist the quiz entity to the database
        $entityManager->persist($quiz);
        $entityManager->flush();

        // Redirect to the appropriate route after successful submission
        return $this->redirectToRoute('quiz_management_indexFront');
    }

    return $this->render('Front_office/exam_management/quiz/new.html.twig', [
        'form' => $form->createView(),
    ]);
}


#[Route("/quiz-management/{idquiz}/add-questionFront", name: "app_quiz_add_questionFront")]
public function addQuestioFront(Request $request, EntityManagerInterface $entityManager, QuizRepository $quizRepository, int $idquiz): Response
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
        return $this->redirectToRoute('app_quiz_add_questionFront', ['idquiz' => $idquiz]);
    }

    return $this->render('Front_office/exam_management/questionevaluation/add.html.twig', [
        'form' => $form->createView(),
        'quiz' => $quiz,
    ]);
    
}

#[Route("/QuizManagement/{idquiz}/questionsFront", name:"view_questionsFront")]
public function viewQuestionsFront(int $idquiz, EntityManagerInterface $entityManager): Response
{
    // Get the question entities associated with the given form ID using DQL
    $query = $entityManager->createQuery(
        'SELECT q FROM App\Entity\QuestionEvaluation q WHERE q.quiz= :idquiz'
    )->setParameter('idquiz', $idquiz);

    $questions = $query->getResult();

    return $this->render('Front_office/exam_management/questionevaluation/showquestion.html.twig', [
        'questions' => $questions,
        'idquiz' => $idquiz,
    ]);
}


#[Route("/QuizManagement/{idquiz}/export-excelFront", name: "export_questions_excelFront", methods:["GET","POST"])]
public function generateExcel1Front(int $idquiz,EntityManagerInterface $entityManager): Response
{
    // $filename = $excelController->generateQuestionsExcel();

    // // Generate the full path to the Excel file
    // $filePath = $this->getParameter('kernel.project_dir') . '/public/excel/' . $filename;

    // // Check if the file exists
    // if (!file_exists($filePath)) {
    //     throw $this->createNotFoundException('The file does not exist.');
    // }

    // // Return the file as a response
    // return  $this->file($filePath);
    //     //return $idquiz;


    // Create a new Spreadsheet
     $spreadsheet = new Spreadsheet();
     $sheet = $spreadsheet->getActiveSheet();

     // Add headers
     $sheet->setCellValue('A1', 'ID');
     $sheet->setCellValue('B1', 'Subject');
     $sheet->setCellValue('C1', 'Question');
     $sheet->setCellValue('D1', 'Answer');
     $sheet->setCellValue('E1', 'Description');
     $sheet->setCellValue('F1', 'Option 1');
     $sheet->setCellValue('G1', 'Option 2');
     $sheet->setCellValue('H1', 'Option 3');

     // Fetch questions from repository
     $query = $entityManager->createQuery(
        'SELECT q FROM App\Entity\QuestionEvaluation q WHERE q.quiz= :idquiz'
    )->setParameter('idquiz', $idquiz);

    $questions = $query->getResult();

     // Populate data
     $row = 2;
     foreach ($questions as $question) {
         $sheet->setCellValue('A'.$row, $question->getIdQuestion());
         $sheet->setCellValue('B'.$row, $question->getSubject());
         $sheet->setCellValue('C'.$row, $question->getQuestion());
         $sheet->setCellValue('D'.$row, $question->getAnswer());
         $sheet->setCellValue('E'.$row, $question->getDescription());
         $sheet->setCellValue('F'.$row, $question->getOption1());
         $sheet->setCellValue('G'.$row, $question->getOption2());
         $sheet->setCellValue('H'.$row, $question->getOption3());

         // Add more columns as needed
         $row++;
     }

     // Save Excel file
     $filename = 'questions_' . date('Ymd_His') . '.xlsx';
     $excelDirectory = $this->getParameter('kernel.project_dir') . '/public/excel';
     if (!is_dir($excelDirectory)) {
         mkdir($excelDirectory, 0777, true);
     }
     $writer = new Xlsx($spreadsheet);
     $writer->save($excelDirectory . '/' . $filename);

     // Return a response with the Excel file
     return $this->file($excelDirectory . '/' . $filename);
  
   
    }



    
#[Route("/QuizManagement/{idquiz}/import", name: "import_excel")]
public function importExcelAction(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, int $idquiz): Response
{
    // Get the uploaded file
    $uploadedFile = $request->files->get('excel_file');

    // Check if a file is uploaded and has a valid extension
    if ($uploadedFile instanceof UploadedFile && in_array($uploadedFile->getClientOriginalExtension(), ['xls', 'xlsx'])) {
        try {
            // Start a transaction
            $entityManager->beginTransaction();

            // Get the Quiz entity
            $quiz = $entityManager->getRepository(Quiz::class)->find($idquiz);
            if (!$quiz) {
                throw new \Exception('Quiz not found for ID: ' . $idquiz);
            }

            // Save the file to the project's path
            $fileDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
            $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
            $uploadedFile->move($fileDirectory, $fileName);

            // Log file details
            $logger->info('Uploaded file saved:', ['filename' => $fileName, 'size' => $uploadedFile->getSize()]);

            // Load the Excel file
            $spreadsheet = IOFactory::load($fileDirectory . '/' . $fileName);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = [];

            // Iterate over rows and extract data
            foreach ($worksheet->getRowIterator() as $row) {
                $rowData = [];
                foreach ($row->getCellIterator() as $cell) {
                    $rowData[] = $cell->getValue();
                }
                $data[] = $rowData;
            }

            // Log extracted data
            $logger->info('Extracted data:', ['data' => $data]);

            foreach ($data as $rowData) {
                // Validate data
                if (count($rowData) === 8) {
                    // Create a new instance of the QuestionEvaluation entity
                    $question = new QuestionEvaluation();
                    
                    $question->setSubject($rowData[1]);
                    $question->setQuestion($rowData[2]);
                    $question->setAnswer($rowData[3]);
                    $question->setDescription($rowData[4]);
                    $question->setOption1($rowData[5]);
                    $question->setOption2($rowData[6]);
                    $question->setOption3($rowData[7]);

                    // Set the quiz reference
                    $question->setQuiz($quiz);

                    // Persist the entity
                    $entityManager->persist($question);
                } else {
                    $logger->error('Invalid row data:', ['data' => $rowData]);
                }
            }

            // Flush changes to the database
            $entityManager->flush();

            // Commit the transaction
            $entityManager->commit();

            // Redirect to view_questions route with the quiz ID
            return $this->redirectToRoute('view_questions', ['idquiz' => $idquiz]);
        } catch (\Exception $e) {
            // Roll back the transaction in case of error
            $entityManager->rollback();

            // Log error for any exception during file processing
            $logger->error('Exception occurred while processing the Excel file:', ['message' => $e->getMessage()]);
            // Return a response indicating the error
            return new Response('An error occurred while processing the Excel file.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    } else {
        // Return a response indicating invalid or missing file
        return new Response('Invalid or missing file. Please upload an Excel file with .xls or .xlsx extension.', Response::HTTP_BAD_REQUEST);
    }
}
    #[Route("/quiz-management/consultFront", name:"quiz_consultFront")]
    
    public function listquizFront(QuizRepository $quizRepository,UserRepository $userRepository): Response
    {
        $user = $userRepository->find(3); // Replace 3 with the actual user ID if needed

        // Fetch all forms using the repository method
        $quizzes = $quizRepository->findAll();

        // Render the template and pass the list of forms
        return $this->render('Front_office/exam_management/quizstudent/quizlist.html.twig', [
            'quizzes' => $quizzes,
        ]);
    }
    #[Route("/QuizManagement/{idquiz}/details_studentFront", name: "quiz_details_studentFront")]
    public function viewQuizDetails_studentFront(int $idquiz, QuizRepository $QuizRepository, EntityManagerInterface $entityManager): Response
    {
        // Fetch the form entity by ID
        $quiz = $QuizRepository->find($idquiz);
        
        // Check if the quiz has expired
        $quizExpired = $this->isQuizExpired($quiz);
        
        // Fetch the questions associated with the form
        $questions = $quiz->getQuestionEvaluation();
    
        // Render the form details view and pass the form, questions, and form ID
        return $this->render('Front_office/exam_management/quizstudent/quizdetailsstudent.html.twig', [
            'quiz' => $quiz,
            'questions' => $questions,
            'idquiz' => $idquiz,
            'quizExpired' => $quizExpired, // Pass the quizExpired variable to the Twig template
        ]);
    }
    
private function isQuizExpired($quiz): bool
{
    // Get the creation date of the quiz (assuming it represents the deadline)
    $creationDate = $quiz->getDatecreated();
    
    // Get the current date and time
    $currentDate = new DateTimeImmutable();
    
    // Check if the current date is after the creation date (deadline)
    return $currentDate > $creationDate;
}

#[Route("/QuizManagement/{idquiz}/submitFront", name: "submit_quizFront")]
public function submitQuizFront(
    Request $request,
    int $idquiz,
    QuestionEvaluationRepository $questionEvaluationRepository,
    QuizResultRepository $quizResultRepository,
    QuizRepository $quizRepository,
    UserRepository $userRepository
): Response {
    // Retrieve the submitted answers from the form data
    $submittedAnswers = $request->request->get('answer');

    // Retrieve the correct answers and descriptions for the questions in this quiz
    $quiz = $quizRepository->find($idquiz);
    $questionEvaluations = $questionEvaluationRepository->findBy(['quiz' => $quiz]);
    // Get the user entity (assuming user_id is static)
    $user = $userRepository->find(3); // Replace 3 with the actual user ID if needed

  
  
  
  $quizResultData = [];
    foreach ($questionEvaluations as $questionEvaluation) {
        $questionId = $questionEvaluation->getIdQuestion();
        $selectedOption = $submittedAnswers[$questionId] ?? null;
        $correctOption = $questionEvaluation->getAnswer();
        $description = $questionEvaluation->getDescription();
        $questionText = $questionEvaluation->getQuestion(); // Retrieve question text

        $quizResultData[] = [
            'questionId' => $questionId,
            'questionText' => $questionText,
            'selectedOption' => $selectedOption,
            'correctOption' => $correctOption,
            'description' => $description,
        ];
    }

    // Calculate the score based on the submitted answers and correct answers
    $score = 0;
    foreach ($submittedAnswers as $questionId => $selectedOption) {
        $questionEvaluation = $questionEvaluationRepository->findOneBy(['idQuestion' => $questionId]);
        if ($questionEvaluation && $selectedOption === $questionEvaluation->getAnswer()) {
            // Increment the score if the selected option matches the correct answer
            $score += 1;
        }
    }

    // Create a new QuizResult entity and save the result
    $quizResult = new QuizResult();
    $quizResult->setQuiz($quiz); // Set the Quiz entity
    $quizResult->setScore($score);
    $quizResult->setUser($user); // Set the user entity is a static user
    $quizResult->setQuestionnumber(count($submittedAnswers)); // Set the number of questions submitted

    // Ensure that the quizsubmitted property is not null
    // Assuming $quiz is the Quiz entity associated with the quiz submission
    $quizResult->setQuizSubmitted($quiz);

    $entityManager = $this->getDoctrine()->getManager();
    $entityManager->persist($quizResult);
    $entityManager->flush();

    // Pass the quiz result data to the template
    return $this->render('Front_office/exam_management/quizstudent/quizresult.html.twig', [
        'score' => $score,
        'quizResultData' => $quizResultData,
    ]);
}
#[Route("/QuizManagement/{idquiz}/generate-qr-codeFront", name: "generate-qr-codeFront")]
public function generateQrCode(int $idquiz, UrlGeneratorInterface $urlGenerator): Response
{
    // Generate the URL for the quizdetailsstudent route
    $quizDetailsUrl = $urlGenerator->generate('quiz_details_studentFront', ['idquiz' => $idquiz], UrlGeneratorInterface::ABSOLUTE_URL);
   // echo "Generated URL: " . $quizDetailsUrl; // or use Symfony's logger: $this->logger->info("Generated URL: " . $quizDetailsUrl);
  
    // Generate the QR code
    $qrCodeImage = $this->generateQrCodeImage($quizDetailsUrl);
////ecample
   // URL to encode in the QR code
   //$url = 'https://www.google.com';

   // Generate the QR code
   //$qrCodeImage = $this->generateQrCodeImage($url);
    // Output the QR code image to the browser
    $response = new Response($qrCodeImage, Response::HTTP_OK, ['Content-Type' => 'image/png']);

    return $response;
}

private function generateQrCodeImage(string $url): string
{
    // Set up the QR code with the provided URL
    $qrCode = QrCode::create($url)
        ->setSize(600) // Set the size of the QR code
        ->setMargin(40) // Set the margin
        ->setForegroundColor(new Color(255, 128, 0)) // Set foreground color
        ->setBackgroundColor(new Color(153, 204, 255)); // Set background color
        //->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh); // Use the correct class

    // Define the label
    //$label = Label::create("This is a label")
    //    ->setTextColor(new Color(255, 0, 0))
      //  ->setAlignment(new LabelAlignmentLeft);

    // Define the path to your logo image file and set its width
    //$logoPath = "/public/img/logo.png"; // Update this with the path to your logo
    //$logo = Logo::create($logoPath)
       // ->setResizeToWidth(150); // Adjust the width if needed

    // Set up the QR code writer
    $writer = new PngWriter;

    // Generate the QR code with the logo and label

    $result = $writer->write($qrCode);//, label: $label, logo: $logo
    // Return the QR code image as a string
    return $result->getString();
}
public function searchQuizzesFront(Request $request)
{
    // Get the search input from the request
    $searchInput = $request->request->get('searchInput');

    // Query the database to retrieve matching quizzes
    $entityManager = $this->getDoctrine()->getManager();
    $query = $entityManager->createQuery(
        'SELECT q FROM App\Entity\Quiz q
        WHERE q.title LIKE :searchInput
        OR q.subject LIKE :searchInput
        OR q.nbQuestions LIKE :searchInput'
    )->setParameter('searchInput', '%'.$searchInput.'%');

    $quizzes = $query->getResult();

    // Render the Twig template with the search results
   // return $this->render('/FrontOffice/quiz/show.html.twig', [
       // 'quizzes' => $quizzes,
   // ]);
}

#[Route('/quiz-submittedFront', name: 'quiz-submitted_Front')]
public function quizSubmitted(QuizResultRepository $quizResultRepository): Response
{
    // Fetch quiz results with associated user and quiz details
    $quizResults = $quizResultRepository->findAllWithUserAndQuiz();

    return $this->render('Front_office/exam_management/teacher/quizsubmitted.html.twig', [
        'quizResults' => $quizResults,
    ]);
}
#[Route("/quiz-resultstudentFront", name:"quiz-resultstudentFront")]
public function showQuizResults(QuizRepository $quizRepository, QuizResultRepository $quizResultRepository): Response
{
    // Fetch quiz statistics from the repository
    $quizStatistics = $quizResultRepository->getQuizStatistics();

    // Fetch quiz data from the repository
    $quizData = $quizResultRepository->getQuizData();

    // Fetch all quizzes from the database
    $quizzes = $quizRepository->findAll();

    // Pass the fetched data to the template
    return $this->render('Front_office/exam_management/teacher/resultstudent.html.twig', [
        'quizStatistics' => $quizStatistics,
        'quizzes' => $quizzes,
        'quizData' => $quizData,
    ]);
}



    
}


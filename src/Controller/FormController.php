<?php

namespace App\Controller;
use App\Entity\Categories;
use App\Form\FormType;
use App\Repository\FormRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Entity\Question;
use App\Entity\Form;
use App\Entity\Answer;
use App\Entity\Formsubmitted;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\User;
use App\Repository\CategoriesRepository;


class FormController extends AbstractController
{
    private $entityManager;
    private $mailer;
    private $categories;

    
    public function __construct(EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    #[Route("/form-management", name:"form_management_index")]
    
    public function index(FormRepository $formRepository, Request $request): Response
{
    // Get the search query from the request
    $searchQuery = $request->query->get('search');

    // Fetch forms based on the search query, or all forms if no search query is provided
    $forms = $searchQuery ? $formRepository->findByTitle($searchQuery) : $formRepository->findAllForms();

    $categories = $this->getDoctrine()->getRepository(Categories::class)->findAll();


    // Render the template and pass the list of forms
    return $this->render('Back_office/form_management/form.html.twig', [
        'forms' => $forms,
        'searchQuery' => $searchQuery, // Pass the search query to the template
        'categories' => $categories,   // Pass the categories to the template
    ]);
}

    
  #[Route("/form/{id}/delete", name:"delete_form", methods:["GET"])]
     
    public function deleteForm(Request $request, FormRepository $formRepository, EntityManagerInterface $entityManager): Response
    {
        $formId = $request->get('id');

        // Fetch the form entity by ID from the repository
        $form = $formRepository->find($formId);

        // Check if the form can be deleted
        // For example, you may have some validation logic here
        // If the form can be deleted, proceed with deletion
        $entityManager->remove($form);
        $entityManager->flush();
        

        // Redirect back to the same page after deletion
        return $this->redirectToRoute('form_management_index');
    }


    #[Route("/form-management/create", name: "create_form")]
    public function createFormAction(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, CategoriesRepository $categoriesRepository): Response
    {
        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);
        
        // Fetch categories from the repository
        $categories = $categoriesRepository->findAll();
    
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $entityManager->persist($formData);
            $entityManager->flush();
    
            // Get all user email addresses
            $emailAddresses = $userRepository->getAllEmailAddresses();
    
            // Send customized emails to all users
            $this->sendCustomizedEmails($emailAddresses, $formData);
    
            return $this->redirectToRoute('form_management_index');
        }
    
        return $this->render('Back_office/form_management/create_form.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories, // Pass the $categories variable to the Twig template
        ]);
    }
    
    

private function sendCustomizedEmails(array $emailAddresses)
{
    // Loop through each email address
    foreach ($emailAddresses as $userEmail) {
        // Create a new email instance
        $email = (new TemplatedEmail())
            ->from('fatma.naoui25@gmail.com')
            ->to($userEmail)
            ->subject('New Form');

        // Render the Twig template with the form data
        $htmlContent = $this->renderView('Back_office/form_management/mail.html.twig', [
        ]);

        // Set the HTML content of the email to the rendered template
        $email->html($htmlContent);

        // Send the email
        $this->mailer->send($email);
    }
}

#[Route("/form/{id}/edit", name:"edit_form")]
public function editForm(Request $request, FormRepository $formRepository, EntityManagerInterface $entityManager, int $id, CategoriesRepository $categoriesRepository): Response
{
    // Fetch the form entity by ID from the repository
    $form = $formRepository->find($id);

    // Create the form using the FormType and pass the form entity
    $form = $this->createForm(FormType::class, $form);
    $form->handleRequest($request);

    // Fetch categories from the repository
    $categories = $categoriesRepository->findAll();

    if ($form->isSubmitted() && $form->isValid()) {
        // Handle form submission, e.g., update the entity in the database
        $entityManager->flush();

        // Redirect to form management index page after form update
        return $this->redirectToRoute('form_management_index');
    }

    return $this->render('Back_office/form_management/edit_form.html.twig', [
        'form' => $form->createView(),
        'categories' => $categories, // Pass the $categories variable to the Twig template
    ]);
}

    
#[Route("/form/{formId}/questions", name:"view_questions")]
public function viewQuestions(int $formId, EntityManagerInterface $entityManager, CategoriesRepository $categoriesRepository): Response
{
    // Get the question entities associated with the given form ID using DQL
    $query = $entityManager->createQuery(
        'SELECT q FROM App\Entity\Question q WHERE q.form = :formId'
    )->setParameter('formId', $formId);

    $questions = $query->getResult();

    // Fetch categories from the repository
    $categories = $categoriesRepository->findAll();

    return $this->render('Back_office/form_management/questions.html.twig', [
        'questions' => $questions,
        'formId' => $formId,
        'categories' => $categories, // Pass the $categories variable to the Twig template
    ]);
}

#[Route("/forms", name:"form_index")]
public function showForms(FormRepository $formRepository): Response
{
    // Fetch only active forms using the repository method
    $activeForms = $formRepository->findBy(['status' => 'active']);

    // Render the template and pass the list of active forms
    return $this->render('Front_office/form/forms.html.twig', [
        'forms' => $activeForms,
    ]);
}


#[Route("/form/{formId}/details", name: "form_details")]
public function viewFormDetails(int $formId, FormRepository $formRepository, EntityManagerInterface $entityManager, UserRepository $userRepository, Request $request): Response
{
    // Fetch the form entity by ID
    $form = $formRepository->find($formId);

    // Fetch the questions associated with the form
    $questions = $form->getQuestion();

    // Get the user by ID (assuming user_id is static) badel hedhi bl currently logged in user lezem ykoun teacher or student ofc
    $user = $userRepository->find(3); // Replace 3 with the actual user ID if needed

    // Check if the form is submitted
    if ($request->isMethod('POST')) {
        $formData = $request->request->get('questions');

        // Check if $formData is an array
        if (!is_array($formData)) {
            // Handle the case where $formData is not an array
            // You can log an error, throw an exception, or handle it in another way
            // For now, let's just return a response with an error message
            return new Response('Form data is not in the correct format', Response::HTTP_BAD_REQUEST);
        }

        // Iterate through the submitted data and save it in FormSubmitted entity
        foreach ($formData as $questionId => $answerId) {
            // Fetch the question and answer entities by IDs
            $question = $entityManager->getReference(Question::class, $questionId);
            $answer = $entityManager->getReference(Answer::class, $answerId);

            // Create a new FormSubmitted entity
            $formSubmitted = new FormSubmitted();
            $formSubmitted->setUser($user);
            $formSubmitted->setForm($form);
            $formSubmitted->setQuestion($question);
            $formSubmitted->setAnswer($answer);

            // Persist the FormSubmitted entity
            $entityManager->persist($formSubmitted);
        }

        // Flush all changes to the database
        $entityManager->flush();

        // Redirect to some success page or back to form details page
        return $this->redirectToRoute('form_index');
    }

    // Render the form details view and pass the form, questions, and form ID
    return $this->render('Front_office/form/form_details.html.twig', [
        'form' => $form,
        'questions' => $questions,
        'formId' => $formId,
    ]);
}
#[Route('/reports', name: 'form_reports')]
public function viewFormReports(CategoriesRepository $categoriesRepository): Response
{
    // Fetch total and active forms counts
    $totalFormsCount = $this->getTotalFormsCount($this->entityManager);
    $activeFormsCount = $this->getActiveFormsCount($this->entityManager);

    // Fetch forms from the database
    $forms = $this->entityManager->getRepository(Form::class)->findAll();

    // Fetch categories from the repository
    $categories = $categoriesRepository->findAll();

    // Render the template and pass necessary data
    return $this->render('Back_office/form_management/reports.html.twig', [
        'totalFormsCount' => $totalFormsCount,
        'activeFormsCount' => $activeFormsCount,
        'forms' => $forms,
        'categories' => $categories, // Pass the categories variable to the Twig template
    ]);
}

private function extractAnswersSubmitted(int $formId, EntityManagerInterface $entityManager): array
{
    // Fetch form submissions by form ID
    $answersSubmitted = $entityManager->getRepository(Formsubmitted::class)->findBy(['form' => $formId]);

    $answers = [];

    // Iterate through form submissions
    foreach ($answersSubmitted as $formSubmission) {
        $answerText = $formSubmission->getAnswer()->getPredefans(); 
        $answers[] = $answerText;
    }

    return $answers;
}

private function getAnswerFromAPI(array $answers): string
{
    // Concatenate all answers into a single string with the message
    $allAnswers = 'Can you give me the overall sentiment of the users (like this the users are mostly (insert feeling here)) and dont say anything else' . implode(' the users are ', $answers);

    // Prepare the data to send to the API
    $requestData = [['content' => $allAnswers, 'role' => 'user']];

    $curl = curl_init();
    
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://chatgpt-api8.p.rapidapi.com/",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($requestData),
        CURLOPT_HTTPHEADER => [
            "X-RapidAPI-Host: chatgpt-api8.p.rapidapi.com",
            "X-RapidAPI-Key: 9f1c3fc389msh4a6a2a6e30b7acfp1cdbbdjsnadd2d6a93851",
            "content-type: application/json"
        ],
    ]);
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    
    curl_close($curl);
    
    if ($err) {
        return "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

#[Route('/process/{formId}', name: 'process_answers')]
public function processAnswersAction(int $formId, EntityManagerInterface $entityManager): Response
{
    // Extract answers submitted for the given form ID
    $answers = $this->extractAnswersSubmitted($formId, $entityManager);

    // Call API to process the answers
    $apiResponse = $this->getAnswerFromAPI($answers);

    // Decode the JSON string to an array
    $apiResponseArray = json_decode($apiResponse, true);

    // Extract the 'text' value from the API response
    $text = isset($apiResponseArray['text']) ? $apiResponseArray['text'] : '';

    // Render the response using Twig
    return $this->render('analysis.html.twig', [
        'apiResponse' => $text, // Assigning the 'text' value to 'apiResponse'
    ]);
}

   private function getTotalFormsCount(EntityManagerInterface $entityManager): int
    {
        return $entityManager
            ->getRepository(Form::class)
            ->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function getActiveFormsCount(EntityManagerInterface $entityManager): int
    {
        return $entityManager
            ->getRepository(Form::class)
            ->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->where('f.status = :status')
            ->setParameter('status', 'active')
            ->getQuery()
            ->getSingleScalarResult();
    }

    #[Route('/chart/{formId}', name: 'chart')]
    public function loadAnswerChart(int $formId, EntityManagerInterface $entityManager): Response
    {
        try {
            
            // Fetch form submissions by form ID
            $formSubmissions = $entityManager->getRepository(Formsubmitted::class)->findBy(['form' => $formId]);
    
            // Prepare an array to hold answer counts
            $answerCounts = [];
    
            // Iterate through form submissions
            foreach ($formSubmissions as $formSubmission) {
                $answerText = $formSubmission->getAnswer()->getPredefans(); 
    
                // Increment answer count
                if (!isset($answerCounts[$answerText])) {
                    $answerCounts[$answerText] = 1;
                } else {
                    $answerCounts[$answerText]++;
                }
            }
    
            // Prepare the data for the chart
            $chartData = [];
            foreach ($answerCounts as $answerText => $count) {
                $chartData[] = ['answer' => $answerText, 'count' => $count];
            }
    
            // Return JSON response with the chart data
            return $this->json($chartData);
    
        } catch (\Exception $e) {
            // Handle any exceptions
            return new Response('Error: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/download-form-reports', name: 'app_download_form_reports')]
    public function downloadFormReports(Request $request, FormRepository $formRepository, EntityManagerInterface $entityManager): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
    
        // Fetch forms from the repository (replace 'Form' with your actual entity)
        $totalFormsCount = $this->getTotalFormsCount($entityManager);
        $activeFormsCount = $this->getActiveFormsCount($entityManager);
        
    
        $forms = $formRepository->findAll();
    
        // Render the HTML content using Twig template
        $html = $this->renderView('Back_office/form_management/reportspdf.html.twig', [
            'forms' => $forms,
            'totalFormsCount' => $totalFormsCount,
            'activeFormsCount' => $activeFormsCount,
            'forms' => $forms,
          // Pass the sentiment data to the template
        ]);
    
        // Load HTML content into Dompdf
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
    
        // Render the PDF
        $dompdf->render();
    
        // Set the filename for the downloaded PDF file
        $filename = 'form_reports.pdf';
    
        // Return the PDF as a response with appropriate headers
        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
    


}

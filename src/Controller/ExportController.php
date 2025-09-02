<?php

namespace App\Controller;
use App\Entity\QuestionEvaluation; // Import the Question entity
use App\Repository\QuestionEvaluationRepository; // Import the QuestionRepository
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
class ExportController extends AbstractController

{
    private $questionRepository;

    public function __construct(QuestionEvaluationRepository $questionevaluationRepository)
    {
        $this->questionevaluationRepository = $questionevaluationRepository;
    }

    #[Route('/generate/excel', name: 'generate_excel')]
    public function generateQuestionsExcel(): Response
    {
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
        $questions = $this->questionevaluationRepository->findAll();

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
}


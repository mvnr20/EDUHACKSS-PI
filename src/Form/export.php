<?php 
 

 
// Include XLSX generator library 
require_once 'PhpXlsxGenerator.php'; 
 
// Excel file name for download 
$fileName = "members-data_" . date('Y-m-d') . ".xlsx"; 
 
// Define column names 
$excelData[] = array('idQuestion', 'subject', 'question', 'answer', 'description', 'option1', 'option2', 'option3'); 
 
// Fetch records from database and store in an array 
$query = $db->query("SELECT * FROM question_evaluation ORDER BY idQuestion ASC"); 
if($query->num_rows > 0){ 
    while($row = $query->fetch_assoc()){ 
      //  $subject = ($row['subject'] == 1)?'Active':'Inactive'; 
        $lineData = array($row['idQuestion'], $row['subject'], $row['question'], $row['answer'], $row['description'], $row['option1'], $row['option2'], $row['option3']);  
        $excelData[] = $lineData; 
    } 
} 
 
// Export data to excel and download as xlsx file 
$xlsx = CodexWorld\PhpXlsxGenerator::fromArray( $excelData ); 
$xlsx->downloadAs($fileName); 
 
exit; 
 
?>
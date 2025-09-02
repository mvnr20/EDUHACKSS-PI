<?php

require "vendor/autoload.php";

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Label\Alignment\LabelAlignmentLeft;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;

function generateQrCode(string $url): string
{
    // Set up the QR code with the provided URL
    $qrCode = QrCode::create($url)
        ->setSize(600) // Set the size of the QR code
        ->setMargin(40) // Set the margin
        ->setForegroundColor(new Color(255, 128, 0)) // Set foreground color
        ->setBackgroundColor(new Color(153, 204, 255)) // Set background color
        ->setErrorCorrectionLevel(new ErrorCorrectionLevelHigh); // Set error correction level

    // Define the label
    $label = Label::create("This is a label")
        ->setTextColor(new Color(255, 0, 0))
        ->setAlignment(new LabelAlignmentLeft);

    // Define the path to your logo image file and set its width
    $logoPath = "/path/to/your/logo.png"; // Update this with the path to your logo
    $logo = Logo::create($logoPath)
        ->setResizeToWidth(150); // Adjust the width if needed

    // Set up the QR code writer
    $writer = new PngWriter;

    // Generate the QR code with the logo and label
    $result = $writer->write($qrCode, logo: $logo, label: $label);

    // Return the QR code image as a string
    return $result->getString();
}

// Usage example:
$quizDetailsUrl = $router->generate('quizdetailsstudent', [], UrlGeneratorInterface::ABSOLUTE_URL);

$qrCodeImage = generateQrCode($quizDetailsUrl);

// Output the QR code image to the browser
header("Content-Type: image/png");
echo $qrCodeImage;
?>




//$quizDetailsUrl = $router->generate('quizdetailsstudent', [], UrlGeneratorInterface::ABSOLUTE_URL);

<?php
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader (if using Composer)
// require 'vendor/autoload.php';

// OR include PHPMailer files manually
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

$name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
$phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : 'Not provided';
$service = isset($_POST['service']) ? sanitizeInput($_POST['service']) : '';
$budget = isset($_POST['budget']) ? sanitizeInput($_POST['budget']) : 'Not specified';
$message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';

$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($service)) {
    $errors[] = 'Service selection is required';
}

if (empty($message)) {
    $errors[] = 'Message is required';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Create PHPMailer instance
$mail = new PHPMailer(true);

try {
    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // Change to your SMTP host
    $mail->SMTPAuth   = true;
    $mail->Username   = 'your-email@gmail.com'; // Your email
    $mail->Password   = 'your-app-password'; // Your app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Recipients
    $mail->setFrom('your-email@gmail.com', 'Marvab Technology');
    $mail->addAddress('info@marvabtech.com.ng', 'Marvab Technology');
    $mail->addReplyTo($email, $name);

    // Content
    $mail->isHTML(false);
    $mail->Subject = "New Contact Form Submission - Marvab Technology";
    
    $email_body = "You have received a new message from the Marvab Technology contact form.\n\n";
    $email_body .= "Contact Details:\n";
    $email_body .= "----------------------------------------\n";
    $email_body .= "Name: $name\n";
    $email_body .= "Email: $email\n";
    $email_body .= "Phone: $phone\n";
    $email_body .= "Service Interested In: $service\n";
    $email_body .= "Budget Range: $budget\n\n";
    $email_body .= "Message:\n";
    $email_body .= "----------------------------------------\n";
    $email_body .= "$message\n\n";
    $email_body .= "----------------------------------------\n";
    $email_body .= "Submission Date: " . date('Y-m-d H:i:s') . "\n";
    
    $mail->Body = $email_body;

    $mail->send();

    // Send confirmation email to user
    $mail->clearAddresses();
    $mail->addAddress($email, $name);
    $mail->Subject = "Thank you for contacting Marvab Technology";
    
    $user_message = "Dear $name,\n\n";
    $user_message .= "Thank you for reaching out to us. We have received your message and will respond within 24 hours.\n\n";
    $user_message .= "Your Message:\n$message\n\n";
    $user_message .= "Best regards,\n";
    $user_message .= "Marvab Technology Team\n";
    $user_message .= "info@marvabtech.com.ng\n";
    $user_message .= "+2347038089568";
    
    $mail->Body = $user_message;
    $mail->send();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Your message has been sent successfully! We will get back to you soon.'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send email. Please try again or contact us directly at info@marvabtech.com.ng'
    ]);
}
?>
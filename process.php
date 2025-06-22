<?php
header('Content-Type: application/json');

// Validate input
$errors = [];
$required = ['name', 'email', 'company', 'service'];

foreach ($required as $field) {
    if (empty($_POST[$field])) {
        $errors[] = "The $field field is required.";
    }
}

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

// Process data
$name = htmlspecialchars($_POST['name']);
$email = htmlspecialchars($_POST['email']);
$company = htmlspecialchars($_POST['company']);
$phone = htmlspecialchars($_POST['phone'] ?? 'Not provided');
$service = htmlspecialchars($_POST['service']);
$message = htmlspecialchars($_POST['message'] ?? 'No additional message');

// Email configuration (adapt to your server)
$to = "contact@beyondsec.com"; // Your company email
$subject = "New Security Consultation Request: $company";
$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Email template
$email_body = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #0a192f; color: #64ffda; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .footer { padding: 10px; text-align: center; font-size: 0.8em; color: #666; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        .highlight { background-color: #f0f8ff; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>BeyondSec Consultation Request</h2>
        </div>
        <div class='content'>
            <table>
                <tr class='highlight'>
                    <td><strong>From:</strong></td>
                    <td>$name ($company)</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>$email</td>
                </tr>
                <tr class='highlight'>
                    <td><strong>Phone:</strong></td>
                    <td>$phone</td>
                </tr>
                <tr>
                    <td><strong>Service Needed:</strong></td>
                    <td>$service</td>
                </tr>
                <tr class='highlight'>
                    <td><strong>Message:</strong></td>
                    <td>$message</td>
                </tr>
            </table>
        </div>
        <div class='footer'>
            <p>This request was submitted through BeyondSec's registration form.</p>
        </div>
    </div>
</body>
</html>
";

// Send email to admin
$mailSent = mail($to, $subject, $email_body, $headers);

// Confirmation email to user
if ($mailSent) {
    $user_subject = "Thank you for contacting BeyondSec";
    $user_headers = "From: contact@beyondsec.com\r\n";
    $user_headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    $user_email = "
    <html>
    <body>
        <p>Dear $name,</p>
        <p>Thank you for your interest in BeyondSec's security services. We've received your request for <strong>$service</strong> and our team will contact you within 24 hours.</p>
        <p>For urgent matters, please call our support line at +1 (555) 123-4567.</p>
        <p>Best regards,<br>The BeyondSec Team</p>
    </body>
    </html>
    ";
    
    mail($email, $user_subject, $user_email, $user_headers);
}

// Return response
if ($mailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your request has been submitted. We will contact you shortly.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'There was an error submitting your request. Please try again later.'
    ]);
}
?>

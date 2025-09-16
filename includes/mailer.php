<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function getMailer() {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = SMTP_PORT;
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    
    return $mail;
}

function sendPasswordResetEmail($email, $token) {
    try {
        $mail = getMailer();
        $reset_link = SITE_URL . "/auth/reset_token.php?token=" . $token;
        
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Reset Your Password - OrgFinPro';
        
        $body = "
            <h2>Password Reset Request</h2>
            <p>You have requested to reset your password. Click the button below to set a new password:</p>
            <p style='margin: 20px 0;'>
                <a href='{$reset_link}' 
                   style='background-color: #0d6efd; color: white; padding: 10px 20px; 
                          text-decoration: none; border-radius: 5px;'>
                    Reset Password
                </a>
            </p>
            <p>If you did not request this reset, please ignore this email.</p>
            <p>This link will expire in 1 hour.</p>
            <p>If the button above doesn't work, copy and paste this URL into your browser:</p>
            <p>{$reset_link}</p>
        ";
        
        $mail->Body = $body;
        $mail->AltBody = "Reset your password by visiting: {$reset_link}";
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Failed to send password reset email: " . $e->getMessage());
        return false;
    }
}

function sendInvitationEmail($email, $temp_password, $message = '') {
    try {
        $mail = getMailer();
        $login_link = SITE_URL . "/auth/login.php";
        
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to OrgFinPro';
        
        $body = "
            <h2>Welcome to OrgFinPro</h2>
            <p>You have been invited to join an organization. Here are your temporary login credentials:</p>
            <p><strong>Email:</strong> {$email}<br>
               <strong>Temporary Password:</strong> {$temp_password}</p>
            " . ($message ? "<p><strong>Message:</strong> {$message}</p>" : "") . "
            <p style='margin: 20px 0;'>
                <a href='{$login_link}' 
                   style='background-color: #0d6efd; color: white; padding: 10px 20px; 
                          text-decoration: none; border-radius: 5px;'>
                    Login Now
                </a>
            </p>
            <p>Please change your password after logging in for the first time.</p>
        ";
        
        $mail->Body = $body;
        $mail->AltBody = "Login at {$login_link} with email: {$email} and password: {$temp_password}";
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Failed to send invitation email: " . $e->getMessage());
        return false;
    }
}
<?php
/**
 * Mail Helper
 * 
 * Handles email sending using PHPMailer with Gmail SMTP.
 */

require_once __DIR__ . '/../../config/mail.php';

// Load PHPMailer
$phpmailerLoaded = false;

// Option 1: If using Composer
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    $phpmailerLoaded = true;
}
// Option 2: If PHPMailer is in vendor/phpmailer directory
elseif (file_exists(__DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/Exception.php';
    require_once __DIR__ . '/../../vendor/phpmailer/phpmailer/src/SMTP.php';
    $phpmailerLoaded = true;
}
// Option 3: If PHPMailer is directly in vendor
elseif (file_exists(__DIR__ . '/../../vendor/PHPMailer/PHPMailer.php')) {
    require_once __DIR__ . '/../../vendor/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/../../vendor/PHPMailer/Exception.php';
    require_once __DIR__ . '/../../vendor/PHPMailer/SMTP.php';
    $phpmailerLoaded = true;
}

if (!$phpmailerLoaded) {
    error_log("ERROR: PHPMailer library not found! Please install PHPMailer.");
    // Don't die, just return false from functions
}

/**
 * Send verification email with 6-digit code
 * 
 * @param string $to Recipient email address
 * @param string $code 6-digit verification code
 * @param string $name Recipient name (optional)
 * @return bool True on success, false on failure
 */
function send_verification_email($to, $code, $name = '') {
    // Check if PHPMailer is available
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("Cannot send email: PHPMailer class not found");
        return false;
    }
    
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        // Remove spaces from app password (Gmail displays them with spaces but they should be used without)
        $mail->Password = str_replace(' ', '', MAIL_PASSWORD);
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        $mail->CharSet = MAIL_CHARSET;

        // Enable verbose debug output (only in development)
        if (defined('ENV') && ENV === 'development') {
            $mail->SMTPDebug = 0; // Disable verbose debug in production
        }

        // Recipients
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($to, $name);

        // Content
        $mail->isHTML(MAIL_IS_HTML);
        $mail->Subject = 'Verify Your Email - ' . SITE_NAME;
        
        // Email body
        $mail->Body = get_verification_email_template($code, $name);
        $mail->AltBody = get_verification_email_plain($code);

        $mail->send();
        return true;
        
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        // Log error (essential for production debugging)
        $errorMsg = "Email sending failed to {$to}: " . $e->getMessage();
        if (isset($mail)) {
            $errorMsg .= " | PHPMailer Error: " . $mail->ErrorInfo;
        }
        error_log($errorMsg);
        return false;
    }
}

/**
 * Get HTML email template for verification
 * 
 * @param string $code Verification code
 * @param string $name User name
 * @return string HTML email body
 */
function get_verification_email_template($code, $name = '') {
    $greeting = !empty($name) ? "Hello {$name}," : "Hello,";
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px 20px; background-color: #f9f9f9; }
            .code-box { background-color: #fff; border: 2px solid #007bff; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
            .code { font-size: 32px; font-weight: bold; color: #007bff; letter-spacing: 5px; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . SITE_NAME . "</h1>
            </div>
            <div class='content'>
                <p>{$greeting}</p>
                <p>Thank you for registering! Please verify your email address by entering the verification code below:</p>
                
                <div class='code-box'>
                    <div class='code'>{$code}</div>
                </div>
                
                <p>This code will expire in 30 minutes.</p>
                <p>If you didn't create an account, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Get plain text email for verification
 * 
 * @param string $code Verification code
 * @return string Plain text email body
 */
function get_verification_email_plain($code) {
    return "
" . SITE_NAME . " - Email Verification

Thank you for registering! Please verify your email address by entering the verification code below:

Verification Code: {$code}

This code will expire in 30 minutes.

If you didn't create an account, please ignore this email.

© " . date('Y') . " " . SITE_NAME . ". All rights reserved.
    ";
}

/**
 * Send password reset email
 * 
 * @param string $toEmail Recipient email address
 * @param string $userName Recipient name
 * @param string $resetUrl Password reset URL
 * @return bool True on success, false on failure
 */
function send_password_reset_email(string $toEmail, string $userName, string $resetUrl): bool {
    // Check if PHPMailer is available
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("Cannot send email: PHPMailer class not found");
        return false;
    }
    
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USERNAME;
        // Remove spaces from app password
        $mail->Password = str_replace(' ', '', MAIL_PASSWORD);
        $mail->SMTPSecure = MAIL_ENCRYPTION;
        $mail->Port = MAIL_PORT;
        $mail->CharSet = MAIL_CHARSET;

        // Enable verbose debug output (only in development)
        if (defined('ENV') && ENV === 'development') {
            $mail->SMTPDebug = 0; // Disable verbose debug in production
        }

        // Recipients
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $userName);

        // Content
        $mail->isHTML(MAIL_IS_HTML);
        $mail->Subject = 'Reset Your Password - ' . SITE_NAME;
        
        $greeting = !empty($userName) ? "Hello {$userName}," : "Hello,";
        $mail->Body = get_password_reset_email_template($resetUrl, $userName);
        $mail->AltBody = get_password_reset_email_plain($resetUrl);

        $mail->send();
        return true;
        
    } catch (\PHPMailer\PHPMailer\Exception $e) {
        // Log error (essential for production debugging)
        error_log("Password reset email failed to {$toEmail}: " . $e->getMessage());
        if (isset($mail)) {
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
        }
        return false;
    }
}

/**
 * Get HTML email template for password reset
 * 
 * @param string $resetUrl Password reset URL
 * @param string $userName User name
 * @return string HTML email body
 */
function get_password_reset_email_template($resetUrl, $userName = '') {
    $greeting = !empty($userName) ? "Hello {$userName}," : "Hello,";
    
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #007bff; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px 20px; background-color: #f9f9f9; }
            .button { display: inline-block; padding: 12px 30px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . SITE_NAME . "</h1>
            </div>
            <div class='content'>
                <p>{$greeting}</p>
                <p>You requested to reset your password. Click the button below to reset it:</p>
                <p style='text-align: center;'>
                    <a href='{$resetUrl}' class='button'>Reset Password</a>
                </p>
                <p>Or copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #007bff;'>{$resetUrl}</p>
                <p><strong>This link will expire in 1 hour.</strong></p>
                <p>If you didn't request a password reset, please ignore this email.</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " " . SITE_NAME . ". All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
}

/**
 * Get plain text email for password reset
 * 
 * @param string $resetUrl Password reset URL
 * @return string Plain text email body
 */
function get_password_reset_email_plain($resetUrl) {
    return "
" . SITE_NAME . " - Password Reset

You requested to reset your password. Click the link below to reset it:

{$resetUrl}

This link will expire in 1 hour.

If you didn't request a password reset, please ignore this email.

© " . date('Y') . " " . SITE_NAME . ". All rights reserved.
    ";
}

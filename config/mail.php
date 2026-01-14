<?php
/**
 * Email Configuration (Gmail SMTP)
 * 
 * Configure your Gmail SMTP settings here.
 * 
 * IMPORTANT: Use an App Password, not your regular Gmail password!
 * To generate an App Password:
 * 1. Go to your Google Account settings
 * 2. Security > 2-Step Verification > App passwords
 * 3. Generate a new app password for "Mail"
 * 4. Use that 16-character password here
 */

// Gmail SMTP Settings
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USERNAME', 'web.learnify@gmail.com'); // Your Gmail address
define('MAIL_PASSWORD', 'xsqszvbziyiydjsb'); // Gmail App Password (16 characters, spaces removed from "xsqs zvbz iyiy djsb")
define('MAIL_PORT', 587);
define('MAIL_ENCRYPTION', 'tls'); // 'tls' or 'ssl'

// Email From Settings
define('MAIL_FROM_ADDRESS', 'web.learnify@gmail.com'); // Usually same as MAIL_USERNAME
define('MAIL_FROM_NAME', 'E-Learning Platform');

// Email Settings
define('MAIL_IS_HTML', true);
define('MAIL_CHARSET', 'UTF-8');


<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/env.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function createMailer(): PHPMailer {

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST'];    // smtp.gmail.com
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USER'];    // user1inf1005@gmail.com
    $mail->Password   = $_ENV['MAIL_PASS'];    // your app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = (int) $_ENV['MAIL_PORT'];  // 587

    $mail->setFrom($_ENV['MAIL_USER'], 'MealMate');

    return $mail;
}
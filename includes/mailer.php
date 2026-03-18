<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getMailer(): PHPMailer
{
    $env = parse_ini_file(__DIR__ . '/../.env');

    if ($env === false) {
        throw new Exception('Failed to load .env file.');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $env['MAIL_HOST'];
    $mail->SMTPAuth = true;
    $mail->Username = $env['MAIL_USER'];
    $mail->Password = $env['MAIL_PASS'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int) $env['MAIL_PORT'];

    $mail->setFrom($env['MAIL_USER'], 'MealMate Admin');
    $mail->isHTML(true);

    return $mail;
}

function sendRecipeApprovedEmail(string $toEmail, string $username, string $recipeTitle): void
{
    $mail = getMailer();
    $mail->addAddress($toEmail, $username);
    $mail->Subject = 'Your recipe has been approved';
    $mail->Body = "
        <p>Hi " . htmlspecialchars($username) . ",</p>
        <p>Your recipe <strong>" . htmlspecialchars($recipeTitle) . "</strong> has been approved by the MealMate admin team.</p>
        <p>You can now view it on the platform.</p>
        <p>Regards,<br>MealMate Admin</p>
    ";
    $mail->AltBody = "Hi {$username}, your recipe '{$recipeTitle}' has been approved by the MealMate admin team.";
    $mail->send();
}

function sendRecipeRejectedEmail(string $toEmail, string $username, string $recipeTitle): void
{
    $mail = getMailer();
    $mail->addAddress($toEmail, $username);
    $mail->Subject = 'Your recipe has been rejected';
    $mail->Body = "
        <p>Hi " . htmlspecialchars($username) . ",</p>
        <p>Your recipe <strong>" . htmlspecialchars($recipeTitle) . "</strong> was not approved by the MealMate admin team.</p>
        <p>You may review and resubmit it later.</p>
        <p>Regards,<br>MealMate Admin</p>
    ";
    $mail->AltBody = "Hi {$username}, your recipe '{$recipeTitle}' was not approved by the MealMate admin team.";
    $mail->send();
}
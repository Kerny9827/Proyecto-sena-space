<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'libs/vendor/autoload.php';

function sendEmail(string $to, ?string $toName, string $subject, string $body): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'logpsin@gmail.com';
        $mail->Password   = 'zghrjtqkvuxvnamc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('remitente@gmail.com', 'LOG-IN');
        $mail->addAddress($to, $toName ?: $to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['destinatario'])) {
    $destinatario = trim($_POST['destinatario']);
    $asunto = trim($_POST['asunto'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($destinatario === '' || !filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
        echo 'Destinatario inválido';
        exit;
    }

    if (sendEmail($destinatario, '', $asunto, $descripcion)) {
        echo 'Message has been sent';
    } else {
        echo 'Message could not be sent.';
    }
}

<?php

use PHPMailer\PHPMailer\Exception as MailerException;
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/mail.php';

class MailService
{
  public static function enviarResetPassword(string $destinatario, string $token): bool
  {
    if (empty(SMTP_PASS)) {
      error_log('MTasking Mail: SMTP_PASS no configurada en backend/config/mail.php');
      return false;
    }

    $enlace = RESET_PASSWORD_URL . '?token=' . urlencode($token);

    $mail = new PHPMailer(true);

    try {
      $mail->isSMTP();
      $mail->Host       = SMTP_HOST;
      $mail->SMTPAuth   = true;
      $mail->Username   = SMTP_USER;
      $mail->Password   = SMTP_PASS;
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = SMTP_PORT;
      $mail->CharSet    = 'UTF-8';

      $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
      $mail->addAddress($destinatario);

      $mail->isHTML(true);
      $mail->Subject = 'Recuperación de contraseña - MTasking';
      $mail->Body    = '
        <p>Hola,</p>
        <p>Recibimos una solicitud para restablecer tu contraseña en <strong>MTasking</strong>.</p>
        <p><a href="' . htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8') . '">Haz clic aquí para restablecer tu contraseña</a></p>
        <p>O copia y pega este enlace en tu navegador:</p>
        <p>' . htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8') . '</p>
        <p>Este enlace expira en <strong>1 hora</strong>.</p>
        <p>Si no solicitaste este cambio, ignora este correo.</p>
      ';
      $mail->AltBody = "Haz clic en el enlace para restablecer tu contraseña:\n\n$enlace\n\nExpira en 1 hora.";

      $mail->send();
      return true;
    } catch (MailerException $e) {
      error_log('MTasking Mail: ' . $mail->ErrorInfo);
      return false;
    }
  }
}

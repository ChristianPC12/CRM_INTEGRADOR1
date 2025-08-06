<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../LIB/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../LIB/phpmailer/SMTP.php';
require_once __DIR__ . '/../LIB/phpmailer/Exception.php';

/**
 * Envía un e-mail con un código Express (6 dígitos) que expira en 5 min.
 * @return bool  true si se envió sin errores.
 */
function enviarCodigoExpress(string $destinatario, string $nombre, string $codigo): bool
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp-relay.brevo.com';
        $mail->SMTPAuth = true;
        $mail->Username = '91e0a3001@smtp-brevo.com'; // Este es tu correo Brevo
        $mail->Password = 'Apxa06dYz4NP1X9f'; // Tu clave SMTP de Brevo
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // IMPORTANTE: el remitente debe ser el mismo correo del SMTP o uno verificado en Brevo
        $mail->setFrom('anuelmorera@gmail.com', 'Restaurante Bastos');

        $mail->addAddress($destinatario, $nombre);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = '🚀 Código Express (5 min)';

        $mail->Body = "
            <div style='font-family:Arial, sans-serif; font-size:16px'>
                <p>Hola <strong>$nombre</strong>,</p>
                <p>Tu código Express para redimir tu beneficio es:</p>
                <h2 style='letter-spacing:4px; color:#f9c41f;'>$codigo</h2>
                <p>El código expira en <strong>5 minutos</strong>.</p>
                <br>
                <p style='font-size:12px; color:gray'>
                   Si vos no solicitaste este código, ignorá este correo.
                </p>
            </div>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error (Express): " . $mail->ErrorInfo);
        return false;
    }
}

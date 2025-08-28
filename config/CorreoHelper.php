<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../LIB/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../LIB/phpmailer/SMTP.php';
require_once __DIR__ . '/../LIB/phpmailer/Exception.php';

function enviarCorreoCumple($destinatario, $nombre, $mensaje)
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
        $mail->Subject = '🎉 ¡Feliz Cumpleaños!';

        $mail->Body = "
    <div style='font-family: Arial, sans-serif; font-size: 16px; color: #333;'>
        <p>🥳 ¡Hola <strong>$nombre</strong>!</p>
        <p>En <strong>Restaurante Bastos</strong> nos encanta ser parte de tu cumpleaños 🎂.</p>
        <p>Queremos invitarte como <strong>Cliente VIP</strong> a celebrarlo con nosotros y disfrutar de tu <strong>regalía especial</strong> 🎉.</p>
        <p>
            <a href='http://www.bastoscr.com/' 
               style='display:inline-block; margin-top:15px; padding:10px 20px; background-color:#f9c41f; color:#000; text-decoration:none; font-weight:bold; border-radius:5px;'>
               Más info en www.bastoscr.com
            </a>
        </p>
        <br>
        <p style='font-size: 12px; color: gray;'>
            Promoción exclusiva para nuestros <strong>clientes VIP</strong>. Vigente durante la semana de tu cumpleaños.
        </p>
    </div>
";



        $mail->send();
        return true;
    } catch (Exception $e) {
        // Mostrar el error real para depurar
        echo "Mailer Error: " . $mail->ErrorInfo;
        return false;
    }
}

<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../LIB/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../LIB/phpmailer/SMTP.php';
require_once __DIR__ . '/../LIB/phpmailer/Exception.php';

function enviarCorreoCumple($destinatario, $nombre, $mensaje) {
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
            <div style='font-family: Arial, sans-serif; font-size: 16px;'>
                <p>¡Hola $nombre! 🎂</p>
                <p>Todo el equipo del <strong>Restaurante Bastos</strong> te desea un <strong>feliz cumpleaños</strong>.</p>
                <p>Hoy celebramos tu día especial y queremos consentirte como te merecés. 🎉</p>
                <p><strong>¡Presentá este mensaje en el restaurante y elegí entre una comida, una bebida o un postre gratis!</strong> 🍔🍹🍰</p>
                <br>
                <p style='font-size: 12px; color: gray;'>Promoción válida únicamente hoy para nuestros clientes VIP. ¡Te esperamos con gusto!</p>
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

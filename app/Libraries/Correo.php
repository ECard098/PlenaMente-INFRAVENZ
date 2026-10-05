<?php
namespace App\Libraries;

use App\Core\Logger;

class Correo {

    // Envía un correo en formato HTML usando la función mail() de PHP
    public static function enviar(string $destinatario, string $asunto, string $mensajeHtml): bool {
        $dominio = $_SERVER['SERVER_NAME'] ?? 'localhost';
        $remitente = 'no-reply@' . $dominio;

        $cabeceras = "MIME-Version: 1.0\r\n";
        $cabeceras .= "Content-type: text/html; charset=UTF-8\r\n";
        $cabeceras .= "From: PlenaMente <" . $remitente . ">\r\n";
        $cabeceras .= "Reply-To: " . $remitente . "\r\n";
        $cabeceras .= "X-Mailer: PHP/" . phpversion();

        $enviado = @mail($destinatario, $asunto, $mensajeHtml, $cabeceras);

        if (!$enviado) {
            Logger::error('Fallo al enviar correo', [
                'destinatario' => $destinatario,
                'asunto' => $asunto
            ]);
        }
        return $enviado;
    }
}

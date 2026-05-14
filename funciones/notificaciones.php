<?php
/**
 * Lucina - Comparador Energético (DAW DUAL - Proyecto Intermodular - 25/26)
 * Clase: LucinaCorreo
 * Descripción: Implementación de un cliente SMTP básico mediante sockets para
 * el envío de notificaciones por correo electrónico sin dependencias externas.
 * Ideal para demostrar conocimientos de protocolos y redes en PHP.
 */

class LucinaCorreo {
    // Configuración del servidor de correo corporativo
    private $servidor   = 'ssl://smtp.tudominio.com';
    private $puerto     = 465;
    private $usuario    = 'USUARIOCORREO';
    private $clave      = 'CONTRASEÑA';
    private $remitente  = 'USUARIOCORREO';
    private $nombre     = 'Lucina - Notificaciones';

    /**
     * Envía un correo electrónico utilizando comandos SMTP directos.
     * @param string $destinatario - Correo del receptor.
     * @param string $asunto - Título del mensaje.
     * @param string $cuerpo - Contenido en formato HTML.
     */
    public function enviar($destinatario, $asunto, $cuerpo) {
        $espera = 30; // Tiempo de espera para la conexión
        $conexion = fsockopen($this->servidor, $this->puerto, $numeroError, $textoError, $espera);

        if (!$conexion) {
            error_log("Fallo en SMTP: $numeroError - $textoError");
            return false;
        }

        /**
         * Función interna para leer la respuesta del servidor SMTP.
         */
        $leerRespuesta = function($socket) {
            $respuesta = "";
            while ($linea = fgets($socket, 515)) {
                $respuesta .= $linea;
                if (substr($linea, 3, 1) == " ") break;
            }
            return $respuesta;
        };

        /**
         * Función interna para enviar un comando y capturar la respuesta.
         */
        $enviarComando = function($socket, $comando) use ($leerRespuesta) {
            fputs($socket, $comando . "\r\n");
            return $leerRespuesta($socket);
        };

        // Protocolo de negociación SMTP (Handshake)
        $leerRespuesta($conexion); 
        $enviarComando($conexion, "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
        $enviarComando($conexion, "AUTH LOGIN");
        $enviarComando($conexion, base64_encode($this->usuario));
        $enviarComando($conexion, base64_encode($this->clave));
        $enviarComando($conexion, "MAIL FROM: <{$this->usuario}>");
        $enviarComando($conexion, "RCPT TO: <$destinatario>");
        $enviarComando($conexion, "DATA");

        // Construcción de las cabeceras del mensaje (MIME)
        $cabeceras = "From: {$this->nombre} <{$this->remitente}>\r\n";
        $cabeceras .= "To: <$destinatario>\r\n";
        // Codificamos el asunto en Base64 para evitar problemas con tildes
        $cabeceras .= "Subject: =?UTF-8?B?" . base64_encode($asunto) . "?=\r\n";
        $cabeceras .= "MIME-Version: 1.0\r\n";
        $cabeceras .= "Content-Type: text/html; charset=UTF-8\r\n";
        $cabeceras .= "Date: " . date('r') . "\r\n";

        // Envío del cuerpo del mensaje y finalización del comando DATA
        fputs($conexion, $cabeceras . "\r\n" . $cuerpo . "\r\n.\r\n");
        $enviarComando($conexion, "QUIT");
        
        fclose($conexion);
        return true;
    }

    /**
     * Genera y envía un aviso al administrador cuando un cliente muestra interés.
     */
    public function enviarAvisoInteres($nombreCliente, $nombreTarifa, $nombreCompania, $ahorro, $idCliente) {
        $correoAdmin = 'javijaraiz@gmail.com';
        $asunto = "🔥 ¡Nuevo interés detectado! - $nombreCliente";
        
        // Obtenemos la URL base para el enlace al panel
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $urlFicha = "http://" . $host . "/Luzina/admin/ver_cliente.php?id=" . $idCliente;
        
        $contenido = "
        <html>
        <head>
            <style>
                .contenedor { font-family: sans-serif; max-width: 600px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                .titulo { color: #0d6efd; font-size: 20px; font-weight: bold; }
                .caja { background: #f4f4f4; padding: 15px; margin: 15px 0; border-radius: 5px; }
                .btn { display: inline-block; padding: 10px 20px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class='contenedor'>
                <div class='titulo'>¡Nueva captación de cliente!</div>
                <p>El cliente <strong>$nombreCliente</strong> ha indicado que le interesa una de las tarifas mostradas.</p>
                <div class='caja'>
                    <p><strong>Tarifa elegida:</strong> $nombreTarifa ($nombreCompania)</p>
                    <p><strong>Ahorro mensual previsto:</strong> $ahorro €</p>
                </div>
                <p>Para gestionar esta consulta con el cliente, pulsa el siguiente botón:</p>
                <p style='text-align: center;'>
                    <a href='$urlFicha' class='btn'>Ver expediente en el panel administrativo</a>
                </p>
                <hr>
                <p style='font-size: 11px; color: #888;'>Lucina - DAW DUAL - Proyecto Intermodular - 25/26</p>
            </div>
        </body>
        </html>";
        
        return $this->enviar($correoAdmin, $asunto, $contenido);
    }
}
?>

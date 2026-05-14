<?php
header('Content-Type: application/json');
require_once 'sesiones.php';

$archivo = '';

if (isset($_FILES['factura']) && $_FILES['factura']['error'] === UPLOAD_ERR_OK) {
    $ext     = strtolower(pathinfo($_FILES['factura']['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf', 'jpg', 'jpeg', 'png'];

    if (in_array($ext, $allowed)) {
        $filename = md5(time() . $_FILES['factura']['name']) . '.' . $ext;
        if (move_uploaded_file($_FILES['factura']['tmp_name'], '../archivos/' . $filename)) {
            $archivo = $filename;
        }
    }
}

$_SESSION['cliente_temp'] = [
    'nombre'   => $_POST['nombre'] ?? 'Invitado',
    'email'    => $_POST['email'] ?? '',
    'telefono' => $_POST['telefono'] ?? ''
];

$config = require '../config.php';
$apiKey = $config['gemini_api_key'] ?? '';

// Valores por defecto para cuando Gemini falla o no hay archivo (modo manual)
$datos = [
    'cups' => '', 'poblacion' => '', 'provincia' => '',
    'consumo_p1' => 0, 'consumo_p2' => 0, 'consumo_p3' => 0,
    'potencia_p1' => 0, 'potencia_p2' => 0,
    'importe_total' => 0, 'impuesto_electrico' => 0, 'alquiler_contador' => 0,
    'iva' => 21, 'bono_social' => 'No', 'excedentes' => 0,
    'observaciones' => '', 'dias_factura' => 30
];

$mensaje = 'Modo de entrada manual activado.';

if (!empty($apiKey) && !empty($archivo)) {
    $docBase64 = base64_encode(file_get_contents('../archivos/' . $archivo));

    $prompt = "Actúa como un experto en facturación eléctrica española. Analiza minuciosamente la factura.
    Instrucciones de extracción requeridas:
    1. CUPS: Código ES... (20-22 caracteres).
    2. DIRECCIÓN: Ubicación del suministro.
    3. CONSUMOS: Valores numéricos de P1 (Punta), P2 (Llano) y P3 (Valle).
    4. POTENCIAS: Potencia contratada en kW (normalmente P1 y P2).
    5. IMPORTES: Importe total de la factura con todos los impuestos incluidos.
    6. BONO SOCIAL: Indica 'Si' solo si hay un descuento explícito por esta condición.

    Formato de salida (JSON ESTRICTO):
    {
        \"cups\": \"string\",
        \"direccion\": \"string\",
        \"poblacion\": \"string\",
        \"provincia\": \"string\",
        \"consumo_p1\": float,
        \"consumo_p2\": float,
        \"consumo_p3\": float,
        \"potencia_p1\": float,
        \"potencia_p2\": float,
        \"importe_total\": float,
        \"bono_social\": \"Si/No\",
        \"dias_factura\": integer
    }";

    $body = [
        "contents" => [[
            "parts" => [
                ["text" => $prompt],
                ["inline_data" => ["mime_type" => "application/pdf", "data" => $docBase64]]
            ]
        ]],
        "generationConfig" => ["response_mime_type" => "application/json"]
    ];

    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=" . $apiKey);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // desactivado para XAMPP local
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);

    $result   = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($httpCode === 200) {
        $json = json_decode($result, true);
        $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
        $raw  = json_decode($text, true);

        if ($raw) {
            // Gemini a veces devuelve números como string con coma decimal ("1.234,56")
            $toFloat = function($val) {
                if (is_numeric($val)) return (float)$val;
                $val = str_replace(',', '.', (string)$val);
                return (float)preg_replace('/[^0-9.]/', '', $val);
            };

            $datos = array_merge($datos, [
                'cups'          => (string)($raw['cups'] ?? ''),
                'direccion'     => (string)($raw['direccion'] ?? ''),
                'poblacion'     => (string)($raw['poblacion'] ?? ''),
                'provincia'     => (string)($raw['provincia'] ?? ''),
                'consumo_p1'    => $toFloat($raw['consumo_p1'] ?? 0),
                'consumo_p2'    => $toFloat($raw['consumo_p2'] ?? 0),
                'consumo_p3'    => $toFloat($raw['consumo_p3'] ?? 0),
                'potencia_p1'   => $toFloat($raw['potencia_p1'] ?? 0),
                'potencia_p2'   => $toFloat($raw['potencia_p2'] ?? 0),
                'importe_total' => $toFloat($raw['importe_total'] ?? 0),
                'bono_social'   => (string)($raw['bono_social'] ?? 'No'),
                'dias_factura'  => (int)($raw['dias_factura'] ?? 30),
            ]);
            $mensaje = 'Análisis completado correctamente.';
        } else {
            $mensaje = 'Error al interpretar los datos de la factura.';
        }
    } else {
        $mensaje = 'El servicio de procesamiento no respondió adecuadamente.';
    }
}

$datos['ruta_archivo'] = $archivo;
$_SESSION['datos_extraidos'] = $datos;

echo json_encode(['success' => true, 'mensaje' => $mensaje, 'data' => $datos]);

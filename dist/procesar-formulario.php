<?php
/**
 * procesar-formulario.php — Jaso y Asociados
 * Procesa el formulario de contacto y envía el correo al despacho.
 */

// ── Configuración ────────────────────────────────────────────
define('EMAIL_DESTINO',  'cjaso@jasoyasociados.com.mx');
define('EMAIL_REMITENTE','no-reply@jasoyasociados.com.mx');
define('NOMBRE_DESPACHO','Jaso y Asociados');
define('URL_GRACIAS',    'agradecimiento.html');
define('URL_ERROR',      'index.html?error=1');

// ── Solo aceptar POST ────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// ── Sanitización ─────────────────────────────────────────────
function limpiar(string $valor): string {
    return htmlspecialchars(strip_tags(trim($valor)), ENT_QUOTES, 'UTF-8');
}

$nombre   = limpiar($_POST['nombre']   ?? '');
$telefono = limpiar($_POST['telefono'] ?? '');
$origen   = limpiar($_POST['origen']   ?? 'Landing Jaso y Asociados');
$telefono_digitos = preg_replace('/\D+/', '', html_entity_decode($telefono, ENT_QUOTES, 'UTF-8'));

// ── Validación backend ───────────────────────────────────────
$errores = [];

if (empty($nombre)) {
    $errores[] = 'El nombre es requerido.';
}

if (
    empty($telefono)
    || !preg_match('/^[\d\s+\-()]+$/', $telefono)
    || strlen($telefono_digitos) < 10
    || strlen($telefono_digitos) > 15
) {
    $errores[] = 'El teléfono no es válido.';
}

// ── Honeypot anti-spam (campo oculto vacío) ──────────────────
if (!empty($_POST['website'])) {
    // Bot detectado — redirigir silenciosamente
    header('Location: ' . URL_GRACIAS);
    exit;
}

// ── Si hay errores, regresar al formulario ───────────────────
if (!empty($errores)) {
    header('Location: ' . URL_ERROR);
    exit;
}

// ── Construir correo HTML ────────────────────────────────────
$fecha    = date('d/m/Y H:i:s');
$asunto   = "Nuevo contacto para Carlos Jaso: {$nombre}";
$telefono_whatsapp = strlen($telefono_digitos) === 10
    ? '52' . $telefono_digitos
    : $telefono_digitos;

$cuerpo_html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  body { font-family: 'Inter', Arial, sans-serif; background: #F8F6F1; margin: 0; padding: 20px; color: #24272A; }
  .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.08); }
  .header { background: #173F37; padding: 28px 32px; }
  .header h1 { color: #ffffff; font-size: 20px; margin: 0; }
  .header p { color: rgba(255,255,255,.7); font-size: 13px; margin: 6px 0 0; }
  .body { padding: 28px 32px; }
  .field { margin-bottom: 18px; }
  .label { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #A89F91; margin-bottom: 4px; }
  .value { font-size: 15px; color: #24272A; font-weight: 500; }
  .footer { background: #f0ede8; padding: 16px 32px; font-size: 12px; color: #A89F91; text-align: center; border-top: 1px solid #e8e4df; }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <h1>Nuevo contacto — Jaso y Asociados</h1>
    <p>Formulario recibido el {$fecha}</p>
  </div>
  <div class="body">
    <div class="field">
      <div class="label">Nombre</div>
      <div class="value">{$nombre}</div>
    </div>
    <div class="field">
      <div class="label">Teléfono</div>
      <div class="value"><a href="tel:+{$telefono_digitos}" style="color:#173F37;">{$telefono}</a></div>
      <p style="margin:14px 0 0;">
        <a href="tel:+{$telefono_digitos}" style="display:inline-block;background:#173F37;color:#fff;text-decoration:none;padding:10px 16px;border-radius:6px;margin-right:8px;">Llamar ahora</a>
        <a href="https://wa.me/{$telefono_whatsapp}" style="display:inline-block;background:#25D366;color:#fff;text-decoration:none;padding:10px 16px;border-radius:6px;">Abrir WhatsApp</a>
      </p>
    </div>
    <div class="field">
      <div class="label">Origen</div>
      <div class="value" style="font-size:13px; color:#A89F91;">{$origen}</div>
    </div>
  </div>
  <div class="footer">
    Este correo fue generado automáticamente por el formulario de contacto del sitio web de Jaso y Asociados.
    No responder a este mensaje directamente.
  </div>
</div>
</body>
</html>
HTML;

// ── Cabeceras MIME para correo HTML ─────────────────────────
$boundary = md5(uniqid(rand(), true));

$cabeceras  = "MIME-Version: 1.0\r\n";
$cabeceras .= "Content-Type: multipart/alternative; boundary=\"{$boundary}\"\r\n";
$cabeceras .= "From: " . NOMBRE_DESPACHO . " <" . EMAIL_REMITENTE . ">\r\n";
$cabeceras .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Parte texto plano (fallback)
$cuerpo_plano = <<<TXT
Nuevo contacto recibido — Jaso y Asociados
Fecha: {$fecha}

Nombre:   {$nombre}
Teléfono: {$telefono}
Origen:   {$origen}
TXT;

$cuerpo_completo  = "--{$boundary}\r\n";
$cuerpo_completo .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
$cuerpo_completo .= $cuerpo_plano . "\r\n";
$cuerpo_completo .= "--{$boundary}\r\n";
$cuerpo_completo .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
$cuerpo_completo .= $cuerpo_html . "\r\n";
$cuerpo_completo .= "--{$boundary}--";

// ── Enviar correo ────────────────────────────────────────────
$enviado = mail(
    EMAIL_DESTINO,
    '=?UTF-8?B?' . base64_encode($asunto) . '?=',
    $cuerpo_completo,
    $cabeceras
);

// ── Redirigir según resultado ────────────────────────────────
if ($enviado) {
    header('Location: ' . URL_GRACIAS);
} else {
    // En producción con Hostinger, usar SMTP (ver README)
    header('Location: ' . URL_ERROR);
}
exit;

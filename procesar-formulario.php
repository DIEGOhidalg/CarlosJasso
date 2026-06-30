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
$correo   = limpiar($_POST['correo']   ?? '');
$tipo     = limpiar($_POST['tipo']     ?? '');
$mensaje  = limpiar($_POST['mensaje']  ?? '');
$origen   = limpiar($_POST['origen']   ?? 'Landing Jaso y Asociados');

// ── Validación backend ───────────────────────────────────────
$errores = [];

if (empty($nombre)) {
    $errores[] = 'El nombre es requerido.';
}

if (empty($telefono) || !preg_match('/^[\d\s\+\-\(\)]{8,15}$/', $telefono)) {
    $errores[] = 'El teléfono no es válido.';
}

if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El correo electrónico no es válido.';
}

if (empty($tipo)) {
    $errores[] = 'El tipo de caso es requerido.';
}

if (empty($mensaje)) {
    $errores[] = 'El mensaje es requerido.';
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
$asunto   = "Nuevo contacto desde la landing: {$nombre} — {$tipo}";

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
  .mensaje-box { background: #F8F6F1; border-left: 4px solid #173F37; border-radius: 4px; padding: 14px 16px; font-size: 14px; line-height: 1.7; color: #4b5563; }
  .footer { background: #f0ede8; padding: 16px 32px; font-size: 12px; color: #A89F91; text-align: center; border-top: 1px solid #e8e4df; }
  .badge { display: inline-block; background: #D9E1DC; color: #173F37; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 20px; }
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
      <div class="value">{$telefono}</div>
    </div>
    <div class="field">
      <div class="label">Correo electrónico</div>
      <div class="value"><a href="mailto:{$correo}" style="color:#173F37;">{$correo}</a></div>
    </div>
    <div class="field">
      <div class="label">Tipo de caso</div>
      <div class="value"><span class="badge">{$tipo}</span></div>
    </div>
    <div class="field">
      <div class="label">Mensaje</div>
      <div class="mensaje-box">{$mensaje}</div>
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
$cabeceras .= "Reply-To: {$nombre} <{$correo}>\r\n";
$cabeceras .= "X-Mailer: PHP/" . phpversion() . "\r\n";

// Parte texto plano (fallback)
$cuerpo_plano = <<<TXT
Nuevo contacto recibido — Jaso y Asociados
Fecha: {$fecha}

Nombre:   {$nombre}
Teléfono: {$telefono}
Correo:   {$correo}
Tipo:     {$tipo}
Origen:   {$origen}

Mensaje:
{$mensaje}
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

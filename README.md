# Jaso y Asociados — Landing Page

Despacho boutique especializado en litigio civil y mercantil.

---

## Estructura del proyecto

```
jaso-landing/
├── index.html                 ← Landing principal
├── agradecimiento.html        ← Página post-envío de formulario
├── procesar-formulario.php    ← Backend PHP para el correo
├── build.js                   ← Script de build (Node.js)
├── package.json
└── assets/
    ├── css/styles.css
    ├── js/script.js
    └── img/logo.png
```

---

## Desarrollo local

### Requisitos previos

- [Node.js](https://nodejs.org/) v18 o superior
- Servidor PHP local (XAMPP, Laragon, MAMP o similar) **solo si vas a probar el formulario**

### Instalación

```bash
cd jaso-landing
npm install
```

### Servidor de desarrollo (recarga automática)

```bash
npm run dev
```

Abre el navegador en `http://localhost:3000`. Los cambios en HTML, CSS y JS
recargan la página automáticamente gracias a **BrowserSync**.

> **Nota:** El formulario PHP no funciona con BrowserSync puro.
> Para probarlo usa XAMPP/Laragon y accede por `http://localhost/jaso-landing/`.

### Build para producción

```bash
npm run build
```

Genera la carpeta `dist/` con todos los archivos listos para subir a Hostinger.

---

## Formulario PHP

El archivo `procesar-formulario.php` usa la función nativa `mail()` de PHP.

**Campos que recibe:**

| Campo     | Descripción                       |
|-----------|-----------------------------------|
| nombre    | Nombre completo del usuario       |
| telefono  | Teléfono de contacto              |
| correo    | Correo electrónico                |
| tipo      | Tipo de caso seleccionado         |
| mensaje   | Descripción del asunto            |
| origen    | Campo oculto: "Landing Jaso y Asociados" |

El correo se envía a: `cjaso@jasoyasociados.com.mx`

Tras el envío exitoso redirige a: `agradecimiento.html`

---

## Despliegue en Hostinger

### Opción A — File Manager (más sencilla)

1. Ejecuta `npm run build` para generar `/dist`
2. Inicia sesión en **hPanel → File Manager**
3. Navega a `public_html/` (o a la carpeta de tu dominio)
4. Sube el contenido de `/dist` (no la carpeta en sí, sino su contenido)
5. Verifica que `index.html` quede en la raíz de `public_html/`

### Opción B — FTP con FileZilla

1. Abre FileZilla, conecta con los datos SFTP de Hostinger
2. Navega a `public_html/` en el panel remoto
3. Arrastra el contenido de `/dist` desde el panel local

### Configuración del formulario PHP en Hostinger

Hostinger **sí soporta PHP y `mail()`** en todos sus planes de alojamiento compartido.
Sin embargo, para mayor fiabilidad de entrega se recomienda usar **PHPMailer con SMTP**:

#### Instalar PHPMailer (opcional pero recomendado)

```bash
# En tu máquina local (requiere Composer)
composer require phpmailer/phpmailer
```

Luego sube la carpeta `vendor/` junto con los demás archivos.

Edita `procesar-formulario.php` para usar SMTP con las credenciales de
Hostinger (las encuentras en hPanel → Email → Administrar → Detalles SMTP):

```php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';

$mail = new PHPMailer(true);
$mail->isSMTP();
$mail->Host       = 'smtp.hostinger.com';   // servidor SMTP de Hostinger
$mail->SMTPAuth   = true;
$mail->Username   = 'cjaso@jasoyasociados.com.mx';
$mail->Password   = 'TU_CONTRASEÑA_DE_CORREO';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
$mail->CharSet    = 'UTF-8';
// ... resto de la configuración
```

### Dominio y SSL

1. En hPanel → Dominios, apunta `jasoyasociados.com.mx` a Hostinger
2. Activa el certificado SSL gratuito (Let's Encrypt) desde hPanel → SSL
3. Fuerza HTTPS añadiendo en `.htaccess`:

```apache
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

---

## Contacto del despacho

- **Teléfono:** +52 55 4040 8201 / +52 55 2862 4797
- **WhatsApp:** +52 55 5954 3038
- **Correo:** cjaso@jasoyasociados.com.mx
- **Dirección:** Av. Pdte. Masaryk 61-901B, Polanco, CDMX

---

© 2026 Jaso y Asociados. Todos los derechos reservados.

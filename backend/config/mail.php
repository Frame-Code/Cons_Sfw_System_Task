<?php

/*Responsable: De la Vega Sanchez Damian Alejandro*
Campo: Flujo de Recuperacion de Contraseña*/

// MTasking - Configuración SMTP (Gmail)

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'mtasking.noreply@gmail.com');
define('SMTP_PASS', 'xfvgfytkliwfpdfp');

define('MAIL_FROM_EMAIL', 'mtasking.noreply@gmail.com');
define('MAIL_FROM_NAME', 'MTasking');

// URL base del frontend para el enlace de recuperación
define('RESET_PASSWORD_URL', 'http://localhost:8083/mtasking/frontend/html/reset_password.html');

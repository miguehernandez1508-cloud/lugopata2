<?php
session_start();

// Verificar si la extensión GD está disponible
if (!extension_loaded('gd')) {
    header('Content-type: image/png');
    $image = imagecreate(120, 40);
    $background = imagecolorallocate($image, 255, 200, 200);
    $text_color = imagecolorallocate($image, 255, 0, 0);
    imagestring($image, 3, 10, 12, 'GD NOT FOUND', $text_color);
    imagepng($image);
    imagedestroy($image);
    exit;
}

function generateCaptcha() {
    $chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz';
    $captcha = '';
    for ($i = 0; $i < 5; $i++) {
        $captcha .= $chars[rand(0, strlen($chars) - 1)];
    }
    return $captcha;
}

// SIEMPRE generar nuevo CAPTCHA cuando se pide la imagen
// Solo mantener el mismo CAPTCHA si estamos en medio de una validación
$forceNew = isset($_GET['new']) || empty($_SESSION['captcha']);

if ($forceNew) {
    $_SESSION['captcha'] = generateCaptcha();
}

$captcha_text = $_SESSION['captcha'];

// DEBUG: Mostrar el CAPTCHA actual en logs (opcional)
error_log("CAPTCHA generado: " . $captcha_text);

// Configurar headers más agresivos para evitar caché
header('Content-type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: ' . gmdate('D, d M Y H:i:s', time() - 3600) . ' GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');

// Crear imagen
$width = 120;
$height = 60;
$image = imagecreate($width, $height);

// Definir colores
$background = imagecolorallocate($image, 240, 240, 240);
$text_color = imagecolorallocate($image, 0, 100, 200);
$line_color = imagecolorallocate($image, 200, 200, 200);
$noise_color = imagecolorallocate($image, 180, 180, 180);

// Rellenar fondo
imagefill($image, 0, 0, $background);

// Agregar líneas de interferencia aleatorias
for ($i = 0; $i < 8; $i++) { // Más líneas
    $x1 = rand(0, $width);
    $y1 = rand(0, $height);
    $x2 = rand(0, $width);
    $y2 = rand(0, $height);
    imageline($image, $x1, $y1, $x2, $y2, $line_color);
}

// Agregar texto CAPTCHA con MÁS variaciones
$x = 10;
for ($i = 0; $i < strlen($captcha_text); $i++) {
    $char = $captcha_text[$i];
    $font_size = 5;
    $y = 20 + rand(-10, 10); // Más variación vertical
    
    // Color MUY diferente para cada carácter
    $char_color = imagecolorallocate($image, 
        rand(0, 150), 
        rand(0, 150), 
        rand(50, 200)
    );
    
    imagestring($image, $font_size, $x, $y, $char, $char_color);
    $x += 20 + rand(-3, 3); // Más variación en espaciado
}

// Agregar MÁS puntos de interferencia
for ($i = 0; $i < 200; $i++) {
    $point_size = rand(1, 3);
    $point_x = rand(0, $width);
    $point_y = rand(0, $height);
    $point_color = imagecolorallocate($image, 
        rand(100, 200), 
        rand(100, 200), 
        rand(100, 200)
    );
    imagefilledellipse($image, $point_x, $point_y, $point_size, $point_size, $point_color);
}

// Agregar borde
$border_color = imagecolorallocate($image, 100, 100, 100);
imagerectangle($image, 0, 0, $width-1, $height-1, $border_color);

// Generar y liberar imagen
imagepng($image);
imagedestroy($image);
exit;
?>
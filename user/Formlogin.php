<?php 
require_once "gestorsesion.php";
GestorSesiones::iniciar();

$status = GestorSesiones::get("status");
$message = '';
$alert_type = 'danger';
$show_message = false;

if (!empty($status)) {
    switch($status) {
        case 1:
            $message = ' Error de Usuario o Contraseña';
            break;
        case 2:
            $message = ' CAPTCHA incorrecto';
            break;
        case 3:
            $message = ' Por favor complete el CAPTCHA';
            $alert_type = 'warning';
            break;
        case 4:
            $message = ' Su usuario ha sido bloqueado por exceso de intentos, por favor recuperar contraseña.';
            $alert_type = 'danger';
            break;
        case 5:
            $message = ' No puede usar una contraseña anterior. Por favor, elija una nueva contraseña.';
            $alert_type = 'warning';
            break;
    }
    
    $show_message = !empty($message);
    GestorSesiones::set("status", null);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inicio de sesión</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/2.css">
    <link rel="stylesheet" href="../assets/css/estilo.css">
    
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        
        /* Animaciones CSS */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        @keyframes rotateRefresh {
            from { transform: rotate(0deg); }
            to { transform: rotate(180deg); }
        }
    </style>
</head>

<body style="
    background: url('../assets/resources/fondo.png') no-repeat center center fixed;
    background-size: cover;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
">
    <div style="
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
    ">
        <div style="
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            border: 3px solid #007bff;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            position: relative;
            animation: fadeIn 0.8s ease-out;
        " id="loginCard">
            
            <div style="
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 25px;
                padding-bottom: 15px;
                border-bottom: 2px solid #e9ecef;
            ">
                <div>
                    <h3 style="color: #007bff; font-weight: bold; margin: 0;">
                        <img src="../assets/resources/logo.png" width="40" height="40" alt="Logo" style="vertical-align: middle; margin-right: 10px;">
                        Sistema de Gestión
                    </h3>
                    <small style="font-size: 12px; color: #6c757d;">Acceso al sistema</small>
                </div>
            </div>

            <img src="../assets/resources/inicio1.png" alt="Inicio Sesión" style="
                width: 100px;
                height: 100px;
                margin: 0 auto 20px;
                display: block;
                animation: pulse 2s infinite;
            " id="loginIcon">

            <h1 style="
                text-align: center;
                color: #333;
                font-weight: 700;
                font-size: 28px;
                margin-bottom: 25px;
                position: relative;
            ">INICIO DE SESIÓN</h1>
            
            <?php if ($show_message): ?>
                <div id="errorContainer" style="
                    position: relative;
                    width: 100%;
                    margin-bottom: 25px;
                    z-index: 9999;
                    display: block !important;
                    visibility: visible !important;
                    opacity: 1 !important;
                    animation: slideDown 0.5s ease-out;
                ">
                    <div id="errorMessage" style="
                        padding: 15px 20px;
                        border-radius: 12px;
                        margin: 0;
                        border: none;
                        font-weight: 600;
                        text-align: center;
                        display: block !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                        position: relative;
                        font-size: 16px;
                        line-height: 1.5;
                        animation: shake 0.5s ease-in-out;
                        <?php if ($alert_type == 'danger'): ?>
                            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
                            color: white;
                            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2);
                        <?php else: ?>
                            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
                            color: #212529;
                            box-shadow: 0 5px 15px rgba(255, 193, 7, 0.2);
                        <?php endif; ?>
                    ">
                        <?= htmlspecialchars($message) ?>
                    </div>
                </div>
            <?php endif; ?>

            <form action="implementar.php" method="POST" autocomplete="off" id="loginForm">
                <div style="margin-bottom: 25px; position: relative;">
                    <label for="username" style="font-weight: 600; color: #333; margin-bottom: 8px; font-size: 16px; display: flex; align-items: center; gap: 10px;">
                        <img src="../assets/resources/in.png" width="28" height="28" alt="Usuario">
                        Usuario
                    </label>
                    <input type="text" name="username" id="username" style="
                        padding: 12px 15px;
                        border: 2px solid <?php echo ($status == 1) ? '#dc3545' : '#dee2e6'; ?>;
                        border-radius: 10px;
                        font-size: 16px;
                        transition: all 0.3s ease;
                        background-color: #fff;
                        width: 100%;
                        display: block;
                        <?php echo ($status == 1) ? 'box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);' : ''; ?>
                    " 
                           required placeholder="Ingrese su nombre de usuario"
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>

                <div style="margin-bottom: 25px; position: relative;">
                    <label for="password" style="font-weight: 600; color: #333; margin-bottom: 8px; font-size: 16px; display: flex; align-items: center; gap: 10px;">
                        <img src="../assets/resources/contra.png" width="30" height="30" alt="Contraseña">
                        Contraseña
                    </label>
                    <input type="password" name="password" id="password" style="
                        padding: 12px 15px;
                        border: 2px solid <?php echo ($status == 1) ? '#dc3545' : '#dee2e6'; ?>;
                        border-radius: 10px;
                        font-size: 16px;
                        transition: all 0.3s ease;
                        background-color: #fff;
                        width: 100%;
                        display: block;
                        <?php echo ($status == 1) ? 'box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);' : ''; ?>
                    " 
                           required placeholder="Ingrese su contraseña">
                    <button type="button" id="togglePassword" style="
                        position: absolute;
                        right: 15px;
                        top: 50px;
                        background: none;
                        border: none;
                        color: #6c757d;
                        cursor: pointer;
                        font-size: 18px;
                    ">
                        <img src="../assets/resources/ojo.png" width="20" style="vertical-align: middle; margin-right: 10px;">
                    </button>
                </div>

                <!-- CAPTCHA -->
                <div style="margin-bottom: 25px;">
                    <label style="font-weight: 600; color: #333; margin-bottom: 8px; font-size: 16px; display: flex; align-items: center; gap: 10px;">
                        <img src="../assets/resources/seguridad.png" width="28" height="28" alt="Seguridad">
                        Verificación de seguridad
                    </label>
                    <div style="
                        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                        border: 2px solid <?php echo (in_array($status, [2, 3])) ? '#dc3545' : '#dee2e6'; ?>;
                        border-radius: 12px;
                        padding: 15px;
                        margin-bottom: 20px;
                        <?php echo (in_array($status, [2, 3])) ? 'box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);' : ''; ?>
                    ">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-weight: 600; color: #333;">Ingrese el código</span>
                            <div onclick="window.refreshCaptcha()" style="
                                cursor: pointer;
                                width: 32px;
                                height: 32px;
                                padding: 6px;
                                border-radius: 50%;
                                background: white;
                                color: white;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                transition: all 0.3s ease;
                            " id="refreshCaptchaBtn" title="Actualizar CAPTCHA">
                            <img src="../assets/resources/otra.png" width="25" style="vertical-align: middle; margin-right: 10px;">
                            </div>
                        </div>
                        <img src="captcha_image.php?new=1&t=<?php echo time(); ?>" 
                             alt="CAPTCHA" 
                             style="
                                border: 2px solid #ced4da;
                                border-radius: 8px;
                                margin-bottom: 12px;
                                width: 100%;
                                height: 90px;
                                object-fit: cover;
                                background: #fff;
                                padding: 5px;
                             "
                             id="captchaImage"
                             onerror="this.onerror=null; this.src='../assets/resources/error.png';">
                        <input type="text" 
                               name="captcha" 
                               style="
                                    padding: 12px 15px;
                                    border: 2px solid <?php echo (in_array($status, [2, 3])) ? '#dc3545' : '#dee2e6'; ?>;
                                    border-radius: 10px;
                                    font-size: 16px;
                                    transition: all 0.3s ease;
                                    background-color: #fff;
                                    width: 100%;
                                    display: block;
                                    text-transform: uppercase;
                                    <?php echo (in_array($status, [2, 3])) ? 'box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.15);' : ''; ?>
                               " 
                               required 
                               placeholder="Ingrese el código mostrado"
                               maxlength="5"
                               value="<?php echo isset($_POST['captcha']) ? htmlspecialchars($_POST['captcha']) : ''; ?>"
                               id="captchaInput">
                        <span style="font-size: 12px; color: #6c757d; margin-top: 5px; display: block; text-align: center;">
                            Mayúsculas y minúsculas no importan
                        </span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; gap: 15px; margin-top: 10px;">
                    <button type="submit" name="login_button" style="
                        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                        border: none;
                        border-radius: 10px;
                        padding: 14px 30px;
                        font-size: 16px;
                        font-weight: 600;
                        color: white;
                        transition: all 0.3s ease;
                        width: 48%;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                        cursor: pointer;
                    " id="loginButton">
                        Acceder al Sistema
                    </button>
                    <button type="reset" style="
                        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
                        border: none;
                        border-radius: 10px;
                        padding: 14px 30px;
                        font-size: 16px;
                        font-weight: 600;
                        color: white;
                        transition: all 0.3s ease;
                        width: 48%;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                        cursor: pointer;
                    " id="resetButton">
                        Limpiar Campos
                    </button>
                </div>

                <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 2px dashed #dee2e6;">
                    <a href="formrecuperar.php" id="forgotLink" style="
                        display: inline-flex;
                        align-items: center;
                        gap: 10px;
                        color: #007bff;
                        text-decoration: none;
                        font-weight: 600;
                        padding: 12px 25px;
                        border-radius: 25px;
                        transition: all 0.3s ease;
                        background: rgba(0, 123, 255, 0.1);
                    " title="Se enviará un código a su correo registrado">
                        <img src="../assets/resources/token.png" alt="Token" width="50" height="50" id="tokenImg">
                        <div>
                            <strong>¿Olvidó su contraseña?</strong><br>
                            <small>Recuperar acceso</small>
                        </div>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
    // FUNCIÓN GLOBAL PARA REFRESCAR CAPTCHA
    window.refreshCaptcha = function() {
        console.log('Refrescando CAPTCHA...');
        
        const captchaImage = document.getElementById('captchaImage');
        const refreshBtn = document.getElementById('refreshCaptchaBtn');
        const timestamp = new Date().getTime();
        
        // Animación del botón
        if (refreshBtn) {
            refreshBtn.style.animation = 'rotateRefresh 0.3s ease-in-out';
            refreshBtn.style.transform = 'rotate(180deg) scale(1.1)';
            refreshBtn.style.boxShadow = '0 4px 8px rgba(0, 123, 255, 0.3)';
            
            setTimeout(() => {
                refreshBtn.style.animation = '';
                refreshBtn.style.transform = 'rotate(0) scale(1)';
                refreshBtn.style.boxShadow = 'none';
            }, 300);
        }
        
        // Animación de la imagen
        if (captchaImage) {
            captchaImage.style.opacity = '0.5';
            captchaImage.style.transform = 'scale(0.9)';
            captchaImage.style.transition = 'all 0.3s ease';
            
            // Cambiar la fuente con timestamp para evitar caché
            captchaImage.src = 'captcha_image.php?new=1&t=' + timestamp;
            
            // Restaurar animación después de cargar
            captchaImage.onload = function() {
                setTimeout(() => {
                    captchaImage.style.opacity = '1';
                    captchaImage.style.transform = 'scale(1)';
                }, 100);
            };
            
            // Manejar error de carga
            captchaImage.onerror = function() {
                console.error('Error cargando CAPTCHA');
                this.src = '../assets/resources/error.png';
                this.style.opacity = '1';
                this.style.transform = 'scale(1)';
            };
        }
        
        // Limpiar campo de texto
        const captchaInput = document.getElementById('captchaInput');
        if (captchaInput) {
            captchaInput.value = '';
            captchaInput.style.borderColor = '#dee2e6';
            captchaInput.style.boxShadow = 'none';
        }
        
        // Forzar recarga si hay caché
        setTimeout(() => {
            if (captchaImage && captchaImage.complete) {
                captchaImage.src = 'captcha_image.php?new=1&t=' + (new Date().getTime());
            }
        }, 100);
    };
    
    // INICIALIZACIÓN CUANDO EL DOM ESTÁ LISTO
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM cargado - Inicializando funciones de login');
        
        // 1. Botón mostrar/ocultar contraseña
        const togglePassword = document.getElementById('togglePassword');
        if (togglePassword) {
            togglePassword.addEventListener('click', function() {
                const passwordInput = document.getElementById('password');
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.innerHTML = '<img src="../assets/resources/ojon.png" width="20" style="vertical-align: middle; margin-right: 10px;">';
                    this.title = 'Ocultar contraseña';
                } else {
                    passwordInput.type = 'password';
                    this.innerHTML = '<img src="../assets/resources/ojo.png" width="20" style="vertical-align: middle; margin-right: 10px;">';
                    this.title = 'Mostrar contraseña';
                }
            });
        }
        
        // 2. Configurar hover en refresh CAPTCHA
        const refreshCaptchaBtn = document.getElementById('refreshCaptchaBtn');
        if (refreshCaptchaBtn) {
            refreshCaptchaBtn.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.1)';
                this.style.boxShadow = '0 4px 8px rgba(0, 123, 255, 0.3)';
            });
            
            refreshCaptchaBtn.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = 'none';
            });
        }
        
        // 3. Efectos hover en botones
        const loginButton = document.getElementById('loginButton');
        const resetButton = document.getElementById('resetButton');
        const forgotLink = document.getElementById('forgotLink');
        
        if (loginButton) {
            loginButton.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 8px 15px rgba(0, 123, 255, 0.3)';
            });
            loginButton.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        }
        
        if (resetButton) {
            resetButton.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 8px 15px rgba(108, 117, 125, 0.3)';
            });
            resetButton.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
            
            resetButton.addEventListener('click', function() {
                // Limpiar estilos de error
                document.getElementById('username').style.borderColor = '#dee2e6';
                document.getElementById('username').style.boxShadow = 'none';
                document.getElementById('password').style.borderColor = '#dee2e6';
                document.getElementById('password').style.boxShadow = 'none';
                document.getElementById('captchaInput').style.borderColor = '#dee2e6';
                document.getElementById('captchaInput').style.boxShadow = 'none';
                
                // Remover mensaje de error si existe
                const errorContainer = document.getElementById('errorContainer');
                if (errorContainer) {
                    errorContainer.remove();
                }
            });
        }
        
        if (forgotLink) {
            forgotLink.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 5px 15px rgba(0, 123, 255, 0.2)';
                const img = document.getElementById('tokenImg');
                if (img) {
                    img.style.transform = 'scale(1.2) rotate(10deg)';
                    img.style.transition = 'transform 0.3s ease';
                }
            });
            forgotLink.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
                const img = document.getElementById('tokenImg');
                if (img) {
                    img.style.transform = 'scale(1) rotate(0deg)';
                }
            });
        }
        
        // 4. Auto-focus en usuario
        document.getElementById('username').focus();
        
        // 5. Validación de formulario
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value.trim();
                const captcha = document.getElementById('captchaInput').value.trim();
                
                // Remover error anterior si existe
                const oldError = document.getElementById('errorContainer');
                if (oldError) {
                    oldError.remove();
                }
                
                // Validar campos
                let errors = [];
                if (!username) errors.push('Usuario');
                if (!password) errors.push('Contraseña');
                if (!captcha) errors.push('CAPTCHA');
                
                if (errors.length > 0) {
                    e.preventDefault();
                    
                    // Crear mensaje de error
                    const errorMessage = ' Complete los siguientes campos: ' + errors.join(', ');
                    
                    // Crear contenedor de error
                    const errorContainer = document.createElement('div');
                    errorContainer.id = 'errorContainer';
                    errorContainer.style.cssText = `
                        position: relative;
                        width: 100%;
                        margin-bottom: 25px;
                        z-index: 9999;
                        display: block !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                        animation: slideDown 0.5s ease-out;
                    `;
                    
                    const errorDiv = document.createElement('div');
                    errorDiv.id = 'errorMessage';
                    errorDiv.style.cssText = `
                        padding: 15px 20px;
                        border-radius: 12px;
                        margin: 0;
                        border: none;
                        font-weight: 600;
                        text-align: center;
                        display: block !important;
                        visibility: visible !important;
                        opacity: 1 !important;
                        position: relative;
                        font-size: 16px;
                        line-height: 1.5;
                        animation: shake 0.5s ease-in-out;
                        background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
                        color: white;
                        box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2);
                    `;
                    errorDiv.innerHTML = `<strong>${errorMessage}</strong>`;
                    
                    errorContainer.appendChild(errorDiv);
                    
                    // Insertar después del título
                    const title = document.querySelector('h1');
                    title.parentNode.insertBefore(errorContainer, title.nextSibling);
                    
                    // Resaltar campos con error
                    if (!username) {
                        document.getElementById('username').style.borderColor = '#dc3545';
                        document.getElementById('username').style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.15)';
                    }
                    if (!password) {
                        document.getElementById('password').style.borderColor = '#dc3545';
                        document.getElementById('password').style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.15)';
                    }
                    if (!captcha) {
                        document.getElementById('captchaInput').style.borderColor = '#dc3545';
                        document.getElementById('captchaInput').style.boxShadow = '0 0 0 3px rgba(220, 53, 69, 0.15)';
                    }
                    
                    // Auto-remover error después de 5 segundos
                    setTimeout(() => {
                        if (errorContainer.parentNode) {
                            errorContainer.remove();
                        }
                    }, 5000);
                    
                    return false;
                }
                
                // Si todo está bien, permitir envío
                return true;
            });
        }
        
        // 6. Actualizar CAPTCHA automáticamente cada 2 minutos
        setInterval(window.refreshCaptcha, 120000);
        
        // 7. Verificar que los errores del PHP se muestren
        <?php if ($show_message): ?>
            console.log('Error PHP detectado:', '<?= $message ?>');
            
            // Aplicar shake a los campos con error
            setTimeout(function() {
                <?php if ($status == 1): ?>
                    const usernameField = document.getElementById('username');
                    const passwordField = document.getElementById('password');
                    if (usernameField) {
                        usernameField.style.animation = 'shake 0.5s ease-in-out';
                        setTimeout(() => usernameField.style.animation = '', 500);
                    }
                    if (passwordField) {
                        passwordField.style.animation = 'shake 0.5s ease-in-out';
                        setTimeout(() => passwordField.style.animation = '', 500);
                    }
                <?php elseif (in_array($status, [2, 3])): ?>
                    const captchaField = document.getElementById('captchaInput');
                    if (captchaField) {
                        captchaField.style.animation = 'shake 0.5s ease-in-out';
                        setTimeout(() => captchaField.style.animation = '', 500);
                    }
                <?php endif; ?>
            }, 100);
        <?php endif; ?>
        
        // 8. Configurar input del CAPTCHA para auto-mayúsculas
        const captchaInput = document.getElementById('captchaInput');
        if (captchaInput) {
            captchaInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
                
                // Efecto visual cuando se completa
                if (this.value.length === 5) {
                    this.style.borderColor = '#28a745';
                    this.style.boxShadow = '0 0 0 3px rgba(40, 167, 69, 0.15)';
                } else {
                    this.style.borderColor = '#dee2e6';
                    this.style.boxShadow = 'none';
                }
            });
        }
        
        // 9. Forzar primera carga del CAPTCHA
        setTimeout(() => {
            window.refreshCaptcha();
        }, 500);
    });
    
    // Función para debug - probar que refreshCaptcha funciona
    console.log('refreshCaptcha disponible:', typeof window.refreshCaptcha);
    </script>
</body>
</html>
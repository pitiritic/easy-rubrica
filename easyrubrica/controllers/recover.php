<?php
// controllers/recover.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'libs/Exception.php';
require 'libs/PHPMailer.php';
require 'libs/SMTP.php';

$error = ""; 
$mensaje = ""; 
$step = 1; 

// 1. VERIFICACIÓN DE TOKEN (Cuando el usuario hace clic en el enlace del correo)
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    // Buscamos el token. Nota: usamos 'reset_expires' que es la columna que creamos hoy
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->execute([$token]);
    if ($stmt->fetch()) { 
        $step = 3; 
    } else { 
        $error = "El enlace de recuperación es inválido o ha caducado."; 
    }
}

// 2. PROCESAR ACCIONES POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // ACCIÓN: Enviar el correo electrónico
    if (isset($_POST['enviar_email'])) {
        $email = trim($_POST['email'] ?? '');
        $stmt = $pdo->prepare("SELECT id, nombre FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // Intentamos obtener la configuración de la tabla 'ajustes_smtp'
            $stmtSmtp = $pdo->query("SELECT * FROM ajustes_smtp WHERE id = 1");
            $smtp = $stmtSmtp->fetch();
            
            if (!$smtp || empty($smtp['smtp_host'])) {
                $error = "El sistema de correo no está configurado en 'ajustes_smtp'.";
            } else {
                // Generar token y guardar expiración
                $token = bin2hex(random_bytes(20));
                // Usamos las columnas reset_token y reset_expires que confirmamos que existen
                $pdo->prepare("UPDATE usuarios SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = ?")
                    ->execute([$token, $user['id']]);

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = $smtp['smtp_host'];
                    $mail->SMTPAuth   = true;
                    $mail->Username   = $smtp['smtp_user'];
                    $mail->Password   = $smtp['smtp_pass'];
                    $mail->SMTPSecure = ($smtp['smtp_secure'] == 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = $smtp['smtp_port'];
                    $mail->CharSet    = 'UTF-8';

                    $mail->setFrom($smtp['from_email'], $smtp['from_name']);
                    $mail->addAddress($email, $user['nombre']);

                    // Construcción de la URL
                    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                    $url = "$protocol://" . $_SERVER['HTTP_HOST'] . explode('?', $_SERVER['REQUEST_URI'])[0] . "?action=recover&token=" . $token;

                    $mail->isHTML(true);
                    $mail->Subject = 'Restablecer Contraseña - EasyRúbrica';
                    
                    // Diseño del correo
                    $mail->Body = "
                    <div style='background:#f4f7f9; padding:30px; font-family:sans-serif;'>
                        <div style='max-width:500px; margin:0 auto; background:#fff; padding:30px; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.05); text-align:center;'>
                            <h2 style='color:#198754; margin-bottom:20px;'>EasyRúbrica</h2>
                            <p style='color:#555; font-size:16px;'>Hola <strong>" . htmlspecialchars($user['nombre']) . "</strong>,</p>
                            <p style='color:#777; line-height:1.5;'>Has solicitado restablecer tu contraseña. Haz clic en el botón de abajo para elegir una nueva:</p>
                            <div style='margin:30px 0;'>
                                <a href='{$url}' style='background:#198754; color:#fff; padding:12px 25px; text-decoration:none; border-radius:5px; font-weight:bold;'>Cambiar Contraseña</a>
                            </div>
                            <p style='color:#999; font-size:12px;'>Este enlace caducará en 1 hora. Si no solicitaste este cambio, puedes ignorar este correo.</p>
                        </div>
                    </div>";

                    $mail->send();
                    $mensaje = "Correo de recuperación enviado correctamente a <b>$email</b>."; 
                    $step = 2; 
                } catch (Exception $e) { 
                    $error = "Error al enviar el correo: {$mail->ErrorInfo}"; 
                }
            }
        } else { 
            $error = "El correo electrónico introducido no está registrado."; 
        }
    }

    // ACCIÓN: Cambiar la contraseña (Paso 3)
    if (isset($_POST['cambiar_password'])) {
        $pass = $_POST['password'] ?? '';
        $passConfirm = $_POST['password_confirm'] ?? '';
        $tokenHidden = $_POST['token_hidden'] ?? '';

        if ($pass === $passConfirm && strlen($pass) >= 4) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
            $stmt->execute([$hash, $tokenHidden]);
            
            echo "<script>alert('Tu contraseña ha sido actualizada con éxito.'); window.location='?action=login';</script>"; 
            exit;
        } else { 
            $error = "Las contraseñas no coinciden o son demasiado cortas (mínimo 4 caracteres)."; 
            $step = 3; 
        }
    }
}

require 'views/recover.view.php';

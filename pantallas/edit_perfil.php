<?php
require '../conexion/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


// Obtener datos actualasdasdasdasdario

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Procesar formulario de actualización

$error = '';
$success = '';



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar datos
    $nombres = filter_input(INPUT_POST, 'nombres', FILTER_SANITIZE_STRING);
    $apellidos = filter_input(INPUT_POST, 'apellidos', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $celular = filter_input(INPUT_POST, 'celular', FILTER_SANITIZE_NUMBER_INT);
    $fecha_nacimiento = filter_input(INPUT_POST, 'fecha_nacimiento', FILTER_SANITIZE_STRING);

    // Validaciones básicas
    if (empty($nombres) || empty($apellidos) || empty($email)) {
        $error = "Nombre, apellido y email son obligatorios";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "El formato de email no es válido";
    } else {
        try {
            $pdo->beginTransaction();

            // Actualizar datos básicos
            $stmt = $pdo->prepare("UPDATE users SET nombres = ?, apellidos = ?, email = ?, celular = ?, fecha_nacimiento = ? WHERE id = ?");
            $stmt->execute([$nombres, $apellidos, $email, $celular, $fecha_nacimiento, $_SESSION['user_id']]);

            // Procesar foto de perfil si se subió
            if (!empty($_FILES['foto_perfil']['name'])) {
                $allowedTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp'
                ];
                
                $fileType = $_FILES['foto_perfil']['type'];
                
                if (array_key_exists($fileType, $allowedTypes)) {
                    $userId = $_SESSION['user_id'];
                    $extension = $allowedTypes[$fileType];
                    $targetDir = "../assets/foto_perfil/";
                    
                    // Crear directorio si no existe
                    if (!file_exists($targetDir)) {
                        mkdir($targetDir, 0755, true);
                    }
                    
                    // Eliminar imágenes anteriores
                    $oldFiles = glob($targetDir . $userId . ".*");
                    foreach ($oldFiles as $file) {
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                    
                    // Guardar nueva imagen
                    $newFileName = $targetDir . $userId . '.' . $extension;
                    
                    if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $newFileName)) {
                        // Redimensionar imagen
                        resizeImage($newFileName, 300, 300);
                        
                        // Actualizar ruta en la base de datos
                        $stmt = $pdo->prepare("UPDATE users SET foto_perfil = ? WHERE id = ?");
                        $stmt->execute([$newFileName, $_SESSION['user_id']]);
                    } else {
                        throw new Exception("Error al subir la imagen");
                    }
                } else {
                    throw new Exception("Formato de imagen no permitido. Use JPG, PNG, GIF o WEBP");
                }
            }

            // En caso de éxito:
            $pdo->commit();
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'message' => 'Perfil actualizado correctamente'
            ];
            echo '<script>sessionStorage.setItem("flashMessage", JSON.stringify(' . json_encode([
                'type' => 'success',
                'message' => 'Perfil actualizado correctamente'
            ]) . '));</script>';
            header("Location: home_screen.php");
            exit();

            // Actualizar datos en variable $user
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error al actualizar: " . $e->getMessage();
        }
    }
}

// Función para redimensionar imágenes
function resizeImage($file, $w, $h, $crop = true) {
    // Verificar si GD está instalado
    if (!function_exists('imagecreatefromjpeg')) {
        throw new Exception("La extensión GD no está instalada");
    }

    list($width, $height) = getimagesize($file);
    $type = exif_imagetype($file);
    
    // Determinar nuevas dimensiones manteniendo aspecto
    $ratio = $width / $height;
    if ($crop) {
        if ($width > $height) {
            $width = ceil($width - ($width * abs($ratio - $w / $h)));
        } else {
            $height = ceil($height - ($height * abs($ratio - $w / $h)));
        }
        $newWidth = $w;
        $newHeight = $h;
    } else {
        if ($w / $h > $ratio) {
            $newWidth = $h * $ratio;
            $newHeight = $h;
        } else {
            $newHeight = $w / $ratio;
            $newWidth = $w;
        }
    }
    
    // Crear imagen según tipo
    switch ($type) {
        case IMAGETYPE_JPEG:
            $src = imagecreatefromjpeg($file);
            break;
        case IMAGETYPE_PNG:
            $src = imagecreatefrompng($file);
            break;
        case IMAGETYPE_GIF:
            $src = imagecreatefromgif($file);
            break;
        case IMAGETYPE_WEBP:
            $src = imagecreatefromwebp($file);
            break;
        default:
            throw new Exception("Tipo de imagen no soportado");
    }
    
    if (!$src) {
        throw new Exception("No se pudo crear la imagen desde el archivo");
    }
    
    $dst = imagecreatetruecolor($newWidth, $newHeight);
    
    // Manejar transparencia para PNG/GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagecolortransparent($dst, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Guardar imagen según tipo
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($dst, $file, 90);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($dst, $file, 9);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($dst, $file);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($dst, $file, 90);
            break;
    }
    
    // Liberar memoria
    imagedestroy($dst);
    imagedestroy($src);
    
    if (!$result) {
        throw new Exception("Error al guardar la imagen redimensionada");
    }
    
    return true;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Zidkenu</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="email"],
        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .profile-picture {
            text-align: center;
            margin: 20px 0;
        }
        .profile-picture img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #eee;
        }
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }
        .error {
            color: #dc3545;
            margin-bottom: 15px;
        }
        .success {
            color: #28a745;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include '../estructura/header.php'; ?>

    <div class="profile-container">
        <h1>Editar Perfil</h1>
        
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="edit_perfil.php" method="post" enctype="multipart/form-data">
            <div class="profile-picture">
                <img src="<?= BASE_URL . '/' . htmlspecialchars($user['foto_perfil']) ?>" alt="Foto de perfil">
                <div class="form-group">
                    <label for="foto_perfil">Cambiar foto de perfil:</label>
                    <input type="file" name="foto_perfil" accept="image/jpeg,image/png,image/gif,image/webp">
                    <small>Formatos admitidos: JPG, PNG, GIF, WEBP (Máx. 2MB)</small>
                </div>
            </div>

            <div class="form-group">
                <label for="nombres">Nombres:</label>
                <input type="text" name="nombres" value="<?= htmlspecialchars($user['nombres']) ?>" required>
            </div>

            <div class="form-group">
                <label for="apellidos">Apellidos:</label>
                <input type="text" name="apellidos" value="<?= htmlspecialchars($user['apellidos']) ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-group">
                <label for="celular">Celular:</label>
                <input type="number" name="celular" value="<?= htmlspecialchars($user['celular']) ?>">
            </div>

            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                <input type="date" name="fecha_nacimiento" value="<?= htmlspecialchars($user['fecha_nacimiento']) ?>">
            </div>

            <button type="submit" class="btn">Guardar Cambios</button>
            <a href="<?= BASE_URL ?>pantallas/home_screen.php" class="btn" style="background: #6c757d;">Cancelar</a>
        </form>
    </div>
    <?php include '../estructura/footer.php'; ?>
</body>
</html>

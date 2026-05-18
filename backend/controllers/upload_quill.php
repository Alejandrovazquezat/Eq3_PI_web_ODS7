<?php
// Archivo: backend/controllers/upload_quill.php
session_start();

// Verificamos que solo usuarios registrados puedan subir imágenes
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'error' => 'No autorizado']);
    exit;
}

if (isset($_FILES['imagen_quill']) && $_FILES['imagen_quill']['error'] === UPLOAD_ERR_OK) {
    // Carpeta donde se guardarán las imágenes dentro de la publicación
    $directorioDestino = '../../assets/uploads/quill/';
    
    // Si la carpeta no existe, la creamos
    if (!is_dir($directorioDestino)) {
        mkdir($directorioDestino, 0777, true);
    }

    $extension = strtolower(pathinfo($_FILES['imagen_quill']['name'], PATHINFO_EXTENSION));
    // Validar que sea imagen
    if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
        echo json_encode(['success' => false, 'error' => 'Formato no permitido']);
        exit;
    }

    // Nombre único para que no choquen
    $nombreArchivo = 'quill_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
    $rutaFisica = $directorioDestino . $nombreArchivo;

    if (move_uploaded_file($_FILES['imagen_quill']['tmp_name'], $rutaFisica)) {
        // Le devolvemos a Quill la URL de la imagen guardada
        echo json_encode([
            'success' => true, 
            'url' => '../../assets/uploads/quill/' . $nombreArchivo
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al mover el archivo']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No se recibió ninguna imagen válida']);
}
?>
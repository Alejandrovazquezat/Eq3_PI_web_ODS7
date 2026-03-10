<?php
session_start();

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = $_POST['titulo'];
    $categoria = $_POST['categoria'];
    $contenido = $_POST['contenido'];
    $user_id = $_SESSION['user_id']; 

    try {
        $db = new PDO('sqlite:../../database.sqlite');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->prepare("INSERT INTO posts (titulo, categoria, contenido, user_id) VALUES (:titulo, :categoria, :contenido, :user_id)");
        $stmt->bindParam(':titulo', $titulo);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->bindParam(':contenido', $contenido);
        $stmt->bindParam(':user_id', $user_id);
        
        if ($stmt->execute()) {
            $mensaje = "<div style='background: #dcfce7; color: #166534; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.95rem; text-align: center;'><i class='fas fa-check-circle'></i> ¡Publicación creada con éxito! <a href='index.php' style='color: #15803d; font-weight: bold;'>Ver en inicio</a></div>";
        }
    } catch (Exception $e) {
        $mensaje = "<div style='background: #fee2e2; color: #ef4444; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.95rem; text-align: center;'><i class='fas fa-exclamation-circle'></i> Error al publicar: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Publicar - Plataforma ODS7</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background-color: #f1f5f9;">
    <?php include 'navbar.php'; ?>

    <main class="main-content" style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 40px 0;">
        
        <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); max-width: 600px; width: 100%; border: 1px solid #e2e8f0;">
            <div style="text-align: center; margin-bottom: 30px;">
                <div style="width: 60px; height: 60px; background: #e0e7ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                    <i class="fas fa-pen-nib" style="font-size: 2rem; color: #4f46e5;"></i>
                </div>
                <h2 style="color: #0f172a; margin-bottom: 5px;">Crear Nueva Publicación</h2>
                <p style="color: #64748b; font-size: 0.95rem;">Comparte tu idea para un mundo más sustentable</p>
            </div>

            <?php echo $mensaje; ?>
            
            <form method="POST" action="publicar.php">
                <label style="color: #475569; font-weight: 600; font-size: 0.95rem; margin-bottom: 8px; display: block;">Título de tu idea:</label>
                <input type="text" name="titulo" required placeholder="Escribe un título llamativo..." style="width: 100%; padding: 14px 15px; margin-bottom: 20px; border: 1px solid #cbd5e1; border-radius: 10px; background: #f8fafc; font-size: 1rem; transition: 0.2s; outline: none;" onfocus="this.style.borderColor='#4f46e5'; this.style.boxShadow='0 0 0 3px rgba(79, 70, 229, 0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'">

                <label style="color: #475569; font-weight: 600; font-size: 0.95rem; margin-bottom: 8px; display: block;">Categoría:</label>
                <select name="categoria" required style="width: 100%; padding: 14px 15px; margin-bottom: 20px; border: 1px solid #cbd5e1; border-radius: 10px; background: #f8fafc; font-size: 1rem; transition: 0.2s; outline: none; cursor: pointer;" onfocus="this.style.borderColor='#4f46e5'; this.style.boxShadow='0 0 0 3px rgba(79, 70, 229, 0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'">
                    <option value="Energía Renovable">Energía Renovable</option>
                    <option value="Eficiencia Energética">Eficiencia Energética</option>
                    <option value="Innovación">Innovación</option>
                    <option value="Educación">Educación</option>
                </select>
                
                <label style="color: #475569; font-weight: 600; font-size: 0.95rem; margin-bottom: 8px; display: block;">Contenido:</label>
                <textarea name="contenido" rows="6" required placeholder="Desarrolla tu publicación aquí..." style="width: 100%; padding: 14px 15px; margin-bottom: 25px; border: 1px solid #cbd5e1; border-radius: 10px; background: #f8fafc; font-size: 1rem; transition: 0.2s; outline: none; resize: vertical;" onfocus="this.style.borderColor='#4f46e5'; this.style.boxShadow='0 0 0 3px rgba(79, 70, 229, 0.1)'" onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none'"></textarea>
                
                <button type="submit" style="width: 100%; padding: 15px; font-size: 1.05rem; font-weight: bold; cursor: pointer; background: linear-gradient(135deg, #4338ca, #4f46e5); color: white; border: none; border-radius: 10px; transition: 0.3s; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 15px rgba(79, 70, 229, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(79, 70, 229, 0.3)'"><i class="fas fa-paper-plane" style="margin-right: 8px;"></i> Publicar ahora</button>
            </form>
        </div>
    </main>

    <?php include 'footer.php'; ?>
</body>
</html>
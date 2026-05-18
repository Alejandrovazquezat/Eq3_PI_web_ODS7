<?php
$mensaje_mascota = "";
$tipo_mascota = ""; // Nueva variable para distinguir éxito de error

if (!empty($error)) {
    $mensaje_mascota = $error;
    $tipo_mascota = "error";
} elseif (!empty($mensaje)) {
    $mensaje_mascota = $mensaje;
    $tipo_mascota = "exito";
} elseif (!empty($_SESSION['error'])) {
    $mensaje_mascota = $_SESSION['error'];
    $tipo_mascota = "error";
    unset($_SESSION['error']);
}
?>
<div id="mascota-container" class="mascota-oculto lado-izquierdo" data-php-mensaje="<?php echo htmlspecialchars($mensaje_mascota); ?>" data-php-tipo="<?php echo htmlspecialchars($tipo_mascota); ?>">
    <div class="mascota-wrapper">
        <img id="franxx-img" src="../image/franxx_base.png" alt="Franxx - EcoBot">
        <div class="mascota-sombra"></div>
    </div>

    <div id="mascota-globo">
        <p id="mascota-mensaje"></p>
    </div>
</div>

<script src="../js/mascota.js"></script>
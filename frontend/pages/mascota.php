<?php
$mensaje_mascota = "";
if (!empty($error)) {
    $mensaje_mascota = $error;
} elseif (!empty($mensaje)) {
    $mensaje_mascota = $mensaje;
} elseif (!empty($_SESSION['error'])) {
    $mensaje_mascota = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<div id="mascota-container" class="mascota-oculto lado-izquierdo" data-php-mensaje="<?php echo htmlspecialchars($mensaje_mascota); ?>">
    <div class="mascota-wrapper">
        <img id="franxx-img" src="../image/franxx_base.png" alt="Franxx - EcoBot">
        <div class="mascota-sombra"></div>
    </div>

    <div id="mascota-globo">
        <p id="mascota-mensaje"></p>
    </div>
</div>

<script src="../js/mascota.js"></script>
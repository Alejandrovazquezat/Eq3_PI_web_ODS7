<?php
$mensaje_mascota = "";
if (!empty($error)) {
    $mensaje_mascota = $error;
} elseif (!empty($_SESSION['error'])) {
    $mensaje_mascota = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>

<div id="mascota-container" class="mascota-oculto" data-php-mensaje="<?php echo htmlspecialchars($mensaje_mascota); ?>">
    <div class="mascota-wrapper">
        <div id="mascota-css">
            <div class="eco-antena-bolita"></div>
            <div class="eco-antena"></div>
            <div class="eco-cabeza">
                <div class="eco-ojo"></div>
                <div class="eco-ojo"></div>
            </div>
            
            <div class="eco-cuerpo-contenedor">
                <div class="eco-brazo izq"></div>
                <div class="eco-cuerpo">
                    <div class="eco-pantalla">⚡</div>
                </div>
                <div class="eco-brazo der"></div>
            </div>
            
            <div class="eco-llanta"></div>
        </div>
        <div class="mascota-sombra"></div>
    </div>

    <div id="mascota-globo">
        <p id="mascota-mensaje"></p>
    </div>
</div>

<script src="../js/mascota.js"></script>
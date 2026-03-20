<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Redrenovable</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <!-- PANEL IZQUIERDO -->
    <div class="left">

        <h2>Iniciar Sesion</h2>
        <img src="image/LogotipoSinfondo.png" class="logotipo" alt="Logotipo">

        <form id="loginForm" action="login.php" method="POST">

            <label>Correo Electronico:</label>
            <input type="email" name="email" required>

            <label>Contraseña:</label>
            <input type="password" name="password" required>

            <!---<div class="remember">
                <input type="checkbox">
                <span>Remember</span>
            </div>--->

            <div style="text-align: right;">
                <button type="submit">Iniciar Secion</button>
            </div>

        </form>

        <div class="crearCuenta">
            <a href="registro.php">Crear cuenta</a>
            <!---<a href="#">No recuerdo mi contraseña</a>--->
        </div>

    </div>


    <!-- PANEL DERECHO -->
    <div class="right">
    <!--<img src="image/EquipodetrabajoPaneles.png" class="equipoTrabajo" alt="EquipoTrabajo">-->

        <div class="welcome">
            <h1>Bienvenido a<br>Redrenovable!!</h1>
            <p>Ingresa tus datos correspondientes en las casillas</p>
        </div>

        <div class="footer">
            2026 redrenovable.com todos los derechos en ISO 994
        </div>

    </div>

</div>

<script src="script.js"></script>

</body>
</html>


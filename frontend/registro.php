<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REGISTRO</title>
    <link rel="stylesheet" href="styleRegistro.css">
</head>
<body>
    <div class="conteiner">

        <!---PANEL DERECHO (FORMULARIO) --->
        <div class="right">

            <h2>REGISTRO</h2>
            <form id="registro" action="registro.php" method="POST">

                <label for="text">Nombre de usuario</label>
                <input type="text" id="user" name="user" required>

                <label for="email">Correo electronico</label>
                <input type="email" id="email" name="email" placeholder="ejemplo@email.com" required>

                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" minlength="6" required>

                <button type="submit">Registrarse</button>
            </form>
        </div>


        <!---PANEL IZQUIERDO--->
        <div class="left">

            <div class="welcome">
                <h1>Gracias por ingresar a<br>Redrenovable!!</h1>
                <p>Ingresa tus datos correspondientes en las casillas</p>
            </div>

            <div class="footer">2026 redrenovable.com all reserve to iso 994</div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
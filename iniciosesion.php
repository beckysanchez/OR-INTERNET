<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SocioMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/iniciosesion.css">
</head>

<body>

    <header>
        <div class="container d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-4">
                <h4 class="text-primary fw-bold m-0">⚽ SocioMatch</h4>
                </div>

        </div>
    </header>
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <form id="loginForm">
            <input id="correo" type="email" class="form-control" placeholder="Correo electrónico" required>
            <input id="contraseña" type="password" class="form-control" placeholder="Contraseña" required>
            <button type="submit" class="btn btn-primary">Ingresar</button>
        </form>
        <script>
            // ******************************************************
            // CONSTANTE DE BASE URL LOCAL
            // Reemplaza 'sociomatch' con el nombre de tu carpeta si es diferente
            const BASE_API_URL = 'http://192.168.1.120/api';
            // ******************************************************

            document.addEventListener("DOMContentLoaded", () => {
                const user = JSON.parse(localStorage.getItem('usuario'));
                if (user) {
                    // Si el usuario ya está logueado, redirigir a index.php
                    window.location.href = 'index.php'; 
                }
            });

            const loginForm = document.getElementById('loginForm');

            loginForm.addEventListener('submit', async (e) => {
                e.preventDefault();

                const correo = document.getElementById('correo').value;
                const contraseña = document.getElementById('contraseña').value;

                try {
                    // ******************************************************
                    // CAMBIO DE URL: De Render a XAMPP (API PHP)
                    const response = await fetch(`${BASE_API_URL}/login.php`, {
                    // ******************************************************
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        // Nota: El backend PHP necesitará leer este JSON (usando file_get_contents("php://input"))
                       body: JSON.stringify({ 
    CORREO: correo, 
    CONTRA: contraseña 
})

                    });

                    const data = await response.json();

                    if (response.ok) {
                        alert('¡Bienvenido!');
                        localStorage.setItem('usuario', JSON.stringify(data.user));
                        // ******************************************************
                        // Redirigir a la página principal (index.php)
                        window.location.href = 'index.php'; 
                        // ******************************************************

                    } else {
                        // El backend PHP debe devolver un código de error (e.g., 401)
                        alert(data.msg || 'Credenciales incorrectas');
                    }

                } catch (error) {
                    console.error('Error de conexión:', error);
                    alert('Ocurrió un error al conectar con el servidor local. Asegúrate de que XAMPP esté corriendo y el script login.php exista.');
                }
            });
        </script>


        <div class="register-link">
            ¿No tienes cuenta? <a href="registro.php">Regístrate</a> 
        </div>
    </div>


</body>

</html>
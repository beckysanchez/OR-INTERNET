<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - SocioMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/registro.css">

</head>

<body>

    <header>
        <div class="container d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-4">
                <h4 class="text-primary fw-bold m-0">⚽ SocioMatch</h4>
            </div>
        </div>
    </header>

    <div class="register-container">
        <h2>¡Regístrate!</h2>
        <p>Chatea y diviértete con tus amigos. <br>Te regalamos <strong>10 puntos</strong> al crearte una cuenta.</p>
        <form id="registerForm">
            <input id="nombre" type="text" class="form-control" placeholder="Nombre completo" required>
            <input id="Username" type="text" class="form-control" placeholder="Nombre de usuario" required>
            <input id="correo" type="email" class="form-control" placeholder="Correo electrónico" required>
            <input id="contraseña" type="password" class="form-control" placeholder="Contraseña" required>
            <input id="confirmar" type="password" class="form-control" placeholder="Confirmar contraseña" required>
            <input type="file" id="imagen" name="img_p" accept="image/*" onchange="previsualizarImagen()">
            <img id="preview" style="max-width:150px; margin-top:10px; display:block;">

            <button type="submit" class="btn btn-primary">Crear cuenta</button>
        </form>
        <div class="login-link">
            ¿Ya tienes cuenta? <a href="iniciosesion.php">Inicia sesión</a>
        </div>
    </div>

    <script>
        // ******************************************************
        // CONSTANTE DE BASE URL LOCAL
        const BASE_API_URL = 'http://localhost/sociomatch/api';
        // ******************************************************

        const registerForm = document.getElementById('registerForm');

        function previsualizarImagen() {
            let input = document.getElementById('imagen');
            let preview = document.getElementById('preview');

            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '';
            }
        }

        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const contraseña = document.getElementById('contraseña').value;
            const confirmar = document.getElementById('confirmar').value;

            if (contraseña !== confirmar) {
                alert("Las contraseñas no coinciden");
                return;
            }

            // Usamos FormData ya que el registro incluye la subida de un archivo (imagen)
            const formData = new FormData();
            formData.append('NOMBRE', document.getElementById('nombre').value);
            formData.append('CORREO', document.getElementById('correo').value);
            formData.append('Username', document.getElementById('Username').value);
            formData.append('CONTRA', contraseña);
            const imagen = document.getElementById('imagen').files[0];
            if (imagen) formData.append('img_p', imagen);
            // Requisito: Creación de usuario mediante correo electrónico [cite: 115]

            try {
                // ******************************************************
                // CAMBIO DE URL: De Render a XAMPP (API PHP)
                const response = await fetch(`${BASE_API_URL}/registro.php`, {
                // ******************************************************
                    method: 'POST',
                    body: formData // No Content-Type header necesario para FormData
                });

                const data = await response.json();

                if (response.ok) {
                    alert('✅ ' + data.msg);
                    registerForm.reset();
                    // Redirigir a iniciosesion.php
                    window.location.href = 'iniciosesion.php'; 
                } else {
                    alert('⚠️ ' + (data.msg || 'Ocurrió un error al registrar el usuario'));
                }


            } catch (error) {
                console.error('Error de conexión:', error);
                alert('Hubo un error al conectar con el servidor local. Asegúrate de que XAMPP esté corriendo y el script registro.php exista.');
            }
        });

    </script>
</body>

</html>
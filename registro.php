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
            <input id="contrasena" type="password" class="form-control" placeholder="Contraseña" required>
            <input id="confirmar" type="password" class="form-control" placeholder="Confirmar contraseña" required>
            <button type="submit" class="btn btn-primary">Crear cuenta</button>
        </form>
        <div class="login-link">
            ¿Ya tienes cuenta? <a href="iniciosesion.php">Inicia sesión</a>
        </div>
    </div>

 <script>
   document.getElementById('registerForm').addEventListener('submit', async function(e) {
  e.preventDefault();

  const nombre = document.getElementById('nombre').value.trim();
  const username = document.getElementById('Username').value.trim();
  const correo = document.getElementById('correo').value.trim();
  const password = document.getElementById('contrasena').value;
  const password2 = document.getElementById('confirmar').value;

  if (password !== password2) {
    alert('Las contraseñas no coinciden.');
    return;
  }

  const res = await fetch('api/register.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ nombre, username, correo, password })
  });

  const data = await res.json();

  if (data.success) {
    alert(data.message);
    window.location.href = 'iniciosesion.php';
  } else {
    alert(data.message);
  }
});
</script>
</body>
</html>
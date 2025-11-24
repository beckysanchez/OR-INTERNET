<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Metas Diarias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/metas.css">

</head>

<body>
    <header>
        <div class="container d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-4">
                <h4 class="text-primary fw-bold m-0">⚽ SocioMatch</h4>
                <nav class="d-none d-md-flex gap-3">
                    <a href="index.php" class="btn btn-link text-decoration-none">Inicio</a>
                    <a href="grupo.php" class="btn btn-link text-decoration-none">Crear grupo</a>
                    <a href="metas.php" class="btn btn-link text-decoration-none active">Metas Diarias</a>
                    <a href="predicciones.php" class="btn btn-link text-decoration-none">Predicciones</a>
                </nav>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="points-badge">
                    ⭐ <span id="userPoints">120</span>
                </div>
                <img src="img/image1.png" alt="perfil" class="rounded-circle"
                    style="width:40px; height:40px; object-fit:cover;">

            </div>
        </div>
    </header>

    <h2>Metas Diarias</h2>

    <div class="cards">
        <div class="card-tarea" data-tarea-id="1">✨ Crea un chat grupal</div>
        <div class="card-tarea" data-tarea-id="2">📹 Haz una videollamada</div>
        <div class="card-tarea" data-tarea-id="3">🔮 Haz una predicción</div>
        <div class="card-tarea completada" data-tarea-id="4">
            🖼️ Cambia tu foto de perfil
            <i class="bi bi-check2-circle check-icon"></i>
        </div>
    </div>

    <footer>
        ⚡ Nuevas tareas aparecerán cuando se cumplan las actuales.
        <br> Cada tarea completada da <strong>+10 puntos</strong>.
    </footer>

    <script>
        // ******************************************************
        // CONSTANTE DE BASE URL LOCAL
     const BASE_API_URL = 'http://localhost/OR-INTERNET/api';
        // ******************************************************

        document.addEventListener('DOMContentLoaded', () => {
            const user = JSON.parse(localStorage.getItem('usuario'));

            if (!user) {
                // Redirigir si no hay sesión
                window.location.href = 'iniciosesion.php';
                return;
            }

            // Actualizar datos del header (puntos e imagen de perfil)
            const userPoints = document.getElementById('userPoints');
            userPoints.textContent = user.puntos || 0;
            // Código para cargar la imagen de perfil (similar al index.php)

            // Implementación futura: Cargar tareas del usuario y su estado desde la BD
            // loadTasks(user.id_usuario); 
        });

        async function loadTasks(userId) {
            try {
                // Endpoint para obtener tareas. Ejemplo: api/tareas.php?user_id=1
                const response = await fetch(`${BASE_API_URL}/tareas.php?user_id=${userId}`);
                const tasks = await response.json();
                
                // Renderizar las tareas y añadir listeners para marcarlas como completadas
                // ... (lógica de renderizado)

            } catch (error) {
                console.error("Error al cargar tareas:", error);
                // Mostrar mensaje de error al usuario
            }
        }
    </script>
</body>

</html>

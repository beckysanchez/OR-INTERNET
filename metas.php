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

    <div class="container my-4">
        <div class="row">

    <!-- 🟢 COLUMN IZQUIERDA – METAS -->
         <div class="col-lg-5 mb-4">
              <div class="card shadow-sm">
             <div class="card-header bg-primary text-white">
              <h5 class="m-0">🎯 Metas Diarias</h5>
            </div>
            <div class="card-body" id="metasList">
          <!-- Metas generadas dinámicamente desde PHP -->
             <ul class="list-group" id="listaMetas"></ul>
         </div>
          </div>
      </div>

    <!-- 🟡 COLUMN DERECHA – PUNTOS Y RECOMPENSAS -->
     <div class="col-lg-7">

      <!-- 🟡 CARD DE PUNTOS -->
          <div class="card shadow-sm mb-4">
          <div class="card-body d-flex justify-content-between align-items-center">
              <h5 class="m-0 fw-bold">🏆 Tus Puntos Acumulados</h5>
             <span class="badge bg-success fs-5 px-4 py-2">
               ⭐ <span id="userPoints">120</span>
             </span>
         </div>
         </div>

      <!-- 🟠 CARD DE RECOMPENSAS -->
         <div class="card shadow-sm">
          <div class="card-header bg-success text-white">
              <h5 class="m-0">🎁 Recompensas Disponibles</h5>
         </div>
            <div class="card-body">
             <div class="row" id="recompensasContainer">
            <!-- Recompensas dinámicas desde la base de datos -->
             </div>
            </div>
         </div>

     </div>
     </div>
    </div>


    <footer>
        ⚡ Nuevas tareas aparecerán cuando se cumplan las actuales.
        <br> Cada tarea completada da <strong>+10 puntos</strong>.
    </footer>

    <script>
        // ******************************************************
        // CONSTANTE DE BASE URL LOCAL
     const BASE_API_URL = 'http://192.168.2.193/api';
        // ******************************************************

      document.addEventListener('DOMContentLoaded', () => {
    const user = JSON.parse(localStorage.getItem('usuario'));

    if (!user) {
        window.location.href = 'iniciosesion.php';
        return;
    }

    document.getElementById('userPoints').textContent = user.puntos || 0;

    loadMetas(user.id_usuario);
    loadRecompensas();
});

async function loadMetas(userId) {
    const res = await fetch(`${BASE_API_URL}/tareas.php?user_id=${userId}`);
    const metas = await res.json();

    const lista = document.getElementById("listaMetas");
    lista.innerHTML = '';

    metas.forEach(meta => {
        lista.innerHTML += `
          <li class="list-group-item ${meta.completada ? 'completed' : ''}">
            ${meta.descripcion}
            <span class="badge bg-primary">+${meta.puntos} pts</span>
          </li>`;
    });
}

async function loadRecompensas() {
    const res = await fetch(`${BASE_API_URL}/recompensas.php`);
    const recompensas = await res.json();

    const contenedor = document.getElementById("recompensasContainer");
    contenedor.innerHTML = '';

    recompensas.forEach(r => {
        contenedor.innerHTML += `
        <div class="col-6 col-md-4 mb-3">
          <div class="reward-card">
            <img src="${r.imagen_url}" alt="${r.nombre}">
            <h6 class="mt-2">${r.nombre}</h6>
            <p class="text-muted small">Costo: ${r.costo} pts</p>
            <button class="btn btn-sm btn-success" onclick="canjear(${r.id})">Canjear</button>
          </div>
        </div>`;
    });
}

function canjear(rewardId) {
    alert(`Intentando canjear recompensa ID: ${rewardId}`);
}


     
    </script>
</body>

</html>

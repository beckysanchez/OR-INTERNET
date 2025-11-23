<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear grupo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/grupo.css">
</head>

<body>

    <header>
        <div class="container d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-4">
                <h4 class="text-primary fw-bold m-0">⚽ SocioMatch</h4>
                <nav class="d-none d-md-flex gap-3">
                    <a href="index.php" class="btn btn-link text-decoration-none">Inicio</a>
                    <a href="grupo.php" class="btn btn-link text-decoration-none active">Crear grupo</a>
                    <a href="metas.php" class="btn btn-link text-decoration-none">Metas Diarias</a>
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

    <h2>Crear grupo</h2>

    <div class="friends-list">
        <div class="friend-item" data-id="101">
            <span>Ana</span>
            <div class="status">
                <span class="online">🟢 Conectado</span>
                <input type="checkbox" class="form-check-input friend-checkbox" value="101">
            </div>
        </div>
        <div class="friend-item" data-id="102">
            <span>Diego</span>
            <div class="status">
                <span class="offline">⚪ Desconectado</span>
                <input type="checkbox" class="form-check-input friend-checkbox" value="102">
            </div>
        </div>
        <div class="friend-item" data-id="103">
            <span>Luis</span>
            <div class="status">
                <span class="online">🟢 Conectado</span>
                <input type="checkbox" class="form-check-input friend-checkbox" value="103">
            </div>
        </div>
        <div class="friend-item" data-id="104">
            <span>María</span>
            <div class="status">
                <span class="offline">⚪ Desconectado</span>
                <input type="checkbox" class="form-check-input friend-checkbox" value="104">
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="chatTitle" class="form-label">Título del Chat</label>
        <input type="text" id="chatTitle" class="form-control" placeholder="Escribe el nombre del grupo">
    </div>

    <button class="btn btn-primary btn-create" id="createGroupBtn">Crear grupo</button>

    <div class="groups-list">
        <h4>grupos Creados</h4>
        <div id="groupsContainer">
            <p class="text-muted">No hay grupos creados aún.</p>
        </div>
    </div>

    <script>
        // ******************************************************
        // CONSTANTE DE BASE URL LOCAL
        const BASE_API_URL = 'http://localhost/sociomatch/api';
        // ******************************************************
        
        const createGroupBtn = document.getElementById('createGroupBtn');
        const groupsContainer = document.getElementById('groupsContainer');

        const user = JSON.parse(localStorage.getItem('usuario'));

        // 🚀 Al cargar la página, obtener los grupos del usuario
        document.addEventListener('DOMContentLoaded', async () => {
            if (!user) {
                alert('Debes iniciar sesión para ver tus grupos.');
                window.location.href = 'iniciosesion.php'; // Redirigir si no está logueado
                return;
            }
            // Actualizar header con datos del usuario
            document.getElementById('userPoints').textContent = user.puntos || 0;
            // Lógica para actualizar la imagen de perfil si está disponible en 'user'
            
            await cargarGruposUsuario(user.id_usuario); // Asumiendo que el ID del usuario logueado es 'id_usuario'
        });

        // 📦 Función para cargar grupos del usuario desde la BD
        async function cargarGruposUsuario(userId) {
            groupsContainer.innerHTML = '<p class="text-muted">Cargando grupos...</p>';
            try {
                // ******************************************************
                // CAMBIO DE URL: De Render a XAMPP (API PHP)
                const res = await fetch(`${BASE_API_URL}/grupos.php?user_id=${userId}`);
                // ******************************************************
                const grupos = await res.json();

                if (!grupos || grupos.length === 0) {
                    groupsContainer.innerHTML = '<p class="text-muted">No hay grupos creados aún.</p>';
                    return;
                }

                groupsContainer.innerHTML = '';
                grupos.forEach(g => {
                    const div = document.createElement('div');
                    div.classList.add('group-item', 'border', 'rounded', 'p-2', 'mb-2');
                    div.innerHTML = `
           <strong>${g.NOMBRE}</strong><br>
           <small>Creado el ${new Date(g.FECHA_CREACION).toLocaleString()}</small><br>
           <button class="btn btn-sm btn-outline-primary mt-2" onclick="abrirChatGrupo(${g.ID_GRUPO}, '${g.NOMBRE}')">
             Abrir chat
           </button>
         `;
                    groupsContainer.appendChild(div);
                });
            } catch (err) {
                console.error('❌ Error al cargar grupos:', err);
                groupsContainer.innerHTML = '<p class="text-danger">Error al conectar con el servidor.</p>';
            }
        }

        // 📤 Crear nuevo grupo
        createGroupBtn.addEventListener('click', async () => {
            const title = document.getElementById('chatTitle').value.trim();
            // Obtener los IDs de los amigos seleccionados (asumiendo que los value ahora son IDs)
            const selectedFriends = Array.from(document.querySelectorAll('.friend-checkbox:checked')).map(cb => cb.value);

            if (!user) {
                alert("Debes iniciar sesión para crear un grupo.");
                return;
            }

            // Según la rúbrica, los chats grupales deben tener un mínimo de 3 integrantes (el creador + 2 amigos).
            // Ya que el creador es automáticamente un miembro, necesitamos 2 amigos seleccionados.
            if (selectedFriends.length < 2) {
                alert("Debes seleccionar al menos 2 amigos para formar un grupo de 3 integrantes o más.");
                return;
            }

            if (!title) {
                alert("Por favor ingresa un título para el grupo.");
                return;
            }


            // Los miembros deben incluir al usuario actual (creador) y los amigos seleccionados.
            // Los IDs de los miembros deben ser numéricos (simulados en el HTML estático con value="ID").
            const miembrosIds = [user.id_usuario, ...selectedFriends.map(Number)];


            try {
                // ******************************************************
                // CAMBIO DE URL: De Render a XAMPP (API PHP)
                const res = await fetch(`${BASE_API_URL}/crear-grupo.php`, {
                // ******************************************************
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        nombre: title,
                        creador_id: user.id_usuario, 
                        miembros_ids: miembrosIds
                    })
                });

                const data = await res.json();
                if (res.ok) {
                    alert('✅ Grupo creado correctamente');
                    await cargarGruposUsuario(user.id_usuario);
                } else {
                    alert('⚠️ ' + (data.msg || 'Error al crear grupo'));
                }

            } catch (err) {
                console.error('❌ Error al conectar con el servidor:', err);
                alert('Error al conectar con el servidor.');
            }
        });

        // 💬 Abrir chat del grupo (simple por ahora)
        function abrirChatGrupo(idGrupo, nombreGrupo) {
            alert(`Abrir chat del grupo "${nombreGrupo}" (ID ${idGrupo})`);
            // Implementación futura: conectar con socket.io para el chat grupal
        }
    </script>

</body>

</html>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocioMatch</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <header>
        <div class="container d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-4">
                <h4 class="text-primary fw-bold m-0">⚽ SocioMatch</h4>
                <nav class="d-none d-md-flex gap-3">
                    <a href="iniciosesion.php" class="btn btn-link text-decoration-none active">Inicio de sesion</a>
                    <a href="grupo.php" class="btn btn-link text-decoration-none">Crear grupo</a>
                    <a href="metas.php" class="btn btn-link text-decoration-none">Metas Diarias</a>
                    <a href="predicciones.php" class="btn btn-link text-decoration-none">Predicciones</a>
                    <a href="registro.php" class="btn btn-link text-decoration-none">Registro</a>
                </nav>
            </div>

            <div class="d-flex align-items-center gap-3">
                <input type="text" class="form-control form-control-sm d-none d-sm-block" style="width:200px;"
                    placeholder="Buscar...">

                <img id="userProfilePic" src="" alt="perfil" class="rounded-circle"
                     style="width:40px; height:40px; object-fit:cover;">

                <button id="btnCerrarSesion" class="btn btn-outline-danger btn-sm" style="display:none;">
                    Cerrar sesión
                </button>
            </div>
        </div>
    </header>

    <div class="container-fluid mt-4">
        <div class="row">
            <aside class="col-lg-2 d-none d-lg-block sidebar">
                <div class="card mt-3 p-2">
                    <h6 class="fw-bold">Mis Amigos</h6>
                    <div id="sidebarFriendsList">
                        <p class="text-muted small">Aún no tienes amigos agregados.</p>
                    </div>
                </div>
            </aside>

            <section class="col-lg-7">
                <div class="row g-3">
                    <!-- 🟢 PARTIDO EN CURSO (API) -->
                    <div class="col-12">
                        <div class="card p-3 shadow-sm">
                            <h6 class="fw-bold">Partido en curso</h6>
                            <div id="partidoEnCurso" class="mt-2">
                                <em>Cargando partido...</em>
                            </div>
                        </div>
                    </div>

                    <!-- 🟢 AMIGOS -->
                    <div class="col-12">
                        <div class="card p-3">
                            <h6 class="fw-bold">Amigos</h6>
                            <input type="text" id="searchUser" class="form-control mb-2"
                                   placeholder="Buscar usuario por username">
                            <div id="searchResults" class="mb-2"></div>
                            <div id="friendsList" class="mt-3"></div>
                        </div>
                    </div>
                </div>
            </section>

            <aside class="col-lg-3">
                <div class="card p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold m-0" id="chatTitle">Chat</h6>
                        <div class="d-flex align-items-center gap-2">
                            <button id="btnVideollamada" class="btn btn-outline-primary btn-sm rounded-circle">
                                <i class="bi bi-camera-video-fill"></i>
                            </button>
                        </div>
                    </div>

                    <!-- 🔐 SWITCH DE CIFRADO -->
                    <div class="d-flex justify-content-end mb-2">
                        <div class="form-check form-switch small">
                            <input class="form-check-input" type="checkbox" id="toggleEncrypt">
                            <label class="form-check-label" for="toggleEncrypt">Cifrado</label>
                        </div>
                    </div>
                    <!-- 🔐 FIN SWITCH -->

                    <div class="chat-box mb-2"></div>

                    <div class="input-group">
                        <button class="btn btn-outline-secondary" id="btnAdjuntar" type="button">
                            <i class="bi bi-paperclip"></i>
                        </button>

                        <!-- Input real de archivos (oculto) -->
                        <input type="file" id="fileInput" class="d-none">

                        <input type="text" class="form-control" id="chatInput" placeholder="Selecciona un amigo para chatear">
                        <button class="btn btn-primary" id="btnEnviar" type="button">Enviar</button>
                    </div>

                </div>

                <!-- SOLO ESTE POPUP, nada más -->
                <div id="videoPopup" class="video-popup" style="display:none;">
                    <div class="video-content">
                        <button id="closePopup" class="btn btn-sm btn-danger mb-2">Cerrar</button>
                        <h5 class="text-center text-primary">Videollamada</h5>

                        <div class="video-row">
                            <div class="video-wrapper">
                                <video id="myVideo" autoplay playsinline muted class="rounded border"></video>
                                <div id="myNameLabel" class="video-name">Tú</div>
                            </div>

                            <div class="video-wrapper">
                                <video id="friendVideo" autoplay playsinline class="rounded border"></video>
                                <div id="friendNameLabel" class="video-name">Amigo</div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Pantalla de llamada entrante -->
                <div id="incomingCall" class="incoming-call-overlay" style="display:none;">
                    <div class="incoming-call-card">
                        <div class="incoming-call-icon">📞</div>
                        <p id="incomingCallerName" class="mb-3 fw-bold">Alguien te está llamando...</p>
                        <div class="d-flex justify-content-center gap-3">
                            <button id="btnAcceptCall" class="btn btn-success">
                                Aceptar
                            </button>
                            <button id="btnRejectCall" class="btn btn-danger">
                                Rechazar
                            </button>
                        </div>
                    </div>
                </div>
            </aside>

        </div>
    </div>

    <footer class="text-center text-muted small py-4">
        SocioMatch • Quinela + chat con amigos
    </footer>

    <script>
        // ******************************************************
        // CONSTANTE DE BASE URL
        // ******************************************************
        const BASE_API_URL = 'http://10.142.14.31/OR-INTERNET/api';

        // variables globales para el chat
        let user = null;
        let targetUserId = null;
        let conversationId = null;

        // Manejo de usuario logueado / cierre de sesión
        document.addEventListener("DOMContentLoaded", async () => {
            user = JSON.parse(localStorage.getItem('usuario'));

            const profileImg = document.getElementById('userProfilePic');
            const loginBtn = document.querySelector('nav a[href="iniciosesion.php"]');
            const registerBtn = document.querySelector('nav a[href="registro.php"]');
            const btnCerrarSesion = document.getElementById('btnCerrarSesion');

            if (user) {
                if (loginBtn) loginBtn.remove();
                if (registerBtn) registerBtn.remove();
                btnCerrarSesion.style.display = 'inline-block';

                // Imagen de perfil genérica o la de recompensa
                profileImg.src = 'img/usuario-generico.png';
            } else {
                if (profileImg) {
                    profileImg.src = 'img/usuario-generico.png';
                }
                btnCerrarSesion.style.display = 'none';
            }

            btnCerrarSesion.addEventListener('click', () => {
                localStorage.removeItem('usuario');
                location.reload();
            });

            // ---------- amigos y chat con DB real ----------
            const searchInput = document.getElementById('searchUser');
            const searchResults = document.getElementById('searchResults');
            const friendsList = document.getElementById('friendsList');
            const chatBox = document.querySelector('.chat-box');
            const input = document.getElementById('chatInput');
            const sendBtn = document.getElementById('btnEnviar');
            const attachBtn = document.getElementById('btnAdjuntar');
            const fileInput = document.getElementById('fileInput');

            let encryptionEnabled = false; // por defecto apagado

            const encryptSwitch = document.getElementById('toggleEncrypt');
            if (encryptSwitch) {
                encryptSwitch.addEventListener('change', () => {
                    encryptionEnabled = encryptSwitch.checked;
                    console.log('🔐 Cifrado activado?', encryptionEnabled);
                });
            }

            let friends = [];
            let chats = {};

            // ========= FUNCIONES PARA PINTAR MENSAJES =========
            window.addMessageToUI = function (text, isMine) {
                const div = document.createElement('div');
                div.classList.add('mensaje');
                div.classList.add(isMine ? 'mensaje-mio' : 'mensaje-otro');
                div.textContent = text;

                chatBox.appendChild(div);
                chatBox.scrollTop = chatBox.scrollHeight;
            };

            window.addFileMessageToUI = function (url, mime, isMine) {
                const div = document.createElement('div');
                div.classList.add('mensaje');
                div.classList.add(isMine ? 'mensaje-mio' : 'mensaje-otro');

                let inner = '';

                if (mime && mime.startsWith('image/')) {
                    inner = `<img src="${url}" class="img-fluid rounded" style="max-width:200px;">`;
                } else if (mime && mime.startsWith('video/')) {
                    inner = `<video src="${url}" controls style="max-width:200px;"></video>`;
                } else if (mime && mime.startsWith('audio/')) {
                    inner = `<audio src="${url}" controls></audio>`;
                } else {
                    inner = `<a href="${url}" target="_blank">Descargar archivo</a>`;
                }

                div.innerHTML = inner;
                chatBox.appendChild(div);
                chatBox.scrollTop = chatBox.scrollHeight;
            };

            function detectarTipoArchivo(mime) {
                if (!mime) return 'archivo';
                if (mime.startsWith('image/')) return 'imagen';
                if (mime.startsWith('video/')) return 'video';
                if (mime.startsWith('audio/')) return 'audio';
                return 'archivo';
            }

            // ========= CIFRADO SENCILLO (XOR + Base64) =========
            const ENC_KEY = 'mi_clave_super_secreta';

            window.encryptText = function (plain) {
                if (!plain) return plain;
                let xored = '';
                for (let i = 0; i < plain.length; i++) {
                    const k = ENC_KEY.charCodeAt(i % ENC_KEY.length);
                    xored += String.fromCharCode(plain.charCodeAt(i) ^ k);
                }
                return 'ENC:' + btoa(xored);
            };

            window.decryptText = function (stored) {
                if (!stored || !stored.startsWith('ENC:')) return stored;
                const base = stored.slice(4);
                const xored = atob(base);
                let plain = '';
                for (let i = 0; i < xored.length; i++) {
                    const k = ENC_KEY.charCodeAt(i % ENC_KEY.length);
                    plain += String.fromCharCode(xored.charCodeAt(i) ^ k);
                }
                return plain;
            };

            // ========= BOTÓN DE ADJUNTAR ARCHIVO =========
            if (attachBtn && fileInput) {
                attachBtn.addEventListener('click', () => {
                    if (!targetUserId) {
                        alert('Selecciona un amigo para enviar archivos (haz clic en "Mensaje" primero).');
                        return;
                    }
                    fileInput.click();
                });

                fileInput.addEventListener('change', async () => {
                    const file = fileInput.files[0];
                    if (!file) return;

                    if (!targetUserId) {
                        alert('Selecciona un amigo para enviar archivos.');
                        fileInput.value = '';
                        return;
                    }

                    try {
                        const formData = new FormData();
                        formData.append('archivo', file);

                        const res = await fetch(`${BASE_API_URL}/subir_archivo.php`, {
                            method: 'POST',
                            body: formData
                        });

                        const data = await res.json();
                        console.log("Archivo subido:", data);

                        if (!res.ok || data.error) {
                            alert(data.error || 'Error al subir el archivo');
                            return;
                        }

                        const tipo = detectarTipoArchivo(data.mime);

                        // 1) Mostrar de inmediato en mi chat
                        addFileMessageToUI(data.url, data.mime, true);

                        // 2) Mandar a Node para guardar en BD y reenviar al otro usuario
                        socket.emit("mensajePrivado", {
                            de: user.ID_USUARIO,
                            para: targetUserId,
                            texto: '',
                            archivo_url: data.url,
                            archivo_mime: data.mime,
                            archivo_nombre: data.nombre,
                            tipo
                        });

                    } catch (e) {
                        console.error("Error subiendo archivo:", e);
                        alert('Error de conexión al subir archivo');
                    } finally {
                        fileInput.value = '';
                    }
                });
            }

            // ========= CARGAR AMIGOS =========
            async function renderFriends() {
                if (!user) return;
                console.log("Usuario guardado:", user);
                const res = await fetch(`${BASE_API_URL}/amigos.php?id=${user.ID_USUARIO}`);

                friends = await res.json();
                friendsList.innerHTML = '';
                friends.forEach(f => {
                    const div = document.createElement('div');
                    div.classList.add('d-flex', 'justify-content-between', 'align-items-center', 'border-bottom', 'py-2');
                    div.innerHTML = `
                        <div>
                            <img src="${f.img_p || 'img/usuario-generico.png'}" class="rounded-circle me-2" style="width:30px;height:30px;object-fit:cover;">
                            ${f.Username} <small class="text-muted">Amigo</small>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-outline-secondary btnMessage">Mensaje</button>
                        </div>
                    `;
                    friendsList.appendChild(div);

                    div.querySelector('.btnMessage').addEventListener('click', () => {
                        window.targetUserId = f.ID_USUARIO;
                        window.targetUserName = f.Username;
                        openChat(f.Username, f.ID_USUARIO);
                    });
                });
                renderSidebarFriends();
            }

            // 🟢 NUEVA función que llena la card lateral
            function renderSidebarFriends() {
                const sidebarFriends = document.getElementById('sidebarFriendsList');
                sidebarFriends.innerHTML = '';
                if (friends.length === 0) {
                    sidebarFriends.innerHTML = `<p class="text-muted small">Aún no tienes amigos agregados.</p>`;
                    return;
                }

                friends.forEach(f => {
                    const div = document.createElement('div');
                    div.classList.add('d-flex', 'align-items-center', 'mb-2');
                    div.innerHTML = `
                        <img src="${f.img_p || 'img/usuario-generico.png'}" 
                            class="rounded-circle me-2" 
                            style="width:30px;height:30px;object-fit:cover;">
                        <span>${f.Username}</span>
                    `;
                    sidebarFriends.appendChild(div);
                });
            }

            // 🔍 Buscar usuarios en la BD y mostrar botón "Agregar"
            searchInput.addEventListener('input', async () => {
                const query = searchInput.value.trim();
                searchResults.innerHTML = '';
                if (!query) return;

                try {
                    const res = await fetch(`${BASE_API_URL}/usuarios.php?q=${query}`);
                    const users = await res.json();

                    console.log('👀 Usuarios recibidos:', users);

                    users.forEach(u => {
                        if (friends.some(f => f.ID_USUARIO === u.ID_USUARIO) || u.ID_USUARIO === user.ID_USUARIO) return;

                        const div = document.createElement('div');
                        div.classList.add('d-flex', 'justify-content-between', 'align-items-center', 'border', 'p-1', 'mb-1', 'rounded');
                        div.innerHTML = `
                            <div class="d-flex align-items-center">
                                <img src="${u.img_p ? `data:image/png;base64,${u.img_p}` : 'img/usuario-generico.png'}"
                                    class="rounded-circle me-2"
                                    style="width:30px;height:30px;object-fit:cover;">
                                ${u.Username}
                            </div>
                            <button class="btn btn-sm btn-primary">Agregar</button>
                        `;
                        searchResults.appendChild(div);

                        div.querySelector('button').addEventListener('click', async () => {
                            try {
                                const res = await fetch(`${BASE_API_URL}/agregar_amigo.php`, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json' },
                                    body: JSON.stringify({
                                        ID_USUARIO1: user.ID_USUARIO,
                                        ID_USUARIO2: u.ID_USUARIO
                                    })
                                });
                                const data = await res.json();
                                console.log('📩 Respuesta al agregar amigo:', data);
                                console.log('Voy a mandar:', {ID_USUARIO1: user.ID_USUARIO, ID_USUARIO2: u.ID_USUARIO});

                                if (res.ok) {
                                    alert('Amigo agregado correctamente');
                                    renderFriends();
                                    searchResults.innerHTML = '';
                                    searchInput.value = '';
                                } else {
                                    alert('Error al agregar amigo: ' + (data.msg || ''));
                                }
                            } catch (err) {
                                console.error('Error en fetch:', err);
                                alert('Error al conectar con el servidor');
                            }
                        });
                    });

                } catch (error) {
                    console.error('❌ Error cargando usuarios:', error);
                }
            });

            document.addEventListener("DOMContentLoaded", loadLiveMatch);

            // ========= ABRIR CHAT CON UN AMIGO =========
            async function openChat(friendUsername, friendId) {
                try {
                    chats = {};

                    if (!user) {
                        alert("No hay usuario en sesión. Vuelve a iniciar sesión.");
                        return;
                    }

                    targetUserId = friendId;

                    const chatTitle = document.getElementById('chatTitle');
                    chatTitle.textContent = `Chat con ${friendUsername}`;

                    chatBox.innerHTML = '';
                    input.placeholder = `Escribe un mensaje a ${friendUsername}...`;

                    // 1️⃣ Obtener ID_CONVERSACION desde PHP (si no existe, lo crea)
                    console.log("Pidiendo conversación a PHP...", user.ID_USUARIO, friendId);
                    const res = await fetch(`${BASE_API_URL}/obtener_conversacion.php?id1=${user.ID_USUARIO}&id2=${friendId}`);

                    const raw = await res.text();
                    console.log("🔎 Respuesta cruda obtener_conversacion.php:", raw);

                    let data;
                    try {
                        data = JSON.parse(raw);
                    } catch (e) {
                        console.error("❌ No es JSON válido en obtener_conversacion:", e);
                        alert("La API de conversación no está devolviendo JSON válido. Revisa la consola.");
                        return;
                    }

                    console.log("Conversación:", data);
                    conversationId = data.ID_CONVERSACION;

                    // 2️⃣ Cargar historial desde BD
                    const resHist = await fetch(`${BASE_API_URL}/obtener_mensaje.php?ID_CONVERSACION=${conversationId}`);

                    const rawHist = await resHist.text();
                    console.log("🔎 Respuesta cruda obtener_mensaje.php:", rawHist);

                    let mensajes;
                    try {
                        mensajes = JSON.parse(rawHist);
                    } catch (e) {
                        console.error("❌ No es JSON válido en obtener_mensaje:", e);
                        alert("La API de mensajes no está devolviendo JSON válido. Revisa la consola.");
                        return;
                    }

                    console.log("Historial:", mensajes);

                    mensajes.forEach(m => {
                        const emisorId = m.ID_EMISOR ?? m.id_emisor;
                        const esMio = emisorId == user.ID_USUARIO;

                        if (m.ARCHIVO_URL) {
                            addFileMessageToUI(m.ARCHIVO_URL, m.ARCHIVO_MIME, esMio);
                        }
                        if (m.MENSAJE) {
                            const textoPlano = decryptText(m.MENSAJE);
                            addMessageToUI(textoPlano, esMio);
                        }
                    });

                    // 3️⃣ Enviar mensaje: mostrar + enviar solo por socket
                    sendBtn.onclick = () => {
                        let text = input.value.trim();
                        if (!text) return;

                        console.log("Enviando mensaje (texto plano):", text);

                        addMessageToUI(text, true);
                        input.value = '';

                        const textoParaEnviar = encryptionEnabled ? encryptText(text) : text;

                        socket.emit("mensajePrivado", {
                            de: user.ID_USUARIO,
                            para: friendId,
                            texto: textoParaEnviar
                        });
                    };

                } catch (err) {
                    console.error("❌ Error en openChat:", err);
                    alert("Ocurrió un error abriendo el chat (ver consola).");
                }
            }

            renderFriends();
        });
    </script>

    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const partido = JSON.parse(localStorage.getItem("partidoSeleccionado"));
            const selectedMatchEl   = document.getElementById("selectedMatch");
            const homeTeamEl        = document.getElementById("homeTeam");
            const awayTeamEl        = document.getElementById("awayTeam");
            const selectedMatchIdEl = document.getElementById("selectedMatchId");
            const predictionFormEl  = document.getElementById("predictionForm");

            if (!selectedMatchEl || !homeTeamEl || !awayTeamEl || !selectedMatchIdEl || !predictionFormEl) {
                return;
            }

            if (partido) {
                selectedMatchEl.textContent   = partido.name;
                homeTeamEl.textContent        = partido.home;
                awayTeamEl.textContent        = partido.away;
                selectedMatchIdEl.value       = partido.id;
                predictionFormEl.style.display = 'block';
            }
        });

        const partidoCard = document.getElementById("partidoEnCurso");
        let partidos = [];
        let indexPartido = 0;

        async function loadLiveMatch() {
            try {
                const res = await fetch("https://www.thesportsdb.com/api/v1/json/3/eventspastleague.php?id=4328");
                const data = await res.json();

                partidos = data.events || [];
                mostrarPartido();
            } catch (err) {
                console.error(err);
                partidoCard.innerHTML = `<em>Error al cargar partido.</em>`;
            }
        }

        function mostrarPartido() {
            if (!partidos || partidos.length === 0) {
                partidoCard.innerHTML = `<em>No hay partidos disponibles.</em>`;
                return;
            }

            const partido = partidos[indexPartido];

            partidoCard.innerHTML = `
                <div class="alert alert-danger p-2 mt-2 shadow-sm text-center">
                    <strong>${partido.strHomeTeam} vs ${partido.strAwayTeam}</strong><br>
                    ${partido.intHomeScore} - ${partido.intAwayScore} <br>
                    <small>${partido.dateEvent} • ${partido.strTime}</small><br>

                    <div class="mt-2 d-flex gap-2 justify-content-center">
                        <button class="btn btn-sm btn-warning" id="btnPredecir">Predecir</button>
                        <button class="btn btn-sm btn-outline-secondary" id="btnSiguiente">Siguiente partido ⏭️</button>
                    </div>
                </div>
            `;

            document.getElementById("btnSiguiente").addEventListener("click", () => {
                indexPartido = (indexPartido + 1) % partidos.length;
                mostrarPartido();
            });

            document.getElementById("btnPredecir").addEventListener("click", () => {
                const matchInfo = {
                    id: partido.idEvent,
                    name: `${partido.strHomeTeam} vs ${partido.strAwayTeam}`,
                    home: partido.strHomeTeam,
                    away: partido.strAwayTeam
                };
                localStorage.setItem("partidoSeleccionado", JSON.stringify(matchInfo));
                window.location.href = "predicciones.php";
            });
        }

        async function loadProfileImage() {
            const user = JSON.parse(localStorage.getItem('usuario'));
            if (!user) return;

            try {
                const res = await fetch(`${BASE_API_URL}/get_recompensa_usuario.php?id_usuario=${user.ID_USUARIO}`);
                const recompensas = await res.json();

                const activa = recompensas.find(r => r.seleccionada == 1);

                const img = document.getElementById('userProfilePic');
                if (!img) return;

                img.src = activa ? activa.imagen_url : 'img/usuario-generico.png';
            } catch (e) {
                console.error("Error cargando recompensas/imagen de perfil:", e);
                const img = document.getElementById('userProfilePic');
                if (img) img.src = 'img/usuario-generico.png';
            }
        }

        loadProfileImage();
        loadLiveMatch();
    </script>

    <script>
        // =========================================================
        // ⚙️ DATOS DEL USUARIO LOGUEADO
        // =========================================================
        const storedUser = JSON.parse(localStorage.getItem('usuario'));
        let currentUser = null;

        if (storedUser) {
            currentUser = {
                id: storedUser.ID_USUARIO,
                username: storedUser.Username
            };
        } else {
            console.warn("No hay usuario logueado, la videollamada se desactivará.");
        }

        window.targetUserId = window.targetUserId || null;
        window.targetUserName = window.targetUserName || null;

        // =========================================================
        // ⚙️ SOCKET.IO
        // =========================================================
        const socket = io(`${window.location.protocol}//${window.location.hostname}:3000`);

        socket.on('recibirMensaje', ({ de, texto, archivo_url, archivo_mime, tipo }) => {
            if (de !== targetUserId) {
                console.log('Mensaje de otro chat:', { de, texto, archivo_url });
                return;
            }

            if (archivo_url) {
                addFileMessageToUI(archivo_url, archivo_mime, false);
            }

            if (texto) {
                const textoPlano = window.decryptText(texto);
                addMessageToUI(textoPlano, false);
            }
        });

        // =========================================================
        // ⚙️ VARIABLES WEBRTC
        // =========================================================
        let pc = null;
        let localStream = null;
        let remoteStream = null;
        const pendingIceCandidates = [];

        let inCall = false;
        let currentCallPeerId = null;
        let pendingOffer = null;

        const rtcConfig = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        };

        const btnVideollamada = document.getElementById("btnVideollamada");
        const videoPopup      = document.getElementById("videoPopup");
        const closePopup      = document.getElementById("closePopup");
        const myVideo         = document.getElementById("myVideo");
        const friendVideo     = document.getElementById("friendVideo");

        const myNameLabel     = document.getElementById("myNameLabel");
        const friendNameLabel = document.getElementById("friendNameLabel");

        const incomingCallOverlay = document.getElementById("incomingCall");
        const incomingCallerName  = document.getElementById("incomingCallerName");
        const btnAcceptCall       = document.getElementById("btnAcceptCall");
        const btnRejectCall       = document.getElementById("btnRejectCall");

        document.addEventListener("DOMContentLoaded", () => {
            if (!btnVideollamada || !videoPopup || !closePopup || !myVideo || !friendVideo) {
                console.error("⚠️ Elementos de videollamada no encontrados en el DOM.");
                return;
            }

            if (!currentUser) {
                btnVideollamada.addEventListener("click", () => {
                    alert("Inicia sesión para usar la videollamada.");
                });
                return;
            }

            socket.on('connect', () => {
                console.log("🟢 Conectado a Socket.IO:", socket.id);
                socket.emit("registrarUsuario", currentUser.id);
            });

            btnVideollamada.addEventListener("click", () => {
                if (!window.targetUserId) {
                    alert('Selecciona un amigo para llamar (haz clic en "mensaje" primero).');
                    return;
                }
                startCall(window.targetUserId, window.targetUserName);
            });

            closePopup.addEventListener("click", () => {
                endCall(true);
            });

            btnAcceptCall.addEventListener("click", async () => {
                if (!pendingOffer) return;
                incomingCallOverlay.style.display = "none";
                videoPopup.style.display = "flex";

                try {
                    inCall = true;
                    currentCallPeerId = pendingOffer.from;

                    myNameLabel.textContent     = currentUser.username;
                    friendNameLabel.textContent = pendingOffer.fromName || "Amigo";

                    await preparePeer(pendingOffer.from);
                    await pc.setRemoteDescription(new RTCSessionDescription(pendingOffer.sdp));

                    const answer = await pc.createAnswer();
                    await pc.setLocalDescription(answer);

                    socket.emit('answer', {
                        to: pendingOffer.from,
                        from: currentUser.id,
                        sdp: answer
                    });

                    flushPendingIceCandidates();
                    pendingOffer = null;

                } catch (e) {
                    console.error("❌ Error al aceptar llamada:", e);
                    endCall(false);
                }
            });

            btnRejectCall.addEventListener("click", () => {
                pendingOffer = null;
                currentCallPeerId = null;
                inCall = false;
                incomingCallOverlay.style.display = "none";
            });

            setupSocketListeners();
        });

        async function startCall(targetId, targetName) {
            if (inCall && currentCallPeerId === targetId) {
                console.log('⚠️ Ya estás en llamada con este usuario.');
                return;
            }

            if (inCall && currentCallPeerId !== targetId) {
                alert('Ya estás en una llamada, cuelga primero.');
                return;
            }

            console.log('📞 Iniciando llamada a usuario:', targetId);
            videoPopup.style.display = "flex";

            try {
                inCall = true;
                currentCallPeerId = targetId;

                myNameLabel.textContent     = currentUser.username;
                friendNameLabel.textContent = targetName || "Amigo";

                await preparePeer(targetId);
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);

                socket.emit('offer', {
                    to: targetId,
                    from: currentUser.id,
                    fromName: currentUser.username,
                    sdp: offer
                });
                console.log('📤 Oferta enviada.');

            } catch (e) {
                console.error('Error al iniciar llamada:', e);
                alert('Error al iniciar llamada: ' + (e?.message || e));
                endCall(false);
            }
        }

        async function preparePeer(targetId) {
            if (pc) return;

            pc = new RTCPeerConnection(rtcConfig);

            pc.onicecandidate = (event) => {
                if (event.candidate) {
                    socket.emit('ice-candidate', {
                        to: targetId,
                        from: currentUser.id,
                        candidate: event.candidate
                    });
                }
            };

            pc.ontrack = (event) => {
                console.log('✅ Stream remoto recibido.');
                if (friendVideo) friendVideo.srcObject = event.streams[0];
            };

            try {
                localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
                if (myVideo) myVideo.srcObject = localStream;
                localStream.getTracks().forEach((track) => pc.addTrack(track, localStream));
            } catch (e) {
                console.error('❌ Error accediendo a cámara/micro:', e);
                throw new Error('Cámara/micrófono no disponibles. Verifica permisos (HTTPS/localhost).');
            }
        }

        async function flushPendingIceCandidates() {
            if (!pc || !pc.remoteDescription || !pendingIceCandidates.length) return;
            const queued = pendingIceCandidates.splice(0, pendingIceCandidates.length);
            for (const c of queued) {
                try {
                    await pc.addIceCandidate(new RTCIceCandidate(c));
                } catch (e) {
                    console.error('Error aplicando ICE candidate:', e);
                }
            }
        }

        function endCall(notifyPeer = false) {
            if (notifyPeer && currentCallPeerId) {
                console.log('👋 Fin de llamada (podrías emitir end-call aquí).');
            }

            try { localStream && localStream.getTracks().forEach(t => t.stop()); } catch {}
            try { pc && pc.close(); } catch {}

            pc = null;
            localStream = null;
            remoteStream = null;
            pendingIceCandidates.length = 0;

            if (myVideo) myVideo.srcObject = null;
            if (friendVideo) friendVideo.srcObject = null;

            videoPopup.style.display = "none";

            inCall = false;
            currentCallPeerId = null;

            console.log('📞 Llamada finalizada y recursos limpiados.');
        }

        function setupSocketListeners() {
            socket.on('offer', async ({ from, fromName, sdp }) => {
                if (!currentUser) return;
                if (from === currentUser.id) return;

                if (inCall && currentCallPeerId && currentCallPeerId !== from) {
                    console.log('⚠️ Ya estoy en llamada con otro usuario. Ignoro la oferta.');
                    return;
                }

                console.log(`📥 Oferta recibida de ${from} (${fromName}).`);

                pendingOffer = { from, fromName, sdp };
                currentCallPeerId = from;

                incomingCallerName.textContent = `📞 ${fromName || 'Alguien'} te está llamando...`;
                incomingCallOverlay.style.display = "flex";
            });

            socket.on('answer', async ({ from, sdp }) => {
                if (!pc || !currentCallPeerId || from !== currentCallPeerId) {
                    console.log('⚠️ Respuesta ignorada (no coincide con la llamada actual).');
                    return;
                }

                console.log(`📥 Respuesta recibida de ${from}.`);
                try {
                    await pc.setRemoteDescription(new RTCSessionDescription(sdp));
                    flushPendingIceCandidates();
                    console.log('🎉 Conexión P2P establecida (answer).');
                } catch (e) {
                    console.error('Error al procesar respuesta:', e);
                }
            });

            socket.on('ice-candidate', async ({ from, candidate }) => {
                if (!currentCallPeerId || from !== currentCallPeerId) {
                    console.log('⚠️ ICE candidate ignorado (no coincide con la llamada actual).');
                    return;
                }

                if (!candidate) return;
                if (pc && pc.remoteDescription && pc.remoteDescription.type) {
                    try {
                        await pc.addIceCandidate(new RTCIceCandidate(candidate));
                    } catch (e) {
                        console.warn('Error aplicando ICE candidate:', e.name);
                    }
                } else {
                    pendingIceCandidates.push(candidate);
                }
            });
        }
    </script>

</body>
</html>

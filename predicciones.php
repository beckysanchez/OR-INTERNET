<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Predicciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/predicciones.css">

</head>

<body>

    <header>
        <div class="container d-flex justify-content-between align-items-center py-3">
            <div class="d-flex align-items-center gap-4">
                <h4 class="text-primary fw-bold m-0">⚽ SocioMatch</h4>
                <nav class="d-none d-md-flex gap-3">
                    <a href="index.php" class="btn btn-link text-decoration-none">Inicio</a>
                    <a href="grupo.php" class="btn btn-link text-decoration-none">Crear grupo</a>
                    <a href="metas.php" class="btn btn-link text-decoration-none">Metas Diarias</a>
                    <a href="predicciones.php" class="btn btn-link text-decoration-none active">Predicciones</a>
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

    <h2>Predicciones Mundial 2025</h2>

    <div class="container-main">

        <div class="matches-list">
            <h4>Partidos</h4>
            <div class="match-item" data-match-id="M001" data-match="Brasil vs España">Brasil vs España</div>
            <div class="match-item" data-match-id="M002" data-match="Francia vs Alemania">Francia vs Alemania</div>
            <div class="match-item" data-match-id="M003" data-match="Argentina vs México">Argentina vs México</div>
            <div class="match-item" data-match-id="M004" data-match="Portugal vs Inglaterra">Portugal vs Inglaterra</div>
            <div class="match-item" data-match-id="M005" data-match="Japón vs Corea del Sur">Japón vs Corea del Sur</div>
        </div>

        <div class="prediction-card" id="predictionCard">
            <h3>MARCADOR</h3>
            <div id="predictionForm" style="display:none;">
                <p id="selectedMatch" class="text-center fw-bold mb-3"></p>
                <input type="hidden" id="selectedMatchId">

                <div class="score-inputs">
                    <div>
                        <select id="scoreHome" aria-label="Goles equipo local">
                            <option value="">-</option>
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                        </select>
                        <div class="team-label" id="homeTeam">Local</div>
                    </div>

                    <span class="fw-bold">vs</span>

                    <div>
                        <select id="scoreAway" aria-label="Goles equipo visitante">
                            <option value="">-</option>
                            <option value="0">0</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                        </select>
                        <div class="team-label" id="awayTeam">Visitante</div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <button class="btn btn-primary btn-sm" id="submitPrediction">Predecir</button>
                </div>
            </div>
        </div>

        <div class="results-card">
            <h4>Predicciones Pasadas</h4>
            <div id="pastResults">
                <div class="result-item">Brasil 2 - 1 España ✅ +25 pts</div>
                <div class="result-item">Francia 1 - 1 Alemania ❌ +0 pts</div>
                <div class="result-item">Argentina 3 - 0 México ✅ +25 pts</div>
                <div class="result-item">Portugal 2 - 2 Inglaterra ❌ +0 pts</div>
            </div>
        </div>

    </div>

    <script>
        // ******************************************************
        // CONSTANTE DE BASE URL LOCAL
     const BASE_API_URL = 'http://localhost/OR-INTERNET/api';
        // ******************************************************
        
        const matchItems = document.querySelectorAll('.match-item');
        const predictionForm = document.getElementById('predictionForm');
        const selectedMatchText = document.getElementById('selectedMatch');
        const selectedMatchIdInput = document.getElementById('selectedMatchId');
        const submitPrediction = document.getElementById('submitPrediction');
        const userPointsElement = document.getElementById('userPoints');
        const homeTeamLabel = document.getElementById('homeTeam');
        const awayTeamLabel = document.getElementById('awayTeam');
        const pastResults = document.getElementById('pastResults');

        let user; 
        
        document.addEventListener('DOMContentLoaded', () => {
            user = JSON.parse(localStorage.getItem('usuario'));
            if (!user) {
                window.location.href = 'iniciosesion.php';
                return;
            }
            
            // Actualizar puntos y perfil del usuario
            userPointsElement.textContent = user.puntos || 0;
            // ... (Lógica para actualizar la imagen de perfil)
            
            // Implementación futura: cargar partidos y resultados pasados desde la BD
            // loadMatches(); 
            // loadPastPredictions(user.id_usuario);
        });
        
        function clearSelectedMatchHighlight() {
            matchItems.forEach(mi => mi.classList.remove('selected'));
        }

        matchItems.forEach(item => {
            item.addEventListener('click', () => {
                clearSelectedMatchHighlight();
                item.classList.add('selected');

                const matchName = item.getAttribute('data-match');
                const matchId = item.getAttribute('data-match-id');
                
                selectedMatchText.textContent = matchName;
                selectedMatchIdInput.value = matchId; // Almacenar el ID
                
                const parts = matchName.split(" vs ");
                const home = parts[0] ? parts[0].trim() : '';
                const away = parts[1] ? parts[1].trim() : '';
                
                homeTeamLabel.textContent = home || 'Local';
                awayTeamLabel.textContent = away || 'Visitante';
                predictionForm.style.display = 'block';
            });
        });

        submitPrediction.addEventListener('click', async () => {
            const home = document.getElementById('scoreHome').value;
            const away = document.getElementById('scoreAway').value;
            const matchName = selectedMatchText.textContent.trim();
            const matchId = selectedMatchIdInput.value;
            
            if (!user) {
                alert("Debes iniciar sesión para predecir.");
                return;
            }

            if (!matchId || !matchName) {
                alert('Selecciona un partido primero.');
                return;
            }
            if (home === '' || away === '') {
                alert('Selecciona ambos marcadores (0–10).');
                return;
            }

            try {
                // ******************************************************
                // CAMBIO DE URL: De Render a XAMPP (API PHP)
                const res = await fetch(`${BASE_API_URL}/predecir.php`, {
                // ******************************************************
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        usuario_id: user.id_usuario,
                        partido_id: matchId,
                        marcador_local: parseInt(home),
                        marcador_visitante: parseInt(away)
                    })
                });

                const data = await res.json();

                if (res.ok) {
                    alert('✅ Predicción enviada correctamente: ' + data.msg);
                    
                    // --- Simulación de Actualización de Puntos (debe venir del backend) ---
                    // En la vida real, solo se sumarían puntos si se acierta y el partido ha terminado.
                    // Aquí, solo actualizamos la interfaz limpiándola.
                    
                    // Limpiar interfaz
                    document.getElementById('scoreHome').value = '';
                    document.getElementById('scoreAway').value = '';
                    predictionForm.style.display = 'none';
                    selectedMatchText.textContent = '';
                    homeTeamLabel.textContent = 'Local';
                    awayTeamLabel.textContent = 'Visitante';
                    clearSelectedMatchHighlight();

                } else {
                    alert('⚠️ Error al enviar predicción: ' + (data.msg || 'Inténtalo de nuevo.'));
                }
                
            } catch (error) {
                console.error('Error al conectar con el servidor:', error);
                alert('Error al conectar con el servidor. Asegúrate de que XAMPP esté corriendo.');
            }
        });
    </script>

</body>

</html>
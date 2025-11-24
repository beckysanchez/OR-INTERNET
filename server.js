const express = require('express');
const path = require('path');
const cors = require('cors');
const db = require('./db');
const multer = require('multer');
const http = require('http');
const { Server } = require('socket.io');

const app = express();

// ------------------ CORS ------------------
const allowedOrigins = [
  'http://localhost',        // Frontend en XAMPP
  'http://localhost:3000',   // Por si usas React o Node frontend
  'http://127.0.0.1',        // Otro localhost válido
];


app.use(cors({
  origin: (origin, callback) => {
    if (!origin || allowedOrigins.includes(origin)) {
      return callback(null, true);
    } else {
      console.warn('Bloqueado por CORS:', origin);
      return callback(new Error('No permitido por CORS'));
    }
  },
  credentials: true,
}));

app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', req.headers.origin || '*');
  res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  next();
});

// ------------------ MIDDLEWARES ------------------
app.use(express.json());
app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'public')));

const storage = multer.memoryStorage();
const upload = multer({ storage });

// ------------------ RUTAS ------------------
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'registro.html'));
});

app.post('/registro', upload.single('img_p'), (req, res) => {
  const nombre = req.body.NOMBRE;
  const correo = req.body.CORREO.trim().toLowerCase();
  const Username = req.body.Username.trim().toLowerCase();
  const contraseña = req.body.CONTRA;
  const imagen = req.file ? req.file.buffer.toString('base64') : null;

  if (!nombre || !correo || !contraseña || !Username)
    return res.status(400).json({ msg: 'Faltan datos' });

  const checkSql = 'SELECT * FROM usuario WHERE CORREO = ? OR Username = ?';
  db.query(checkSql, [correo, Username], (err, result) => {
    if (err) {
      console.error('❌ Error al verificar duplicados:', err);
      return res.status(500).json({ msg: 'Error en el servidor' });
    }

    if (result.length > 0) {
      const existente = result[0];
      if (existente.CORREO === correo && existente.Username === Username)
        return res.status(409).json({ msg: 'Correo y usuario ya registrados' });
      else if (existente.CORREO === correo)
        return res.status(409).json({ msg: 'Correo ya registrado' });
      else if (existente.Username === Username)
        return res.status(409).json({ msg: 'Usuario ya registrado' });
    }

    const insertSql = `
      INSERT INTO usuario (NOMBRE, CORREO, CONTRA, Username, puntos, img_p)
      VALUES (?, ?, ?, ?, 10, ?)
    `;
    db.query(insertSql, [nombre, correo, contraseña, Username, imagen], (err, result) => {
      if (err) {
        if (err.code === 'ER_DUP_ENTRY')
          return res.status(409).json({ msg: 'Correo o usuario ya existen' });
        console.error('❌ Error al insertar:', err);
        return res.status(500).json({ msg: 'Error en el servidor' });
      }

      console.log('✅ Usuario registrado con ID:', result.insertId);
      res.json({ msg: 'Usuario registrado exitosamente' });
    });
  });
});

app.post('/login', (req, res) => {
  const correo = req.body.CORREO;
  const contraseña = req.body.CONTRA;

  if (!correo || !contraseña)
    return res.status(400).json({ msg: 'Faltan datos' });

  const sql = 'SELECT * FROM usuario WHERE CORREO = ? AND CONTRA = ?';
  db.query(sql, [correo, contraseña], (err, result) => {
    if (err) {
      console.error('❌ Error al consultar:', err);
      return res.status(500).json({ msg: 'Error en el servidor' });
    }

    if (result.length === 0)
      return res.status(401).json({ msg: 'Correo o contraseña incorrectos' });

    res.json({
      msg: 'Login exitoso',
      user: {
        id: result[0].ID_USUARIO, // 👈 revisa si en tu tabla es ID_USUARIO o ID_usuario
        NOMBRE: result[0].NOMBRE,
        CORREO: result[0].CORREO,
        Username: result[0].Username,
        puntos: result[0].puntos,
        img_p: result[0].img_p,
      },
    });
  });
});

// 🔍 Buscar usuarios (para buscador de amigos)
app.get('/usuarios', (req, res) => {
  const q = req.query.q || '';
  const sql = 'SELECT ID_USUARIO, Username, img_p, puntos FROM usuario WHERE Username LIKE ?';
  
  db.query(sql, [`%${q}%`], (err, result) => {
    if (err) {
      console.error('❌ Error al consultar usuarios:', err);
      return res.status(500).json({ msg: 'Error al obtener usuarios' });
    }
    res.json(result);
  });
});


// 🤝 Agregar amigo
// 📩 Agregar amigo
app.post('/agregar-amigo', (req, res) => {
  const { usuario_id, amigo_id } = req.body;

  if (!usuario_id || !amigo_id) {
    return res.status(400).json({ msg: 'Faltan datos' });
  }

  const sql = `
    INSERT INTO amistad (ID_USUARIO1, ID_USUARIO2, FECHA_AMISTAD)
    VALUES (?, ?, NOW())
  `;

  db.query(sql, [usuario_id, amigo_id], (err) => {
    if (err) {
      if (err.code === 'ER_DUP_ENTRY') {
        return res.status(409).json({ msg: 'Ya son amigos' });
      }
      console.error('❌ Error al agregar amigo:', err);
      return res.status(500).json({ msg: 'Error en el servidor' });
    }

    console.log(`✅ Nueva amistad: ${usuario_id} ↔ ${amigo_id}`);
    res.json({ msg: 'Amigo agregado correctamente' });
  });
});



// 📋 Obtener lista de amigos de un usuario
app.get('/amigos/:userId', (req, res) => {
  const { userId } = req.params;
  const sql = `
    SELECT u.ID_USUARIO, u.Username, u.img_p, u.puntos
    FROM amigos a
    JOIN usuario u ON u.ID_USUARIO = a.ID_USUARIO2
    WHERE a.ID_USUARIO1 = ?
  `;

  db.query(sql, [userId], (err, result) => {
    if (err) {
      console.error('❌ Error al obtener amigos:', err);
      return res.status(500).json({ msg: 'Error al obtener amigos' });
    }
    res.json(result);
  });
});


// ====================
// 📌 RUTAS DE GRUPOS
// ====================

// Crear un grupo
app.post('/crear-grupo', (req, res) => {
  const { nombre, creador_id, miembros } = req.body;

  if (!nombre || !creador_id || !Array.isArray(miembros)) {
    return res.status(400).json({ msg: 'Faltan datos para crear grupo' });
  }

  // 1️⃣ Crear grupo
  const insertGrupo = 'INSERT INTO grupo (NOMBRE) VALUES (?)';
  db.query(insertGrupo, [nombre], (err, result) => {
    if (err) {
      if (err.code === 'ER_DUP_ENTRY') {
        return res.status(409).json({ msg: 'Ya existe un grupo con ese nombre' });
      }
      console.error('❌ Error al crear grupo:', err);
      return res.status(500).json({ msg: 'Error al crear grupo' });
    }

    const grupoId = result.insertId;

    // 2️⃣ Agregar miembros (incluyendo al creador)
    const todosMiembros = [...miembros, creador_id];
    const values = todosMiembros.map(id => [grupoId, id]);

    const insertMiembros = 'INSERT INTO grupo_miembros (ID_GRUPO, ID_USUARIO) VALUES ?';
    db.query(insertMiembros, [values], (err2) => {
      if (err2) {
        console.error('❌ Error al agregar miembros:', err2);
        return res.status(500).json({ msg: 'Error al agregar miembros' });
      }

      console.log(`✅ Grupo "${nombre}" creado con ID ${grupoId}`);
      res.json({ msg: 'Grupo creado correctamente', grupoId });
    });
  });
});

// Obtener los grupos de un usuario
app.get('/grupos/:userId', (req, res) => {
  const { userId } = req.params;
  const sql = `
    SELECT g.ID_GRUPO, g.NOMBRE, g.FECHA_CREACION
    FROM grupo g
    JOIN grupo_miembros gm ON g.ID_GRUPO = gm.ID_GRUPO
    WHERE gm.ID_USUARIO = ?
  `;
  db.query(sql, [userId], (err, result) => {
    if (err) {
      console.error('❌ Error al obtener grupos:', err);
      return res.status(500).json({ msg: 'Error al obtener grupos' });
    }
    res.json(result);
  });
});

// Enviar mensaje a un grupo
app.post('/grupo/:grupoId/mensaje', (req, res) => {
  const { grupoId } = req.params;
  const { emisorId, mensaje } = req.body;

  if (!grupoId || !emisorId || !mensaje) {
    return res.status(400).json({ msg: 'Faltan datos para enviar mensaje' });
  }

  const sql = `
    INSERT INTO mensaje_grupo (ID_GRUPO, ID_EMISOR, MENSAJE)
    VALUES (?, ?, ?)
  `;
  db.query(sql, [grupoId, emisorId, mensaje], (err, result) => {
    if (err) {
      console.error('❌ Error al guardar mensaje:', err);
      return res.status(500).json({ msg: 'Error al guardar mensaje' });
    }
    res.json({ msg: 'Mensaje enviado', idMensaje: result.insertId });
  });
});

// Obtener mensajes de un grupo
app.get('/grupo/:grupoId/mensajes', (req, res) => {
  const { grupoId } = req.params;

  const sql = `
    SELECT m.ID_MENSAJE, m.MENSAJE, m.FECHA_ENVIO, u.Username AS autor
    FROM mensaje_grupo m
    JOIN usuario u ON m.ID_EMISOR = u.ID_USUARIO
    WHERE m.ID_GRUPO = ?
    ORDER BY m.FECHA_ENVIO ASC
  `;
  db.query(sql, [grupoId], (err, result) => {
    if (err) {
      console.error('❌ Error al obtener mensajes:', err);
      return res.status(500).json({ msg: 'Error al obtener mensajes' });
    }
    res.json(result);
  });
});



// ------------------ SOCKET.IO ------------------
const server = http.createServer(app);
const io = new Server(server, {
  cors: { 
    origin: ['http://localhost', 'http://localhost:3000', 'http://127.0.0.1'],
    methods: ['GET', 'POST']
  },
});


const usuariosConectados = {};

io.on("connection", (socket) => {
  console.log("🟢 Usuario conectado:", socket.id);

  // Registrar usuario
  socket.on("registrarUsuario", (userId) => {
    usuariosConectados[userId] = socket.id;
    console.log(`🆕 Usuario ${userId} registrado con socket ${socket.id}`);
  });

  // 🔹 Señal WebRTC
  socket.on("signal", ({ to, from, data }) => {
    const destino = usuariosConectados[to];
    if (destino) {
      io.to(destino).emit("signal", { from, data });
    }
  });

  // 🔹 Oferta WebRTC
  socket.on("offer", (offer) => {
    socket.broadcast.emit("offer", offer);
  });

  // 🔹 Respuesta WebRTC
  socket.on("answer", (answer) => {
    socket.broadcast.emit("answer", answer);
  });

  // 🔹 ICE Candidates
  socket.on("ice-candidate", (candidate) => {
    socket.broadcast.emit("ice-candidate", candidate);
  });

  // 🔹 Chat privado
  socket.on("mensajePrivado", ({ de, para, texto }) => {
    console.log(`📨 Mensaje de ${de} para ${para}: ${texto}`);
    const destino = usuariosConectados[para];
    if (destino) {
      io.to(destino).emit("recibirMensaje", { de, texto });
    }
  });

  // 🔚 Cuando se desconecta
  socket.on("disconnect", () => {
    for (const [id, sid] of Object.entries(usuariosConectados)) {
      if (sid === socket.id) {
        delete usuariosConectados[id];
        console.log(`🚫 Usuario ${id} desconectado`);
      }
    }
  });
});


const PORT = process.env.PORT || 3000;
server.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 Servidor con Socket.IO corriendo en puerto ${PORT}`);
});

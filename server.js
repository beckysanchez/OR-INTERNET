const express = require('express');
const path = require('path');
const cors = require('cors');
const db = require('./db');
const multer = require('multer'); // ⚠️ Asegúrate de instalarlo con npm i multer

const app = express();

// Middlewares
// 🧠 Configurar CORS correctamente
const allowedOrigins = [
  'https://or-internet.onrender.com', // frontend Render
  'http://localhost:3000',            // si pruebas local
];

app.use(
  cors({
    origin: function (origin, callback) {
      // Permitir solicitudes sin origen (como Postman o extensiones)
      if (!origin) return callback(null, true);
      if (allowedOrigins.includes(origin)) {
        return callback(null, true);
      } else {
        console.warn('Bloqueado por CORS:', origin);
        return callback(new Error('No permitido por CORS'));
      }
    },
    credentials: true,
  })
);

// 🚨 Asegurar encabezados CORS para todas las respuestas
app.use((req, res, next) => {
  res.header('Access-Control-Allow-Origin', 'https://or-internet.onrender.com');
  res.header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
  res.header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
  next();
});


// body-parser solo para JSON y URL-encoded
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Servir archivos estáticos (CSS, JS, imágenes, HTML)
app.use(express.static(path.join(__dirname, 'public')));

// Configurar multer (para subir imágenes en memoria)
const storage = multer.memoryStorage();
const upload = multer({ storage });

// Abrir 'registro.html' al visitar la raíz '/'
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'public', 'registro.html'));
});

// Ruta POST para registrar usuario con imagen
app.post('/registro', upload.single('img_p'), (req, res) => {
  console.log('📥 Petición recibida en /registro:', req.body); 
  const nombre = req.body.NOMBRE;
  const correo = req.body.CORREO.trim().toLowerCase();
  const Username = req.body.Username.trim().toLowerCase();
  const contraseña = req.body.CONTRA;
  const imagen = req.file ? req.file.buffer.toString('base64') : null;

  if (!nombre || !correo || !contraseña || !Username) {
    return res.status(400).json({ msg: '1 Faltan datos' });
  }

  // 🔍 Verificar si el correo o el username ya existen
  const checkSql = 'SELECT * FROM usuario WHERE CORREO = ? OR Username = ?';
  db.query(checkSql, [correo, Username], (err, result) => {
    if (err) {
      console.error('❌ 2 Error al verificar duplicados:', err);
      return res.status(500).json({ msg: '3 Error en el servidor' });
    }

    if (result.length > 0) {
      const existente = result[0];
      if (existente.CORREO === correo && existente.Username === Username) {
        return res.status(409).json({ msg: '4 El correo y el nombre de usuario ya están registrados' });
      } else if (existente.CORREO === correo) {
        return res.status(409).json({ msg: '5 El correo ya está registrado' });
      } else if (existente.Username === Username) {
        return res.status(409).json({ msg: '6 El nombre de usuario ya está registrado' });
      }
    }

    // ✅ Si no hay duplicados, intentar registrar
    const insertSql = `
      INSERT INTO usuario (NOMBRE, CORREO, CONTRA, Username, puntos, img_p)
      VALUES (?, ?, ?, ?, 10, ?)
    `;
    db.query(insertSql, [nombre, correo, contraseña, Username, imagen], (err, result) => {
      // 🧠 Aquí va lo del "err.code === 'ER_DUP_ENTRY'"
      if (err) {
        if (err.code === 'ER_DUP_ENTRY') {
          // ⚠️ Este error lo lanza MySQL si hay índices UNIQUE activos
          return res.status(409).json({ msg: '7 El correo o el nombre de usuario ya existen' });
        }
        console.error('❌ Error al insertar:', err);
        return res.status(500).json({ msg: '8 Error en el servidor' });
      }

      console.log('✅ usuario registrado con ID:', result.insertId);
      res.json({ msg: 'usuario registrado exitosamente' });
    });
  });
});



// Ruta POST para iniciar sesión
app.post('/login', (req, res) => {
  const correo = req.body.CORREO;
  const contraseña = req.body.CONTRA;

  if (!correo || !contraseña) {
    return res.status(400).json({ msg: 'Faltan datos' });
  }

  const sql = 'SELECT * FROM usuario WHERE CORREO = ? AND CONTRA = ?';
  db.query(sql, [correo, contraseña], (err, result) => {
    if (err) {
      console.error('❌ Error al consultar:', err);
      return res.status(500).json({ msg: 'Error en el servidor' });
    }

    if (result.length === 0) {
      return res.status(401).json({ msg: 'Correo o contraseña incorrectos' });
    }

    // Login exitoso
    res.json({ 
  msg: 'Login exitoso', 
  user: {
    id: result[0].ID_usuario,
    NOMBRE: result[0].NOMBRE,
    CORREO: result[0].CORREO,
    Username: result[0].Username,
    puntos: result[0].puntos,
    img_p: result[0].img_p // si guardaste la imagen en base64
  }
});
    
  });
});

// Buscar usuarios por username
app.get('/usuarios', (req, res) => {
    const q = req.query.q || '';
    const sql = 'SELECT ID_usuario, Username, img_p, puntos FROM usuario WHERE Username LIKE ?';
    db.query(sql, [`%${q}%`], (err, result) => {
        if(err) return res.status(500).json(err);
        res.json(result);
    });
});


// Obtener amigos de un usuario
app.get('/amigos/:userid', (req, res) => {
    const userid = req.params.userid;
    const sql = `
        SELECT u.ID_usuario, u.Username, u.img_p, u.puntos
        FROM amigos a
        JOIN usuario u ON (u.ID_usuario = a.ID_usuario2 AND a.ID_usuario1 = ?)
    `;
    db.query(sql, [userid], (err, result) => {
        if(err) return res.status(500).json(err);
        res.json(result);
    });
});


// Agregar amigo
app.post('/agregar-amigo', (req, res) => {
    const { usuario_id, amigo_id } = req.body;
    if(!usuario_id || !amigo_id) return res.status(400).json({msg:'Faltan datos'});

    const sql = 'INSERT INTO amigos (ID_usuario1, ID_usuario2) VALUES (?, ?)';
    db.query(sql, [usuario_id, amigo_id], (err, result) => {
        if(err) return res.status(500).json({msg:'Error al agregar amigo'});
        res.json({msg:'Amigo agregado'});
    });
});




const http = require('http');
const { Server } = require('socket.io');

const PORT = process.env.PORT || 3000;
const server = http.createServer(app);

// Configurar Socket.IO
const io = new Server(server, {
  cors: {
    origin: ['https://or-internet.onrender.com'], // tu frontend en Render
    methods: ['GET', 'POST']
  }
});

// Guardar usuarios conectados
const usuariosConectados = {}; // { userId: socket.id }

io.on('connection', (socket) => {
  console.log('🟢 Usuario conectado:', socket.id);

  // Recibir el ID del usuario que se conecta
  socket.on('registrarUsuario', (userId) => {
    usuariosConectados[userId] = socket.id;
    console.log('👤 Usuario registrado:', userId);
  });

  // Escuchar mensaje privado
  socket.on('mensajePrivado', ({ de, para, texto }) => {
    console.log(`📨 Mensaje de ${de} para ${para}: ${texto}`);
    const socketDestino = usuariosConectados[para];
    if (socketDestino) {
      io.to(socketDestino).emit('recibirMensaje', { de, texto });
    }
  });

  socket.on('disconnect', () => {
    for (const [id, sock] of Object.entries(usuariosConectados)) {
      if (sock === socket.id) delete usuariosConectados[id];
    }
    console.log('🔴 Usuario desconectado:', socket.id);
  });
});



// Escuchar conexión de un cliente
io.on('connection', (socket) => {
  console.log('🟢 Usuario conectado:', socket.id);

  socket.on('enviarMensaje', (data) => {
    console.log('📨 Mensaje recibido:', data);
    // reenviar mensaje a todos los clientes
    io.emit('recibirMensaje', data);
  });

  socket.on('disconnect', () => {
    console.log('🔴 Usuario desconectado:', socket.id);
  });
});

server.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 Servidor con Socket.IO corriendo en puerto ${PORT}`);
});




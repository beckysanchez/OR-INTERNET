// db.js - conexión MySQL desde NODEJS
const mysql = require('mysql2');

const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: 'Monse171002.',
  database: 'pwci'
});
//password: 'Monse171002.',
// Conexión
db.connect((err) => {
  if (err) {
    console.error('❌ Error conectando a MySQL:', err);
    return;
  }
  console.log('🟢 Conectado a MySQL desde Node');
});

module.exports = db;

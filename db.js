// db.js - conexión MySQL desde NODEJS
const mysql = require('mysql2');

const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '1234',
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

const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const bcrypt = require('bcrypt');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const path = require('path');

const app = express();
const PORT = 3000;
const SECRET_KEY = "clave_secreta_super_segura_para_el_prototipo";

// Middlewares
app.use(express.json());
app.use(cors());
app.use(express.static(path.join(__dirname, 'frontend')));

// 1. CONFIGURACIÓN DE BASE DE DATOS (SQLite)
const db = new sqlite3.Database('./database.sqlite', (err) => {
    if (err) console.error("Error al conectar BD:", err);
    else console.log("BD SQLite conectada bro.");
});

// Crear tablas automáticamente si no existen
db.serialize(() => {
    db.run(`CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nombre TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL
    )`);

    db.run(`CREATE TABLE IF NOT EXISTS posts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        titulo TEXT NOT NULL,
        categoria TEXT NOT NULL,
        contenido TEXT NOT NULL,
        user_id INTEGER,
        FOREIGN KEY(user_id) REFERENCES users(id)
    )`);
});

// 2. RUTAS DE USUARIOS (REGISTRO Y LOGIN)
app.post('/api/registro', async (req, res) => {
    const { nombre, email, password } = req.body;
    try {
        const hashedPassword = await bcrypt.hash(password, 10);
        db.run(`INSERT INTO users (nombre, email, password) VALUES (?, ?, ?)`, 
            [nombre, email, hashedPassword], 
            function(err) {
                if (err) return res.status(400).json({ error: "Ese correo ya está registrado bro." });
                res.json({ message: "¡Usuario registrado con éxito!" });
            }
        );
    } catch (error) {
        res.status(500).json({ error: "Error en el servidor" });
    }
});

app.post('/api/login', (req, res) => {
    const { email, password } = req.body;
    
    db.get(`SELECT * FROM users WHERE email = ?`, [email], async (err, user) => {
        if (err || !user) return res.status(400).json({ error: "Usuario no encontrado, regístrate primero." });

        const validPassword = await bcrypt.compare(password, user.password);
        if (!validPassword) return res.status(400).json({ error: "Contraseña incorrecta." });

        const token = jwt.sign({ id: user.id, nombre: user.nombre }, SECRET_KEY, { expiresIn: '1h' });
        res.json({ message: "Login exitoso", token, usuario: user.nombre });
    });
});

// 3. RUTAS DE PUBLICACIONES
app.post('/api/posts', (req, res) => {
    const { titulo, categoria, contenido, token } = req.body;
    try {
        const decoded = jwt.verify(token, SECRET_KEY);
        db.run(`INSERT INTO posts (titulo, categoria, contenido, user_id) VALUES (?, ?, ?, ?)`, 
            [titulo, categoria, contenido, decoded.id], 
            function(err) {
                if (err) return res.status(500).json({ error: "Error al crear la publicación" });
                res.json({ message: "¡Publicación creada con éxito!" });
            }
        );
    } catch (error) {
        res.status(401).json({ error: "No autorizado. Inicia sesión bro." });
    }
});

app.get('/api/posts', (req, res) => {
    const query = `
        SELECT posts.*, users.nombre AS autor 
        FROM posts 
        JOIN users ON posts.user_id = users.id
        ORDER BY posts.id DESC
    `;
    db.all(query, [], (err, rows) => {
        if (err) return res.status(500).json({ error: "Error al obtener publicaciones" });
        res.json(rows);
    });
});

// Borrar publicaciones
app.delete('/api/posts/:id', (req, res) => {
    const token = req.headers['authorization'];
    if (!token) return res.status(401).json({ error: "¡Alto ahí! Necesitas iniciar sesión bro." });

    try {
        const decoded = jwt.verify(token, SECRET_KEY);
        const postId = req.params.id;

        // Solo lo borra si el ID del post coincide con el ID del usuario
        db.run(`DELETE FROM posts WHERE id = ? AND user_id = ?`, [postId, decoded.id], function(err) {
            if (err) return res.status(500).json({ error: "Error en el servidor al borrar." });
            if (this.changes === 0) return res.status(403).json({ error: "¡Hey! Solo puedes borrar tus propias publicaciones." });
            res.json({ message: "¡Publicación borrada con éxito!" });
        });
    } catch (error) {
        res.status(401).json({ error: "Sesión inválida." });
    }
});

// ARRANCAR EL MOTOR
app.listen(PORT, () => {
    console.log(`Servidor corriendo al cien en http://localhost:${PORT}`);
});
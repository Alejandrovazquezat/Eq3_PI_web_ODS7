# Redrenovable

## Descripción:

Este proyecto consiste en el desarrollo de una plataforma web de publicación y gestión de contenidos digitales enfocada en la difusión de información sobre energías renovables y el Objetivo de Desarrollo Sostenible 7 (Energía asequible y no contaminante).

La aplicación funciona como un CMS básico que permite crear, administrar y publicar contenidos mediante un sistema de roles y control de acceso.

Proyecto desarrollado como parte del Proyecto Integrador de Ingeniería de Software — Universidad de Colima.

## Objetivo

Diseñar e implementar una plataforma web que permita la creación y gestión de contenidos digitales aplicando principios de ingeniería de software, programación orientada a objetos y arquitectura cliente-servidor.

## Funcionalidades Implementadas

- Registro e inicio de sesión de usuarios con autenticación segura
- Control de acceso por roles (Administrador, Editor, Autor y Visitante)
- CRUD completo de publicaciones con soporte para imágenes
- Organización de contenidos por categorías
- Panel de administración con gestión de usuarios
- Cambio de roles y eliminación de usuarios desde el panel admin
- Filtro de usuarios por rol
- Menú desplegable con opciones de usuario
- Página principal con publicaciones destacadas en grid horizontal y lista vertical infinita
- Visualización de publicaciones por categoría
- Base de datos relacional con almacenamiento de imágenes en BLOB

## Tecnologías Utilizadas

- HTML5
- CSS3
- JavaScript
- PHP 8.0
- MySQL
- Git y GitHub
- XAMPP

## Estructura del Proyecto
- Eq3_PI_web_ODS7/
- ├── frontend/
- │ ├── admin/ # Panel de administración
- │ │ ├── dashboard.php
- │ │ ├── usuarios.php
- │ │ ├── publicaciones.php
- │ │ ├── crear_publicacion.php
- │ │ ├── cambiar_rol.php
- │ │ └── eliminar_usuario.php
- │ ├── css/ # Hojas de estilo
- │ │ ├── navbar-style.css
- │ │ ├── index-styles.css
- │ │ ├── categorias-styles.css
- │ │ ├── categoria-styles.css
- │ │ ├── login-style.css
- │ │ └── dash_usuario_styles.css
- │ ├── image/ # Recursos gráficos
- │ ├── js/ # Scripts JavaScript
- │ │ └── usuarios.js
- │ └── pages/ # Páginas públicas
- │ ├── index.php
- │ ├── navbar.php
- │ ├── footer.php
- │ ├── inicioSesion.php
- │ ├── registro.php
- │ ├── categorias.php
- │ └── categoria.php
- ├── backend/
- │ ├── config/ # Configuración de base de datos
- │ └── models/ # Modelos de datos
- ├── database/ # Scripts SQL
- ├── docs/ # Documentación
- └── assets/ # Recursos estáticos


## Roles del Sistema

|      Rol      | Permisos |
|---------------|-------------------------------------------------------------------------------------------------------------|
| Administrador | Control total: gestionar usuarios, cambiar roles, eliminar usuarios, crear publicaciones, moderar contenido |
| Editor        | Revisar y aprobar publicaciones                                                                             |
| Autor         | Crear y editar sus propias publicaciones                                                                    |
| Visitante     | Solo visualizar contenido público                                                                           |

## Instalación Local

1. Clonar el repositorio:
git clone https://github.com/Alejandrovazquezat/Eq3_PI_web_ODS7.git


2. Copiar la carpeta a htdocs de XAMPP:
C:\xampp\htdocs\Eq3_PI_web_ODS7

3. Iniciar Apache y MySQL desde XAMPP Control Panel

4. Crear la base de datos en phpMyAdmin:
CREATE DATABASE plataforma_contenidos;


5. Importar el archivo SQL ubicado en:
database/schema.sql

6. Configurar credenciales de base de datos en:
frontend/admin/Conexion.php

7. Acceder a la aplicación:
http://localhost/Eq3_PI_web_ODS7/frontend/pages/index.php



## Credenciales de Prueba

|      Rol       |           Email          | Contraseña  |
|----------------|--------------------------|-------------|
| Usuario Normal | alan_ecolima@cityboy.com | alanecolima |

## Estado del Proyecto

En desarrollo activo.

## Equipo de Desarrollo - Equipo 3

- Carlos Arturo Argüellez Ruiz
- Jesús Enrique Ibarra Figueroa
- Alan Yakxel Juárez Cano
- Fernando Franco Juárez Lara
- Diego Alejandro Vazquez Atanacio

    <?php

    require_once __DIR__ . "/../models/Usuario.php";
    require_once __DIR__ . "/AuthController.php";

    class UsuarioController {
        private $db;
        
        public function __construct($db) {
            $this->db = $db;
        }
        
        // ==========================
        // OBTENER TODOS LOS USUARIOS (solo admin)
        // ==========================
        public function listarTodos($admin_id) {
            $auth = new AuthController($this->db);
            if (!$auth->tienePermiso($admin_id, 'crear_usuario')) { // admin tiene este permiso
                return "Error: Solo administradores pueden ver usuarios";
            }
            
            $usuario = new Usuario($this->db);
            return $usuario->obtenerTodos();
        }
        
        // ==========================
        // OBTENER USUARIO POR ID (solo admin)
        // ==========================
        public function obtenerPorId($admin_id, $usuario_id) {
            $auth = new AuthController($this->db);
            if (!$auth->tienePermiso($admin_id, 'editar_usuario')) {
                return "Error: No tienes permiso para ver este usuario";
            }
            
            $query = "SELECT u.*, r.nombre as rol_nombre 
                    FROM usuarios u
                    LEFT JOIN roles r ON u.rol_id = r.id
                    WHERE u.id = :usuario_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // ==========================
        // CAMBIAR ROL DE USUARIO (solo admin)
        // ==========================
        public function cambiarRol($admin_id, $usuario_id, $nuevo_rol_id) {
            // Validar que el rol exista
            $query = "SELECT id FROM roles WHERE id = :rol_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":rol_id", $nuevo_rol_id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                return "Error: El rol no existe";
            }
            
            $auth = new AuthController($this->db);
            if (!$auth->tienePermiso($admin_id, 'editar_usuario')) {
                return "Error: No tienes permiso para cambiar roles";
            }
            
            $query = "UPDATE usuarios SET rol_id = :rol_id WHERE id = :usuario_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":rol_id", $nuevo_rol_id);
            $stmt->bindParam(":usuario_id", $usuario_id);
            
            return $stmt->execute() ? "Rol actualizado correctamente" : "Error al actualizar rol";
        }
        
        // ==========================
        // ELIMINAR USUARIO (solo admin)
        // ==========================
        public function eliminar($admin_id, $usuario_id) {
            // No permitir eliminarse a sí mismo
            if ($admin_id == $usuario_id) {
                return "Error: No puedes eliminar tu propio usuario";
            }
            
            $auth = new AuthController($this->db);
            if (!$auth->tienePermiso($admin_id, 'eliminar_usuario')) {
                return "Error: No tienes permiso para eliminar usuarios";
            }
            
            // Verificar que el usuario existe
            $query = "SELECT id FROM usuarios WHERE id = :usuario_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":usuario_id", $usuario_id);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                return "Error: El usuario no existe";
            }
            
            // Eliminar (o podrías implementar "borrado lógico")
            $query = "DELETE FROM usuarios WHERE id = :usuario_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":usuario_id", $usuario_id);
            
            return $stmt->execute() ? "Usuario eliminado correctamente" : "Error al eliminar usuario";
        }
        
        // ==========================
        // CREAR USUARIO (admin)
        // ==========================
        public function crear($admin_id, $datos) {
            $auth = new AuthController($this->db);
            if (!$auth->tienePermiso($admin_id, 'crear_usuario')) {
                return "Error: No tienes permiso para crear usuarios";
            }
            
            // Validar datos
            if (empty($datos['nombre']) || empty($datos['email']) || empty($datos['password'])) {
                return "Todos los campos son obligatorios";
            }
            
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                return "Email no válido";
            }
            
            // Verificar si el email ya existe
            $usuario = new Usuario($this->db);
            $usuario->email = $datos['email'];
            $stmt = $usuario->buscarPorEmail();
            
            if($stmt->rowCount() > 0){
                return "El email ya está registrado";
            }
            
            // Crear usuario
            $usuario->nombre = $datos['nombre'];
            $usuario->password = $datos['password'];
            $usuario->rol_id = $datos['rol_id'] ?? 4; // Por defecto usuario normal
            
            if($usuario->registrar()){
                return "Usuario creado correctamente";
            }
            
            return "Error al crear usuario";
        }
        
        // ==========================
        // LISTAR USUARIOS POR ROL (solo admin)
        // ==========================
        public function listarPorRol($admin_id, $rol_id) {
        $auth = new AuthController($this->db);
        if (!$auth->tienePermiso($admin_id, 'crear_usuario')) { // admin tiene este permiso
            return "Error: Solo administradores pueden ver usuarios";
        }
        
        $usuario = new Usuario($this->db);
        return $usuario->obtenerPorRol($rol_id);
        }


        // ==========================
        // OBTENER ESTADÍSTICAS (solo admin/editor)
        // ==========================
        public function obtenerEstadisticas($usuario_id) {
            $auth = new AuthController($this->db);
            if (!$auth->tienePermiso($usuario_id, 'ver_estadisticas')) {
                return "Error: No tienes permiso para ver estadísticas";
            }
            
            $stats = [];
            
            // Total usuarios
            $query = "SELECT COUNT(*) as total FROM usuarios";
            $stmt = $this->db->query($query);
            $stats['total_usuarios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Usuarios por rol
            $query = "SELECT r.nombre, COUNT(u.id) as total 
                    FROM roles r
                    LEFT JOIN usuarios u ON r.id = u.rol_id
                    GROUP BY r.id, r.nombre";
            $stmt = $this->db->query($query);
            $stats['usuarios_por_rol'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Total publicaciones
            $query = "SELECT COUNT(*) as total FROM publicaciones";
            $stmt = $this->db->query($query);
            $stats['total_publicaciones'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Publicaciones por estado
            $query = "SELECT estado, COUNT(*) as total 
                    FROM publicaciones 
                    GROUP BY estado";
            $stmt = $this->db->query($query);
            $stats['publicaciones_por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $stats;
        }
    }
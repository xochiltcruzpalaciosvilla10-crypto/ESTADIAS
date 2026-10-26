<?php
// 1. Datos de configuración de tu servidor de Base de Datos (ajusta si usas otros)
$host     = 'localhost'; 
$db       = 'profesor'; // Tu base de datos
$user     = 'root';      // Usuario por defecto en XAMPP/WampServer
$password = '';          // Contraseña por defecto (vacía en XAMPP)

try {
    // 2. Crear la conexión con PDO
    $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    // Configurar PDO para que muestre errores si ocurren
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 3. Verificar que los datos del formulario hayan llegado
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombreUser = $_POST['nombre'];
        $passUser   = $_POST['password'];

        // IMPORTANTE: Encriptar la contraseña por seguridad antes de guardarla
        $passEncriptada = password_hash($passUser, PASSWORD_BCRYPT);

        // 4. Preparar la consulta SQL (Asumiendo que tu tabla se llama 'usuarios')
        // Cambia 'usuarios', 'nombre' y 'contrasena' por los nombres reales de tu tabla y columnas
        $sql = "INSERT INTO usuarios (nombre, contrasena) VALUES (:nombre, :password)";
        $stmt = $conexion->prepare($sql);

        // 5. Vincular los datos y ejecutar la consulta
        $stmt->bindParam(':nombre', $nombreUser);
        $stmt->bindParam(':password', $passEncriptada);
        
        if ($stmt->execute()) {
            echo "<script>
                    alert('¡Registro exitoso en la base de datos profesor!');
                    window.location.href = 'clasificacion.php';
                  </script>";
        } else {
            echo "Hubo un error al registrar el usuario.";
        }
    }

} catch (PDOException $e) {
    // Si la conexión falla, mostrará el motivo
    die("Error de conexión a la base de datos: " . $e->getMessage());
}
?>
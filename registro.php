 = password_hash($passUser, PASSWORD_BCRYPT);

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
                    window.location.href = 'index.html';
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
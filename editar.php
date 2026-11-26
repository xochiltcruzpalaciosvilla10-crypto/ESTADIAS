<?php
session_start();
$conexion = new mysqli("localhost", "root", "", "utensilios");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
$conexion->set_charset("utf8mb4");

// Detectamos el nombre de tu página principal automáticamente para regresar ahí
$pagina_principal = $_SERVER['HTTP_REFERER'] ?? 'index.php';
if (strpos($pagina_principal, 'editar.php') !== false) {
    $pagina_principal = 'index.php';
}

// Verificamos que venga un ID en el botón Modificar
if (!isset($_GET['id'])) {
    header("Location: " . $pagina_principal);
    exit();
}

$id = intval($_GET['id']);

// 1. CUANDO EL USUARIO PRESIONA "GUARDAR CAMBIOS"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = $_POST['nombre_articulo'];
    $entrada     = intval($_POST['cantidad_ideal']);
    $disponibles = intval($_POST['cantidad_actual']);
    $notas       = $_POST['notas_ubicacion'];
    $seccion     = $_POST['seccion'];
    $volver_a    = $_POST['volver_a'];

    if ($disponibles > $entrada) {
        $disponibles = $entrada;
    }

    $sql = "UPDATE equipo_menor SET 
            nombre_articulo = ?, 
            cantidad_ideal = ?, 
            cantidad_actual = ?, 
            notas_ubicacion = ?,
            seccion = ?
            WHERE id = ?";
            
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("siissi", $nombre, $entrada, $disponibles, $notas, $seccion, $id);
    
    if ($stmt->execute()) {
        // Aseguramos que la tabla eventos exista con la estructura correcta
        $conexion->query("CREATE TABLE IF NOT EXISTS eventos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(100) NOT NULL DEFAULT 'Desconocido',
            accion VARCHAR(30) NOT NULL,
            seccion VARCHAR(50) NOT NULL DEFAULT 'general',
            descripcion TEXT NOT NULL,
            fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        // Datos del movimiento
        $usuario_ev = $_SESSION['usuario'] ?? $_SESSION['nombre'] ?? 'Administrador';
        $accion_ev  = "EDICIÓN";
        $desc_ev    = "Se modificó '$nombre' (Total: $entrada, Disponibles: $disponibles)";

        // Insertamos enviando NOW() explícitamente para evitar fallos en fecha_hora
        $sql_ev  = "INSERT INTO eventos (usuario, accion, seccion, descripcion, fecha_hora) VALUES (?, ?, ?, ?, NOW())";
        $stmt_ev = $conexion->prepare($sql_ev);

        if ($stmt_ev) {
            $stmt_ev->bind_param("ssss", $usuario_ev, $accion_ev, $seccion, $desc_ev);
            if (!$stmt_ev->execute()) {
                die("Error en tabla eventos: " . $stmt_ev->error);
            }
        } else {
            die("Error en consulta eventos: " . $conexion->error);
        }

        header("Location: " . $volver_a);
        exit();
    } else {
        echo "Error al actualizar: " . $conexion->error;
    }
}

// 2. OBTENER LOS DATOS ACTUALES DEL UTENSILIO PARA MOSTRARLOS EN EL FORMULARIO
$sql_select = "SELECT * FROM equipo_menor WHERE id = ?";
$stmt_select = $conexion->prepare($sql_select);
$stmt_select->bind_param("i", $id);
$stmt_select->execute();
$resultado = $stmt_select->get_result();

if ($resultado->num_rows === 0) {
    header("Location: " . $pagina_principal);
    exit();
}

$utensilio = $resultado->fetch_assoc();
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Utensilio - BakeStock</title>
    <style>
        img {
  max-width: 100%;
  height: auto;
}
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background-color: #9e8a78; /* Fondo café taupe igual que Añadir */
            padding: 40px; 
            margin: 0; 
        }
        .contenedor { 
            max-width: 500px; 
            margin: auto; 
            background: #ffffff; 
            padding: 30px; 
            border-radius: 14px; 
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18); 
            border-top: 6px solid #462814; /* Borde café oscuro BakeStock */
        }
        h2 { 
            color: #462814; 
            margin-top: 0; 
            font-size: 22px; 
        }
        label { 
            display: block; 
            margin-top: 15px; 
            font-weight: 600; 
            color: #462814; 
            font-size: 14px; 
        }
        input, select, textarea { 
            width: 100%; 
            padding: 10px 12px; 
            margin-top: 6px; 
            background-color: #fbf7f3; /* Fondo cremita suave en las cajitas */
            border: 1px solid #eadfd5; 
            border-radius: 8px; 
            box-sizing: border-box; 
            font-size: 14px; 
            color: #3d2c1e;
            font-family: inherit;
        }
        input:focus, select:focus, textarea:focus { 
            outline: none; 
            border-color: #b08256; 
            background-color: #ffffff;
        }
        .ayuda { 
            font-size: 12px; 
            color: #8c7a6b; 
            margin-top: 4px; 
        }
        .botones { 
            margin-top: 25px; 
            display: flex; 
            gap: 12px; 
        }
        .btn-guardar { 
            flex: 1; 
            background-color: #462814; /* Café oscuro igual al de Añadir */
            color: white; 
            border: none; 
            padding: 12px; 
            border-radius: 8px; 
            cursor: pointer; 
            font-weight: bold; 
            font-size: 15px; 
            transition: background 0.2s;
        }
        .btn-guardar:hover { 
            background-color: #321b0c; 
        }
        .btn-cancelar { 
            flex: 1; 
            background-color: #d6a472; /* Tono caramelo/arena igual al botón regresar */
            color: #462814; 
            text-decoration: none; 
            padding: 12px; 
            border-radius: 8px; 
            text-align: center; 
            font-weight: bold; 
            font-size: 15px; 
            transition: background 0.2s;
        }
        .btn-cancelar:hover { 
            background-color: #c5915e; 
        }
    </style>
</head>
<body>

<div class="contenedor">
    <h2>✏️ Modificar Utensilio</h2>
    <form method="POST">
        <input type="hidden" name="volver_a" value="<?php echo htmlspecialchars($pagina_principal); ?>">

        <label>Nombre del artículo:</label>
        <input type="text" name="nombre_articulo" value="<?php echo htmlspecialchars($utensilio['nombre_articulo']); ?>" required>

        <label>Área / Sección:</label>
        <select name="seccion">
            <option value="cocina caliente" <?php if($utensilio['seccion'] == 'cocina caliente') echo 'selected'; ?>>Cocina caliente</option>
            <option value="panaderia" <?php if($utensilio['seccion'] == 'panaderia') echo 'selected'; ?>>Panadería</option>
            <option value="reposteria" <?php if($utensilio['seccion'] == 'reposteria') echo 'selected'; ?>>Repostería</option>
        </select>

        <label>Entrada (Total de piezas en el negocio):</label>
        <input type="number" name="cantidad_ideal" min="0" value="<?php echo intval($utensilio['cantidad_ideal']); ?>" required>
        <div class="ayuda">Solo cámbialo si compraron nuevos o se tiró alguno roto.</div>

        <label>Disponibles (Cuántos quedan libres en el estante ahorita):</label>
        <input type="number" name="cantidad_actual" min="0" value="<?php echo intval($utensilio['cantidad_actual']); ?>" required>
        <div class="ayuda">Si alguien agarró 2 utensilios, réstalos aquí. El sistema calculará los "En uso" solo.</div>

        <label>Notas / Ubicación:</label>
        <textarea name="notas_ubicacion" rows="3" placeholder="Ej. 2 los tiene el turno matutino, o 1 abollado"><?php echo htmlspecialchars($utensilio['notas_ubicacion']); ?></textarea>

        <div class="botones">
            <button type="submit" class="btn-guardar">Guardar Cambios</button>
            <a href="<?php echo htmlspecialchars($pagina_principal); ?>" class="btn-cancelar">Cancelar</a>
        </div>
    </form>
</div>
</body>
</html>
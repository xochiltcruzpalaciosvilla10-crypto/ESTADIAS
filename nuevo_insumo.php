<?php
include 'conexion.php'; // Asegúrate de que el nombre de tu conexión sea este

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];
    $proveedor = $_POST['proveedor'];
    $categoria = $_POST['categoria'];
    $existencia = intval($_POST['existencia']);
    $stock_min = intval($_POST['stock_min']);
    $stock_max = intval($_POST['stock_max']);
    $precio = floatval($_POST['precio']);

    // Ajusta los nombres de las columnas según tu tabla en MySQL
    $sql = "INSERT INTO insumos (nombre, proveedor, categoria, existencia, stock_min, stock_max, precio) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiiid", $nombre, $proveedor, $categoria, $existencia, $stock_min, $stock_max, $precio);

    if ($stmt->execute()) {
        header("Location: insumos.php");
        exit();
    } else {
        $error = "Error al guardar: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Insumo | BakeStock</title>
    <style>
        img {
  max-width: 100%;
  height: auto;
}
        body {
            background-color: #8c7560;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            color: #3e2723;
        }
        .header-bar {
            background-color: #fcf8f2;
            max-width: 1100px;
            margin: 0 auto 30px auto;
            padding: 15px 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        .header-bar h2 { margin: 0; font-size: 20px; color: #3e2723; }
        .btn-volver {
            background-color: #d99b52;
            color: #3e2723;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
        }
        .card-form {
            background-color: #ffffff;
            max-width: 450px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block;
            font-weight: bold;
            font-size: 13px;
            margin-bottom: 5px;
            color: #4a3525;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e0d6cc;
            background-color: #faf6f2;
            border-radius: 8px;
            box-sizing: border-box;
            font-size: 14px;
        }
        .row-2 {
            display: flex;
            gap: 10px;
        }
        .row-2 .form-group { flex: 1; }
        .btn-guardar {
            width: 100%;
            background-color: #4a2c11;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-guardar:hover { background-color: #361f0b; }
    </style>
</head>
<body>

    <div class="header-bar">
        <span><strong>Añadir</strong></span>
        <h2>Agregar Nuevo Insumo</h2>
        <a href="insumos.php" class="btn-volver">← Cancelar y regresar</a>
    </div>

    <div class="card-form">
        <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Nombre del insumo:</label>
                <input type="text" name="nombre" placeholder="Ej. Harina de trigo bulto 44kg" required>
            </div>

            <div class="form-group">
                <label>Proveedor:</label>
                <input type="text" name="proveedor" placeholder="Ej. Molinos La Fama" required>
            </div>

            <div class="form-group">
                <label>Categoría:</label>
                <select name="categoria" required>
                    <option value="Harinas y Polvos">Harinas y Polvos</option>
                    <option value="Lácteos y Huevos">Lácteos y Huevos</option>
                    <option value="Azúcares y Endulzantes">Azúcares y Endulzantes</option>
                    <option value="Grasas y Aceites">Grasas y Aceites</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>

            <div class="row-2">
                <div class="form-group">
                    <label>Existencia actual:</label>
                    <input type="number" name="existencia" value="0" min="0" required>
                </div>
                <div class="form-group">
                    <label>Costo unitario ($):</label>
                    <input type="number" step="0.01" name="precio" placeholder="0.00" required>
                </div>
            </div>

            <div class="row-2">
                <div class="form-group">
                    <label>Stock Mínimo (Reorden):</label>
                    <input type="number" name="stock_min" value="5" min="1" required>
                </div>
                <div class="form-group">
                    <label>Stock Máximo:</label>
                    <input type="number" name="stock_max" value="20" min="1" required>
                </div>
            </div>

            <button type="submit" class="btn-guardar">💾 Guardar insumo</button>
        </form>
    </div>

</body>
</html>

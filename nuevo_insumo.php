<?php
$conn = new mysqli("localhost", "root", "", "insumos");
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = $_POST['categoria'] ?? 'Secos y Harinas';
    $unidad = $_POST['unidad'] ?? 'bultos';
    $entrada = floatval($_POST['entrada'] ?? 0);
    $en_uso = min($entrada, max(0, floatval($_POST['en_uso'] ?? 0)));
    $minimo = floatval($_POST['minimo'] ?? 2);
    $caducidad = !empty($_POST['fecha_caducidad']) ? $_POST['fecha_caducidad'] : null;

    if ($nombre !== '') {
        // 1. Verificar si ya existe un insumo con el mismo nombre (sin importar mayúsculas/minúsculas)
        $check = $conn->prepare("SELECT id FROM inventario_insumos WHERE LOWER(nombre) = LOWER(?) LIMIT 1");
        $check->bind_param("s", $nombre);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            // Ya existe: bloquear el guardado y preparar alerta
            $error = "⚠️ El insumo «" . htmlspecialchars($nombre) . "» ya está registrado en el inventario. Si deseas sumar más cantidad, edítalo desde la lista principal.";
            $check->close();
        } else {
            // No existe: guardar normalmente
            $check->close();
            $stmt = $conn->prepare("INSERT INTO inventario_insumos (nombre, categoria, unidad, entrada, en_uso, minimo, fecha_caducidad) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssddds", $nombre, $categoria, $unidad, $entrada, $en_uso, $minimo, $caducidad);
            $stmt->execute();
            $stmt->close();

            header("Location: insumos.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Nuevo Insumo | BakeStock</title>
  <link rel="stylesheet" href="nuevo_insumo.css">
    <style>
* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  background-color: #937f6c !important; /* Fondo café claro / taupe exacto */
  color: #362215;
  font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  min-height: 100vh;
  display: flex !important;
  flex-direction: column;
  align-items: center;
  padding: 1.5rem 1rem;
}

/* BARRA SUPERIOR CREMA */
.top-bar {
  background-color: #faf6f0;
  width: 100%;
  max-width: 1120px;
  padding: 0.9rem 1.75rem;
  border-radius: 0.75rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
  margin-bottom: 1.75rem;
}

.top-left {
  font-size: 0.9rem;
  font-weight: 700;
  color: #42210b;
}

.top-title {
  font-size: 1.15rem;
  font-weight: 800;
  color: #362215;
}

.btn-volver {
  background-color: #d69449; /* Botón miel / caramelo exacto */
  color: #2b1a0f;
  text-decoration: none;
  padding: 0.5rem 1.1rem;
  border-radius: 0.45rem;
  font-size: 0.82rem;
  font-weight: 700;
  transition: background 0.2s;
}

.btn-volver:hover {
  background-color: #c28036;
}

/* CONTENEDOR Y TARJETA BLANCA CENTRAL */
.form-wrapper {
  width: 100%;
  display: flex;
  justify-content: center;
}

.form-card {
  background-color: #ffffff;
  width: 100%;
  max-width: 440px; /* Ancho vertical esbelto igual que anadir.php */
  padding: 2rem 2.25rem;
  border-radius: 0.85rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.18);
}

.form-vertical {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.campo label {
  display: block;
  font-size: 0.78rem;
  font-weight: 700;
  color: #362215;
  margin-bottom: 0.35rem;
}

.campo input,
.campo select {
  width: 100%;
  background-color: #faf8f5;
  border: 1px solid #e5ddd5;
  border-radius: 0.45rem;
  padding: 0.65rem 0.85rem;
  font-size: 0.88rem;
  color: #362215;
  outline: none;
  transition: border-color 0.2s, background 0.2s;
}

.campo input:focus,
.campo select:focus {
  border-color: #937f6c;
  background-color: #ffffff;
}

/* BOTÓN ANCHO CAFÉ CHOCOLATE OSCURO */
.btn-guardar {
  width: 100%;
  background-color: #42210b;
  color: #ffffff;
  border: none;
  padding: 0.8rem 1rem;
  border-radius: 0.45rem;
  font-size: 0.88rem;
  font-weight: 700;
  cursor: pointer;
  margin-top: 0.5rem;
  transition: background 0.2s;
}

.btn-guardar:hover {
  background-color: #2b1507;
}

/* RESPONSIVO PARA CELULAR */
@media (max-width: 550px) {
  .top-bar {
    flex-direction: column;
    text-align: center;
    padding: 1rem;
  }
  .form-card {
    padding: 1.5rem 1.25rem;
  }
}


  </style>
</head>
<body>

  <!-- BARRA SUPERIOR FLOTANTE -->
  <header class="top-bar">
    <span class="top-left">Añadir</span>
    <h1 class="top-title">Registrar Nuevo Insumo</h1>
    <a href="insumos.php" class="btn-volver">← Cancelar y regresar</a>
  </header>

  <!-- TARJETA BLANCA CENTRAL -->
  <main class="form-wrapper">
        <div class="form-card">
      <?php if ($error !== ""): ?>
        <div style="background:#fee2e2; border:1.5px solid #ef4444; color:#991b1b; padding:0.75rem 1rem; border-radius:0.5rem; margin-bottom:1.1rem; font-size:0.83rem; font-weight:600; line-height:1.4;">
          <?= $error ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="nuevo_insumo.php" class="form-vertical" autocomplete="off">
  
  <div class="campo">
    <label for="nombre">Nombre del insumo:</label>
    <input type="text" id="nombre" name="nombre" placeholder="Ej. Azúcar estándar" autocomplete="off" required autofocus>
  </div>


        <div class="campo">
          <label for="categoria">Categoría:</label>
          <select id="categoria" name="categoria">
            <option value="Secos y Harinas">Secos y Harinas</option>
            <option value="Lácteos y Refrigerados">Lácteos y Refrigerados</option>
            <option value="Rellenos y Decoración">Rellenos y Decoración</option>
            <option value="Empaques">Empaques</option>
          </select>
        </div>

        <div class="campo">
          <label for="unidad">Unidad de medida:</label>
          <select id="unidad" name="unidad">
            <option value="bultos">Bultos</option>
            <option value="kg">Kilogramos (kg)</option>
            <option value="L">Litros (L)</option>
            <option value="pzas">Piezas / Huevos</option>
          </select>
        </div>

        <div class="campo">
          <label for="entrada">Entrada:</label>
          <input type="number" id="entrada" name="entrada" value="1" step="0.01" min="0" required>
        </div>

        <div class="campo">
          <label for="en_uso">En uso:</label>
          <input type="number" id="en_uso" name="en_uso" value="0" step="0.01" min="0" required>
        </div>

        <div class="campo">
          <label for="minimo">Stock mínimo (alerta):</label>
          <input type="number" id="minimo" name="minimo" value="2" step="0.01" min="0" required>
        </div>

        <div class="campo">
          <label for="fecha_caducidad">Fecha de caducidad:</label>
          <input type="date" id="fecha_caducidad" name="fecha_caducidad">
        </div>

        <button type="submit" class="btn-guardar">Guardar Insumo</button>
      </form>
    </div>
  </main>

</body>
</html>


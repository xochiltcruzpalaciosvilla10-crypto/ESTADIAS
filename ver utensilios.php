<?php
session_start();

// 1. Detectar si el usuario viene de Insumos, Maquinaria o Utensilios desde la URL
$categoria = $_GET['categoria'] ?? 'utensilios';
$categoria_limpia = strtolower(trim($categoria));

// 2. Definir los 4 pasos específicos para cada una de las 3 opciones de tu panadería
if ($categoria_limpia === 'insumos') {
    $titulo_pantalla = "Pasos de Producción: Insumos";
    $pasos = [
        ["num" => "1", "titulo" => "Recepción y Almacenaje"],
        ["num" => "2", "titulo" => "Dosificación y Pesado"],
        ["num" => "3", "titulo" => "Integración a Mezcla"],
        ["num" => "4", "titulo" => "Control de Mermas"]
    ];
} elseif ($categoria_limpia === 'maquinaria') {
    $titulo_pantalla = "Pasos de Producción: Maquinaria";
    $pasos = [
        ["num" => "1", "titulo" => "Encendido y Verificación"],
        ["num" => "2", "titulo" => "Precalentamiento de Hornos"],
        ["num" => "3", "titulo" => "Operación y Horneado"],
        ["num" => "4", "titulo" => "Limpieza y Mantenimiento"]
    ];
} else {
    // Valores por defecto para Utensilios (los de tu captura de pantalla original)
    $titulo_pantalla = "Pasos de Producción: Utensilios";
    $pasos = [
        ["num" => "1", "titulo" => "Medición y pesado"],
        ["num" => "2", "titulo" => "Mezcla y preparación"],
        ["num" => "3", "titulo" => "Corte, división y formado"],
        ["num" => "4", "titulo" => "Horneado y reposo"]
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pantalla; ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        body { 
            background-color: #FAF7F2; 
            min-height: 100vh; 
            display: flex; 
            flex-direction: column;
            align-items: center; 
            justify-content: center;
            padding: 20px;
        }
        .titulo-seccion {
            color: #3B1F0E;
            font-size: 2rem;
            margin-bottom: 40px;
            font-weight: bold;
        }
        .pasos-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            width: 100%;
            max-width: 1200px;
        }
        .tarjeta-paso {
            background: #FFFFFF;
            border: 1px solid #EADCC9;
            border-radius: 12px;
            padding: 25px 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.01);
            flex: 1;
            min-width: 220px;
        }
        .numero-circulo {
            background-color: #F5F0E8;
            color: #8A4F35;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.95rem;
            flex-shrink: 0;
        }
        .texto-paso {
            color: #3B1F0E;
            font-weight: 600;
            font-size: 1rem;
        }
    </style>
</head>
<body>

    <!-- El título cambia solo de acuerdo a la categoría -->
    <h1 class="titulo-seccion"><?php echo $titulo_pantalla; ?></h1>

    <!-- El contenedor dibuja las 4 tarjetas idénticas a tu diseño gráfico -->
    <div class="pasos-container">
        <?php foreach ($pasos as $paso): ?>
            <div class="tarjeta-paso">
                <div class="numero-circulo"><?php echo $paso['num']; ?></div>
                <div class="texto-paso"><?php echo htmlspecialchars($paso['titulo']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>

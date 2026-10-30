<?php
session_start();

// 1. Detectar si el usuario viene de Insumos, Maquinaria o Utensilios desde la URL
$categoria = $_GET['categoria'] ?? 'utensilios';
$categoria_limpia = strtolower(trim($categoria));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            display: grid !important;
            justify-content: center;
            align-items: center;
            gap: 1cm 0px !important;
            flex-wrap: unset !important;
            grid-template-columns: 11cm 11cm !important;
            align-content: center !important;
            width: calc(11cm + 11cm) !important;
            margin: 30 auto !important;
        }
        .tarjeta-paso {
            background:
           "#B3826C",
           "#a76141",
           "#c58161",
           "#a05a39";
            border: 1px solid #EADCC9;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: border-box;
            flex direction: column;
            box-shadow: 0 4px 10px rgba(0,0,0,0.01);
            width: 10cm !important;/*ANCHO*/
            height: 10cm !important;/*CCAMBIA EL TAMAÑOS DE LO ALTO*/

        }
        /* EFECTO HOVER: Hace que actúe como un botón real cuando pasas el mouse */
        .tarjeta-paso:hover {
            transform: scale(1.02) !important;
            cursor: pointer !important;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
        }
        .numero-circulo {
            background-color: #F5F0E8;
            color: #6e2a0d;
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
            text-transform: uppercase;
            font-weight: bold; 
        }
    </style>
</head>
<body>

    <!-- El contenedor dibuja las 4 tarjetas idénticas a tu diseño gráfico -->
    <div class="pasos-container">

    <div class="pasos-container">

    <!-- BOTÓN 1 (Medición y pesado) -->
    <a href="#" class="tarjeta-paso" style="background-color: #B3826C !important; text-decoration: none;">
        <div class="numero-circulo">1</div>
        <div class="texto-paso" style="color: #FFFFFF !important;">MEDICIÓN Y PESADO</div>
    </a>

    <!-- BOTÓN 2 (Mezcla y preparación) -->
    <a href="#" class="tarjeta-paso" style="background-color: #a76141 !important; text-decoration: none;">
        <div class="numero-circulo">2</div>
        <div class="texto-paso" style="color: #FFFFFF !important;">MEZCLA Y PREPARACIÓN</div>
    </a>

    <!-- BOTÓN 3 (Corte, división y formado) -->
    <a href="#" class="tarjeta-paso" style="background-color: #c58161 !important; text-decoration: none;">
        <div class="numero-circulo">3</div>
        <div class="texto-paso" style="color: #FFFFFF !important;">CORTE, DIVISIÓN Y FORMADO</div>
    </a>

    <!-- BOTÓN 4 (Horneado y reposo) -->
    <a href="#" class="tarjeta-paso" style="background-color: #a05a39 !important; text-decoration: none;">
        <div class="numero-circulo">4</div>
        <div class="texto-paso" style="color: #FFFFFF !important;">HORNEADO Y REPOSO</div>
    </a>

</div>
        <?php foreach ($pasos as $paso): ?>
            <div class="tarjeta-paso">
                <div class="numero-circulo"><?php echo $paso['num']; ?></div>
                <div class="texto-paso"><?php echo htmlspecialchars($paso['titulo']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>

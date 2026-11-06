<?php
session_start();

// Detectar categoría desde la URL, valor por defecto 'utensilios'
$categoria = $_GET['categoria'] ?? 'utensilios';
$categoria_limpia = strtolower(trim($categoria));

// Pasos definidos
$pasos = [
    ['num' => 1, 'titulo' => 'Medición y pesado'],
    ['num' => 2, 'titulo' => 'Mezcla y preparación'],
    ['num' => 3, 'titulo' => 'Corte, división y formado'],
    ['num' => 4, 'titulo' => 'Horneado y reposo'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        body {
            background-color: #FAF7F2;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .pasos-container {
            display: grid;
            grid-template-columns: repeat(2, 11cm);
            gap: 1cm;
            justify-content: center;
            width: max-content;
            margin: 30px auto;
        }
        .tarjeta-paso {
            border: 1px solid #EADCC9;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center; /* ✅ ESTA era la línea que faltaba */
            gap: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.01);
            width: 10cm;
            height: 10cm;
            color: #FFFFFF;
            text-decoration: none;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .tarjeta-paso:hover {
            transform: scale(1.02);
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
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
            font-weight: bold;
            font-size: 1rem;
            text-transform: uppercase;
            text-align: center;
            color: inherit;
        }
        /* Colores específicos para cada paso */
        .tarjeta-paso.color-1 { background-color: #B3826C; }
        .tarjeta-paso.color-2 { background-color: #a76141; }
        .tarjeta-paso.color-3 { background-color: #c58161; }
        .tarjeta-paso.color-4 { background-color: #a05a39; }
    </style>
</head>
<body>

<div class="pasos-container">
    <?php foreach ($pasos as $paso): ?>
        <a href="#" class="tarjeta-paso color-<?php echo $paso['num']; ?>">
            <div class="numero-circulo"><?php echo $paso['num']; ?></div>
            <div class="texto-paso"><?php echo htmlspecialchars($paso['titulo']); ?></div>
        </a>
    <?php endforeach; ?>
</div>

</body>
</html>
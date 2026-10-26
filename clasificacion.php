<!DOCTYPE html>
<html lang="es">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>panel</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(160deg, #F5E6C8 0%, #EDD5A3 40%, #D4956A 100%);
            min-height: 100vh;
        }

        /* ===== NAVBAR test ===== */
        .navbar {
            width: 100%;
            background: linear-gradient(90deg, #3D2314, #8B4513, #C85A32);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 40px;
            box-shadow: 0 4px 12px rgba(61, 35, 20, 0.4);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: #F5E6C8;
            letter-spacing: 2px;
        }

        .nav-links {
            list-style: none;
            display: flex;
            gap: 30px;
        }

        .nav-links a {
            text-decoration: none;
            color: #F5E6C8;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 1px;
            padding: 6px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover,
        .nav-links a.activo {
            background-color: #F5E6C8;
            color: #8B4513;
        }

        /* ===== SECCIÓN ===== */
        .seccion-equipo {
            padding: 40px 20px 60px 20px;
            text-align: center;
        }

        .titulo-equipo {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: 6px;
            margin-bottom: 30px;
            background: linear-gradient(90deg, #8B4513, #C85A32, #D4A017);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* ===== VISOR ESTILO CANVA ===== */
        .visor-canva {
            display: flex;
            gap: 16px;
            justify-content: center;
            align-items: flex-start;
            max-width: 1100px;
            margin: 0 auto;
        }

        /* Panel izquierdo — miniaturas */
        .panel-miniaturas {
            display: flex;
            flex-direction: column;
            gap: 10px;
            overflow-y: auto;
            max-height: 560px;
            padding: 6px;
            background: rgba(255,248,235,0.8);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(61,35,20,0.15);
            scrollbar-width: thin;
            scrollbar-color: #C85A32 #F5E6C8;
        }

        .panel-miniaturas::-webkit-scrollbar { width: 6px; }
        .panel-miniaturas::-webkit-scrollbar-thumb { background: #C85A32; border-radius: 4px; }

        .miniatura {
            width: 90px;
            height: 90px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s ease;
            flex-shrink: 0;
            position: relative;
        }

        .miniatura img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .miniatura:hover {
            border-color: #C85A32;
            transform: scale(1.05);
        }

        .miniatura.activa {
            border-color: #8B4513;
            box-shadow: 0 0 12px rgba(139,69,19,0.5);
        }

        .miniatura .num {
            position: absolute;
            bottom: 4px;
            left: 4px;
            background: rgba(61,35,20,0.7);
            color: #F5E6C8;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 2px 5px;
            border-radius: 4px;
        }

        /* Vista principal grande */
        .vista-principal {
            flex: 1;
            background: rgba(255,248,235,0.9);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(61,35,20,0.2);
            overflow: hidden;
            position: relative;
            max-width: 700px;
            height: 560px;
        }

        .vista-principal img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: opacity 0.3s ease;
        }

        /* Flechas sobre la imagen grande */
        .flecha-visor {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(200,90,50,0.85);
            color: #fff;
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            z-index: 10;
            transition: background 0.3s ease, transform 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .flecha-visor:hover {
            background: #3D2314;
            transform: translateY(-50%) scale(1.1);
        }

        .flecha-visor.izq { left: 12px; }
        .flecha-visor.der { right: 12px; }

        /* Contador abajo de la imagen */
        .contador {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(61,35,20,0.7);
            color: #F5E6C8;
            font-size: 0.85rem;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 20px;
        }
    </style>
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="nav-logo">🍞</div>
        <ul class="nav-links">
            <li><a href="index.html">Inicio</a></li>
            <li><a href="carrucel2.php">Utensilios</a></li>
            <li><a href="carrucel2.php" class="activo">Maquinaria</a></li>
        </ul>
    </nav>

    <!-- SECCIÓN -->
    <section class="seccion-equipo">
        <h2 class="titulo-equipo">BAKESTOCK</h2>

        <div class="visor-canva">

            <!-- Panel de miniaturas izquierdo -->
            <div class="panel-miniaturas" id="panel-miniaturas">
                <!-- Se genera con JS -->
            </div>

            <!-- Vista principal -->
            <div class="vista-principal">
                <button class="flecha-visor izq" id="btn-prev">&#8592;</button>
                <img id="img-principal" src="imagenes/imagen2.png" alt="Imagen principal">
                <button class="flecha-visor der" id="btn-next">&#8594;</button>
                <div class="contador" id="contador">1 / 9</div>
            </div>

        </div>
    </section>

    <script>
        const imagenes = [
            'imagenes/imagen2.png',
            'imagenes/imagen3.png',
            'imagenes/imagen4.png',
            'imagenes/imagen5.png',
            'imagenes/imagen6.png',
            'imagenes/imagen7.png',
            'imagenes/imagen8.png',
            'imagenes/imagen9.png',
            'imagenes/imagen10.png',
        ];

        const panelMiniaturas = document.getElementById('panel-miniaturas');
        const imgPrincipal    = document.getElementById('img-principal');
        const contador        = document.getElementById('contador');
        const btnPrev         = document.getElementById('btn-prev');
        const btnNext         = document.getElementById('btn-next');

        let actual = 0;

        // Crear miniaturas
        imagenes.forEach((src, i) => {
            const div = document.createElement('div');
            div.classList.add('miniatura');
            div.innerHTML = `<img src="${src}" alt="Imagen ${i+1}"><span class="num">${i+1}</span>`;
            div.addEventListener('click', () => irA(i));
            panelMiniaturas.appendChild(div);
        });

        function irA(indice) {
            actual = ((indice % imagenes.length) + imagenes.length) % imagenes.length;

            // Cambiar imagen principal con fade
            imgPrincipal.style.opacity = 0;
            setTimeout(() => {
                imgPrincipal.src = imagenes[actual];
                imgPrincipal.style.opacity = 1;
            }, 150);

            // Actualizar contador
            contador.textContent = `${actual + 1} / ${imagenes.length}`;

            // Actualizar miniatura activa
            document.querySelectorAll('.miniatura').forEach((m, i) => {
                m.classList.toggle('activa', i === actual);
            });

            // Hacer scroll a la miniatura activa
            const miniaturaActiva = panelMiniaturas.children[actual];
            if (miniaturaActiva) {
                miniaturaActiva.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }

        btnNext.addEventListener('click', () => irA(actual + 1));
        btnPrev.addEventListener('click', () => irA(actual - 1));

        irA(0);
    </script>

</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel</title>
    <style>
        /* 1. Obligamos a la página a medir el 100% de la ventana sin barras de scroll innecesarias */
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100vh;
            overflow: hidden; /* Evita que la pantalla baile hacia arriba y abajo */
            background: linear-gradient(160deg, #F5E6C8 0%, #EDD5A3 40%, #D4956A 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Reducimos un poco el margen del título principal para ganar espacio vertical */
        .titulo-equipo {
            font-size: 2.5rem;
            font-weight: 800;
            letter-spacing: 6px;
            margin-top: 5px;
            margin-bottom: 15px;
            text-align: center;
            background: linear-gradient(90deg, #8B4513, #C85A32, #D4A017);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }            

        /* Contenedor principal que se divide en 3 columnas */
        .pantalla-dividida {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Crea 3 columnas exactamente iguales */
            gap: 15px;
            width: 100%;
            max-width: 98vw;
            margin: 0 auto;
            box-sizing: border-box;
            flex: 1; /* Hace que el grid se estire hasta abajo ocupando el espacio libre */
        }

        .seccion-equipo {
            width: 100%;
            height: 100vh; 
            display: flex;
            flex-direction: column;
            padding: 15px;
            box-sizing: border-box;
        }

        /* Estilo para cada una de las 3 tarjetas/columnas */
        .columna-modulo {
            background: rgba(255, 248, 235, 0.9);   
            border-radius: 14px;                   
            box-shadow: 0 6px 20px rgba(61, 35, 20, 0.15); 
            padding: 0;                            /* Quitamos el espacio para que la foto llegue al borde */
            overflow: hidden;                      /* Corta las esquinas de la foto para que se redondeen con la tarjeta */
            height: 100%;                          
            display: flex;                    
            flex-direction: column;                      
            box-sizing: border-box;
            transition: transform 0.3s ease;
        }

        /* Efecto visual al pasar el mouse por encima */
        .columna-modulo:hover {
            transform: translateY(-5px);          
        }

        /* Estilo para los títulos de cada columna */
        .columna-modulo h3 {
            color: #8B4513;                        
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0;                      
            border-bottom: 2px solid #D4956A;      
            padding: 15px 10px;                    
            text-align: center;      
        }

        /* Contenedor del minicarrusel */
        .visor-mini-carrusel {
            position: relative;
            width: 100%;
            flex: 1;            /* Hace que la caja de fotos crezca hasta tocar el fondo de la tarjeta */
            background: #fff;
            overflow: hidden;
        }

        /* Estilo para las imágenes del carrusel */
        .img-mini {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0;
            transition: opacity 0.8s ease-in-out; /* Desvanecimiento suave */
        }

        /* Fuerza a que se muestre la que tenga la clase "activa" O la primera de la lista */
        .img-mini.activa,
        .img-mini:first-of-type {
            opacity: 1 !important;
            z-index: 1; /* La pone al frente */
        }

        /* Botón centrado en el medio de cada carrusel */
        .btn-entrar {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* Centrado matemático perfecto */
            z-index: 10;                      /* Lo pone por encima de las imágenes */
            background: #8B4513;              
            color: #F5E6C8;                   
            border: 2px solid #F5E6C8;
            padding: 12px 24px;
            font-size: 1.1rem;
            font-weight: 700;
            border-radius: 25px;              
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
            transition: all 0.3s ease;
            text-transform: uppercase;        
            letter-spacing: 1px;
        }

        /* Efecto al pasar el mouse por encima */
        .btn-entrar:hover {
            background: #F5E6C8;
            color: #8B4513;
            border-color: #8B4513;
            transform: translate(-50%, -50%) scale(1.08); /* Se hace un poquito más grande */
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.6);
        }
    </style>
</head>
<body>


<!-- SECCIÓN -->
<section class="seccion-equipo">
    <h2 class="titulo-equipo">BAKESTOCK</h2>

    <!-- Contenedor que activa las 3 divisiones -->
    <div class="pantalla-dividida">
        
        <!-- División 1: Insumos -->
        <div class="columna-modulo">
            <h3>Insumos</h3>

            <!-- Envoltura para el carrusel automático -->
            <div class="visor-mini-carrusel">
                <img src="insumos/insumo1.jfif" alt="Insumos" class="img-mini activa">
                <img src="insumos/insumo2.jfif" alt="Insumos" class="img-mini">
                <img src="insumos/insumo3.jfif" alt="Insumos" class="img-mini">
                <img src="insumos/insumo4.jfif" alt="Insumos" class="img-mini">
                <img src="insumos/insumo5.jfif" alt="Insumos" class="img-mini">
                <img src="insumos/insumo6.jfif" alt="Insumos" class="img-mini">
                <img src="insumos/insumo7.jfif" alt="Insumos" class="img-mini">
                <img src="insumos/insumo8.jfif" alt="Insumos" class="img-mini">
                <img src="insumos/insumo9.jfif" alt="Insumos" class="img-mini">
                <img src="insumos/insumo10.jfif" alt="Insumos" class="img-mini">
                
                <!-- BOTÓN INTEGRADO CON ONCLICK DIRECTO -->
                <button class="btn-entrar" onclick="window.location.href='inventario.php'">Entrar al sistema</button>
            </div>
        </div>

        <!-- División 2: Utensilios (Equipo Menor) -->
        <div class="columna-modulo">
            <h3>Utensilios</h3>

            <!-- Envoltura para el carrusel automático -->
            <div class="visor-mini-carrusel">
                <img src="equipoMenor/menor1.jfif" alt="Utensilios" class="img-mini activa">
                <img src="equipoMenor/menor2.jfif" alt="Utensilios" class="img-mini">
                <img src="equipoMenor/menor3.jfif" alt="Utensilios" class="img-mini">
                <img src="equipoMenor/menor4.jfif" alt="Utensilios" class="img-mini">
                <img src="equipoMenor/menor5.jfif" alt="Utensilios" class="img-mini">
                <img src="equipoMenor/menor6.jfif" alt="Utensilios" class="img-mini">
                <img src="equipoMenor/menor7.jfif" alt="Utensilios" class="img-mini">
                <img src="equipoMenor/menor8.jfif" alt="Utensilios" class="img-mini">
                <img src="equipoMenor/menor9.jfif" alt="Utensilios" class="img-mini">
                <img src="equipoMenor/menor10.jfif" alt="Utensilios" class="img-mini">
                <img src="equipoMenor/menor11.jfif" alt="Utensilios" class="img-mini">
                <img src="equipoMenor/menor12.jfif" alt="Utensilios" class="img-mini">
                <img src="equipoMenor/menor13.jfif" alt="Utensilios" class="img-mini">
                <img src="equipoMenor/menor14.jfif" alt="Utensilios" class="img-mini">
                
                <!-- BOTÓN INTEGRADO CON ONCLICK DIRECTO -->
                <button class="btn-entrar" onclick="window.location.href='inventario.php'">Entrar al sistema</button>
            </div>
        </div>

        <!-- División 3: Maquinaria (Equipo Mayor) -->
        <div class="columna-modulo">
            <h3>Maquinaria</h3>

            <!-- Envoltura para el carrusel automático -->
            <div class="visor-mini-carrusel">
                <img src="equipoMayor/mayor1.jfif" alt="Maquinaria" class="img-mini activa">
                <img src="equipoMayor/mayor2.jfif" alt="Maquinaria" class="img-mini">
                <img src="equipoMayor/mayor3.jfif" alt="Maquinaria" class="img-mini">
                <img src="equipoMayor/mayor4.jfif" alt="Maquinaria" class="img-mini">
                <img src="equipoMayor/mayor5.jfif" alt="Maquinaria" class="img-mini">
                <img src="equipoMayor/mayor6.jfif" alt="Maquinaria" class="img-mini">
                <img src="equipoMayor/mayor7.jfif" alt="Maquinaria" class="img-mini">
                <img src="equipoMayor/mayor8.jfif" alt="Maquinaria" class="img-mini">
                <img src="equipoMayor/mayor9.jfif" alt="Maquinaria" class="img-mini">
                <img src="equipoMayor/mayor10.jfif" alt="Maquinaria" class="img-mini">
                <img src="equipoMayor/mayor11.jfif" alt="Maquinaria" class="img-mini">
                
                <!-- BOTÓN INTEGRADO CON ONCLICK DIRECTO -->
                <button class="btn-entrar" onclick="window.location.href='inventario.php'">Entrar al sistema</button>
            </div>
        </div>
    </div>
</section>

<script>
    document.querySelectorAll('.visor-mini-carrusel').forEach(carrusel => {
        const imagenes = carrusel.querySelectorAll('.img-mini');
        if (imagenes.length <= 1) return; // Si hay 0 o 1 imagen, no hace nada

        let indiceActual = 0;

function siguienteImagen() {
    imagenes[indiceActual].classList.remove('activa');
    imagenes[indiceActual].style.opacity = "0";
    imagenes[indiceActual].style.zIndex = "0";

    indiceActual = (indiceActual + 1) % imagenes.length;

    imagenes[indiceActual].classList.add('activa');
    imagenes[indiceActual].style.opacity = "1";
    imagenes[indiceActual].style.zIndex = "1";
}

        // Cambia cada 3 segundos
        setInterval(siguienteImagen, 3000); 
    });
</script>

    </div>
</section>
</body>
</html>

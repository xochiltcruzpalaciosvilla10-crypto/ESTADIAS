<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panadería | Galería</title>
    <link rel="stylesheet" href="style.css?v=20261013-5">
        <style>
        /* Estilos para el contenedor del carrusel */
        .carousel {
            position: relative;
            width: 1000%;
            margin: 0 auto;
            overflow: visible;
            border-radius: 20px;
            background: transparent;
        }

        .swiper {
            width: 100%;
            padding-top: 40px;
            padding-bottom: 50px;
        }

        .carousel-slide {
            width: 280px !important; /* Tamaño responsivo para la tarjeta del centro prueba de cambio */
            height: 340px;
            background: #ffffff;
            border-radius: 24px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(142, 92, 29, 0.12);
            transition: all 0.3s ease;
            box-sizing: border-box;
            display: flex;
            align-items: center;
            justify-content: center;
        }
          .swiper-slide-next,
        .swiper-slide-prev {
            filter: blur(4px);
            opacity: 0.7;
        }

        .carousel-slide.is-active {
            display: block;
            animation: desvanecer 0.8s ease-in-out;
        }

        /* 🌟 LAS FLECHAS <> (Diseño Pro) */
        .carousel-btn {
            position: absolute;/*posisionamiento absoluto para que maueladj 
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.85);
            border: 1.5px solid rgba(142, 92, 69, 0.2);
            color: #8E5C45;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 16px rgba(122, 63, 29, 0.15);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .carousel-btn:hover {
            background: #8E5C45;
            color: #ffffff;
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-btn--prev { left: 15px; }
        .carousel-btn--next { right: 15px; }

        /* 🌟 LOS PUNTOS INDICADORES (...) */
        .carousel-dots {
            position: absolute;
            bottom: 15px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 10;
        }

        .carousel-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: none;
            background: rgba(142, 92, 69, 0.35);
            cursor: pointer;
            padding: 0;
            transition: all 0.2s ease;
        }

        .carousel-dot.is-active {
            background: #8E5C45;
            transform: scale(1.3);
            box-shadow: 0 0 6px rgba(142, 92, 69, 0.4);
        }

        .carousel-btn--prev { left: min(50px, 2%); }
        .carousel-btn--next { right: min(50px, 2%); }

        .gallery-bottom {
    background: #ffffff;
    margin: 40px auto;
    padding: 40px 30px;
    border-radius: 32px;
    width: min(1200px, 95%);
    box-shadow: 0 10px 30px rgba(142, 92, 69, 0.04);
}

/* Configura el espacio ordenado entre cada tarjeta */
.gallery-bottom__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 25px;
}

/* Crea la tarjeta premium con bordes suaves */
.gallery-card {
    background: #fffdf9;
    border: 1.5px solid rgba(142, 92, 69, 0.08);
    border-radius: 24px;
    padding: 20px;
    margin: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(142, 92, 69, 0.03);
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

/* El efecto dinámico de flotado y sombra profunda */
.gallery-card:hover {
    transform: translateY(-8px);
    background: #ffffff;
    border-color: rgba(217, 128, 46, 0.3);
    box-shadow: 0 16px 32px rgba(142, 92, 69, 0.12);
}

/* Controla el tamaño de los utensilios y ollas */
.gallery-card img {
    mix-blend-mode: multiply;
    width: 100%;
    height: 180px; 
    object-fit: contain; 
}

.gallery-card:hover img {
    transform: scale(1.06);
}

/* Añade un texto elegante abajo de cada traste */
.gallery-card::after {
    mix-blend-mode: multiply;
    width: 100%;
    height: auto;
    max-height: 150px; /* Tamaño ideal de antes */
    object-fit: contain;
    transition: transform 0.3s ease;
}


    </style>
</head>
<body>

    <header class="site-brand">
        <h1 class="site-brand__name" aria-label="BakeStock">
            <svg viewBox="0 0 700 160" aria-hidden="true" focusable="false">
                <defs>
                    <path id="site-brand-curve" d="M 30 140 Q 350 -5 670 140"></path>
                </defs>
                <text>
                    <textPath href="#site-brand-curve" startOffset="50%" text-anchor="middle">BakeStock</textPath>
                </text>
            </svg>
        </h1>
    </header>

    <nav class="top-nav" aria-label="Navegación principal">
        <a class="top-nav__link" href="index.php">Inicio</a>
        <a class="top-nav__link" href="equipo_mayor.php">Equipo mayor</a>
        <a class="top-nav__link" href="equipo_menor.php">Equipo menor</a>
    </nav>

    <main>
        <section class="carousel" aria-label="Galería de imágenes">
            <button class="carousel-btn carousel-btn--prev" type="button" aria-label="Imagen anterior">
                <span aria-hidden="true">&lt;</span>
            </button>
<div class="carousel-track">
    <figure class="carousel-slide is-active">
        <img src="images/carrusel-1.jpg" alt="Imagen 1 de la panadería" style="mix-blend-mode: multiply;">
    </figure>
    <figure class="carousel-slide">
        <img src="images/equipo mayor1.jfif" alt="Imagen 2 de la panadería" style="mix-blend-mode: multiply;">
    </figure>
    <figure class="carousel-slide">
        <img src="images/equipo mayor2.jfif" alt="Imagen 3 de la panadería" style="mix-blend-mode: multiply;">
    </figure>
    <figure class="carousel-slide">
        <img src="images/equipo mayor3.jfif" alt="Imagen 4 de la panadería" style="mix-blend-mode: multiply;">
    </figure>
    <figure class="carousel-slide">
        <img src="images/equipo mayor4.jfif" alt="Imagen 5 de la panadería" style="mix-blend-mode: multiply;">
    </figure>
    <figure class="carousel-slide">
        <img src="images/equipo mayor5.jfif" alt="Imagen 6 de la panadería" style="mix-blend-mode: multiply;">
    </figure>
    <figure class="carousel-slide">
        <img src="images/panaderia-nueva-2.jpg" alt="Imagen adicional de la panadería" style="mix-blend-mode: multiply;">
    </figure>
    <figure class="carousel-slide">
        <img src="images/panaderia-nueva-3.jpg" alt="Otra imagen de la panadería" style="mix-blend-mode: multiply;">
    </figure>
</div>


            <button class="carousel-btn carousel-btn--next" type="button" aria-label="Imagen siguiente" data-carousel-next>
                <span aria-hidden="true">&gt;</span>
            </button>

            <div class="carousel-dots" aria-label="Indicador de imágenes" data-carousel-dots></div>
            <p class="visually-hidden" aria-live="polite" data-status></p>
        </section>

        <section class="gallery-bottom" aria-label="Galería de imágenes">
            <div class="gallery-bottom__grid">
                <?php
                $imagenes = glob(__DIR__ . '/images/*');
                $imagenes = array_filter($imagenes, function ($imagen) {
                    $nombreArchivo = strtolower(basename($imagen));
                    $archivosExcluidos = ['reposteria-utensilios.png'];

                    return is_file($imagen)
                        && preg_match('/\.(jpg|jpeg|jpe|jfif|png|webp|gif)$/i', $imagen)
                        && !in_array($nombreArchivo, $archivosExcluidos, true);
                });
                natsort($imagenes);
                $imagenes = array_slice(array_values($imagenes), 8, -1);
                ?>
                <?php foreach ($imagenes as $indice => $imagen): ?>
   <figure class="gallery-card" 
           style="background: #ffffff; border-radius: 24px; padding: 20px; box-shadow: 0 6px 16px rgba(142, 92, 69, 0.05); overflow: hidden; cursor: pointer; transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1), box-shadow 0.3s ease; margin: 0; display: flex; align-items: center; justify-content: center;" 
            onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 16px 32px rgba(142, 92, 69, 0.15)';" 
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 6px 16px rgba(142, 92, 69, 0.05)';">
        <img src="images/<?= rawurlencode(basename($imagen)) ?>" alt="Imagen <?= $indice + 1 ?> de la galería" loading="lazy">
    </figure>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

     <script src="https://jsdelivr.net"></script>

    <script>
        const swiper = new Swiper(".mySwiper", {
            effect: "coverflow",
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: "auto",
            loop: true,
            coverflowEffect: {
                rotate: 0,       /* No rotamos las tarjetas para que se mantengan planas al frente */
                stretch: -20,    /* Se enciman ligeramente para imitar tu captura */
                depth: 150,      /* Empuja las tarjetas laterales hacia el fondo generando el efecto 3D */
                modifier: 1.5,
                slideShadows: false,
            },
            navigation: {
                nextEl: ".carousel-btn--next",
                prevEl: ".carousel-btn--prev",
            },
        });
    </script>
</body>
</html>

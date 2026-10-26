<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrusel de panadería</title>
    <style>
        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            overflow: hidden;
            background: #1B120D;
        }

/* CARRUSEL ELEGANTE: CAFÉ OSCURO Y DORADO */

.carrusel-fondo {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    width: min(90%, 1000px);
    height: min(55vh, 420px);
    min-height: 250px;

    overflow: hidden;
    border-radius: 22px;
    border: 2px solid #C9A44C;

    z-index: 1;
    background-color: #24150F;

    box-shadow:
        0 15px 40px rgba(0, 0, 0, 0.50),
        0 0 18px rgba(201, 164, 76, 0.20);
}

/* Fotografías */
.carrusel-fondo .slide-imagen {
    position: absolute;
    inset: 0;

    display: block;
    width: 100%;
    height: 100%;

    object-fit: cover;
    object-position: center;

    opacity: 0;
    transform: scale(1.04);

    transition:
        opacity 1.2s ease-in-out,
        transform 4.5s ease-in-out;

    will-change: opacity, transform;
}

/* Fotografía activa */
.carrusel-fondo .slide-imagen.activo {
    opacity: 1;
    transform: scale(1);
}

/* Capa oscura para facilitar la lectura */
.carrusel-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    width: min(90%, 1000px);
    height: min(55vh, 420px);
    min-height: 250px;

    border-radius: 22px;

    background: linear-gradient(
        135deg,
        rgba(29, 17, 12, 0.72),
        rgba(53, 31, 20, 0.42),
        rgba(22, 13, 9, 0.72)
    );

    z-index: 2;
    pointer-events: none;
}

/* Contenedor de las flechas e indicadores */
.carrusel-controles {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);

    width: min(90%, 1000px);
    height: min(55vh, 420px);
    min-height: 250px;

    z-index: 4;
    pointer-events: none;
}

/* Flechas de navegación */
.carrusel-flecha {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);

    display: flex;
    justify-content: center;
    align-items: center;

    width: 48px;
    height: 48px;

    border: 1px solid rgba(231, 197, 112, 0.85);
    border-radius: 50%;

    background: rgba(38, 23, 15, 0.82);
    color: #F2D58A;

    font-size: 32px;
    line-height: 1;

    cursor: pointer;
    pointer-events: auto;

    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);

    transition:
        background 0.25s ease,
        color 0.25s ease,
        transform 0.25s ease,
        box-shadow 0.25s ease;
}

.carrusel-flecha.anterior {
    left: 16px;
}

.carrusel-flecha.siguiente {
    right: 16px;
}

.carrusel-flecha:hover {
    background: #C9A44C;
    color: #24150F;

    transform: translateY(-50%) scale(1.08);

    box-shadow: 0 0 18px rgba(201, 164, 76, 0.4);
}

/* Accesibilidad del teclado */
.carrusel-flecha:focus-visible,
.carrusel-indicador:focus-visible {
    outline: 3px solid #F2D58A;
    outline-offset: 4px;
}

/* Indicadores inferiores */
.carrusel-indicadores {
    position: absolute;
    left: 50%;
    bottom: 16px;
    transform: translateX(-50%);

    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;

    width: calc(100% - 110px);
    pointer-events: auto;
}

/* Cada indicador */
.carrusel-indicador {
    width: 10px;
    height: 10px;
    padding: 0;

    border: 1px solid #E7C570;
    border-radius: 50%;

    background: rgba(255, 255, 255, 0.40);
    cursor: pointer;

    transition:
        width 0.3s ease,
        background 0.3s ease,
        border-radius 0.3s ease,
        box-shadow 0.3s ease;
}

/* Indicador seleccionado */
.carrusel-indicador.activo {
    width: 28px;
    border-radius: 10px;
    background: #D4AF59;

    box-shadow: 0 0 10px rgba(212, 175, 89, 0.65);
}

/* Adaptación para celulares */
@media (max-width: 600px) {
    .carrusel-fondo,
    .carrusel-overlay,
    .carrusel-controles {
        width: 92%;
        height: 42vh;
        min-height: 240px;
    }

    .carrusel-fondo,
    .carrusel-overlay {
        border-radius: 16px;
    }

    .carrusel-flecha {
        width: 38px;
        height: 38px;
        font-size: 27px;
    }

    .carrusel-flecha.anterior {
        left: 9px;
    }

    .carrusel-flecha.siguiente {
        right: 9px;
    }

    .carrusel-indicadores {
        bottom: 12px;
        gap: 8px;
        width: calc(100% - 80px);
    }

    .carrusel-indicador {
        width: 8px;
        height: 8px;
    }

    .carrusel-indicador.activo {
        width: 22px;
    }
}

/* Respeta la preferencia de movimiento reducido */
@media (prefers-reduced-motion: reduce) {
    .carrusel-fondo .slide-imagen,
    .carrusel-flecha,
    .carrusel-indicador {
        transition: none;
    }
}

/* Texto y botón delante del carrusel */
.bienvenida-contenido {
    position: relative;
    z-index: 3;
    width: 100%;
    max-width: 900px;
    padding: 20px;
}

/* Orden de las capas */
.carrusel-fondo {
    z-index: 1;
}

.carrusel-overlay {
    z-index: 2;
}

.carrusel-controles {
    z-index: 4;
}
</style>

</head>
<body>

<!-- Carrusel de fotografías -->
<div class="carrusel-fondo">

    <img
        class="slide-imagen activo"
        src="img/pan1.jfif"
        alt="Panadería, imagen 1"
    >

    <img
        class="slide-imagen"
        src="img/pan2.jfif"
        alt="Panadería, imagen 2"
    >

    <img
        class="slide-imagen"
        src="img/pan3.jfif"
        alt="Panadería, imagen 3"
    >

    <img
        class="slide-imagen"
        src="img/pan4.jfif"
        alt="Panadería, imagen 4"
    >

    <img
        class="slide-imagen"
        src="img/pan5.jfif"
        alt="Panadería, imagen 5"
    >

    <img
        class="slide-imagen"
        src="img/pan6.jfif"
        alt="Panadería, imagen 6"
    >

    <img
        class="slide-imagen"
        src="img/pan7.jfif"
        alt="Panadería, imagen 7"
    >

</div>

<!-- Capa oscura sobre las imágenes -->
<div class="carrusel-overlay"></div>

<!-- Flechas e indicadores -->
<div class="carrusel-controles">

    <button
        type="button"
        class="carrusel-flecha anterior"
        aria-label="Mostrar imagen anterior"
    >
        &#10094;
    </button>

    <button
        type="button"
        class="carrusel-flecha siguiente"
        aria-label="Mostrar imagen siguiente"
    >
        &#10095;
    </button>

    <!-- Indicadores de las siete imágenes -->
    <div class="carrusel-indicadores">

        <button
            type="button"
            class="carrusel-indicador activo"
            data-slide="0"
            aria-label="Mostrar imagen 1"
            aria-pressed="true"
        ></button>

        <button
            type="button"
            class="carrusel-indicador"
            data-slide="1"
            aria-label="Mostrar imagen 2"
            aria-pressed="false"
        ></button>

        <button
            type="button"
            class="carrusel-indicador"
            data-slide="2"
            aria-label="Mostrar imagen 3"
            aria-pressed="false"
        ></button>

        <button
            type="button"
            class="carrusel-indicador"
            data-slide="3"
            aria-label="Mostrar imagen 4"
            aria-pressed="false"
        ></button>

        <button
            type="button"
            class="carrusel-indicador"
            data-slide="4"
            aria-label="Mostrar imagen 5"
            aria-pressed="false"
        ></button>

        <button
            type="button"
            class="carrusel-indicador"
            data-slide="5"
            aria-label="Mostrar imagen 6"
            aria-pressed="false"
        ></button>

        <button
            type="button"
            class="carrusel-indicador"
            data-slide="6"
            aria-label="Mostrar imagen 7"
            aria-pressed="false"
        ></button>

    </div>
</div>

<script>
    const slides = Array.from(document.querySelectorAll('.slide-imagen'));
    const indicators = Array.from(
        document.querySelectorAll('.carrusel-indicador')
    );
    const previousButton = document.querySelector('.carrusel-flecha.anterior');
    const nextButton = document.querySelector('.carrusel-flecha.siguiente');
    let activeSlide = 0;

    function showSlide(index) {
        activeSlide = (index + slides.length) % slides.length;

        slides.forEach((slide, slideIndex) => {
            slide.classList.toggle('activo', slideIndex === activeSlide);
        });

        indicators.forEach((indicator, slideIndex) => {
            const isActive = slideIndex === activeSlide;
            indicator.classList.toggle('activo', isActive);
            indicator.setAttribute('aria-pressed', String(isActive));
        });
    }

    previousButton.addEventListener('click', () => {
        showSlide(activeSlide - 1);
    });

    nextButton.addEventListener('click', () => {
        showSlide(activeSlide + 1);
    });

    indicators.forEach((indicator) => {
        indicator.addEventListener('click', () => {
            showSlide(Number(indicator.dataset.slide));
        });
    });
</script>

</body>
</html>
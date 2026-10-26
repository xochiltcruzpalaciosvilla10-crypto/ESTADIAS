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
    .carrusel-indicador{
        transition: none;
    }
}

/* Texto y botón delante del carrusel */
.bienvenida-contenido {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 3;
    width: min(90%, 900px);
    padding: 20px;
    text-align: center;
    box-sizing: border-box;
}

.bienvenida-contenido h1 {
    margin: 0 0 14px;
    color: #F2D58A;
    font-size: 3.5rem;
    line-height: 1.05;
    text-shadow: 0 3px 15px rgba(0, 0, 0, 0.65);
}

.bienvenida-contenido p {
    margin: 0 0 24px;
    color: #FFF8E8;
    font-size: 1.25rem;
    line-height: 1.4;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.8);
}

.btn-entrar {
    display: inline-flex;
    min-height: 48px;
    align-items: center;
    justify-content: center;
    padding: 0 24px;
    border: 1px solid #F2D58A;
    border-radius: 8px;
    background: #C9A44C;
    color: #24150F;
    font-size: 1rem;
    font-weight: 700;
    text-decoration: none;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.btn-entrar:hover {
    background: #F2D58A;
    transform: translateY(-2px);
}

.btn-entrar:focus-visible {
    outline: 3px solid #FFF8E8;
    outline-offset: 4px;
}

.btn-musica {
    display: block;
    min-height: 42px;
    margin: 14px auto 0;
    padding: 0 16px;
    border: 1px solid rgba(255, 248, 232, 0.75);
    border-radius: 8px;
    background: rgba(36, 21, 15, 0.82);
    color: #FFF8E8;
    font-size: 0.95rem;
    cursor: pointer;
}

.btn-musica:hover {
    background: #6B3A2A;
}

.btn-musica:focus-visible {
    outline: 3px solid #FFF8E8;
    outline-offset: 4px;
}

@media (max-width: 600px) {
    .bienvenida-contenido h1 {
        font-size: 2.25rem;
    }

    .bienvenida-contenido p {
        font-size: 1rem;
    }
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

<main class="bienvenida-contenido">
    <h1>BAKESTOCK</h1>
    <p>Sistema para el control de inventario</p>
    <a class="btn-entrar" href="Inventario.php?vista=panel">
        Entrar al sistema
    </a>
    <audio id="musicaPanaderia" src="musicaPanaderia.mp3" loop preload="auto" autoplay></audio>
    <button class="btn-musica" id="botonMusica" type="button" aria-label="Reproducir música" aria-pressed="false">
        ♫ Reproducir música
    </button>
</main>

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

    const music = document.getElementById('musicaPanaderia');
    const musicButton = document.getElementById('botonMusica');

    async function playMusic() {
        try {
            await music.play();
            musicButton.textContent = '♫';
            musicButton.setAttribute('aria-label', 'Pausar música');
            musicButton.setAttribute('aria-pressed', 'true');
        } catch (error) {
            musicButton.textContent = '♫ Reproducir música';
            musicButton.setAttribute('aria-label', 'Reproducir música');
            musicButton.setAttribute('aria-pressed', 'false');
        }
    }

    musicButton.addEventListener('click', () => {
        if (music.paused) {
            playMusic();
            return;
        }

        music.pause();
        musicButton.textContent = '♫ Reproducir música';
        musicButton.setAttribute('aria-label', 'Reproducir música');
        musicButton.setAttribute('aria-pressed', 'false');
    });

    playMusic();
</script>

</body>
</html>
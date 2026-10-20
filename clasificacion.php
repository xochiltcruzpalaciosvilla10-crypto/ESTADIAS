<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panadería | Galería</title>
    <link rel="stylesheet" href="style.css?v=20261013-5">
        <style>
        .carousel {
            overflow: hidden;
        }

        .carousel-track {
            position: absolute;
            inset: 0 auto 0 0;
            display: flex;
            width: max-content;
            align-items: center;
            gap: 22px;
            overflow: visible;
            transition: transform 450ms ease;
        }

        .carousel-slide {
            position: relative;
            inset: auto;
            display: flex;
            flex: 0 0 clamp(250px, 34vw, 370px);
            width: clamp(250px, 34vw, 370px);
            height: min(420px, calc(100% - 48px));
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 18px;
            border-radius: 24px;
            background: #fffdf9;
            box-shadow: 0 10px 30px rgba(142, 92, 29, 0.16);
            opacity: 0.58;
            filter: blur(2px);
            transform: scale(0.86);
            transition: transform 450ms ease, opacity 450ms ease, filter 450ms ease;
        }

        .carousel-slide.is-active {
            display: flex;
            opacity: 1;
            filter: none;
            transform: scale(1);
        }

        .carousel-slide img {
            display: block;
            width: 100%;
            height: 100%;
            border-radius: 12px;
            object-fit: contain;
            background: #fffdf9;
        }

        @media (max-width: 600px) {
            .carousel-track {
                gap: 12px;
            }

            .carousel-slide {
                flex-basis: min(72vw, 300px);
                width: min(72vw, 300px);
                height: calc(100% - 48px);
                padding: 12px;
                border-radius: 18px;
            }
        }

        .carousel-btn {
            width: 50px;
            height: 50px;
            border: 1.5px solid rgba(142, 92, 69, 0.2);
            background: rgba(255, 255, 255, 0.9);
            color: #8e5c45;
            font-weight: bold;
            box-shadow: 0 6px 16px rgba(122, 63, 29, 0.15);
        }

        .carousel-btn:hover,
        .carousel-btn:focus-visible {
            background: #8e5c45;
            color: #fff;
        }

        .carousel-btn--prev {
            left: min(50px, 2%);
        }

        .carousel-btn--next {
            right: min(50px, 2%);
        }

        .carousel-dot {
            background: rgba(142, 92, 69, 0.35);
        }

        .carousel-dot.is-active {
            background: #8e5c45;
            box-shadow: 0 0 6px rgba(142, 92, 69, 0.4);
        }

        @media (prefers-reduced-motion: reduce) {
            .carousel-track,
            .carousel-slide {
                transition-duration: 0.01ms;
            }
        }

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
            <button class="carousel-btn carousel-btn--prev" type="button" aria-label="Imagen anterior" data-carousel-prev>
                <span aria-hidden="true">&lt;</span>
            </button>
<div class="carousel-track">
    <figure class="carousel-slide is-active">
        <img src="images/carrusel-1.jpg" alt="Imagen 1 de la panadería">
    </figure>
    <figure class="carousel-slide">
        <img src="images/equipo mayor1.jfif" alt="Imagen 2 de la panadería">
    </figure>
    <figure class="carousel-slide">
        <img src="images/equipo mayor2.jfif" alt="Imagen 3 de la panadería">
    </figure>
    <figure class="carousel-slide">
        <img src="images/equipo mayor3.jfif" alt="Imagen 4 de la panadería">
    </figure>
    <figure class="carousel-slide">
        <img src="images/equipo mayor4.jfif" alt="Imagen 5 de la panadería">
    </figure>
    <figure class="carousel-slide">
        <img src="images/equipo mayor5.jfif" alt="Imagen 6 de la panadería">
    </figure>
    <figure class="carousel-slide">
        <img src="images/panaderia-nueva-2.jpg" alt="Imagen adicional de la panadería">
    </figure>
    <figure class="carousel-slide">
        <img src="images/panaderia-nueva-3.jpg" alt="Otra imagen de la panadería">
    </figure>
    <figure class="carousel-slide">
        <img src="https://loremflickr.com/960/640/bakery,commercial,oven?lock=81201" alt="Horno industrial de panadería" data-fallback="images/equipo mayor1.jfif">
    </figure>
    <figure class="carousel-slide">
        <img src="https://loremflickr.com/960/640/bakery,industrial,mixer?lock=81202" alt="Batidora industrial para panadería" data-fallback="images/equipo mayor2.jfif">
    </figure>
    <figure class="carousel-slide">
        <img src="https://loremflickr.com/960/640/bakery,dough,machine?lock=81203" alt="Amasadora para panadería" data-fallback="images/equipo mayor3.jfif">
    </figure>
    <figure class="carousel-slide">
        <img src="https://loremflickr.com/960/640/bakery,baking,tools?lock=81204" alt="Utensilios de panadería" data-fallback="images/menor.jfif">
    </figure>
    <figure class="carousel-slide">
        <img src="https://loremflickr.com/960/640/bakery,commercial,kitchen?lock=81205" alt="Equipo comercial de panadería" data-fallback="images/menor1.jfif">
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

    <script>
        const carousel = document.querySelector(".carousel");
        const track = carousel.querySelector(".carousel-track");
        const dotsContainer = carousel.querySelector("[data-carousel-dots]");
        const status = carousel.querySelector("[data-status]");
        const originalSlides = Array.from(track.querySelectorAll(".carousel-slide"));
        const slideCount = originalSlides.length;
        const dots = [];
        let slides;
        let activeIndex = 1;

        track.prepend(originalSlides[slideCount - 1].cloneNode(true));
        track.append(originalSlides[0].cloneNode(true));
        slides = Array.from(track.querySelectorAll(".carousel-slide"));

        slides.forEach((slide) => {
            const image = slide.querySelector("img[data-fallback]");
            if (!image) {
                return;
            }

            image.addEventListener("error", () => {
                image.src = image.dataset.fallback;
                image.removeAttribute("data-fallback");
            }, { once: true });
        });

        originalSlides.forEach((slide, index) => {
            const dot = document.createElement("button");
            dot.type = "button";
            dot.className = "carousel-dot";
            dot.setAttribute("aria-label", `Ir a la imagen ${index + 1}`);
            dot.addEventListener("click", () => {
                activeIndex = index + 1;
                updateCarousel();
            });
            dotsContainer.append(dot);
            dots.push(dot);
        });

        function updateCarousel() {
            slides.forEach((slide, index) => {
                slide.classList.toggle("is-active", index === activeIndex);
                slide.setAttribute("aria-hidden", index === activeIndex ? "false" : "true");
            });

            const activeSlide = slides[activeIndex];
            const offset = carousel.clientWidth / 2 - activeSlide.offsetLeft - activeSlide.offsetWidth / 2;
            track.style.transform = `translateX(${offset}px)`;

            const currentDotIndex = (activeIndex - 1 + slideCount) % slideCount;
            dots.forEach((dot, index) => {
                if (index === currentDotIndex) {
                    dot.setAttribute("aria-current", "true");
                } else {
                    dot.removeAttribute("aria-current");
                }
            });
            status.textContent = `Imagen ${currentDotIndex + 1} de ${slideCount}: ${activeSlide.querySelector("img").alt}`;
        }

        function moveCarousel(direction) {
            activeIndex += direction;
            updateCarousel();
        }

        track.addEventListener("transitionend", () => {
            if (activeIndex !== 0 && activeIndex !== slides.length - 1) {
                return;
            }

            activeIndex = activeIndex === 0 ? slideCount : 1;
            track.style.transition = "none";
            updateCarousel();
            track.offsetHeight;
            track.style.removeProperty("transition");
        });

        carousel.querySelector("[data-carousel-prev]").addEventListener("click", () => moveCarousel(-1));
        carousel.querySelector("[data-carousel-next]").addEventListener("click", () => moveCarousel(1));
        window.addEventListener("resize", updateCarousel);
        updateCarousel();
    </script>
</body>
</html>

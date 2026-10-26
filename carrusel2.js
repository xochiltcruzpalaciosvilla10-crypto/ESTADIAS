/*=============javaScrip==============*/

document.addEventListener("DOMContentLoaded", function () {

    const slides = document.querySelectorAll(
        ".carrusel-fondo .slide-imagen"
    );

    const indicadores = document.querySelectorAll(
        ".carrusel-indicador"
    );

    const botonAnterior = document.querySelector(
        ".carrusel-flecha.anterior"
    );

    const botonSiguiente = document.querySelector(
        ".carrusel-flecha.siguiente"
    );

    const zonaImagenes = document.querySelector(
        ".carrusel-fondo"
    );

    const zonaControles = document.querySelector(
        ".carrusel-controles"
    );

    // Si estamos en otra pantalla, no hay carrusel.
    if (slides.length === 0) return;

    let indiceSlide = 0;
    let temporizadorCarrusel = null;

    // Tiempo entre imágenes: 4000 milisegundos = 4 segundos.
    const intervaloCarrusel = 4000;

    // Mostrar la imagen seleccionada.
    function mostrarSlide(nuevoIndice) {

        indiceSlide =
            (nuevoIndice + slides.length) % slides.length;

        slides.forEach(function (slide, indice) {

            const activo = indice === indiceSlide;

            slide.classList.toggle("activo", activo);

            slide.setAttribute(
                "aria-hidden",
                activo ? "false" : "true"
            );

        });

        indicadores.forEach(function (indicador, indice) {

            const activo = indice === indiceSlide;

            indicador.classList.toggle("activo", activo);

            indicador.setAttribute(
                "aria-pressed",
                activo ? "true" : "false"
            );

        });

    }

    // Cambiar automáticamente a la siguiente imagen.
    function cambiarImagenCarrusel() {
        mostrarSlide(indiceSlide + 1);
    }

    // Detener el cambio automático.
    function detenerTemporizadorCarrusel() {

        if (temporizadorCarrusel !== null) {
            clearInterval(temporizadorCarrusel);
            temporizadorCarrusel = null;
        }

    }

    // Iniciar el cambio automático.
    function iniciarTemporizadorCarrusel() {

        if (slides.length <= 1) return;
        if (temporizadorCarrusel !== null) return;

        temporizadorCarrusel = setInterval(
            cambiarImagenCarrusel,
            intervaloCarrusel
        );

    }

    // Reiniciar el tiempo después de usar los controles.
    function reiniciarTemporizadorCarrusel() {
        detenerTemporizadorCarrusel();
        iniciarTemporizadorCarrusel();
    }

    // Flecha para la imagen anterior.
    if (botonAnterior) {

        botonAnterior.addEventListener("click", function (evento) {

            evento.stopPropagation();

            mostrarSlide(indiceSlide - 1);
            reiniciarTemporizadorCarrusel();

        });

    }

    // Flecha para la imagen siguiente.
    if (botonSiguiente) {

        botonSiguiente.addEventListener("click", function (evento) {

            evento.stopPropagation();

            mostrarSlide(indiceSlide + 1);
            reiniciarTemporizadorCarrusel();

        });

    }

    // Seleccionar una imagen mediante los indicadores.
    indicadores.forEach(function (indicador) {

        indicador.addEventListener("click", function (evento) {

            evento.stopPropagation();

            const destino = Number(
                indicador.getAttribute("data-slide")
            );

            if (
                Number.isInteger(destino) &&
                destino >= 0 &&
                destino < slides.length
            ) {

                mostrarSlide(destino);
                reiniciarTemporizadorCarrusel();

            }

        });

    });

    // Pausar el carrusel mientras se utiliza con el ratón
    // o se navega por sus controles con el teclado.
    [zonaImagenes, zonaControles].forEach(function (zona) {

        if (!zona) return;

        zona.addEventListener(
            "mouseenter",
            detenerTemporizadorCarrusel
        );

        zona.addEventListener(
            "mouseleave",
            iniciarTemporizadorCarrusel
        );

        zona.addEventListener(
            "focusin",
            detenerTemporizadorCarrusel
        );

        zona.addEventListener("focusout", function (evento) {

            if (!zona.contains(evento.relatedTarget)) {
                iniciarTemporizadorCarrusel();
            }

        });

    });

    // Mostrar la primera imagen e iniciar el carrusel.
    mostrarSlide(0);
    iniciarTemporizadorCarrusel();

});
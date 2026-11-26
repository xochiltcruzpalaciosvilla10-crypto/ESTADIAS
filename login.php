<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Si viene a cerrar sesión, la borra
if (isset($_GET['cerrar'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

// 2. Si YA inició sesión, lo manda al menú
if (!empty($_SESSION['usuario'])) {
    header("Location: menu.php");
    exit;
}

$error = '';

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=profesor;charset=utf8mb4",
        "root",
        ""
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $usuario = trim($_POST['nombre'] ?? '');
        $password = trim($_POST['password'] ?? '');

        $consulta = $pdo->prepare(
            "SELECT usuario, `contraseña`
             FROM datos
             WHERE usuario = :usuario
             LIMIT 1"
        );

        $consulta->execute([
            'usuario' => $usuario
        ]);

        $datosUsuario = $consulta->fetch();

        if (
            $datosUsuario &&
            (password_verify($password, $datosUsuario['contraseña']) || $password === $datosUsuario['contraseña'])
        ) {
            session_regenerate_id(true);

            $_SESSION['usuario'] = $datosUsuario['usuario'];

            header("Location: menu.php");
            exit;

        } else {
            $error = "Usuario o contraseña incorrectos.";
        }
    }

} catch (PDOException $e) {
    $error = "No se pudo conectar con la base de datos: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>BAKESTOCK | Iniciar sesión</title>

<link rel="preconnect" href="https://googleapis.com">
<link rel="preconnect" href="https://gstatic.com" crossorigin>
<link href="https://googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cloudflare.com">

<style>

:root {
    --cafe-oscuro: #3b2313;
    --cafe-medio: #5c3a21;
    --cafe-texto: #523b2c;
    --dorado: #c88d4b;
    --dorado-claro: #e6b968;
    --crema: #fcf8f2;
    --blanco: #ffffff;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Plus Jakarta Sans', sans-serif;
}

body {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 25px 18px;
    overflow-x: hidden;

    /* Fondo de panadería local con filtro cálido */
    background-image:
        linear-gradient(
            rgba(45, 30, 18, 0.45),
            rgba(45, 30, 18, 0.52)
        ),
        url("fondo.jfif");

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

/* =========================================
   ANIMACIONES (ENTRADA Y BORDE GIRATORIO)
========================================= */

@keyframes entradaSuave {
    from {
        opacity: 0;
        transform: translateY(18px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@property --angulo-borde {
    syntax: "<angle>";
    initial-value: 0deg;
    inherits: false;
}

@keyframes girarBorde {
    to {
        --angulo-borde: 360deg;
    }
}

/* =========================================
   TARJETA PRINCIPAL TRANSPARENTE
========================================= */

.login-tarjeta {
    position: relative;
    width: 100%;
    max-width: 440px;
    padding: 36px 34px;
    border-radius: 26px;

    /* Transparencia media estilo cristal */
    background: rgba(252, 248, 242, 0.55);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);

    border: none;
    box-shadow: 0 20px 45px rgba(0, 0, 0, 0.28);
    color: var(--cafe-oscuro);
    text-align: center;

    animation: entradaSuave 0.8s ease both;
}

/* BORDE DORADO CON MOVIMIENTO */
.login-tarjeta::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 26px;
    padding: 2.5px;

    background: conic-gradient(
        from var(--angulo-borde),
        transparent 15%,
        #8c5a32 35%,
        #e6b968 50%,
        #fff5df 55%,
        #e6b968 60%,
        transparent 80%
    );

    -webkit-mask:
        linear-gradient(#fff 0 0) content-box,
        linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;

    pointer-events: none;
    animation: girarBorde 4.5s linear infinite;
}

/* =========================================
   ENCABEZADO Y MARCA BAKESTOCK
========================================= */

.encabezado-marca {
    margin-bottom: 24px;
}

.icono-pan {
    font-size: 26px;
    color: var(--cafe-medio);
    margin-bottom: 6px;
}

.nombre-marca {
    font-family: 'Fraunces', serif;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 3px;
    color: var(--cafe-oscuro);
    margin-bottom: 10px;
}

.nombre-marca span {
    font-family: 'Fraunces', serif;
    color: var(--dorado);
}

.titulo-bienvenido {
    font-family: 'Fraunces', serif;
    font-size: 38px;
    font-weight: 700;
    line-height: 1.05;
    color: var(--cafe-oscuro);
    margin-bottom: 4px;
}

.subtitulo-login {
    font-size: 13px;
    font-weight: 500;
    color: var(--cafe-texto);
}

/* =========================================
   FORMULARIO Y CAMPOS
========================================= */

form {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.grupo {
    text-align: left;
}

.grupo label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: var(--cafe-oscuro);
    margin-bottom: 6px;
}

.campo {
    position: relative;
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.78);
    border: 1px solid rgba(216, 200, 184, 0.9);
    border-radius: 12px;
    padding: 0 14px;
    transition: all 0.25s ease;
}

.campo:focus-within {
    background: #ffffff;
    border-color: var(--dorado);
    box-shadow: 0 0 0 3px rgba(200, 141, 75, 0.2);
}

.campo > i {
    color: #8c6d53;
    font-size: 14px;
    margin-right: 10px;
    pointer-events: none;
}

.campo input {
    width: 100%;
    border: none;
    background: transparent;
    padding: 12px 0;
    font-size: 14px;
    color: var(--cafe-oscuro);
    outline: none;
}

.campo input::placeholder {
    color: #8f7865;
}

/* Ocultar ojito automático del navegador */
input[type="password"]::-ms-reveal,
input[type="password"]::-ms-clear {
    display: none;
}

input[type="password"]::-webkit-credentials-auto-fill-button {
    visibility: hidden;
    display: none !important;
    pointer-events: none;
}

/* BOTÓN MOSTRAR CONTRASEÑA */
.btn-mostrar {
    background: none;
    border: none;
    color: #7a5c43;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    padding-left: 8px;
    transition: color 0.2s;
}

.btn-mostrar:hover {
    color: var(--cafe-oscuro);
}

/* MENSAJE DE ERROR PHP */
.error-msg {
    padding: 11px 14px;
    border-radius: 10px;
    background: rgba(255, 229, 219, 0.95);
    border: 1px solid rgba(200, 90, 50, 0.45);
    color: #a83d25;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

/* BOTÓN ENTRAR AL SISTEMA */
.btn-entrar {
    width: 100%;
    background: linear-gradient(135deg, #d69a56, #b87b39);
    color: #ffffff;
    border: none;
    border-radius: 25px;
    padding: 13px 20px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    margin-top: 6px;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    box-shadow: 0 6px 15px rgba(184, 123, 57, 0.35);
    transition: transform 0.18s ease, filter 0.18s ease, box-shadow 0.18s ease;
}

.btn-entrar:hover {
    filter: brightness(1.05);
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(184, 123, 57, 0.42);
}

.btn-entrar:active {
    transform: translateY(0);
}

/* =========================================
   SEPARADOR, FRASE Y BOTÓN MÚSICA
========================================= */

.separador {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 22px 0 18px;
}

.separador span {
    flex: 1;
    height: 1px;
    background: rgba(92, 58, 33, 0.3);
}

.separador i {
    color: var(--cafe-medio);
    font-size: 14px;
}

.pie-tarjeta {
    text-align: center;
}

.frase-titulo {
    font-family: 'Fraunces', serif;
    font-size: 23px;
    font-weight: 700;
    color: var(--cafe-oscuro);
    margin-bottom: 4px;
}

.frase-subtitulo {
    font-size: 12.5px;
    font-weight: 500;
    color: var(--cafe-texto);
    margin-bottom: 18px;
}

.btn-musica {
    background: rgba(255, 255, 255, 0.82);
    border: 1px solid #d8c8b8;
    border-radius: 22px;
    padding: 9px 22px;
    font-size: 13px;
    font-weight: 600;
    color: var(--cafe-oscuro);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-musica i {
    color: var(--dorado);
}

.btn-musica:hover {
    background: #ffffff;
    border-color: var(--dorado);
    transform: translateY(-1px);
}

/* DERECHOS DE AUTOR EXTERNOS */
.pie-copyright {
    margin-top: 18px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.9);
    text-align: center;
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.5);
}

/* RESPONSIVE */
@media (max-width: 480px) {
    .login-tarjeta {
        padding: 28px 22px;
        border-radius: 22px;
    }

    .login-tarjeta::before {
        border-radius: 22px;
    }

    .titulo-bienvenido {
        font-size: 32px;
    }

    .frase-titulo {
        font-size: 20px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation: none !important;
        transition: none !important;
    }
}

</style>
</head>

<body>

<main class="login-tarjeta">

<main class="login-tarjeta">

    <!-- ENCABEZADO: BAKESTOCK Y BIENVENIDO -->
    <header class="encabezado-marca">
        <i class="fa-solid fa-bread-slice icono-pan"></i>
        <h2 class="nombre-marca">BAKE<span>STOCK</span></h2>
        <h1 class="titulo-bienvenido">Bienvenido</h1>
        <p class="subtitulo-login">Inicia sesión en tu cuenta BakeStock</p>
    </header>

    <!-- FORMULARIO DE INICIO DE SESIÓN -->
    <form method="POST" action="" autocomplete="off">

        <div class="grupo">
            <label for="nombre">Usuario</label>
            <div class="campo">
                <i class="fa-solid fa-user"></i>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="Ingresa tu usuario"
                    autocomplete="username"
                    required
                >
            </div>
        </div>

        <div class="grupo">
            <label for="password">Contraseña</label>
            <div class="campo">
                <i class="fa-solid fa-lock"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Ingresa tu contraseña"
                    autocomplete="current-password"
                    required
                >
                <button
                    type="button"
                    class="btn-mostrar"
                    id="btnMostrar"
                    onclick="alternarPassword()"
                >
                    Mostrar
                </button>
            </div>
        </div>

        <?php if ($error !== ''): ?>
            <div class="error-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn-entrar">
            <i class="fa-solid fa-right-to-bracket"></i>
            Entrar al Sistema

        </button>

    </form>

    <div class="separador">
        <span></span>
        <i class="fa-solid fa-wheat-awn"></i>
        <span></span>
    </div>

    <footer class="pie-tarjeta">
        <h3 class="frase-titulo">El corazón de tu panadería</h3>
        <p class="frase-subtitulo">Organización, control y calidad en cada detalle.</p>

        <button
            type="button"
            class="btn-musica"
            id="btnMusica"
            onclick="alternarMusica()"
            aria-pressed="false"
        >
            <i class="fa-solid fa-music"></i>
            Activar Música
        </button>

        <audio id="musicaLogin" loop preload="none">
            <source src="musicaPan.mp3" type="audio/mpeg">
        </audio>
    </footer>

</main>

<p class="pie-copyright">
    © BakeStock | Todos los derechos reservados
</p>

<script>

/* MOSTRAR U OCULTAR CONTRASEÑA */
function alternarPassword() {
    const password = document.getElementById('password');
    const boton = document.getElementById('btnMostrar');

    if (password.type === 'password') {
        password.type = 'text';
        boton.textContent = 'Ocultar';
    } else {
        password.type = 'password';
        boton.textContent = 'Mostrar';
    }
}

/* CONTROL DE MÚSICA */
const musica = document.getElementById('musicaLogin');
const botonMusica = document.getElementById('btnMusica');

async function alternarMusica() {
    if (musica.paused) {
        try {
            await musica.play();
            botonMusica.innerHTML =
                '<i class="fa-solid fa-pause"></i> Pausar Música';
            botonMusica.setAttribute('aria-pressed', 'true');
        } catch (error) {
            botonMusica.innerHTML =
                '<i class="fa-solid fa-circle-exclamation"></i> No se pudo reproducir';
        }
    } else {
        musica.pause();
        botonMusica.innerHTML =
            '<i class="fa-solid fa-music"></i> Activar Música';
        botonMusica.setAttribute('aria-pressed', 'false');
    }
}

/* BLOQUEAR FLECHA ATRÁS EN LOGIN */
history.pushState(null, null, location.href);
window.onpopstate = function () {
    history.go(1);
};

</script>

</body>
</html>

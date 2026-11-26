<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal - BakeStock</title>
    <link href="https://googleapis.com" rel="stylesheet">
    <style>
        :root { --cafe-oscuro: #4A3525; --cafe-claro: #8C6239; --crema: #FDFBF7; --dorado: #D4A373; --beige: #F4EAE1; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Segoe UI', Arial, sans-serif; }
        body { background-color: var(--cafe-oscuro); color: white; min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 20px; position: relative; overflow: hidden; }
        .burbujas-fondo { position: fixed; inset: 0; width: 100vw; height: 100vh; z-index: 1; pointer-events: none; overflow: hidden; }
        .burbuja { position: absolute; border-radius: 50%; background: radial-gradient(circle at 30% 28%, rgba(255, 255, 255, 0.28) 0%, rgba(255, 244, 214, 0.08) 45%, rgba(255, 255, 255, 0.01) 75%, transparent 100%); border: 1.5px solid rgba(255, 245, 220, 0.36); box-shadow: inset 0 0 10px rgba(255, 255, 255, 0.18), 0 0 12px rgba(255, 232, 180, 0.12); animation: flotarTodaPagina linear infinite; }
        .burbuja::before { content: ""; position: absolute; top: 16%; left: 18%; width: 24%; height: 13%; border-radius: 50%; background: rgba(255, 255, 255, 0.42); transform: rotate(-28deg); }
        .burbuja:nth-child(1)  { width: 46px; height: 46px; left: 6%; top: 75%; animation-duration: 11.5s; animation-delay: 0s; }
        .burbuja:nth-child(2)  { width: 84px; height: 84px; left: 16%; top: 35%; animation-duration: 15s; animation-delay: -3.5s; }
        .burbuja:nth-child(3)  { width: 32px; height: 32px; left: 26%; top: 88%; animation-duration: 10.5s; animation-delay: -2s; }
        .burbuja:nth-child(4)  { width: 104px; height: 104px; left: 37%; top: 20%; animation-duration: 16.5s; animation-delay: -7s; }
        .burbuja:nth-child(5)  { width: 54px; height: 54px; left: 48%; top: 65%; animation-duration: 12.5s; animation-delay: -3s; }
        .burbuja:nth-child(6)  { width: 76px; height: 76px; left: 58%; top: 15%; animation-duration: 14s; animation-delay: -6s; }
        .burbuja:nth-child(7)  { width: 40px; height: 40px; left: 69%; top: 82%; animation-duration: 11s; animation-delay: -1.5s; }
        .burbuja:nth-child(8)  { width: 92px; height: 92px; left: 79%; top: 45%; animation-duration: 15.5s; animation-delay: -5s; }
        .burbuja:nth-child(9)  { width: 50px; height: 50px; left: 89%; top: 70%; animation-duration: 12s; animation-delay: -2.5s; }
        .burbuja:nth-child(10) { width: 64px; height: 64px; left: 11%; top: 12%; animation-duration: 13s; animation-delay: -8s; }
        .burbuja:nth-child(11) { width: 38px; height: 38px; left: 42%; top: 92%; animation-duration: 11.2s; animation-delay: -4s; }
        .burbuja:nth-child(12) { width: 68px; height: 68px; left: 64%; top: 52%; animation-duration: 13.5s; animation-delay: -6.5s; }
        .burbuja:nth-child(13) { width: 44px; height: 44px; left: 85%; top: 18%; animation-duration: 11.8s; animation-delay: -5.5s; }
        .burbuja:nth-child(14) { width: 58px; height: 58px; left: 22%; top: 60%; animation-duration: 12.8s; animation-delay: -7.5s; }
        @keyframes flotarTodaPagina {
            0% { transform: translateY(25vh) translateX(0) scale(0.94); opacity: 0; }
            15% { opacity: 0.9; }
            50% { transform: translateY(-45vh) translateX(22px) scale(1.03); }
            85% { opacity: 0.8; }
            100% { transform: translateY(-115vh) translateX(-18px) scale(1.07); opacity: 0; }
        }
        .top-bar { position: absolute; top: 25px; right: 30px; display: flex; align-items: center; gap: 15px; z-index: 10; }
        .usuario-tag { color: #E6CCB2; font-size: 0.95rem; font-weight: 500; background: rgba(36, 21, 15, 0.75); padding: 6px 14px; border-radius: 20px; border: 1px solid rgba(212, 163, 115, 0.3); }
        .btn-logout { background-color: #8B2E2E; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 0.85rem; transition: 0.3s; }
        .btn-logout:hover { background-color: #641E1E; transform: translateY(-1px); }
        .menu-container { position: relative; z-index: 10; text-align: center; max-width: 1050px; width: 100%; }
        .menu-container h1 { font-family: 'Playfair Display', serif; font-size: 3.5rem; color: var(--dorado); margin-bottom: 8px; letter-spacing: 2px; text-shadow: 0 2px 10px rgba(0,0,0,0.4); }
        .menu-container p { font-size: 1.15rem; color: #E6CCB2; margin-bottom: 45px; }
        .tarjetas-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 28px;
    max-width: 1050px;
    margin: 0 auto;
}
        .tarjeta-modulo { position: relative; isolation: isolate; background: linear-gradient(145deg, rgba(42, 26, 18, 0.92), rgba(30, 17, 12, 0.92)); border: 2px solid #8c6239; border-radius: 22px; padding: 45px 30px; width: 330px; text-decoration: none; color: white; display: flex; flex-direction: column; align-items: center; gap: 20px; backdrop-filter: blur(3px); transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); }
        .tarjeta-modulo::before { content: ""; position: absolute; inset: -2px; border-radius: inherit; padding: 2px; pointer-events: none; z-index: 20; background: conic-gradient(from var(--angulo-borde, 0deg), transparent 0deg, transparent 280deg, #d4a373 300deg, #fff4c2 315deg, #ffd45e 330deg, #d4a373 340deg, transparent 350deg, transparent 360deg); -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0); -webkit-mask-composite: xor; mask-composite: exclude; animation: giroBordeDorado 3s linear infinite; }
        @property --angulo-borde { syntax: "<angle>"; initial-value: 0deg; inherits: false; }
        @keyframes giroBordeDorado { to { --angulo-borde: 360deg; } }
        .tarjeta-modulo::after { content: ""; position: absolute; inset: -3px; border: 1px solid rgba(255, 212, 94, 0.35); border-radius: inherit; pointer-events: none; z-index: 19; animation: haloDoradoRapido 1.8s ease-in-out infinite alternate; }
        @keyframes haloDoradoRapido { from { box-shadow: 0 0 5px rgba(212, 163, 115, 0.2); } to { box-shadow: 0 0 15px rgba(255, 212, 94, 0.55), 0 0 25px rgba(212, 163, 115, 0.25); } }
        .tarjeta-modulo:hover { transform: translateY(-8px); }
        .tarjeta-icono-container { width: 85px; height: 85px; background: rgba(212, 163, 115, 0.1); border: 1px solid rgba(212, 163, 115, 0.3); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; transition: 0.3s ease; z-index: 25; }
        .tarjeta-modulo:hover .tarjeta-icono-container { background: rgba(212, 163, 115, 0.2); transform: scale(1.05); }
        .tarjeta-modulo h3 { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--dorado); z-index: 25; }
        .tarjeta-modulo span { font-size: 0.95rem; color: #D3C1AD; line-height: 1.5; min-height: 45px; z-index: 25; }
        .btn-card { margin-top: 10px; padding: 12px 24px; background: linear-gradient(to right, #D4A373, #C9A44C); color: var(--cafe-oscuro); font-weight: bold; font-size: 0.95rem; border-radius: 10px; transition: all 0.3s ease; width: 100%; box-shadow: 0 4px 10px rgba(0,0,0,0.2); z-index: 25; }
        .tarjeta-modulo:hover .btn-card { background: linear-gradient(to right, #FFE6BD, #D4A373); box-shadow: 0 6px 15px rgba(212, 163, 115, 0.4); transform: scale(1.02); }
    </style>
</head>
<body>
    <div class="burbujas-fondo">
        <div class="burbuja"></div><div class="burbuja"></div><div class="burbuja"></div><div class="burbuja"></div><div class="burbuja"></div><div class="burbuja"></div><div class="burbuja"></div><div class="burbuja"></div><div class="burbuja"></div><div class="burbuja"></div><div class="burbuja"></div><div class="burbuja"></div><div class="burbuja"></div><div class="burbuja"></div>
    </div>
    <div class="top-bar">
        <form action="inventario_panaderia.php" method="POST" style="margin:0;">
            <input type="hidden" name="tipo_auth" value="logout">
            <button type="submit" class="btn-logout">Cerrar Sesión</button>
        </form>
    </div>
    <div class="menu-container">
        <h1>BAKESTOCK</h1>
        <p>Selecciona el área que deseas administrar</p>
        <div class="tarjetas-grid">
            <a href="Utensilios.php" class="tarjeta-modulo">
                <div class="tarjeta-icono-container">🔪</div>
                <h3>Utensilios</h3>
                <span>Control de materia prima y stock disponible en la panadería.</span>
                <div class="btn-card">Entrar a Utensilios →</div>
            </a>
            <a href="inventario_panaderia.php?vista=panel" class="tarjeta-modulo">
                <div class="tarjeta-icono-container">⚙️</div>
                <h3>Maquinaria</h3>
                <span>Administración de hornos, batidoras, equipos y estado de mantenimiento.</span>
                <div class="btn-card">Entrar a Maquinaria →</div>
                <!-- TERCERA TARJETA: INSUMOS -->
                <a href="insumos.php" class="tarjeta-modulo">
        <div class="tarjeta-icono-container">🌾</div>
        <h3>Insumos</h3>
        <span>Control de materia prima, ingredientes y stock disponible en la panadería.</span>
        <div class="btn-card">Entrar a Insumos →</div>
    </a>
        </div>
    </div>
</body>
</html>

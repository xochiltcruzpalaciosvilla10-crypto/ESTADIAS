<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panadería - Inventario e Obrador</title>
    <style>
        .content-section { display: none; }
        .active-section { display: block; }
        .top-bar {
            background-color: #ffb74d; color: #3e2723; padding: 12px 20px;
            margin-bottom: 25px; border-radius: 6px; display: flex;
            justify-content: space-around; align-items: center; font-weight: 600;
        }
        .top-bar span { display: flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>

    <!-- Menú Lateral -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>🥖 Mi Panadería</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="#" class="menu-link active" onclick="showSection('inicio', this)">Inicio</a></li>
            
            <!-- EL BOTÓN CONECTADO -->
            <li>
                <a href="#" class="menu-link" onclick="showSection('pesado', this)">⚖️ Medición y Pesado</a>
            </li>
            
            <li><a href="#" class="menu-link" onclick="showSection('pan-dulce', this)">🍞 Pan Dulce</a></li>
        </ul>
    </aside>

    <!-- Contenido Principal Dinámico -->
    <main class="main-content">
        
        <!-- Sección 1: Inicio -->
        <section id="inicio" class="content-section active-section">
            <h1>Bienvenido al Inventario</h1>
            <p>Selecciona una opción del menú lateral para comenzar a trabajar.</p>
        </section>

        <!-- Sección 2: Medición y pesado -->
        <section id="pesado" class="content-section">
            <div class="top-bar" aria-label="Herramientas de medición">
                <span>⚖️ Balanza de precisión / Báscula digital</span>
                <span>🥄 Jarras y cucharas medidoras</span>
                <span>🌡️ Termómetro</span>
            </div>
            <h1>Medición y Pesado</h1>
            <p>Pesa y mide cada ingrediente antes de comenzar la preparación.</p>
        </section>


        <!-- Sección 3: Pan Dulce -->
        <section id="pan-dulce" class="content-section">
            <h1>Catálogo de Pan Dulce</h1>
            <p>Información sobre conchas, orejas, donas y tiempos de horneado.</p>
        </section>

    </main>

    <!-- JavaScript para cambiar de sección -->
    <script>
        function showSection(sectionId, element) {
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(sec => sec.classList.remove('active-section'));
            
            document.getElementById(sectionId).classList.add('active-section');
            
            const links = document.querySelectorAll('.menu-link');
            links.forEach(link => link.classList.remove('active'));
            
            element.classList.add('active');
        }
    </script>
</body>
</html>

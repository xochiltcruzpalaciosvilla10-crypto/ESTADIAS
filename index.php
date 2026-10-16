<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio</title>
    <style>
        :root {
            --pan-1: #f8e6c9;
            --pan-2: #f3b66d;
            --pan-3: #d77a2c;
            --pan-4: #7a3f1d;
            --pan-5: #3b1f12;
            --pan-6: #fffdf9;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #fff5ea 0%, #f5d2a8 35%, #d9802e 100%);
            color: var(--pan-5);
        }

        .welcome {
            width: min(900px, 90%);
            background: rgba(255, 253, 249, 0.75);
            border: 2px solid rgba(122, 63, 29, 0.15);
            border-radius: 28px;
            box-shadow: 0 20px 45px rgba(122, 63, 29, 0.18);
            overflow: hidden;
        }

        .content {
            padding: 70px 60px;
            text-align: center;
            background: linear-gradient(120deg, rgba(255,255,255,0.2), rgba(243,182,109,0.12));
        }

        .badge {
            display: inline-block;
            padding: 10px 18px;
            background: var(--pan-4);
            color: var(--pan-6);
            border-radius: 999px;
            font-size: 0.8rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .icono {
            width: 90px;
            height: 90px;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: linear-gradient(135deg, #f9d79f, #d77a2c);
            box-shadow: 0 10px 20px rgba(122, 63, 29, 0.2);
            font-size: 2.5rem;
        }

        h1 {
            margin: 0;
            font-size: clamp(3rem, 7vw, 6rem);
            line-height: 1;
            letter-spacing: 6px;
            color: var(--pan-4);
            text-shadow: 2px 2px 0 rgba(255, 255, 255, 0.8);
        }

        p {
            font-size: clamp(1.1rem, 2vw, 1.5rem);
            margin: 25px auto 35px;
            max-width: 620px;
            color: var(--pan-5);
            line-height: 1.6;
            font-weight: 700;
            font-family: "Cooper Black", Georgia, serif;
        }

        .siguiente {
            display: inline-block;
            border: none;
            border-radius: 12px;
            padding: 18px 42px;
            background: linear-gradient(135deg, var(--pan-3), var(--pan-4));
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-decoration: none;
            box-shadow: 0 8px 20px rgba(122, 63, 29, 0.25);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .siguiente:hover {
            transform: translateY(-4px);
            background: linear-gradient(135deg, #e48535, #693314);
            box-shadow: 0 14px 28px rgba(122, 63, 29, 0.35);
        }
        .siguiente:active {
            transform: translateY(-1px);
            box-shadow: 0 6px 12px rgba(122, 63, 29, 0.2);
        }

        @media (max-width: 600px) {
            .content {
                padding: 48px 28px;
            }

            h1 {
                letter-spacing: 3px;
            }
        }
    </style>
</head>
<body>
    <div class="welcome">
        <div class="content">
            <div class="icono-container" style="background: var(--pan-6); width: 85px; height: 85px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; box-shadow: 0 8px 20px rgba(122, 63, 29, 0.12); border: 2px solid rgba(122, 63, 29, 0.1);">
               <svg xmlns="http://w3.org" viewBox="0 0 24 24" fill="none" stroke="#7a3f1d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 42px; height: 42px;">
        <path d="M2 15V9a4 4 0 0 1 4-4h12a4 4 0 0 1 4 4v6a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4z" />
        <path d="M7 9c.5 1.5 1.5 2.5 3 2.5s2.5-1 3-2.5" />
        <path d="M14 9c.5 1.5 1.5 2.5 3 2.5s2.5-1 3-2.5" />
    </svg> 
            </div>
            <span class="badge">Inventario de Panadería</span>
            <h1>BAKESTOCK</h1>
            <p>"Inventario al día: el secreto de un buen pan y un negocio sano."</p>
            <a class="siguiente" href="registro_profesor.php">Siguiente</a>
        </div>
    </div>
</body>
</html>

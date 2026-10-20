<?php
session_start();

$error = '';

try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=profesor;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($usuario === '' || $password === '') {
            $error = 'Escribe tu usuario y contraseña.';
        } else {
            $consulta = $pdo->prepare(
                'SELECT id_profesor, usuario, contrasena
                 FROM datos
                 WHERE usuario = :usuario
                 LIMIT 1'
            );
            $consulta->execute([':usuario' => $usuario]);
            $profesor = $consulta->fetch();

            if ($profesor && password_verify($password, $profesor['contrasena'])) {
                session_regenerate_id(true);
                $_SESSION['profesor'] = [
                    'id' => $profesor['id_profesor'],
                    'usuario' => $profesor['usuario'],
                ];
                header('Location: clasificacion.php');
                exit;
            }

            $error = 'El usuario o la contraseña son incorrectos.';
        }
    }
} catch (PDOException $e) {
    error_log('Error de conexión o consulta en el login: ' . $e->getMessage());
    $error = 'No se pudo validar el acceso. Revisa la conexión con la base de datos.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <style>
        :root {
            --bg-1: #fff4e2;
            --bg-2: #f3d5a5;
            --panel: rgba(255, 253, 249, 0.98);
            --accent: #a94f25;
            --accent-light: #e5a343;
            --text: #4b2e1f;
            --muted: #795a43;
            --input-bg: #fff9f0;
            --line: rgba(169, 79, 37, 0.38);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background:
                radial-gradient(circle at 30% 20%, rgba(229, 163, 67, 0.28), transparent 28%),
                linear-gradient(135deg, #fff8ed 0%, #f8e4c4 52%, #e8bd7d 100%);
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
        }

        .login-wrap {
            width: min(1200px, 94%);
            min-height: 620px;
            background: var(--panel);
            border: 2px solid rgba(169, 79, 37, 0.55);
            border-radius: 18px;
            overflow: hidden;
            box-shadow:
                0 18px 45px rgba(91, 48, 24, 0.18),
                inset 0 0 18px rgba(229, 163, 67, 0.12);
            display: grid;
            grid-template-columns: 0.9fr 1.1fr;
        }

        .left {
            padding: 60px 56px 40px;
            background: linear-gradient(145deg, #fffdf9, #fff5e7);
            position: relative;
        }

        .left::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(229, 163, 67, 0.1), transparent 45%);
            pointer-events: none;
        }

        .title {
            font-size: 2.6rem;
            font-weight: 700;
            margin: 0 0 30px;
            position: relative;
            z-index: 1;
        }

        .campo {
            position: relative;
            z-index: 1;
            margin-bottom: 22px;
        }

        .campo label {
            display: block;
            margin-bottom: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--muted);
        }

        .input-box {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--input-bg);
            border: 1px solid var(--line);
            border-radius: 12px;
            height: 56px;
            padding: 0 16px;
            box-shadow: inset 0 1px 5px rgba(91, 48, 24, 0.06);
        }

        .icon {
            color: var(--accent);
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .icon svg {
            width: 20px;
            height: 20px;
            stroke: currentColor;
            fill: none;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .input-box input {
            width: 100%;
            background: transparent;
            border: none;
            outline: none;
            color: var(--text);
            font-size: 1rem;
        }

        .input-box input::placeholder {
            color: rgba(75, 46, 31, 0.48);
        }

        .btn {
            width: 100%;
            height: 54px;
            border: none;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--accent-light), var(--accent));
            color: #fffdf9;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            margin-top: 8px;
            box-shadow: 0 8px 18px rgba(169, 79, 37, 0.24);
        }

        .login-error {
            margin-top: 14px;
            padding: 10px 12px;
            border: 1px solid #d99a86;
            border-radius: 10px;
            background: #fff0eb;
            color: #8f2e1c;
            font-size: 0.9rem;
            text-align: center;
        }

        .login-text {
            margin-top: 26px;
            text-align: center;
            color: var(--muted);
            font-size: 1rem;
        }

        .login-text a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
        }

        .right {
            position: relative;
            background:
                radial-gradient(circle at 50% 20%, rgba(255, 255, 255, 0.72), transparent 42%),
                linear-gradient(135deg, #fff8ed, #f7dfbd 58%, #f0cf9e);
            padding: 64px 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .right::before {
            content: "";
            position: absolute;
            width: 520px;
            height: 520px;
            border: 3px solid rgba(169, 79, 37, 0.08);
            border-radius: 50%;
            right: -120px;
            top: -90px;
        }

        .right::after {
            content: "";
            position: absolute;
            width: 220px;
            height: 220px;
            border: 2px solid rgba(169, 79, 37, 0.08);
            border-radius: 50%;
            left: -50px;
            bottom: -60px;
        }

        .welcome {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 760px;
            text-align: center;
        }

        .welcome-icon {
            display: grid;
            width: 82px;
            height: 82px;
            place-items: center;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #fffdf9;
            color: var(--accent);
            box-shadow: 0 8px 24px rgba(91, 48, 24, 0.13);
        }

        .welcome-icon svg {
            width: 40px;
            height: 40px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.4;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .welcome-badge {
            display: inline-block;
            margin-bottom: 18px;
            padding: 9px 18px;
            border-radius: 999px;
            background: var(--accent);
            color: #fffdf9;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .welcome h2 {
            margin: 0;
            color: var(--accent);
            font-size: clamp(2rem, 6vw, 5rem);
            line-height: 1;
            letter-spacing: 0.02em;
            text-transform: uppercase;
            font-weight: 900;
            white-space: nowrap;
        }

        .welcome p {
            max-width: 640px;
            margin: 32px auto 0;
            color: #4b2e1f;
            font-family: Georgia, "Times New Roman", serif;
            font-size: clamp(1.1rem, 2.2vw, 1.5rem);
            font-weight: 700;
            line-height: 1.55;
        }

        @media (max-width: 900px) {
            .login-wrap {
                grid-template-columns: 1fr;
            }

            .right {
                min-height: 340px;
                padding: 40px 22px;
            }

            .left {
                padding: 40px 24px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="left">
            <h1 class="title">Login</h1>

            <form method="POST" action="index.php" autocomplete="off">
            <div class="campo">
                <label for="usuario">Nombre de usuario</label>
                <div class="input-box">
                    <span class="icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M20 21a8 8 0 0 0-16 0"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </span>
                    <input id="usuario" name="usuario" type="text" placeholder="Tu nombre de usuario" autocomplete="off" required />
                </div>
            </div>

            <div class="campo">
                <label for="password">Password</label>
                <div class="input-box">
                    <span class="icon">
                        <svg viewBox="0 0 24 24">
                            <rect x="5" y="11" width="14" height="10" rx="2"></rect>
                            <path d="M8 11V7a4 4 0 1 1 8 0v4"></path>
                        </svg>
                    </span>
                    <input id="password" name="password" type="password" placeholder="********" autocomplete="off" required />
                </div>
            </div>

            <button class="btn" type="submit">Login</button>

            <?php if ($error !== ''): ?>
                <div class="login-error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            </form>
            <div class="login-text">
                <a href="registro_profesor.php">Regístrate</a>
            </div>
        </div>

        <div class="right">
            <div class="welcome">
                <div class="welcome-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24">
                        <path d="M3.5 16V9.5A3.5 3.5 0 0 1 7 6h10a3.5 3.5 0 0 1 3.5 3.5V16a3.5 3.5 0 0 1-3.5 3.5H7A3.5 3.5 0 0 1 3.5 16Z"></path>
                        <path d="M7 6c.2 2.1 1.2 3.2 2.5 3.2S12 8.1 12 6"></path>
                        <path d="M12 6c.2 2.1 1.2 3.2 2.5 3.2S17 8.1 17 6"></path>
                    </svg>
                </div>
                <span class="welcome-badge">Inventario de panadería</span>
                <h2>BAKESTOCK</h2>
                <p>"Inventario al día: el secreto de un buen pan y un negocio sano."</p>
            </div>
        </div>
    </div>
    <script>
        const loginForm = document.querySelector(".left form");
        const clearLoginFields = () => loginForm.reset();

        window.addEventListener("load", () => {
            clearLoginFields();
            window.setTimeout(clearLoginFields, 100);
        });
        window.addEventListener("pageshow", () => {
            clearLoginFields();
            window.setTimeout(clearLoginFields, 100);
        });
    </script>
</body>
</html>
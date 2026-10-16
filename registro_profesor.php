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
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($nombre === '' || $apellido === '' || $telefono === '' || $password === '') {
            $error = 'Completa todos los campos para crear tu cuenta.';
        } else {
            $registro = $pdo->prepare(
                'INSERT INTO datos (nombre, apellido, telefono, contrasena)
                 VALUES (:nombre, :apellido, :telefono, :contrasena)'
            );
            $registro->execute([
                ':nombre' => $nombre,
                ':apellido' => $apellido,
                ':telefono' => $telefono,
                ':contrasena' => password_hash($password, PASSWORD_DEFAULT),
            ]);

            session_regenerate_id(true);
            $_SESSION['profesor'] = [
                'id' => $pdo->lastInsertId(),
                'nombre' => $nombre,
                'apellido' => $apellido,
                'telefono' => $telefono,
            ];
            header('Location: clasificacion.php');
            exit;
        }
    }
} catch (PDOException $e) {

}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 24px 12px;
            display: grid;
            place-items: center;
            font-family: Arial, Helvetica, sans-serif;
            color: #3F3129;
            background: linear-gradient(135deg, #F7F2E7, #EAD7B1 55%, #D8A74A);
        }

        main {
            width: min(440px, 92%);
            background: rgba(255, 255, 255, 0.85);
            border: 2px solid rgba(142, 92, 69, 0.15);
            border-radius: 36px;
            padding: 45px 35px;
            box-shadow: 0 20px 50px rgba(122, 63, 29, 0.16);
            backdrop-filter: blur(12px);
        }

        .aro {
            position: absolute;
            inset: 0;
            border: 8px solid rgba(142, 92, 69, 0.25);
            border-top-color: #8E5C45;
            border-right-color: #C98B4A;
            border-radius: 50%;
            animation: girar 18s linear infinite;
            pointer-events: none;
        }

        @keyframes girar {
            to {
                transform: rotate(360deg);
            }
        }

        section {
             width: 100%;
}
.input-group {
    display: flex;
    flex-direction: column;
    text-align: left;
    margin-bottom: 16px;
        }

        h1 {
            margin: 0;
            color: #8E5C45;
            font-size: clamp(1.5rem, 5vw, 2rem);
            text-align: center;
        }

        p {
            margin: 7px 0 16px;
            color: #6F5B4A;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        label {
            display: block;
            margin-bottom: 4px;
            font-size: 0.86rem;
            font-weight: 700;
        }

        input {
            width: 100%;
            padding: 13px 18px;
            border: 1.5px solid #e1cbb7;
            border-radius: 17px;
            outline: none;
            background: #fcf6ee;
            color: #3F3129;
            font-size: 0.95rem;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        input:focus {
            border-color: #d9802e;
            box-shadow: 0 0 0 4px rgba(217, 128, 46, 0.15);
        }

        button {
            width: 100%;
            margin-top: 10px;
            padding: 14px 16px;
            border: 0;
            border-radius: 50px;
            background: linear-gradient(135deg, #D8A74A, #8E5C45);
            color: white;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(142, 92, 69, 0.25);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        button:hover {
    transform: translateY(-3px);
    background: linear-gradient(135deg, #e4b455, #7a3e2b);
    box-shadow: 0 12px 26px rgba(142, 92, 69, 0.38);
}

button:active {
    transform: translateY(-1px);
    box-shadow: 0 6px 12px rgba(142, 92, 69, 0.2);
}

        .error {
            margin-top: 10px;
            padding: 9px 12px;
            border: 1px solid #e4aaa4;
            border-radius: 10px;
            background: rgba(253, 236, 235, 0.9);
            color: #9E3028;
            text-align: center;
            font-size: 0.88rem;
        }

        .enlace {
            display: block;
            margin-top: 10px;
            color: #8E5C45;
            text-align: center;
            font-size: 0.9rem;
            font-weight: 700;
        }

        .inicio {
            display: inline-block;
    margin-top: 20px;
    color: #8E5C45;
    text-align: center;
    font-size: 0.9rem;
    font-weight: 700;
    text-decoration: none;
    transition: color 0.2s ease;
        }

        .inicio:hover {
    color: #d9802e;
    text-decoration: underline;
}

        @media (max-width: 520px) {
            section {
                width: calc(100% - 76px);
            }

            form {
                gap: 6px;
            }

            label {
                font-size: 0.78rem;
            }

            input {
                padding: 7px 11px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .aro {
                animation: none;
            }
        }
    </style>
</head>
<body>
    <main>
        <section aria-labelledby="titulo">
            <h1 id="titulo">BAKESTOCK</h1>
            <p>Regístrate</p>
            <form method="POST">
<div class="input-group">
    <label for="nombre">Nombre</label>
    <input id="nombre" name="nombre" type="text" maxlength="50" autocomplete="given-name" required>
</div>
<div class="input-group">
    <label for="apellido">Apellido</label>
    <input id="apellido" name="apellido" type="text" maxlength="50" autocomplete="family-name" required>
</div>
<div class="input-group">
    <label for="telefono">Número de teléfono</label>
    <input id="telefono" name="telefono" type="tel" maxlength="20" autocomplete="tel" required>
</div>
<div class="input-group">
    <label for="password">Contraseña</label>
    <input id="password" name="password" type="password" autocomplete="new-password" required>
</div>
<button type="submit">Registrarse</button>
            </form>
            <?php if ($error !== ''): ?>
                <div class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            
        </section>
    </main>
     <a class="inicio" href="index.php">Volver al inicio</a>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - INFRAVENZ</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <h2>🧠 PlenaMente</h2>
        <p>Sistema Institucional de Atención Psicológica</p>

        <?php if (isset($error)): ?>
            <div class="error-message">
                ⚠️ <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($flash)): ?>
            <?php foreach ($flash as $tipo => $mensajes): ?>
                <?php foreach ($mensajes as $mensaje): ?>
                    <div style="padding: 12px; background-color: <?php echo $tipo == 'success' ? '#d4edda' : ($tipo == 'warning' ? '#fff3cd' : '#f8d7da'); ?>; color: <?php echo $tipo == 'success' ? '#155724' : ($tipo == 'warning' ? '#856404' : '#721c24'); ?>; border-radius: 4px; margin-bottom: 16px; font-weight: bold; text-align: left;">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/auth/procesar" method="POST">
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control" required placeholder="admin@infravenz.edu.sv">
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
            <p style="margin-top: 16px; font-size: 13px;">
                <a href="<?php echo BASE_URL; ?>/auth/recuperar" style="color: var(--violeta-principal); text-decoration: none;">¿Olvidaste tu contraseña?</a>
            </p>
        </form>
    </div>

</body>
</html>

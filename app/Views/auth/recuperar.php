<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - INFRAVENZ</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <h2>🔑 Recuperar Contraseña</h2>
        <p>Sistema Institucional de Atención Psicológica</p>

        <?php if (!empty($flash)): ?>
            <?php foreach ($flash as $tipo => $mensajes): ?>
                <?php foreach ($mensajes as $mensaje): ?>
                    <div style="padding: 12px; background-color: <?php echo $tipo == 'success' ? '#d4edda' : ($tipo == 'warning' ? '#fff3cd' : '#f8d7da'); ?>; color: <?php echo $tipo == 'success' ? '#155724' : ($tipo == 'warning' ? '#856404' : '#721c24'); ?>; border-radius: 4px; margin-bottom: 16px; font-weight: bold; text-align: left;">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/auth/recuperar" method="POST">
            <div class="form-group">
                <label for="correo">Correo Electrónico Registrado</label>
                <input type="email" id="correo" name="correo" class="form-control" required placeholder="tucorreo@infravenz.edu.sv">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Enviar Código</button>
        </form>

        <p style="margin-top: 16px; font-size: 13px;">
            <a href="<?php echo BASE_URL; ?>/auth/login" style="color: var(--violeta-principal); text-decoration: none;">⬅ Volver al inicio de sesión</a>
        </p>
    </div>

</body>
</html>

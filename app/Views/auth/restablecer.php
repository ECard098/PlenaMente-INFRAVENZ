<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - INFRAVENZ</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body class="login-page">

    <div class="login-card">
        <h2>🔒 Restablecer Contraseña</h2>
        <p>Ingresa el código que recibiste y define una nueva contraseña</p>

        <?php if (!empty($flash)): ?>
            <?php foreach ($flash as $tipo => $mensajes): ?>
                <?php foreach ($mensajes as $mensaje): ?>
                    <div style="padding: 12px; background-color: <?php echo $tipo == 'success' ? '#d4edda' : ($tipo == 'warning' ? '#fff3cd' : '#f8d7da'); ?>; color: <?php echo $tipo == 'success' ? '#155724' : ($tipo == 'warning' ? '#856404' : '#721c24'); ?>; border-radius: 4px; margin-bottom: 16px; font-weight: bold; text-align: left;">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/auth/restablecer" method="POST">
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" class="form-control" required autocomplete="email" placeholder="tucorreo@infravenz.edu.sv">
            </div>
            <div class="form-group">
                <label for="codigo">Código de Seguridad</label>
                <input type="text" id="codigo" name="codigo" class="form-control" required placeholder="Ej: 845213" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" title="Ingresa el código de 6 dígitos recibido por correo">
            </div>
            <div class="form-group">
                <label for="password">Nueva Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password" placeholder="Mínimo 8 caracteres, 1 mayúscula, 1 número y 1 símbolo">
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirmar Contraseña</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" required autocomplete="new-password" placeholder="Repite la nueva contraseña">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Restablecer Contraseña</button>
        </form>

        <p style="margin-top: 16px; font-size: 13px;">
            <a href="<?php echo BASE_URL; ?>/auth/recuperar" style="color: var(--violeta-principal); text-decoration: none;">¿No recibiste el código? Enviar de nuevo</a>
        </p>
        <p style="font-size: 13px;">
            <a href="<?php echo BASE_URL; ?>/auth/login" style="color: var(--violeta-principal); text-decoration: none;">⬅ Volver al inicio de sesión</a>
        </p>
    </div>

</body>
</html

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<h2 style="color: var(--violeta-principal); margin-bottom: 24px;">Dashboard Principal</h2>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Citas Hoy</div>
        <div class="stat-number">8</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--azul-claro), var(--azul-medio));">
        <div class="stat-label">Estudiantes Activos</div>
        <div class="stat-number">45</div>
    </div>
    <div class="stat-card" style="background: linear-gradient(135deg, var(--verde-menta), var(--verde-menta-claro));">
        <div class="stat-label">Consultas Mes</div>
        <div class="stat-number">127</div>
    </div>
</div>

<div class="card">
    <div class="card-header">Próximas Citas</div>
    <button class="btn btn-primary" style="margin-bottom: 16px;">+ Nueva Cita</button>
    
    <table class="table">
        <thead>
            <tr>
                <th>Hora</th>
                <th>Estudiante</th>
                <th>Motivo</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>09:00 AM</td>
                <td>Juan Pérez</td>
                <td>Seguimiento</td>
                <td><span class="badge badge-programada">Programada</span></td>
                <td>
                    <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Ver</button>
                </td>
            </tr>
            <tr>
                <td>10:30 AM</td>
                <td>Ana Martínez</td>
                <td>Primera consulta</td>
                <td><span class="badge badge-programada">Programada</span></td>
                <td>
                    <button class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Ver</button>
                </td>
            </tr>
            <tr>
                <td>02:00 PM</td>
                <td>Carlos López</td>
                <td>Ansiedad académica</td>
                <td><span class="badge badge-completada">Completada</span></td>
                <td>
                    <button class="btn btn-success" style="padding: 6px 12px; font-size: 12px;">Expediente</button>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
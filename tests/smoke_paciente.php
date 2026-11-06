<?php
/**
 * Prueba de humo (smoke test) - Historia HU-09: Actualizar informacion del paciente
 *
 * Ejecuta validaciones automaticas sobre los modelos, las reglas de validacion del
 * controlador y la logica de disponibilidad de citas, usando la BD local.
 *
 * Uso:  C:\xampp\php\php.exe tests\smoke_paciente.php
 *
 * IMPORTANTE: crea y destruye datos con prefijo SMOKE-TEST-*. Nunca ejecutar contra
 * la base de datos de produccion.
 */

define('BASE_URL', 'http://localhost/plenamente');

// Raiz del proyecto (para que el script funcione desde cualquier directorio)
chdir(__DIR__ . '/..');

session_id('smokeplena');
session_start();
$_SESSION['usuario_id'] = 1;
$_SESSION['id_rol'] = 1;

require_once 'app/Core/Logger.php';
require_once 'app/Core/Flash.php';
require_once 'app/Core/Controller.php';
require_once 'app/Config/Database.php';
require_once 'app/Models/Paciente.php';
require_once 'app/Models/Cita.php';
require_once 'app/Models/Auditoria.php';
require_once 'app/Controllers/PacienteController.php';

use App\Config\Database;
use App\Controllers\PacienteController;
use App\Models\Cita;
use App\Models\Paciente;

$ok = 0;
$fail = 0;

function check($nombre, $condicion, $detalle = '') {
    global $ok, $fail;
    if ($condicion) {
        $ok++;
        echo "  [OK]    $nombre\n";
    } else {
        $fail++;
        echo "  [FALLA] $nombre" . ($detalle !== '' ? " -> $detalle" : '') . "\n";
    }
}

$db = (new Database())->getConnection();
$pacienteModel = new Paciente();
$citaModel = new Cita();
$controller = new PacienteController();

// ---------- Limpieza previa ----------
$db->exec("DELETE FROM citas WHERE id_paciente IN (SELECT id_paciente FROM pacientes WHERE nie_dui = 'SMOKE-TEST-001')");
$db->exec("DELETE FROM pacientes WHERE nie_dui = 'SMOKE-TEST-001'");
$db->exec("DELETE FROM auditoria_acciones WHERE descripcion LIKE '%SMOKE-TEST-001%'");

echo "\n=== 1. Modelo: unicidad de NIE/DUI (nieDuiexiste) ===\n";
$pacienteModel->crearPaciente(
    'SMOKE-TEST-001', 'Prueba', 'Smoke', '2000-05-10', 'Femenino', 'Estudiante',
    '3° A', '7000-0000', 'smoke@test.local', 'Tutor Prueba'
);
$idTmp = (int)$db->query("SELECT id_paciente FROM pacientes WHERE nie_dui = 'SMOKE-TEST-001'")->fetchColumn();
check('Paciente de prueba creado', $idTmp > 0, "id=$idTmp");
check('nieDuiexiste detecta el NIE propio', $pacienteModel->nieDuiexiste('SMOKE-TEST-001') === true);
check('nieDuiexiste ignora al excluir el propio id', $pacienteModel->nieDuiexiste('SMOKE-TEST-001', $idTmp) === false);
check('nieDuiexiste detecta NIE inexistente', $pacienteModel->nieDuiexiste('NO-EXISTE-999') === false);

$nieReal = (string)$db->query("SELECT nie_dui FROM pacientes WHERE nie_dui <> 'SMOKE-TEST-001' LIMIT 1")->fetchColumn();
check('nieDuiexiste detecta NIE de otro paciente', $pacienteModel->nieDuiexiste($nieReal) === true);

echo "\n=== 2. Modelo: actualizar() persiste los cambios ===\n";
$pacienteModel->actualizar(
    $idTmp, 'SMOKE-TEST-001', 'Ana', 'Prueba Editada', '1999-03-22', 'Femenino', 'Empleado',
    null, '7555-1111', 'ana.editada@test.local', 'Responsable Nuevo'
);
$act = $pacienteModel->obtenerPorId($idTmp);
check('nombres actualizados', $act['nombres'] === 'Ana', $act['nombres']);
check('apellidos actualizados', $act['apellidos'] === 'Prueba Editada', $act['apellidos']);
check('fecha de nacimiento actualizada', $act['fecha_nacimiento'] === '1999-03-22', $act['fecha_nacimiento']);
check('tipo actualizado a Empleado', $act['tipo_paciente'] === 'Empleado', $act['tipo_paciente']);
check('grado/seccion en NULL para Empleado', $act['grado_seccion'] === null, var_export($act['grado_seccion'], true));
check('telefono actualizado', $act['telefono_contacto'] === '7555-1111', $act['telefono_contacto']);
check('correo actualizado', $act['correo_paciente'] === 'ana.editada@test.local', $act['correo_paciente']);
check('responsable actualizado', $act['nombre_responsable'] === 'Responsable Nuevo', $act['nombre_responsable']);
check('estado_paciente conservado (Activo)', $act['estado_paciente'] === 'Activo', $act['estado_paciente']);

echo "\n=== 3. Controlador: validacion de datos (validarDatosPaciente) ===\n";
$ref = new ReflectionMethod('App\Controllers\PacienteController', 'validarDatosPaciente');
$ref->setAccessible(true);

$base = [
    'id_paciente'       => $idTmp,
    'nie_dui'           => 'SMOKE-TEST-001',
    'nombres'           => 'Ana',
    'apellidos'         => 'Prueba Editada',
    'fecha_nacimiento'  => '1999-03-22',
    'genero'            => 'Femenino',
    'tipo_paciente'     => 'Estudiante',
    'telefono_contacto' => '7555-1111',
    'correo_paciente'   => 'ana.editada@test.local',
    'nieDuiexiste'      => false
];

$errores = $ref->invoke($controller, $base);
check('datos validos -> 0 errores', count($errores) === 0, implode(' | ', $errores));

$casosRechazo = [
    'NIE/DUI vacio'           => ['nie_dui' => ''],
    'NIE/DUI duplicado'       => ['nieDuiexiste' => true],
    'NIE/DUI > 20 chars'      => ['nie_dui' => str_repeat('9', 21)],
    'nombres vacios'          => ['nombres' => ''],
    'nombres > 100 chars'     => ['nombres' => str_repeat('a', 101)],
    'apellidos vacios'        => ['apellidos' => ''],
    'fecha vacia'             => ['fecha_nacimiento' => ''],
    'fecha con formato malo'  => ['fecha_nacimiento' => '22/03/1999'],
    'fecha inexistente'       => ['fecha_nacimiento' => '2024-02-31'],
    'fecha futura'            => ['fecha_nacimiento' => date('Y-m-d', strtotime('+1 year'))],
    'fecha muy antigua'       => ['fecha_nacimiento' => '1890-01-01'],
    'genero no valido'        => ['genero' => 'X'],
    'tipo no valido'          => ['tipo_paciente' => 'Externo'],
    'correo no valido'        => ['correo_paciente' => 'correo@@roto'],
    'telefono no valido'      => ['telefono_contacto' => 'abc!!']
];
foreach ($casosRechazo as $nombre => $override) {
    $err = $ref->invoke($controller, array_merge($base, $override));
    check("rechaza: $nombre", count($err) > 0, 'no genero error');
}

$casosAceptados = [
    'telefono con + y guiones'  => ['telefono_contacto' => '+503 7777-8888'],
    'correo valido'             => ['correo_paciente' => 'nombre.apellido@correo.com'],
    'telefono vacio (opcional)' => ['telefono_contacto' => ''],
    'correo vacio (opcional)'   => ['correo_paciente' => '']
];
foreach ($casosAceptados as $nombre => $override) {
    $err = $ref->invoke($controller, array_merge($base, $override));
    check("acepta: $nombre", count($err) === 0, implode(' | ', $err));
}

echo "\n=== 4. Controlador: deteccion de campos modificados ===\n";
$refCambios = new ReflectionMethod('App\Controllers\PacienteController', 'detectarCambios');
$refCambios->setAccessible(true);
$cambios = $refCambios->invoke($controller, $act, [
    'nie_dui' => 'SMOKE-TEST-001', 'nombres' => 'Ana Maria', 'apellidos' => 'Prueba Editada',
    'fecha_nacimiento' => '1999-03-22', 'genero' => 'Femenino', 'tipo_paciente' => 'Empleado',
    'grado_seccion' => null, 'telefono_contacto' => '7555-1111',
    'correo_paciente' => 'ana.editada@test.local', 'nombre_responsable' => 'Responsable Nuevo'
]);
check('detecta solo "nombres" como cambio', $cambios === ['nombres'], implode(', ', $cambios));
check('sin cambios -> lista vacia', $refCambios->invoke($controller, $act, $act) === []);

echo "\n=== 5. Disponibilidad de citas (hayConflictoHorario) ===\n";
$idPsicologo = (int)$db->query("SELECT id_usuario FROM usuarios WHERE id_rol = 1 LIMIT 1")->fetchColumn();
check('psicologo disponible para la prueba', $idPsicologo > 0, "id=$idPsicologo");

$baseCita = '2027-03-15 09:00:00';
$stmt = $db->prepare("INSERT INTO citas (id_paciente, id_usuario_psicologo, fecha_hora, motivo_cita, estado_cita)
                       VALUES (:p, :u, :f, 'Cita de prueba', 'Programada')");
$stmt->execute([':p' => $idTmp, ':u' => $idPsicologo, ':f' => $baseCita]);
$idCita = (int)$db->query("SELECT id_cita FROM citas WHERE id_paciente = $idTmp LIMIT 1")->fetchColumn();

check('conflicto: misma hora exacta', $citaModel->hayConflictoHorario($baseCita, $idPsicologo) !== false);
check('conflicto: 30 minutos despues', $citaModel->hayConflictoHorario('2027-03-15 09:30:00', $idPsicologo) !== false);
check('conflicto: 30 minutos antes', $citaModel->hayConflictoHorario('2027-03-15 08:30:00', $idPsicologo) !== false);
check('sin conflicto: 90 minutos despues', $citaModel->hayConflictoHorario('2027-03-15 10:30:00', $idPsicologo) === false);
check('sin conflicto: otro psicologo', $citaModel->hayConflictoHorario($baseCita, $idPsicologo + 999) === false);
check('sin conflicto: excluyendo la propia cita', $citaModel->hayConflictoHorario($baseCita, $idPsicologo, $idCita) === false);

$db->exec("UPDATE citas SET estado_cita = 'Cancelada' WHERE id_cita = $idCita");
check('cita Cancelada no genera conflicto', $citaModel->hayConflictoHorario($baseCita, $idPsicologo) === false);
$db->exec("UPDATE citas SET estado_cita = 'Programada' WHERE id_cita = $idCita");

echo "\n=== 6. Limpieza de datos de prueba ===\n";
$db->exec("DELETE FROM citas WHERE id_paciente = $idTmp");
$db->exec("DELETE FROM pacientes WHERE id_paciente = $idTmp");
$db->exec("DELETE FROM auditoria_acciones WHERE descripcion LIKE '%SMOKE-TEST-001%'");
check('paciente de prueba eliminado', (int)$db->query("SELECT COUNT(*) FROM pacientes WHERE nie_dui = 'SMOKE-TEST-001'")->fetchColumn() === 0);
check('citas de prueba eliminadas', (int)$db->query("SELECT COUNT(*) FROM citas WHERE id_paciente = $idTmp")->fetchColumn() === 0);

echo "\n----------------------------------------\n";
echo "RESULTADO: $ok correctas, $fail fallidas\n";
exit($fail > 0 ? 1 : 0);

# Plan de Pruebas — PlenaMente-INFRAVENZ

**Sistema Institucional de Atención y Seguimiento Psicológico — INFRAVENZ**
Versión del documento: 1.0 · Última actualización: 2026-11-05

---

## 1. Objetivo

Garantizar que cada historia de usuario entregada cumple sus criterios de aceptación, que la **Definition of Done (DoD)** se cumple de forma verificable y que no se introducen regresiones en los módulos ya liberados.

Objetivos específicos:

1. Derivar de cada historia de usuario los casos de prueba que cubren sus criterios de aceptación.
2. Verificar que el código entregado cumple la DoD antes de considerarse terminado.
3. Documentar la evidencia de ejecución (casos ejecutados, defectos detectados, resultado).
4. Proteger la **integridad de la información clínica** (pacientes, expedientes, citas, reportes), que es el activo de mayor riesgo del sistema.

---

## 2. Alcance

### 2.1 Módulos cubiertos

| Módulo | Historias asociadas | Riesgo |
|---|---|---|
| Directorio de pacientes | Crear, Actualizar, Eliminar (desactivar) | **Alto** — datos maestro de todo el sistema |
| Agenda de citas | Crear, Modificar/Reprogramar, Cancelar, Paginación, Filtro por rango | **Alto** — agenda clínica y solapamientos |
| Recordatorios de citas | Envío de recordatorio por correo | Medio — dependencia de SMTP |
| Expedientes clínicos | Apertura, edición de antecedentes, sesiones, estado del caso | **Alto** — información sensible |
| Reportes técnicos | Generación, parametrización, exportación | Medio |
| Autenticación y roles | Login, recuperación de contraseña, RBAC | **Alto** — control de acceso |
| Auditoría | Registro de acciones sensibles | Medio |

### 2.2 Fuera de alcance

- Pruebas de carga/estrés y pruebas de penetración formally certificate (manuales).
- Pruebas de usabilidad con usuarios finales (se gestionan aparte, con el área solicitante).
- Compatibilidad con navegadores no soportados (el sistema es de uso interno en navegador de escritorio).

---

## 3. Marco de referencia

### 3.1 Roles de acceso (RBAC)

| `id_rol` | Rol | Permisos esperados en esta versión |
|---|---|---|
| 1 | **Psicólogo** | Gestión completa de pacientes, citas, expedientes, sesiones, reportes y recordatorios |
| 2 | **Director** | Dashboard, reportes y consulta; **sin** escritura clínica |
| 3 | **Administrador (IT)** | Usuarios y respaldos; **sin** acceso a datos clínicos de escritura |

> Todo módulo clínico (pacientes, citas, expedientes) debe proteger sus rutas de escritura con `$_SESSION['id_rol'] !== 1`.

### 3.2 Entorno

| Elemento | Definición |
|---|---|
| Stack | PHP >= 8.0 (nativo, MVC), MariaDB, HTML5/CSS3/JS |
| Servidor local | XAMPP (`C:\xampp\php\php.exe`), BD `plenamente_bd` en `127.0.0.1` |
| Servidor destino | Hostinger — `https://plenamente.dsoftin.net` (`public_html`) |
| Base de datos | Script maestro: `plenamente_bd.sql` |
| Logs | `logs/app-AAAA-MM-DD.log` (nivel mínimo: `error`) |
| Validación estática | `php -l <archivo>` sobre cada archivo PHP modificado |

### 3.3 Datos de prueba

| ID | Datos | Uso |
|---|---|---|
| `DT-01` | Paciente Estudiante, NIE `TEST-0001`, activo, con expediente y 2 citas | Paciente "poblado" (dependencias críticas) |
| `DT-02` | Paciente Estudiante, NIE `TEST-0002`, activo, sin dependencias | Paciente "limpio" |
| `DT-03` | Paciente Empleado, DUI `TEST-0003`, activo, `grado_seccion = NULL` | Validar reglas por tipo |
| `DT-04` | Paciente con NIE duplicado (copia de `DT-01`) | Caso negativo de unicidad |
| `DT-05` | Paciente inactivo (`estado_paciente = 'Inactivo'`) | Soft delete / reactivación |
| `DT-06` | Cita `Programada` del psicólogo A a las 09:00 | Pruebas de disponibilidad |
| `DT-07` | Cita `Cancelada` del psicólogo A a las 09:00 | No debe generar conflicto |

> Los datos `DT-*` deben crearse y eliminarse al terminar cada ciclo; nunca deben contener información clínica real de estudiantes.

---

## 4. Definition of Done (DoD)

Una historia **no se considera terminada** hasta que los tres bloques siguientes son verificables por el equipo de pruebas.

### 4.1 DoD — Funcional (criterios de aceptación)

| # | Criterio | Verificación |
|---|---|---|
| D1 | Todos los criterios de aceptación de la historia se cumplen | Casos de prueba asociados en estado *Pasado* |
| D2 | Los mensajes de éxito y de error se muestran al usuario | Evidencia en captura / caso de prueba |
| D3 | Las validaciones de entrada bloquean el guardado cuando el dato es inválido | Caso negativo aprobado |
| D4 | Los datos persistidos coinciden exactamente con los enviados (verificado en BD, no solo en pantalla) | Consulta directa a la tabla |
| D5 | La operación no altera datos ajenos a la historia (no hay efectos colaterales) | Comparación de registros antes/después |

### 4.2 DoD — Técnico

| # | Criterio | Verificación |
|---|---|---|
| D6 | `php -l` sin errores en **todos** los archivos PHP creados o modificados | Salida del linter |
| D7 | Todas las consultas SQL usan consultas preparadas (PDO) | Revisión de código |
| D8 | La vista escapa toda entrada del usuario (`htmlspecialchars`) | Revisión de código + CP de XSS |
| D9 | Las rutas respetan el RBAC (rol 1 en escritura clínica) | Casos de seguridad |
| D10 | Las operaciones sensibles quedan registradas en `auditoria_acciones` y en `logs/app-*.log` | Consulta a BD y lectura del log |
| D11 | El esquema SQL actualizado está en `plenamente_bd.sql` **y** se entrega el `ALTER`/`CREATE` equivalente para la BD existente | Revisión del `.sql` |
| D12 | No hay errores PHP (warning/notice/fatal) en la consola del navegador ni en el log | Revisión del log |

### 4.3 DoD — Pruebas

| # | Criterio | Verificación |
|---|---|---|
| D13 | Todos los casos de prueba de la historia están redactados y trazados a un criterio de aceptación | Matriz de trazabilidad |
| D14 | Casos críticos (prioridad *Alta*) ejecutados y aprobados | Registro de ejecución |
| D15 | Sin defectos abiertos de severidad *Crítica* o *Alta* | Registro de defectos |
| D16 | Pruebas de regresión de los módulos afectados superadas | Suite de regresión |
| D17 | La documentación de usuario / README está actualizada si cambió el flujo | Revisión del README |

---

## 5. Estrategia de pruebas

| Tipo | Alcance | Herramienta / técnica | Responsable |
|---|---|---|---|
| **Validación estática** | Sintaxis de todos los archivos PHP | `php -l` | Desarrollador |
| **Prueba unitaria (smoke)** | Modelos y reglas de validación | Script PHP CLI contra BD local (ver §9) | Desarrollador / QA |
| **Prueba funcional** | Flujos de usuario completos por rol | Manual, siguiendo los casos de §7 | QA |
| **Prueba de integración** | Controlador → Modelo → BD; correo (`mail()` → SMTP de Hostinger) | Manual | QA |
| **Prueba de validación** | Reglas de negocio (disponibilidad, unicidad, obligatoriedad) | Manual + smoke | QA |
| **Prueba de seguridad** | RBAC, sesión, inyección SQL, XSS | Manual | QA |
| **Prueba de regresión** | Módulos que comparten datos con el cambiado | Manual (Suite D) | QA |
| **Prueba de despliegue** | Script SQL en Hostinger + carga de archivos + verificación en producción | Manual | QA + Admin |

### 5.1 Niveles de prioridad de los casos

| Prioridad | Definición |
|---|---|
| **Alta** | Cubre un criterio de aceptación o un riesgo de integridad de datos. Su fallo bloquea la entrega. |
| **Media** | Cubre reglas de validación secundarias o variantes de la UI. |
| **Baja** | Verificación cosmética o de robustez no bloqueante. |

### 5.2 Severidad de defectos

| Severidad | Definición | Ejemplo |
|---|---|---|
| **Crítica** | Pérdida/corrupción de datos clínicos o brecha de seguridad total | Se puede eliminar el expediente de un paciente |
| **Alta** | Función principal inutilizable o dato guardado incorrectamente | El paciente se guarda con el teléfono de otro |
| **Media** | Funcionalidad con，力争 alterno o sin mensaje de error | No se muestra el mensaje de confirmación |
| **Baja** | Detalle visual o de redacción | Botón desalineado |

---

## 6. Ciclo de pruebas

### 6.1 Criterios de entrada

1. Historia de usuario con criterios de aceptación redactados.
2. Código integrado en la rama de trabajo y `php -l` sin errores.
3. Script SQL (`plenamente_bd.sql` + `ALTER` para BD existente) disponible.
4. Datos de prueba `DT-*` cargados.

### 6.2 Criterios de salida

1. 100 % de los casos de prioridad **Alta** ejecutados y aprobados.
2. ≥ 90 % de los casos de prioridad **Media** ejecutados y aprobados.
3. Cero defectos abiertos de severidad **Crítica** y **Alta**.
4. Matriz de trazabilidad completa (Historia → DoD → Caso → Resultado).
5. Pruebas de despliegue en Hostinger ejecutadas y registradas.

### 6.3 Registro de ejecución (plantilla)

| Caso | Fecha | Ejecutor | Resultado | Evidencia | Defecto |
|---|---|---|---|---|---|
| CP-A-01 | 2026-11-05 | QA | Pass / Fail / N/A | captura o log | ID |

---

## 7. Casos de prueba

### 7.1 Plantilla de caso de prueba

```
ID:            CP-<SUITE>-<NN>
Historia:      HU-<NN> — <nombre>
Prioridad:     Alta | Media | Baja
Tipo:          Funcional | Validación | Seguridad | Regresión
Precondición:  <estado del sistema y datos>
Datos:         <valores de entrada>
Pasos:         1. ... 2. ... 3. ...
Esperado:      <resultado observable en pantalla y/o en BD>
DoD cubierto:  D1, D3, D4, ...
```

---

### 7.2 Suite A — Actualizar información del paciente (HU-09)

> **Historia:** *Como psicólogo, necesito actualizar la información del paciente para mantener los datos correctos de acuerdo al tiempo.*
> 1. El sistema debe permitir modificar cualquier dato del paciente.
> 2. Debe validar los datos ingresados antes de guardar el registro.
> 3. Debe mostrar un mensaje de confirmación de la actualización.
>
> **Ruta:** `/paciente/editar/{id}` (GET) y `/paciente/actualizar/{id}` (POST)

#### A.1 Casos felices

| ID | Prioridad | Precondición | Datos | Pasos | Esperado | DoD |
|---|---|---|---|---|---|---|
| CP-A-01 | Alta | Sesión rol 1; existe `DT-01` | Todos los campos con valores válidos distintos a los actuales | 1. Ir al directorio de pacientes. 2. Pulsar **✏️ Editar** en `DT-01`. 3. Cambiar nombres, apellidos, fecha de nacimiento, género, tipo, grado, teléfono, correo y responsable. 4. Pulsar **💾 Guardar Cambios**. | Redirige al directorio; banner verde **"Los datos del paciente … se actualizaron correctamente."**; en BD los 10 campos quedan con los valores enviados; `estado_paciente` sin cambios | D1, D2, D4, D5 |
| CP-A-02 | Alta | `DT-02` | Solo se cambia el teléfono | Editar → cambiar únicamente `telefono_contacto` → Guardar | Se actualiza **solo** el teléfono; el resto de campos mantiene su valor original (comparación en BD) | D5 |
| CP-A-03 | Alta | `DT-02` | NIE/DUI sin modificar | Editar → guardar sin tocar el NIE/DUI | Se guarda correctamente (el NIE propio **no** se considera duplicado) | D1, D4 |
| CP-A-04 | Media | `DT-02` | Campos opcionales vacíos | Borrar teléfono, correo y responsable → Guardar | Se guardan como `NULL` en BD, sin error | D4 |
| CP-A-05 | Media | `DT-03` (Empleado) | Cambiar tipo a `Empleado` dejando grado/sección | Editar → seleccionar *Empleado* → Guardar | `grado_seccion` se guarda en `NULL` y el campo se deshabilita en el formulario | D4 |
| CP-A-06 | Alta | `DT-05` (inactivo) | Cualquier dato | Editar el paciente inactivo (visible con *Mostrar inactivos*) → Guardar | Se actualiza correctamente; el paciente **sigue inactivo** | D5 |
| CP-A-07 | Media | `DT-01` | Sin cambios reales | Editar → guardar sin modificar nada | Sin error; se registra `ACTUALIZAR` con campos modificados = *ninguno* | D10 |

#### A.2 Casos de validación (criterio 2)

| ID | Prioridad | Datos | Pasos | Esperado | DoD |
|---|---|---|---|---|---|
| CP-A-08 | Alta | NIE/DUI vacío | Editar → vaciar NIE/DUI → Guardar | No guarda. Mensaje rojo **"El NIE/DUI es obligatorio."**; BD sin cambios | D3, D15 |
| CP-A-09 | Alta | NIE/DUI con el valor de otro paciente | Editar → poner el NIE/DUI de `DT-01` en `DT-02` → Guardar | No guarda. Mensaje **"El NIE/DUI ya está registrado en otro paciente."** (no se produce error SQL 1062) | D3, D15 |
| CP-A-10 | Media | NIE/DUI de 21 caracteres | Guardar | No guarda. Mensaje **"El NIE/DUI no puede superar los 20 caracteres."** | D3 |
| CP-A-11 | Alta | Nombres vacíos | Guardar | No guarda. Mensaje **"Los nombres son obligatorios."** | D3 |
| CP-A-12 | Media | Nombres de 101 caracteres | Guardar | No guarda. Mensaje **"Los nombres no pueden superar los 100 caracteres."** | D3 |
| CP-A-13 | Alta | Apellidos vacíos | Guardar | No guarda. Mensaje **"Los apellidos son obligatorios."** | D3 |
| CP-A-14 | Alta | Fecha de nacimiento vacía | Guardar | No guarda. Mensaje **"La fecha de nacimiento es obligatoria."** | D3 |
| CP-A-15 | Alta | Fecha con formato `22/03/1999` | Guardar | No guarda. Mensaje **"La fecha de nacimiento no tiene un formato válido."** | D3 |
| CP-A-16 | Alta | Fecha inexistente `2024-02-31` | Guardar | No guarda. Mensaje **"La fecha de nacimiento no es una fecha válida."** | D3 |
| CP-A-17 | Alta | Fecha futura (año vigente + 1) | Guardar | No guarda. Mensaje **"La fecha de nacimiento no puede ser una fecha futura."** | D3 |
| CP-A-18 | Baja | Fecha `1890-01-01` | Guardar | No guarda. Mensaje **"La fecha de nacimiento es demasiado antigua."** | D3 |
| CP-A-19 | Alta | Género fuera de catálogo (manipulado por POST) | Guardar | No guarda. Mensaje **"Selecciona un género válido."** | D3 |
| CP-A-20 | Alta | Tipo de paciente fuera de catálogo | Guardar | No guarda. Mensaje **"Selecciona un tipo de paciente válido."** | D3 |
| CP-A-21 | Alta | Correo `correo@@roto` | Guardar | No guarda. Mensaje **"El correo electrónico no tiene un formato válido."** | D3 |
| CP-A-22 | Media | Teléfono `abc!!` | Guardar | No guarda. Mensaje **"El teléfono de contacto no tiene un formato válido."** | D3 |
| CP-A-23 | Media | Varios errores simultáneos | Guardar con 3 campos inválidos | No guarda. Se muestran **todos** los errores en un mismo mensaje | D2, D3 |

#### A.3 Mensaje de confirmación, auditoría y trazabilidad (criterio 3)

| ID | Prioridad | Pasos | Esperado | DoD |
|---|---|---|---|---|
| CP-A-24 | Alta | Ejecutar CP-A-01 | Banner **verde** de confirmación visible en el directorio; el flash se consume una sola vez (no reaparece al recargar de nuevo) | D2 |
| CP-A-25 | Alta | Tras CP-A-01, consultar `auditoria_acciones` | Existe un registro `accion = 'ACTUALIZAR'`, `entidad = 'paciente'`, `id_entidad` = id del paciente, con la descripción de los campos modificados y `id_usuario` del psicólogo | D10 |
| CP-A-26 | Media | Tras CP-A-01, revisar `logs/app-AAAA-MM-DD.log` | Entrada `INFO` con `id_paciente`, `usuario` y campos modificados | D10, D12 |
| CP-A-27 | Alta | Tras un rechazo (CP-A-08) | **No** se crea registro en `auditoria_acciones` ni entrada `INFO` de actualización | D5, D10 |

#### A.4 Seguridad y robustez

| ID | Prioridad | Pasos | Esperado | DoD |
|---|---|---|---|---|
| CP-A-28 | Alta | Cerrar sesión e ir a `/paciente/editar/1` | Redirige a `/auth/login`; no se muestra información del paciente | D9 |
| CP-A-29 | Alta | Iniciar sesión como Director (rol 2) o Admin (rol 3) e ir a `/paciente/editar/1` y a `/paciente/actualizar/1` | Redirige a `/dashboard`; **no** se modifica ningún registro | D9, D15 |
| CP-A-30 | Alta | `/paciente/actualizar/999999` (id inexistente) | Mensaje **"Paciente no encontrado."** y redirección al directorio | D9 |
| CP-A-31 | Alta | Nombre con `<script>alert(1)</script>` | Se guarda el texto literal y se **muestra escapado** en el directorio y en el formulario (no se ejecuta) | D8 |
| CP-A-32 | Alta | NIE/DUI con `' OR '1'='1` | Se trata como texto; no se listan registros ajenos ni se produce error 500 | D7, D12 |
| CP-A-33 | Media | Enviar el formulario por GET o sin sesión iniciada | Se rechaza y redirige al login | D9 |

#### A.5 Regresión

| ID | Prioridad | Pasos | Esperado | DoD |
|---|---|---|---|---|
| CP-A-34 | Alta | Tras actualizar el nombre de `DT-01`, abrir su expediente, su cita y un reporte | Todos muestran el **nuevo** nombre (los datos se leen de `pacientes`, no están duplicados) | D5, D17 |
| CP-A-35 | Media | Tras actualizar `DT-01`, listar pacientes y usar el buscador por NIE/DUI | Aparece con los datos nuevos y el buscador lo encuentra por el NIE nuevo | D5 |
| CP-A-36 | Media | Verificar que el paciente sigue con su `estado_paciente` original y que la función de eliminar/reactivar no se rompió | Comportamiento idéntico al previo | D5, D16 |

---

### 7.3 Suite B — Modificar / reprogramar cita (HU-08)

> **Historia:** *Como psicólogo, necesito modificar cita para adaptarme a cambios.*
> 1. Cambiar fecha y hora. 2. Validar disponibilidad antes de confirmar. 3. Actualizar la cita.
> **Ruta:** `/cita/editar/{id}` (GET/POST)

| ID | Prioridad | Precondición / Datos | Pasos | Esperado | DoD |
|---|---|---|---|---|---|
| CP-B-01 | Alta | `DT-06` | Editar → cambiar fecha y hora a un horario libre → Guardar | Cita actualizada en BD (`fecha_hora`), banner verde de confirmación, redirección al detalle | D1, D2, D4 |
| CP-B-02 | Alta | `DT-06` | Editar → **misma** fecha y hora → Guardar | Sin cambios en BD; mensaje informativo; no se ejecuta un UPDATE efectivo | D5 |
| CP-B-03 | Alta | `DT-06` | Editar → mover la cita a la misma hora de **otra** cita `Programada` del mismo psicólogo | No guarda; mensaje rojo indicando el horario ocupado y el paciente con el que choca | D3 |
| CP-B-04 | Alta | `DT-06` | Editar → mover a +30 min respecto a otra cita del mismo psicólogo | No guarda (solapamiento dentro de la franja de 1 h) | D3 |
| CP-B-05 | Media | `DT-06` | Editar → mover a +90 min | Guarda (no hay solapamiento; el límite es 60 min) | D1 |
| CP-B-06 | Media | `DT-06` | Editar → mover a la hora de una cita de **otro** psicólogo | Guarda (el conflicto se evalúa solo por psicólogo) | D1 |
| CP-B-07 | Alta | `DT-07` (cita `Cancelada` a las 09:00) | Editar → mover a las 09:00 | Guarda (las citas canceladas no bloquean la agenda) | D1 |
| CP-B-08 | Media | `DT-06` | Editar → hora vacía o formato inválido | No guarda; mensaje **"Debes indicar una fecha y una hora válidas."** | D3 |
| CP-B-09 | Alta | Cita `Completada` | Intentar abrir `/cita/editar/{id}` | Redirige al detalle con aviso **"Solo se pueden reprogramar citas en estado Programada."** | D1, D9 |
| CP-B-10 | Alta | `DT-06` | Editar → guardar → consultar `logs/app-*.log` | Entrada `INFO` con `antes` y `despues` de `fecha_hora` | D10 |
| CP-B-11 | Media | `DT-06` | Ver el detalle y el listado | El botón **✏️ Reprogramar / Editar** solo aparece en citas `Programada` | D1 |
| CP-B-12 | Alta | Sesión rol 2/3 | `/cita/editar/{id}` | Redirige a `/dashboard` | D9 |

---

### 7.4 Suite C — Eliminar (desactivar) paciente + auditoría (HU-07)

| ID | Prioridad | Precondición / Datos | Pasos | Esperado | DoD |
|---|---|---|---|---|---|
| CP-C-01 | Alta | `DT-01` (con dependencias) | Eliminar → escribir un NIE/DUI **distinto** → Confirmar | No se desactiva; mensaje **"La confirmación no coincide…"** | D3 |
| CP-C-02 | Alta | `DT-01` | Eliminar → escribir el NIE/DUI exacto pero **sin** marcar la casilla | No se desactiva; mensaje exigiendo marcar la casilla | D3 |
| CP-C-03 | Alta | `DT-01` | Eliminar → escribir NIE/DUI exacto → marcar casilla → Confirmar | `estado_paciente = 'Inactivo'`; el paciente desaparece del listado activo; **no** se borran citas, expediente ni sesiones | D1, D5 |
| CP-C-04 | Alta | `DT-02` (sin dependencias) | Eliminar → Confirmar (sin reinforced validation) | Se desactiva correctamente | D1 |
| CP-C-05 | Alta | `DT-05` | Ver listado con *Mostrar inactivos* → Reactivar | Vuelve a `Activo` y reaparece en el listado activo | D1 |
| CP-C-06 | Alta | Tras CP-C-03 | Consultar `auditoria_acciones` | Registros `DESACTIVAR` y `REACTIVAR` con `id_usuario`, descripción y campos asociados | D10 |
| CP-C-07 | Alta | Rol 2/3 | `/paciente/eliminar/{id}` | Redirige a `/dashboard` | D9 |

---

### 7.5 Suite D — Matriz de cobertura de historias anteriores (regresión)

> Casos resumidos; se ejecutan en cada ciclo de entrega para detectar regresiones.

| ID | Historia / módulo | Verificación mínima | Prioridad |
|---|---|---|---|
| CP-D-01 | Crear paciente | Registro con todos los campos obligatorios; los opcionales en `NULL`; aparece en el listado | Alta |
| CP-D-02 | Crear paciente | NIE/DUI duplicado → error de BD controlado (no pantalla en blanco) | Alta |
| CP-D-03 | Crear cita | Se guarda como `Programada` y aparece en la agenda del día | Alta |
| CP-D-04 | Crear cita | Choque de horario (mismo psychologist, < 60 min) → mensaje de no disponibilidad | Alta |
| CP-D-05 | Cancelar cita | Pasa a `Cancelada`, guarda motivo, fecha y usuario; no puede cancelarse dos veces | Alta |
| CP-D-06 | Cancelar cita | Motivo < 5 caracteres → rechazado | Media |
| CP-D-07 | Paginación de citas | "Mostrando X–Y de N (página P de T)"; los enlaces respetan `vista`, `desde`, `hasta` | Media |
| CP-D-08 | Filtro por rango de fechas | Filtra correctamente; `desde > hasta` invierte el rango; fechas inválidas se ignoran | Media |
| CP-D-09 | Recordatorio | Envío de recordatorio: se registra `Enviado`/`Fallido` en `recordatorios_citas` y en el log | Media |
| CP-D-10 | Estado del caso | Cambio de estado en expediente, badge en listado y detalle, entrada en historial | Media |
| CP-D-11 | Historial de cambios | Editar antecedentes/sesiones genera entrada en `historial_expedientes`/`historial_sesiones` | Media |
| CP-D-12 | Autenticación | Login válido/inválido; ruta protegida redirige a login; cierre de sesión | Alta |
| CP-D-13 | Recuperación de contraseña | Solicitud genera token y correo; token caducado o inválido no permite restablecer | Media |
| CP-D-14 | Reportes | Filtros aplicados; exportación reflecte los datos vigentes (incluye datos actualizados de pacientes) | Media |
| CP-D-15 | Asistencias | Registro por expediente y fecha; duplicado bloqueado por índice único | Media |

---

### 7.6 Suite E — Seguridad y control de acceso (transversal)

| ID | Prioridad | Verificación | Resultado esperado |
|---|---|---|---|
| CP-E-01 | Alta | Cada ruta de escritura clínica (`paciente`, `cita`, `expediente`) probada con rol 2 y rol 3 | Redirección a `/dashboard` en todas |
| CP-E-02 | Alta | Acceso sin sesión a rutas de datos | Redirección a `/auth/login` |
| CP-E-03 | Alta | Manipulación de `id` en la URL (`/paciente/actualizar/0`, id inexistente) | Mensaje de no encontrado; sin error 500 |
| CP-E-04 | Alta | Intento deDal bypass de rol vía POST directo | Rechazado por el constructor del controlador |
| CP-E-05 | Alta | XSS en los campos de texto de todos los formularios | Salida escapada, sin ejecución de script |
| CP-E-06 | Alta | SQL injection en los campos de búsqueda y de los formularios | Consultas preparadas; sin error de sintaxis SQL ni fuga de datos |
| CP-E-07 | Media | Intento de respuesta a `success` forjado en la URL | No se muestra un mensaje falso (el flash solo proviene de la sesión) |

---

### 7.7 Suite F — Despliegue en Hostinger

| ID | Prioridad | Verificación | Resultado esperado |
|---|---|---|---|
| CP-F-01 | Alta | Ejecutar los `ALTER`/`CREATE TABLE` pendientes sobre la BD existente | Sin errores; los datos previos se conservan |
| CP-F-02 | Alta | Subir los archivos modificados a `public_html` | Archivos en la ruta correcta; la aplicación responde 200 |
| CP-F-03 | Alta | Recorrer las Suite A, B y C en producción | Todos los casos *Alta* en estado *Pasado* |
| CP-F-04 | Media | Envío de recordatorio en producción | Correo recibido; si no llega, revisar `logs/app-*.log` |
| CP-F-05 | Media | Revisar `logs/app-*.log` en producción tras la prueba | Sin errores PHP nuevos |

---

## 8. Matriz de trazabilidad

| Historia | Criterios de aceptación | DoD clave | Casos de prueba | Estado |
|---|---|---|---|---|
| HU-07 Eliminar paciente | Soft delete · validación reforzada · auditoría | D1, D3, D4, D9, D10 | CP-C-01…CP-C-07 | En ejecución |
| HU-08 Modificar cita | Cambiar fecha/hora · validar disponibilidad · actualizar | D1, D3, D4, D9, D10 | CP-B-01…CP-B-12 | En ejecución |
| HU-09 Actualizar paciente | Modificar cualquier dato · validar antes de guardar · mensaje de confirmación | D1, D2, D3, D4, D9, D10 | CP-A-01…CP-A-36 | En ejecución |
| HU-01 Crear paciente | Registro con validaciones | D1, D3, D4 | CP-D-01, CP-D-02 | Pendiente |
| HU-02 Crear cita | Agenda + disponibilidad | D1, D3 | CP-D-03, CP-D-04 | Pendiente |
| HU-03 Cancelar cita | Motivo obligatorio + estado | D1, D3 | CP-D-05, CP-D-06 | Pendiente |
| HU-04 Paginación y filtro | Paginación + rango de fechas | D1 | CP-D-07, CP-D-08 | Pendiente |
| HU-05 Recordatorios | Envío y registro | D1, D10 | CP-D-09 | Pendiente |
| HU-06 Estado del caso | Cambio de estado | D1, D4 | CP-D-10 | Pendiente |
| HU-10 Autenticación/RBAC | Acceso por roles | D9 | CP-D-12, CP-D-13, Suite E | Pendiente |

---

## 9. Pruebas automatizadas de humo (smoke test)

Además de las pruebas manuales, el repositorio incluye un arnés de humo en CLI que valida modelos y reglas de validación contra la BD local:

```bash
C:\xampp\php\php.exe tests\smoke_paciente.php
```

Cobertura del arnés:

| Bloque | Qué valida |
|---|---|
| 1. Unicidad de NIE/DUI | `nieDuiexiste()` detecta duplicados y excluye al propio registro |
| 2. `actualizar()` | Persistencia de los 10 campos y conservación de `estado_paciente` |
| 3. Reglas de validación | 15 rechazos (vacíos, formatos, longitudes, fechas) y 4 aceptaciones válidas |
| 4. Detección de cambios | Lista correcta de campos modificados |
| 5. Disponibilidad de citas | Conflicto exacto, ±30 min, ±90 min, otro psicólogo, cita cancelada |
| 6. Limpieza | Eliminación de los datos de prueba creados |

**Regla:** el arnés crea y destruye datos `SMOKE-TEST-*`; nunca debe ejecutarse contra la BD de producción.

---

## 10. Riesgos y mitigaciones

| Riesgo | Impacto | Mitigación |
|---|---|---|
| Envío de correo no probado localmente (`mail()` sin SMTP en XAMPP) | Falsa impresión de que el recordatorio funciona | Prueba obligatoria en Hostinger (CP-F-04) + revisión de `logs/app-*.log` |
| Divergencia entre `plenamente_bd.sql` y la BD de Hostinger | Errores fatales al desplegar | Checklist de `ALTER` pendientes antes de cada despliegue (CP-F-01) |
| Datos clínicos reales mezclados con datos de prueba | Contaminación de la BD | Uso exclusivo de identificadores `TEST-*` / `SMOKE-TEST-*` y limpieza al cierre |
| Modificación de datos maestros sin trazabilidad | Imposible reconstruir el historial | Registro obligatorio en `auditoria_acciones` (D10) |
| Regresión al actualizar un paciente (expediente, reportes) | Inconsistencia entre módulos | Suite D y casos CP-A-34…CP-A-36 |

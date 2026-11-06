# PlenaMente - INFRAVENZ 🧠📚

**Sistema Institucional de Atención y Seguimiento Psicológico** Desarrollado para el Instituto Nacional Profesor Francisco Ventura Zelaya (INFRAVENZ).

---

## 📖 Descripción del Proyecto
**PlenaMente-INFRAVENZ** es una aplicación web diseñada para centralizar, estructurar y automatizar la gestión clínica y administrativa del departamento de psicología de INFRAVENZ. 

El sistema mitiga la carga operativa crítica que representa manejar físicamente los expedientes de más de 700 usuarios, eliminando el riesgo de pérdida de información sensible, optimizando el agendamiento de citas mediante alertas, y permitiendo a la administración generar reportes técnicos de forma inmediata para la atención oportuna de casos críticos.

## 🚀 Características Principales
* **Gestión de Expedientes Clínicos Digitales:** Creación y consulta ágil del historial de estudiantes y empleados.
* **Control de Agenda:** Calendario interactivo para evitar cruces de citas.
* **Sistema de Alertas Automatizado:** Envío de recordatorios vía correo electrónico a pacientes y notificaciones internas al psicólogo.
* **Roles de Acceso (RBAC):** * *Operativo (Psicólogo):* Gestión completa de pacientes y expedientes.
  * *Directivo (Administración):* Visualización de dashboards y reportes.
  * *Administrador (IT):* Mantenimiento de usuarios y respaldos.
* **Reportes Parametrizables:** Generación de métricas e historiales exportables.

## 🛠️ Stack Tecnológico y Arquitectura
El sistema está construido bajo el paradigma de **Programación Orientada a Objetos (POO)** utilizando el patrón de diseño **MVC (Modelo-Vista-Controlador)**.

* **Backend:** PHP >= 8.0 (Nativo)
* **Base de Datos:** MariaDB (SQL)
* **Frontend:** HTML5, CSS3, JavaScript (Diseño Propio UI/UX)
* **Despliegue Objetivo:** Cloud Hosting (Hostinger)

## 📂 Estructura del Proyecto (MVC)
```text
plenamente/
├── app/                    # Lógica central del patrón MVC
│   ├── Config/             # Configuración del sistema y conexión segura (PDO) a la base de datos
│   ├── Core/               # Núcleo del mini-framework (Enrutador principal y clase Controller base)
│   ├── Controllers/        # Controladores (Manejan el flujo de la app y la seguridad por roles - RBAC)
│   │   ├── AsistenciaController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── ReportesController.php
│   │   └── ...
│   ├── Models/             # Modelos (Consultas preparadas de MariaDB para evitar inyección SQL)
│   │   ├── Asistencia.php
│   │   ├── Dashboard.php
│   │   ├── Reporte.php
│   │   ├── Usuario.php
│   │   └── ...
│   └── Views/              # Interfaces gráficas (HTML nativo integrado con PHP)
│       ├── asistencia/     # Módulo de bitácora de asistencias y verificación
│       ├── auth/           # Pantallas de autenticación y login
│       ├── dashboard/      # Panel de control principal con métricas (KPIs) dinámicas
│       ├── layouts/        # Componentes reutilizables (header.php, sidebar, footer.php)
│       ├── reportes/       # Generación de informes técnicos optimizados para impresión
│       └── ...
├── public/                 # Directorio público (Raíz segura expuesta al servidor web)
│   ├── css/                # Hojas de estilo puras utilizando variables CSS nativas
│   ├── img/                # Logotipos y recursos gráficos institucionales
│   ├── js/                 # Scripts interactivos del lado del cliente
│   └── index.php           # Front Controller (Punto único de entrada de toda la aplicación)
└── plenamente_bd.sql       # Script de creación y estructura de la base de datos (MariaDB)

## 🛠️ Standares de codificacion

* **PascalCase** para Clases y Archivos base (ej. AsistenciaController, Dashboard.php).
* **camelCase** para Métodos y Funciones (ej. obtenerAsistenciasPorFecha(), registrarAsistencia()).
* **snake_case** para Variables, Arrays y columnas de Base de Datos (ej. id_expediente, fecha_registro).

---

## 🧪 Pruebas

| Recurso | Ubicación | Descripción |
|---|---|---|
| **Plan de pruebas** | [`docs/PLAN_PRUEBAS.md`](docs/PLAN_PRUEBAS.md) | DoD, estrategia, casos de prueba (Suites A–F) y matriz de trazabilidad Historia → Caso |
| **Smoke test** | [`tests/smoke_paciente.php`](tests/smoke_paciente.php) | Validación automática de modelos y reglas de negocio contra la BD local |

```bash
# Validación de sintaxis de un archivo
C:\xampp\php\php.exe -l app\Models\Paciente.php

# Ejecución del arnés de humo (crea y destruye datos SMOKE-TEST-*)
C:\xampp\php\php.exe tests\smoke_paciente.php
```

> El smoke test **nunca** debe ejecutarse contra la base de datos de producción.


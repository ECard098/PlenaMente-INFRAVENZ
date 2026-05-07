# PlenaMente - INFRAVENZ 🧠📚

**Sistema Institucional de Atención y Seguimiento Psicológico** Desarrollado para el Instituto Nacional Profesor Francisco Ventura Zelaya (INFRAVENZ).

---

## 📖 Descripción del Proyecto
**PlenaMente** es una aplicación web diseñada para centralizar, estructurar y automatizar la gestión clínica y administrativa del departamento de psicología de INFRAVENZ. 

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
├── app/                # Lógica del MVC
│   ├── Config/         # Configuración y conexión PDO a BD
│   ├── Controllers/    # Controladores de la aplicación
│   ├── Models/         # Interacción con MariaDB
│   └── Views/          # Interfaces gráficas y plantillas
├── public/             # Raíz web (CSS, JS, Imágenes y Front Controller index.php)
└── bd_plenamente.sql   # Script de la base de datos

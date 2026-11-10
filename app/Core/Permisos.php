<?php
namespace App\Core;

/**
 * Permisos: fuente unica de verdad de la matriz de acceso por rol.
 * Los identificadores de rol son los de la tabla 'roles':
 * 1 = Psicologo, 2 = Director, 3 = Administrador
 * La matriz define que modulo puede abrir cada rol. Los controladores la
 * consumen mediante Controller::requiere() para no repetir condiciones
 * hardcodeadas en cada archivo.
 */
class Permisos {
    // Claves de rol (alias legibles para evitar numeros magicos)
    const ROL_PSICOLOGO = 1;
    const ROL_DIRECTOR = 2;
    const ROL_ADMINISTRADOR = 3;

    /**
     * Matriz de permisos: modulo => array de roles con acceso.
     * Los sub-permisos (modulo.accion) permiten diferenciar lectura de escritura.
     */
    public static function matriz() {
        return [
            'dashboard'             => [self::ROL_PSICOLOGO, self::ROL_DIRECTOR, self::ROL_ADMINISTRADOR],
            'pacientes'             => [self::ROL_PSICOLOGO],
            'pacientes.eliminar'    => [self::ROL_PSICOLOGO],
            'citas'                 => [self::ROL_PSICOLOGO],
            'recordatorios'         => [self::ROL_PSICOLOGO],
            'expedientes'           => [self::ROL_PSICOLOGO],
            'asistencias'           => [self::ROL_PSICOLOGO, self::ROL_DIRECTOR],
            'asistencias.registrar' => [self::ROL_PSICOLOGO],
            'reportes'              => [self::ROL_PSICOLOGO, self::ROL_DIRECTOR],
            'reportes.crear'        => [self::ROL_PSICOLOGO],
            'metricas'              => [self::ROL_DIRECTOR, self::ROL_ADMINISTRADOR],
            'usuarios'              => [self::ROL_ADMINISTRADOR],
            'roles'                 => [self::ROL_ADMINISTRADOR],
            'backups'               => [self::ROL_ADMINISTRADOR],
            'auditoria'             => [self::ROL_ADMINISTRADOR],
        ];
    }

    /**
     * Etiquetas legibles de cada modulo (para la pantalla de matriz de permisos).
     */
    public static function modulos() {
        return [
            'dashboard'             => 'Dashboard',
            'pacientes'             => 'Directorio de Pacientes',
            'pacientes.eliminar'    => 'Eliminar pacientes',
            'citas'                 => 'Agenda de Citas',
            'recordatorios'         => 'Recordatorios de Citas',
            'expedientes'           => 'Expedientes Clinicos',
            'asistencias'           => 'Registro de Asistencias',
            'asistencias.registrar' => 'Registrar asistencias',
            'reportes'              => 'Informes y Reportes',
            'reportes.crear'        => 'Generar informes',
            'metricas'              => 'Panel de Metricas',
            'usuarios'              => 'Gestion de Usuarios',
            'roles'                 => 'Roles y Permisos',
            'backups'               => 'Respaldos del Sistema',
            'auditoria'             => 'Registro de Auditoria',
        ];
    }

    /**
     * Descripcion de lo que puede hacer cada rol (pantalla de Roles y Permisos).
     */
    public static function descripcionRoles() {
        return [
            self::ROL_PSICOLOGO     => 'Gestion clinica completa: pacientes, agenda de citas, expedientes, sesiones, asistencias, recordatorios e informes.',
            self::ROL_DIRECTOR      => 'Vision administrativa: dashboard, informes, reportes y registro de asistencias. Sin acceso a la informacion clinica de fichas ni a la agenda.',
            self::ROL_ADMINISTRADOR => 'Mantenimiento del sistema: alta de usuarios, asignacion de roles y consulta de la matriz de permisos. Sin acceso a los datos clinicos.',
        ];
    }

    /**
     * Rol del usuario en sesion (0 si no hay sesion iniciada).
     */
    public static function rolActual() {
        return (int)($_SESSION['id_rol'] ?? 0);
    }

    /**
     * Identificadores de los roles validos (los registrados en la tabla 'roles').
     */
    public static function rolesValidos() {
        return array_keys(self::descripcionRoles());
    }

    /**
     * ¿El usuario en sesion tiene acceso al modulo indicado?
     */
    public static function puede($modulo) {
        $rol = self::rolActual();
        if ($rol === 0) {
            return false;
        }
        $matriz = self::matriz();
        if (!isset($matriz[$modulo])) {
            // Modulo desconocido: por seguridad se deniega
            return false;
        }
        return in_array($rol, $matriz[$modulo], true);
    }

    /**
     * ¿El rol indicado (no el de la sesion) tiene acceso al modulo?
     */
    public static function rolTiene($id_rol, $modulo) {
        $matriz = self::matriz();
        if (!isset($matriz[$modulo])) {
            return false;
        }
        return in_array((int)$id_rol, $matriz[$modulo], true);
    }

    /**
     * Modulos a los que tiene acceso un rol concreto.
     */
    public static function modulosDeRol($id_rol) {
        $modulos = [];
        foreach (self::matriz() as $modulo => $roles) {
            if (in_array((int)$id_rol, $roles, true)) {
                $modulos[] = $modulo;
            }
        }
        return $modulos;
    }
}
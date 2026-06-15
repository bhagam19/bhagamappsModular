<?php

namespace App\Auth;

/**
 * Roles institucionales fijos de APPSisGOE.
 *
 * Define los 7 roles de la IEE colombiana. Son inmutables: no se crean
 * ni eliminan roles fuera de este enum. SincronizarRolesYPermisosCoreAction
 * usa este enum para sembrar los roles en Spatie (CORE-002).
 *
 * Conforme a ADR-003 §5 y ARCH-001 §6.
 * Los valores son el guard_name = 'web' slug de cada rol en Spatie.
 */
enum RolInstitucional: string
{
    case Administrador = 'administrador';
    case Rector        = 'rector';
    case Coordinador   = 'coordinador';
    case Auxiliar      = 'auxiliar';
    case Docente       = 'docente';
    case Estudiante    = 'estudiante';
    case Invitado      = 'invitado';

    /**
     * Roles con capacidad de Aprobador en el Dominio Inventario.
     * DOM-INV-001 §3: Administrador y Rectoría aprueban HMB/HEB y modifican directamente.
     *
     * @return list<self>
     */
    public static function aprobadores(): array
    {
        return [self::Administrador, self::Rector];
    }

    /**
     * Roles de acceso básico que solo proponen cambios (no aprueban).
     * DOM-INV-001 §3: Auxiliar y Coordinador proponen, no ejecutan.
     *
     * @return list<self>
     */
    public static function basicos(): array
    {
        return [self::Coordinador, self::Auxiliar, self::Docente];
    }
}

<?php

namespace app\components;

use app\models\InversionPago;
use app\models\Proyecto;

/**
 * Inversiones: pagos de los inversores en los proyectos (varios por inversor, cada uno en su moneda).
 * Superadmin y distribuidor: todos los proyectos. Gerente: los proyectos de sus áreas.
 */
interface InterfaceInversiones
{
    /** @return array ['searchModel', 'dataProvider', 'totales' (por moneda, vigentes con el filtro), 'proyectos', 'monedas'] */
    public function listar($params);

    public function buscar($id, $permiso = 'inversiones_listar'): InversionPago;

    /** Pago nuevo con proyecto/inversor ya elegidos si vienen. */
    public function nuevo($idproyecto = null, $idinversor = null): InversionPago;

    /** @return array ['exito', 'mensaje', 'id'] */
    public function registrar(InversionPago $pago, array $post);

    /** Anula con motivo (queda tachado y deja de sumar). */
    public function anular($id, $motivo);

    /** Sube o reemplaza el comprobante (campo "comprobante"). */
    public function subirComprobante($id);

    /** @return array ['ruta', 'nombre', 'mime'] */
    public function archivoComprobante($id): array;

    /** Proyectos donde se puede registrar un pago (publicados / inversión completa, de su alcance): id => texto. */
    public function proyectosParaPagar(): array;

    /** Datos de cada proyecto para el formulario: id => ['moneda' => id, 'codigo' => 'USD', 'area' => id]. */
    public function datosProyectos(): array;

    /** Inversores aprobados y activos: id => "Nombre (áreas)". */
    public function inversoresAprobados(): array;

    /** Ronda de un proyecto: ['recaudado', 'porcentaje', 'porInversor' => [...], 'totales', 'dataProvider'] */
    public function resumenProyecto(Proyecto $p, $params);

    /** Inversiones de un inversor por proyecto: [['proyecto' => Proyecto, 'totales' => [...], 'pagos' => n], ...] */
    public function resumenInversor($idusuario): array;

    /** @return array ['titulo', 'columnas', 'modelos', 'filtros'] para Exportador */
    public function exportar($params);
}

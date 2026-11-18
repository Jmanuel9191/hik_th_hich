<?php
// components/ResultadosService.php
namespace app\components;

use app\models\Ordenes;
use app\models\ResultadoSecciones;
use app\models\Resultados;
use app\models\ProductoParametro;
use Yii;
use yii\web\NotFoundHttpException;

class ResultadosService implements InterfaceResultados
{
    private $alertService;

    public function __construct(AlertService $alertService)
    {
        $this->alertService = $alertService;
    }

    public function getSeccionesDeOrden($idorden)
    {
        return ResultadoSecciones::find()
            ->where(['idorden' => $idorden])
            ->orderBy('orden')
            ->with(['resultados.parametro'])
            ->all();
    }

    // Devuelve todos los parámetros de los productos comprados en esta orden
    public function getParametrosDisponibles($idorden)
    {
        $orden = Ordenes::findOne($idorden);
        if (!$orden) throw new NotFoundHttpException('Orden no encontrada.');

        $parametros = [];
        foreach ($orden->detalles as $detalle) {
            $producto = $detalle->producto;
            if (!$producto) continue;

            $vinculados = ProductoParametro::find()
                ->where(['idproducto' => $producto->idproducto])
                ->orderBy('orden')
                ->with('parametro')
                ->all();

            foreach ($vinculados as $v) {
    if (!$v->parametro) continue;
    // Clave: idparametro, valor: nombre del parámetro
    $parametros[$v->idparametro] = [
        'idparametro'      => $v->idparametro,
        'nombre'           => $v->parametro->nombre,
        'unidad'           => $v->parametro->unidad,
        'referencia_texto' => $v->parametro->referencia_texto,
        'producto'         => $producto->titulo,
        'tipo_campo'       => $v->parametro->tipo_campo ?? 'texto',
        'opciones'         => $v->parametro->opciones ?? '',
        'valor_default' => $v->parametro->valor_default ?? '',
    ];
}
        }
        return $parametros;
    }

   public function guardarResultados($idorden, $postData)
{
    $secciones = $postData['secciones'] ?? [];

    // Blindaje: si alguna seccion llega sin titulo (por si el "required" del navegador se salteo),
    // se corta ANTES de borrar nada, para no perder datos existentes
    foreach ($secciones as $sec) {
        $titulo = trim($sec['titulo'] ?? '');
        if (empty($titulo)) {
            $this->alertService->agregarMensajeError('Todas las secciones deben tener un título. Si querés quitar una sección, usá el botón "Eliminar sección".');
            return ['exito' => false];
        }
    }

    $transaction = Yii::$app->db->beginTransaction();
    
    try {
        // Fecha de toma de muestra (una sola para toda la ficha)
        $fechaToma = $postData['fecha_toma_muestra'] ?? null;

        // Borrar secciones viejas y recrear (más simple que hacer diff)
        ResultadoSecciones::deleteAll(['idorden' => $idorden]);

        // NUEVO: registrar quién cargó/validó los resultados — el
        // usuario logueado en este momento. Es distinto de quién
        // registró la orden (facturación); de acá sale el sello del
        // PDF. Se pisa cada vez que alguien vuelve a guardar, así
        // siempre refleja quién hizo la última validación.
        $orden = Ordenes::findOne($idorden);
        if ($orden) {
            $orden->idusuario_resultados = Yii::$app->user->id ?? null;
            $orden->save(false);
        }

        foreach ($secciones as $ordenSec => $sec) {
            $titulo = trim($sec['titulo'] ?? '');

            $seccion = new ResultadoSecciones();
            $seccion->mostrar_cabecera = isset($sec['mostrar_cabecera']) ? 1 : 0;
            $seccion->idorden            = $idorden;
            $seccion->titulo             = $titulo;
            $seccion->orden              = (int)$ordenSec;
            $seccion->fecha_toma_muestra = $fechaToma ?: null;
            // NUEVO: Observaciones y Conclusión ahora son por sección,
            // no generales de toda la orden.
            $seccion->observaciones      = $sec['observaciones'] ?? null;
            $seccion->conclusion         = $sec['conclusion'] ?? null;
            if (!$seccion->save()) {
                throw new \Exception('Error al guardar sección: ' . $titulo);
            }

            $filas = $sec['parametros'] ?? [];
            foreach ($filas as $ordenFila => $fila) {
                $idparametro = (int)($fila['idparametro'] ?? 0);
                if ($idparametro <= 0) continue;

                $res              = new Resultados();
                $res->idseccion   = $seccion->idseccion;
                $res->idparametro = $idparametro;
                $res->valor       = $fila['valor'] ?? null;
                $res->orden       = (int)$ordenFila;
                // NUEVO: un checkbox desmarcado NO viaja en el POST (así
                // funciona HTML), por eso isset() y no un simple ?? 0 —
                // si la clave no está, es que quedó desmarcado.
                $res->es_alerta   = isset($fila['alerta']) ? 1 : 0;
                if (!$res->save()) {
                    throw new \Exception('Error al guardar resultado.');
                }
            }
        }

        $transaction->commit();
        $this->alertService->agregarMensajeExito('Resultados guardados correctamente.');
        return ['exito' => true];

    } catch (\Exception $e) {
        $transaction->rollBack();
        Yii::error($e->getMessage());
        $this->alertService->agregarMensajeError('Error al guardar. Inténtelo de nuevo.');
        return ['exito' => false];
    }
}
}
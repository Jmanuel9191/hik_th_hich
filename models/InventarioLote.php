<?php

namespace app\models;

use Yii;

class InventarioLote extends \yii\db\ActiveRecord
{
    const ESTADO_ACTIVO = 1;
    const ESTADO_AGOTADO = 0;
    const DIAS_ALERTA = 30;

    public static function tableName()
    {
        return 'inventario_lotes';
    }

    public function rules()
    {
        return [
            [['idproducto', 'cantidad_inicial', 'cantidad_actual'], 'required'],
            [['idproducto', 'idtalla', 'idinventario', 'cantidad_inicial', 'cantidad_actual', 'estado'], 'integer'],
            [['fecha_vencimiento'], 'date', 'format' => 'php:Y-m-d'],
            [['fecha_registro'], 'safe'],
            [['codigo_lote'], 'string', 'max' => 50],
        ];
    }

    /**
     * Crea un lote a partir de una entrada de inventario
     */
    public static function crearLote($idproducto, $cantidad, $fechaVencimiento, $idinventario = null, $idtalla = 0)
    {
        $lote = new self();
        $lote->idproducto = $idproducto;
        $lote->idtalla = $idtalla ?: 0;
        $lote->idinventario = $idinventario;
        $lote->cantidad_inicial = $cantidad;
        $lote->cantidad_actual = $cantidad;
        $lote->fecha_vencimiento = $fechaVencimiento ?: null;
        $lote->fecha_registro = date('Y-m-d H:i:s');
        $lote->estado = self::ESTADO_ACTIVO;

        $consecutivo = self::find()->where(['idproducto' => $idproducto])->count() + 1;
        $lote->codigo_lote = 'L' . str_pad($consecutivo, 3, '0', STR_PAD_LEFT);

        $lote->save(false);
        return $lote;
    }

    /**
     * Descuenta cantidad de los lotes activos siguiendo FEFO.
     * Si se pasa $idloteForzado, prioriza ese lote antes de seguir con FEFO en el resto.
     * Devuelve el detalle descontado por lote, o false si no alcanza el stock.
     */
    public static function descontarFEFO($idproducto, $cantidad, $idtalla = 0, $idloteForzado = null, $permitirNegativo = false)
{
    $query = self::find()
        ->where(['idproducto' => $idproducto, 'idtalla' => $idtalla ?: 0, 'estado' => self::ESTADO_ACTIVO])
        ->andWhere(['>', 'cantidad_actual', 0]);

    if ($idloteForzado) {
        $query->orderBy(new \yii\db\Expression("idlote != {$idloteForzado}, fecha_vencimiento IS NULL, fecha_vencimiento ASC"));
    } else {
        $query->orderBy(new \yii\db\Expression('fecha_vencimiento IS NULL, fecha_vencimiento ASC'));
    }

    $lotes = $query->all();

    $disponible = array_sum(array_column($lotes, 'cantidad_actual'));

    if ($disponible < $cantidad && !$permitirNegativo) {
        return false;
    }

    $restante = $cantidad;
    $detalle = [];
    foreach ($lotes as $lote) {
        if ($restante <= 0) break;
        $tomar = min($lote->cantidad_actual, $restante);
        $lote->cantidad_actual -= $tomar;
        if ($lote->cantidad_actual <= 0) {
            $lote->estado = self::ESTADO_AGOTADO;
        }
        $lote->save(false);
        $detalle[] = ['idlote' => $lote->idlote, 'codigo_lote' => $lote->codigo_lote, 'cantidad' => $tomar];
        $restante -= $tomar;
    }

    // Si permite negativo y todavía queda sobrante sin cubrir, lo restamos del último lote usado
    // (o si no hay ningún lote, creamos uno "en déficit" para dejarlo registrado)
    if ($restante > 0 && $permitirNegativo) {
        if (!empty($detalle)) {
            $ultimoLote = self::findOne($detalle[count($detalle) - 1]['idlote']);
            $ultimoLote->cantidad_actual -= $restante;
            $ultimoLote->save(false);
            $detalle[count($detalle) - 1]['cantidad'] += $restante;
        } else {
            $loteDeficit = new self();
            $loteDeficit->idproducto = $idproducto;
            $loteDeficit->idtalla = $idtalla ?: 0;
            $loteDeficit->cantidad_inicial = 0;
            $loteDeficit->cantidad_actual = -$restante;
            $loteDeficit->fecha_registro = date('Y-m-d H:i:s');
            $loteDeficit->estado = self::ESTADO_ACTIVO;
            $loteDeficit->codigo_lote = 'DEFICIT-' . date('YmdHis');
            $loteDeficit->save(false);
            $detalle[] = ['idlote' => $loteDeficit->idlote, 'codigo_lote' => $loteDeficit->codigo_lote, 'cantidad' => $restante];
        }
        $restante = 0;
    }

    return $detalle;
}

    public static function stockTotal($idproducto, $idtalla = 0)
    {
        return (int) self::find()
            ->where(['idproducto' => $idproducto, 'idtalla' => $idtalla ?: 0, 'estado' => self::ESTADO_ACTIVO])
            ->sum('cantidad_actual');
    }

    public static function lotesActivos($idproducto, $idtalla = 0)
    {
        return self::find()
            ->where(['idproducto' => $idproducto, 'idtalla' => $idtalla ?: 0])
            ->andWhere(['>', 'cantidad_actual', 0])
            ->orderBy(new \yii\db\Expression('fecha_vencimiento IS NULL, fecha_vencimiento ASC'))
            ->all();
    }

    public function getAlerta()
    {
        if (!$this->fecha_vencimiento) {
            return 'ok';
        }
        $dias = (strtotime($this->fecha_vencimiento) - strtotime(date('Y-m-d'))) / 86400;

        if ($dias < 0) return 'vencido';
        if ($dias <= self::DIAS_ALERTA) return 'por_vencer';
        return 'ok';
    }





    public function getProductoLote()
{
    return $this->hasOne(Productos::class, ['idproducto' => 'idproducto']);
}

public static function alertasGlobales($limit = 10)
{
    $lotes = self::find()
        ->where(['estado' => self::ESTADO_ACTIVO])
        ->andWhere(['>', 'cantidad_actual', 0])
        ->andWhere(['is not', 'fecha_vencimiento', null])
        ->orderBy(new \yii\db\Expression('fecha_vencimiento ASC'))
        ->limit($limit)
        ->all();

    $result = [];
    foreach ($lotes as $lote) {
        $alerta = $lote->getAlerta();
        if ($alerta == 'vencido' || $alerta == 'por_vencer') {
            $result[] = $lote;
        }
    }
    return $result;
}

public static function stockBajo($umbral = 10)
{
    // Suma TODOS los lotes (incluso agotados/negativos) para reflejar déficit real
    $rows = self::find()
        ->select(['idproducto', 'total' => new \yii\db\Expression('SUM(cantidad_actual)')])
        ->groupBy('idproducto')
        ->having(['<', 'total', $umbral])
        ->asArray()
        ->all();

    $result = [];
    foreach ($rows as $r) {
        $result[$r['idproducto']] = (int)$r['total'];
    }
    return $result; // [idproducto => stock_total]
}



}
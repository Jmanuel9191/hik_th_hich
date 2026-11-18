<?php

namespace app\models;

/**
 * This is the model class for table "inventario".
 *
 * @property integer $idinventario
 * @property integer $idproducto
 * @property integer $idtalla
 * @property double $movimiento
 * @property double $saldo
 * @property string $fecha_registro
 * @property integer $estado
 * @property string $detalle
 */
class Inventario extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'inventario';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idproducto', 'movimiento', 'saldo', 'estado'], 'required'],
            [['idproducto', 'estado','idtalla'], 'integer'],
            [['movimiento', 'saldo'], 'number'],
            [['fecha_registro'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'idinventario' => 'Idinventario',
            'idproducto' => 'Idproducto',
            'idtalla' => 'Talla',
            'movimiento' => 'Cantidad',
            'saldo' => 'Saldo',
            'fecha_registro' => 'Fecha de movimiento',
            'estado' => 'Tipo',
            'detalle' => 'Motivo',
        ];
    }

    public function getProducto()
    {
        return $this->hasOne(Productos::className(), ['idproducto' => 'idproducto']);
    }
    public function getTalla()
    {
        return $this->hasOne(ProductosTallas::className(), ['idtalla' => 'idtalla']);
    }


  public function getSalditlo()
    {
        $result = [];
        $tmp = Inventario::find()->all();
        foreach($tmp as $item)
        {
            $result[$item->idproducto] = "". $item->saldo;
        }
        return $result;
    }


    public static function tipos($id = null)
    {
        $array = array(
            '0' => 'Salida',
            '1' => 'Entrada',
        );
        if (is_null($id))
            return $array;
        return $array[$id];
    }


    public function setSaldo()
    {
            $id = $this->idproducto;
        $last = Inventario::find()
            ->orderBy(['fecha_registro' => SORT_DESC])
            ->andWhere(['idproducto' => $id]);
        if(!empty($this->idtalla)){
            $last = $last->andWhere(['idtalla' => $this->idtalla]);;
        }
        $last = $last->one();
        if (!empty($last)) {
            if ($this->estado) {
                $this->saldo = $last->saldo + $this->movimiento;
            } else {
                $this->saldo = $last->saldo - $this->movimiento;
            }
        } else {
            if ($this->estado) {
                $this->saldo = $this->movimiento;
            } else {
                $this->saldo = (-1) * $this->movimiento;
            }
        }
    }

    /**
     * Devuelve el/los lote(s) asociados a este movimiento.
     * Si es entrada: el lote que se creo con este movimiento.
     * Si es salida: el detalle de los lotes de los que se desconto (FEFO).
     */
    public function getLoteInfo()
    {
        if ($this->estado == 1) {
            $lote = InventarioLote::find()->where(['idinventario' => $this->idinventario])->one();
            return $lote ? $lote->codigo_lote : '-';
        } else {
            return InventarioLoteMovimiento::getDetalleTexto($this->idinventario);
        }
    }

}
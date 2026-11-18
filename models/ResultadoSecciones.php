<?php
namespace app\models;

class ResultadoSecciones extends \yii\db\ActiveRecord
{
    public static function tableName() { return 'resultado_secciones'; }

    public function rules()
    {
        return [
            [['idorden', 'titulo'], 'required'],
            [['idorden', 'orden'], 'integer'],
            [['titulo'], 'string', 'max' => 200],
            [['fecha_toma_muestra'], 'safe'],
            [['mostrar_cabecera'], 'integer'],
            [['observaciones', 'conclusion'], 'string'],
        ];
    }

    public function getResultados()
    {
        return $this->hasMany(Resultados::class, ['idseccion' => 'idseccion'])
            ->orderBy('orden');
    }

    public function getOrden()
    {
        return $this->hasOne(Ordenes::class, ['idorden' => 'idorden']);
    }
}
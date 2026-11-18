<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "categorias_servicio".
 *
 * @property int $idcategoria
 * @property string $nombre
 * @property string $alias
 * @property int|null $idpadre
 * @property string|null $estado
 * @property string|null $fecha_registro
 *
 * @property CategoriasServicio $padre
 * @property CategoriasServicio[] $hijos
 */
class CategoriasServicio extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'categorias_servicio';
    }

    public function rules()
    {
        return [
            [['nombre', 'alias'], 'required'],
            [['idpadre'], 'integer'],
            [['fecha_registro'], 'safe'],
            [['estado'], 'string', 'max' => 1],
            [['nombre'], 'string', 'max' => 100],
            [['alias'], 'string', 'max' => 50],
        ];
    }

    public function attributeLabels()
    {
        return [
            'idcategoria' => 'ID',
            'nombre' => 'Nombre',
            'alias' => 'Alias',
            'idpadre' => 'Categoría Padre',
            'estado' => 'Estado',
            'fecha_registro' => 'Fecha de Registro',
        ];
    }

    // Relación con la categoría padre
    public function getPadre()
    {
        return $this->hasOne(CategoriasServicio::class, ['idcategoria' => 'idpadre']);
    }

    // Relación con sus subcategorías hijas
    public function getHijos()
    {
        return $this->hasMany(CategoriasServicio::class, ['idpadre' => 'idcategoria']);
    }

    // Relación con proyectos si tienes campo FK directo
    public function getProductos()
    {
        return $this->hasMany(Productos::class, ['idcategoria' => 'idcategoria']);
    }

    public static function getListaCategoria()
    {
        return \yii\helpers\ArrayHelper::map(self::find()->all(), 'idcategoria', 'nombre');
    }


    public static function getListaCategoriaAgrupada()
    {
        $padres = self::find()->where(['idpadre' => null])->all();
        $resultado = [];

        foreach ($padres as $padre) {
            $hijos = self::find()->where(['idpadre' => $padre->idcategoria])->all();

            foreach ($hijos as $hijo) {
                $resultado[$padre->nombre][$hijo->idcategoria] = $hijo->nombre;
            }
        }

        return $resultado;
    }

    public static function getListaHijosDe($aliasPadre)
    {
        $padre = self::find()->where(['alias' => $aliasPadre])->one();
        if (!$padre) {
            return [];
        }

        return self::find()
            ->where(['idpadre' => $padre->idcategoria])
            ->select(['nombre', 'idcategoria'])
            ->indexBy('idcategoria')
            ->column();
    }

public static function getSelectMenu($modulo, $excluirAlias = null, $incluirSoloAlias = null)
{
    $idsPermitidos = null; // null = sin restricción, entran todas
    if ($incluirSoloAlias) {
        $catIncluir = self::find()->where(['alias' => $incluirSoloAlias])->one();
        $idsPermitidos = $catIncluir ? self::obtenerIdsConHijos($catIncluir->idcategoria) : [];
    }

    $idsExcluir = [];
    if ($excluirAlias) {
        $catExcluir = self::find()->where(['alias' => $excluirAlias])->one();
        if ($catExcluir) {
            $idsExcluir = self::obtenerIdsConHijos($catExcluir->idcategoria);
        }
    }

    $todas = self::find()->all();
    $cat = [];
    foreach ($todas as $item) {
        if ($idsPermitidos !== null && !in_array($item->idcategoria, $idsPermitidos)) {
            continue;
        }
        if (in_array($item->idcategoria, $idsExcluir)) {
            continue;
        }
        $cat[] = $item;
    }

    $result = [];
    $temp = [];
    foreach ($cat as $item) {
        if (empty($item->idpadre)) {
            array_push($temp, $item);
        }
    }
    foreach ($temp as $item) {
        $temps = array_filter($cat, fn($c) => $c->idpadre == $item->idcategoria);
        if (count($temps) > 0) {
            foreach ($temps as $value) {
                $temps2 = array_filter($cat, fn($c) => $c->idpadre == $value->idcategoria);
                if (count($temps2) > 0) {
                    foreach ($temps2 as $value2) {
                        $result["<span class='fa fa-square'></span> " . $item->nombre]["<span class='fa fa-caret-right'></span> " . $value->nombre][$value2->idcategoria] = "<span class='fa fa-angle-double-right'></span> " . $value2->nombre;
                    }
                } else {
                    $result["<span class='fa fa-square'></span> " . $item->nombre][$value->idcategoria] = "<span class='fa fa-caret-right'></span> " . $value->nombre;
                }
            }
        } else {
            $result[$item->idcategoria] = "<span class='fa fa-square'></span> " . $item->nombre;
        }
    }
    return $result;
}


private static function obtenerIdsConHijos($idcategoria)
{
    $ids = [$idcategoria];
    $hijos = self::find()->where(['idpadre' => $idcategoria])->all();
    foreach ($hijos as $hijo) {
        $ids = array_merge($ids, self::obtenerIdsConHijos($hijo->idcategoria));
    }
    return $ids;
}
    // Relación con campañas si también tienen campo FK
    public function getCampanias()
    {
        return $this->hasMany(Campanias::class, ['idcategoria_servicio' => 'idcategoria']);
    }
}

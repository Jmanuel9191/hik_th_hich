<?php

namespace app\models;
use yii;
use sadovojav\cutter\behaviors\CutterBehavior;
use app\models\Sucursal;
use app\models\Sector;
class Productos extends \yii\db\ActiveRecord
{

    public $sucursales;
    public $sectores;
public $parametros_ids;
    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'productos';
    }

    /**
     * @inheritdoc
     */
   public function rules()
{
    return [
        [['idcategoria', 'titulo', 'precio', 'preciooferta' ], 'required'],        
        [['descripcion', 'titulo', 'resumen', 'titulo2'], 'string'],
        [['titulo'], 'string', 'max' => 500],
        [['titulo2'], 'string', 'max' => 500],
        [['foto'], 'string', 'max' => 100],
        [['precio', 'codigo', 'preciooferta', 'preciocaja', 'preciocajaventa'], 'double', 'max' => 100000000],
        [['idcategoria', 'idusuario', 'posicion', 'visitas', 'preciocaja'], 'integer'],
         [['precio', 'preciooferta', 'preciocajaventa'], 'number', 'min' => 0],
        [['fecha_registro', 'fecha_vencimiento', 'fecha_vencimientof'], 'safe'],
        [['estado', 'destacado'], 'string', 'max' => 1],
        [['estado', 'destacado'], 'in', 'range' => ['0', '1']],
         [['titulo'], 'validarTituloClienteUnico'],
    ];
}

public function attributeLabels()
{
    return [
        'idcategoria' => 'Categoría',
        'titulo' => 'Titulo',
        'resumen' => 'Resumen',
        'descripcion' => 'Descripción',
        'titulo2' => 'Subtítulo',
        'precio' => 'Precio de Venta',
        'preciooferta' => 'Precio de Compra',
        'idusuario' => 'Usuario Responsable',
        'foto' => 'Foto',
        'codigo' => 'Codigo',
        'estado' => 'Estado',
        'destacado' => 'Destacado',
        'posicion' => 'Posición',
        'saldo' => 'Saldo',
        'preciocaja' => 'Precio por Caja',
        'preciocajaventa' => 'Precio de Venta por Caja',
        'fecha_vencimiento' => 'Fecha de Vencimiento',
        'fecha_vencimientof' => 'Fecha de Vencimiento (Final)',
    ];
}


    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['cambio_estado'] = ['estado', 'motivobloqueo']; // Solo esos campos requeridos para ese caso
        return $scenarios;
    }


public function beforeValidate()
{
    // Convierte coma decimal a punto, por si el usuario escribe "150,50"
    foreach (['precio', 'preciooferta', 'preciocaja', 'preciocajaventa'] as $campoPrecio) {
        if (isset($this->$campoPrecio) && is_string($this->$campoPrecio)) {
            $this->$campoPrecio = str_replace(',', '.', $this->$campoPrecio);
        }
    }
    return parent::beforeValidate();
}

public function validarTituloClienteUnico($attribute, $params)
{
    if (empty($this->titulo)) {
        return;
    }

    // Traigo la categoria actual para saber si es de inventario (productosg) o de servicios
    $categoriaActual = \app\models\CategoriasServicio::findOne($this->idcategoria);
    $esInventario = $categoriaActual && $categoriaActual->alias === 'productosg';

    $query = self::find()->where(['titulo' => $this->titulo]);

    if ($esInventario) {
        // Si es un material de inventario, solo comparo contra otros materiales de inventario
        $idsCategoriasInventario = \app\models\CategoriasServicio::find()
            ->where(['alias' => 'productosg'])
            ->select('idcategoria')
            ->column();
        $query->andWhere(['idcategoria' => $idsCategoriasInventario]);
    } else {
        // Si es un servicio, solo comparo contra otros servicios (excluyo productosg)
        $idsCategoriasInventario = \app\models\CategoriasServicio::find()
            ->where(['alias' => 'productosg'])
            ->select('idcategoria')
            ->column();
        $query->andWhere(['not in', 'idcategoria', $idsCategoriasInventario]);
    }

    if (!$this->isNewRecord && $this->idproducto) {
        $query->andWhere(['<>', 'idproducto', $this->idproducto]);
    }

    if ($query->exists()) {
        $this->addError($attribute, 'Ya existe un producto con el mismo nombre en esta sección.');
    }
}

    
    public function getFechaVencFormateada()
    {
        return $this->fecha_inicio ? date("d/m/Y", strtotime($this->fecha_vencimiento)) : null;
    }

  
    public function getFechaRegistroFormateada()
    {
        return $this->fecha_registro ? date("d/m/Y", strtotime($this->fecha_registro)) : null;
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['idusuario' => 'idusuario']);
    }
   
    public function getDoctor()
    {
        return $this->hasOne(Usuarios::className(), ['iddoctor' => 'iddoctor']);
    }
   

  public function getCategoria()
    {
        return $this->hasOne(CategoriasServicio::className(), ['idcategoria' => 'idcategoria']);
    }

    
    public function getSaldo()
    {
        return $this->hasOne(Inventario::className(),  ['idproducto' => 'idproducto'])->orderBy(['idinventario' => SORT_DESC]);
    }


// Materiales que este producto (examen) consume, con su cantidad
public function getMaterialesVinculados()
{
    return $this->hasMany(ProductoMaterial::class, ['idproducto_servicio' => 'idproducto'])
        ->with('material');
}


public function getAlertaStockBajo($umbral = 10)
{
    if (!$this->categoria || $this->categoria->alias !== 'productosg') {
        return '';
    }
    $stock = \app\models\InventarioLote::stockTotal($this->idproducto);
    if ($stock < 0) {
        return '<span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Déficit: ' . $stock . '</span>';
    }
    if ($stock < $umbral) {
        return '<span class="label label-warning"><i class="fa fa-exclamation-triangle"></i> Stock bajo: ' . $stock . '</span>';
    }
    return '';
}



    public function getCategoriaServicioNombre($idCategoria)
    {
        $categoria = \app\models\CategoriasServicio::findOne($idCategoria);
        return $categoria ? $categoria->nombre : '(desconocido)';
    }


  
    public function getCategoriaServicio()
    {
        return $this->hasOne(CategoriasServicio::class, ['idcategoria' => 'idcategoria']);
    }


public function getSaldoMostrar()
{
    if ($this->categoria && $this->categoria->alias === 'serviciosg') {
        return 'Servicio';
    }
    return \app\models\InventarioLote::stockTotal($this->idproducto);
}

    public static function getPrioridad()
    {
        $result =[];
        $prioridad = [
            'Alta' =>'Alta',
            'Media' => 'Media',
            'Baja' => 'Baja'
        ];
        $result = $prioridad;
        return $result;
    }


    public static function obtenerTextoEstado($estado)
    {
        $estados = [
            '1' => 'Activo',
            '0'  => 'Bloqueado', // Importante: clave vacía para bloqueados
            ''  => 'Bloqueado', // Importante: clave vacía para bloqueados
        ];

        return $estados[$estado] ?? 'Desconocido';
    }
    public static function obtenerTextoFinalizado($estado)
    {
        $estados = [
            '1' => 'Vigente',
            '2'  => 'Finalizado', // Importante: clave vacía para bloqueados
        ];

        return $estados[$estado] ?? 'Desconocido';
    }

    public function getParametrosIdsActuales()
{
    return \yii\helpers\ArrayHelper::getColumn($this->getParametrosLink()->asArray()->all(), 'idparametro');
}



    public static function getEstadosProductos()
    {
        return [
            '1' => 'Activo',
            '0' => 'Bloqueado', // ✔ correcto
        ];
    }
    public static function getFinalizadoProductos()
    {
        return [
            '1' => 'Vigente',
            '2' => 'Finalizado', // ✔ correcto
        ];
    }

 static  public function saveInventario($items = array(),$id='')
    {

           date_default_timezone_set('America/La_Paz');
        foreach ($items as $item)
        {
            $tmpi = new Inventario();
            $tmpi->idproducto = $item->idproducto;
            $tmpi->estado = 0;
            $tmpi->movimiento = $item->cantidad;
            $tmpi->fecha_registro = date('Y-m-d H:i:s');
            $tmpi->detalle = "Movimiento de compra ({$id})";
            if(!empty($item['idtalla']))
            {
                $tmpi->idtalla = $item['idtalla'];
            }
            $tmpi->setSaldo();
            $tmpi->save();
        }

    }

    public function getAlertaLotes()
    {
        $lotes = \app\models\InventarioLote::find()
            ->where(['idproducto' => $this->idproducto, 'estado' => \app\models\InventarioLote::ESTADO_ACTIVO])
            ->andWhere(['>', 'cantidad_actual', 0])
            ->all();

        $tieneVencido = false;
        $tienePorVencer = false;

        foreach ($lotes as $lote) {
            $alerta = $lote->getAlerta();
            if ($alerta == 'vencido') {
                $tieneVencido = true;
            } elseif ($alerta == 'por_vencer') {
                $tienePorVencer = true;
            }
        }

        if ($tieneVencido) {
            return '<span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Vencido</span>';
        }
        if ($tienePorVencer) {
            return '<span class="label label-warning"><i class="fa fa-clock-o"></i> Por vencer</span>';
        }
        if (!empty($lotes)) {
            return '<span class="label label-success">OK</span>';
        }
        return '';
    }
}
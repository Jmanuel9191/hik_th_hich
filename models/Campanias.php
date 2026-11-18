<?php

namespace app\models;
use yii;
use sadovojav\cutter\behaviors\CutterBehavior;

class Campanias extends \yii\db\ActiveRecord
{

    public $sucursales;
    public $sectores;
    public $audiencias;

    /**
     * @inheritdoc
     */

    public static function tableName()
    {
        return 'campanias';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['titulo', 'descripcion', 'fecha_inicio', 'fecha_fin', 'prioridad', 'idcliente', 'idgestor', 'idcategoria_servicio', 'sucursales', 'sectores',], 'required'],
            [['descripcion', 'titulo'], 'string'],
            [['fecha_inicio', 'fecha_registro', 'fechafinalizacion', 'motivodefinal', 'idgestor'], 'safe'],
            [['estado', 'finalizado'], 'string', 'max' => 1],
            [['motivodefinal'], 'string'],
            [['sucursales', 'sectores', 'idcategoria_servicio', 'audiencias'], 'safe'],
            [['estado'], 'in', 'range' => ['0', '1']],
            [['titulo', 'idcliente'], 'validarTituloClienteUnico'],
        ];
    }



    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'titulo' => 'Titulo de la Campaña',
            'Descripcion' => 'Descripcion General',
            'Fecha_inicio' => 'Fecha inicio',
            'Fecha_fin' => 'Fecha fin estimada',
            'prioridad' => 'Prioridad',
            'idcliente' => 'Cliente',
            'idgestor' => 'Gestor Asignado',
            'idcategoria_servicio' => 'Tipo de Servicio',

        ];
    }


    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['cambio_estado'] = ['estado', 'motivobloqueo']; // Solo esos campos requeridos para ese caso
        return $scenarios;
    }

    public function validarFechaFin($attribute, $params)
    {
        Yii::info("Ejecutando validación personalizada de fecha_fin...", __METHOD__);

        if (!$this->hasErrors()) {
            $fechaInicio = strtotime($this->fecha_inicio);
            $fechaFin = strtotime($this->fecha_fin);

            if ($fechaInicio === false || $fechaFin === false) {
                $this->addError($attribute, 'Las fechas no son válidas.');
            } elseif ($fechaFin < $fechaInicio) {
                $this->addError($attribute, 'La fecha de fin no puede ser menor que la fecha de inicio.');
            }
        }
    }

    public function validarTituloClienteUnico($attribute, $params)
    {
        // Si no vino del formulario, lo traemos del modelo original
        if (empty($this->idcliente) && !$this->isNewRecord) {
            $original = self::findOne($this->idcampania);
            if ($original !== null) {
                $this->idcliente = $original->idcliente;
            }
        }

        if (empty($this->idcliente) || empty($this->titulo)) {
            return;
        }

        $query = self::find()
            ->where(['titulo' => $this->titulo, 'idcliente' => $this->idcliente]);

        if (!$this->isNewRecord && $this->idcampania) {
            $query->andWhere(['<>', 'idcampania', $this->idcampania]);
        }

        if ($query->exists()) {
            $this->addError($attribute, 'Este cliente ya tiene una campaña con este título.');
        }
    }




    public function beforeValidate()
    {
        if ($this instanceof \app\models\CampaniasSearch) {
            return true;
        }


        if (!parent::beforeValidate()) {
            return false;
        }

        $fechaInicio = strtotime($this->fecha_inicio);
        $fechaFin = strtotime($this->fecha_fin);
        $hoy = strtotime(date('Y-m-d'));

        if ($fechaInicio === false || $fechaFin === false) {
            $this->addError('fecha_fin', 'Las fechas no son válidas.');
        } elseif ($fechaFin < $fechaInicio) {
            $this->addError('fecha_fin', 'La fecha de fin no puede ser menor que la fecha de inicio.');
        }

        if ($fechaInicio < $hoy) {
            $this->addError('fecha_inicio', 'La fecha de inicio no puede ser anterior a hoy.');
        }

        return true;
    }




    public function getUsuario()
    {
        return $this->hasOne(Usuarios::className(), ['idusuario' => 'idcliente']);
    }
    public function getAsignartareas()
    {
        return $this->hasMany(Asignacion::className(), ['idcampania' => 'idcampania']);
    }
    public function getGestor()
    {
        return $this->hasOne(Usuarios::className(), ['idusuario' => 'idgestor']);
    }
    public function getCliente()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idcliente']);
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


    public static function getEstadosCampanias()
    {
        return [
            '1' => 'Activo',
            '0' => 'Bloqueado', // ✔ correcto
        ];
    }

    public static function obtenerTextoFinalizado($estado)
    {
        $estados = [
            '1' => 'Vigente',
            '2'  => 'Finalizado', // Importante: clave vacía para bloqueados
        ];

        return $estados[$estado] ?? 'Desconocido';
    }

    public static function getFinalizadoProyectos()
    {
        return [
            '1' => 'Vigente',
            '2' => 'Finalizado', // ✔ correcto
        ];
    }

    public function getCampaniaSucursales()
    {
        return $this->hasMany(CampaniaSucursal::class, ['idcampania' => 'idcampania']);
    }



    public function getCampaniaSectores()
    {
        return $this->hasMany(CampaniaSector::class, ['idcampania' => 'idcampania']);
    }



    public function getSucursales()
    {
        return $this->hasMany(Sucursal::class, ['idsucursal' => 'idsucursal'])
            ->viaTable('campania_sucursal', ['idcampania' => 'idcampania']);



    }

    public function getSectores()
    {
        return $this->hasMany(Sector::class, ['idsector' => 'idsector'])
            ->viaTable('campania_sector', ['idcampania' => 'idcampania']);
    }

    public function getAudiencias()
    {
        return $this->hasMany(Audiencias::class, ['idaudiencia' => 'idaudiencia'])
            ->viaTable('campanias_audiencia', ['idcampania' => 'idcampania']);
    }
    public function getCategoriaServicio()
    {
        return $this->hasOne(CategoriasServicio::class, ['idcategoria' => 'idcategoria_servicio']);
    }

    public function getAudienciasRelacionadas()
    {
        return $this->hasMany(Audiencias::class, ['idaudiencia' => 'idaudiencia'])
            ->viaTable('campania_audiencia', ['idcampania' => 'idcampania']);
    }

    public function getSucursalesIds()
    {
        return \yii\helpers\ArrayHelper::getColumn($this->getSucursales()->asArray()->all(), 'idsucursal');
    }

    public function getCategoriaServicioNombre($idCategoria)
    {
        $categoria = \app\models\CategoriasServicio::findOne($idCategoria);
        return $categoria ? $categoria->nombre : '(desconocido)';
    }




    public function getFechaInicioFormateada()
    {
        return $this->fecha_inicio ? date("d/m/Y", strtotime($this->fecha_inicio)) : null;
    }

    public function getFechaFinFormateada()
    {
        return $this->fecha_fin ? date("d/m/Y", strtotime($this->fecha_fin)) : null;
    }

    public function getFinalizacionFormateada()
    {
        return $this->fechafinalizacion ? date("d/m/Y", strtotime($this->fechafinalizacion)) : null;
    }

    public function getFechaRegistroFormateada()
    {
        return $this->fecha_registro ? date("d/m/Y", strtotime($this->fecha_registro)) : null;
    }


    public function getSectoresIds()
    {
        return \yii\helpers\ArrayHelper::getColumn($this->getSectores()->asArray()->all(), 'idsector');
    }

    public function getAudienciasIds()
    {
        return \yii\helpers\ArrayHelper::getColumn($this->getAudiencias()->asArray()->all(), 'idaudiencia');
    }


    public function getSucursalesRelacionadas()
    {
        return $this->getSucursales()->all(); // fuerza a usar la relación, no la propiedad pública
    }

    public function getSectoresRelacionados()
    {
        return $this->getSectores()->all();
    }
    public function getAudienciasRelacionados()
    {
        return $this->getAudiencias()->all();
    }
}

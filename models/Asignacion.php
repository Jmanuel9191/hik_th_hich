<?php

namespace app\models;
use yii\db\Expression;
use yii\helpers\HtmlPurifier; // esto lo pones arriba del archivo

/**
 * This is the model class for table "categoria".
 *
 * @property integer $idconsultor
 * @property string $nombre
 * @property string $descripcion
 * @property string $imagen
 * @property integer $idpadre
 * @property integer $modulo
 * @property string $estado
 */
class Asignacion extends \yii\db\ActiveRecord
{
    const ESTADO_PENDIENTE = '1';
    const ESTADO_EN_PROCESO = '2';
    const ESTADO_FINALIZADO = '3';
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'asignartareas';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idtarea', 'idconsultor', 'prioridad'], 'required'],
            [['descripcion', 'idproyecto', 'idcampania', 'aceptacionadmin', 'comentario'], 'safe'],
            [['comentario'], 'required', 'on' => 'update'],

            [['idtarea'], 'validarTareaConsultor'],

            [['fechainicio', 'fechafin'], 'safe'],
            [['fechainicio', 'fechafin'], 'required', 'when' => function ($model) {
                return !empty($model->idproyecto) || !empty($model->idcampania);
            }, 'whenClient' => "function (attribute, value) {
                return $('#asignacion-idproyecto').val() !== '' || $('#asignacion-idcampania').val() !== '';
            }"],
            ['fechainicio', 'validarFechaInicio'],
            ['fechafin', 'validarFechaFin'],
            [['estado'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'idasignartarea' => 'ID',
            'idtarea' => 'Tarea para Asignar',
            'aceptacionadmin' => 'Estado de la tarea',
            'descripcion' => 'Descripcion u Objetivo',
            'fechainicio' => 'Fecha inicial',
            'fechafin' => 'Fecha Final',
            'idconsultor' => 'Consultor',
            'idproyecto' => 'Proyecto',
            'prioridad' => 'Prioridad',
            'comentario' => 'Razon por la que se esta modificando?',
            'estado' => 'Estado',
        ];
    }


    public function validarFechaInicio($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if ($this->idproyecto && $this->proyecto) {
                $fechaInicioProyecto = strtotime($this->proyecto->fecha_inicio);
                $fechaInicioAsignacion = strtotime($this->fechainicio);

                if ($fechaInicioAsignacion < $fechaInicioProyecto) {
                    $this->addError($attribute, 'La fecha de inicio no puede ser menor que la fecha de inicio del proyecto.');
                }
            } elseif ($this->idcampania && $this->campania) {
                $fechaInicioCampania = strtotime($this->campania->fecha_inicio);
                $fechaInicioAsignacion = strtotime($this->fechainicio);

                if ($fechaInicioAsignacion < $fechaInicioCampania) {
                    $this->addError($attribute, 'La fecha de inicio no puede ser menor que la fecha de inicio de la campaña.');
                }
            }
        }
    }


    public function validarUnicaAsignacionMonitoreo($attribute, $params)
    {
        // Cargar la tarea asignada
        $tarea = $this->tarea ?? null;

        if ($tarea && strtolower(trim($tarea->titulo)) === 'monitoreo y ajustes') {
            $query = self::find()
                ->where(['idtarea' => $this->idtarea]);

            // Limita por proyecto o campaña
            if ($this->idproyecto) {
                $query->andWhere(['idproyecto' => $this->idproyecto]);
            } elseif ($this->idcampania) {
                $query->andWhere(['idcampania' => $this->idcampania]);
            }

            // Excluir la asignación actual en modo edición
            if (!$this->isNewRecord) {
                $query->andWhere(['not', ['idasignartarea' => $this->idasignartarea]]);
            }

            // Si ya hay una, bloquear
            if ($query->exists()) {
                $this->addError($attribute, 'La tarea "Monitoreo y Ajustes" solo puede ser asignada una vez por proyecto o campaña.');
            }
        }
    }



    public function validarFechaFin($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $fechaInicioAsignacion = strtotime($this->fechainicio);
            $fechaFinAsignacion = strtotime($this->fechafin);

            if ($fechaFinAsignacion < $fechaInicioAsignacion) {
                $this->addError($attribute, 'La fecha de fin no puede ser menor que la fecha de inicio.');
            }

            if ($this->idproyecto && $this->proyecto) {
                $fechaFinProyecto = strtotime($this->proyecto->fecha_fin);
                if ($fechaFinAsignacion > $fechaFinProyecto) {
                    $this->addError($attribute, 'La fecha de fin no puede ser mayor que la fecha de fin del proyecto.');
                }
            } elseif ($this->idcampania && $this->campania) {
                $fechaFinCampania = strtotime($this->campania->fecha_fin);
                if ($fechaFinAsignacion > $fechaFinCampania) {
                    $this->addError($attribute, 'La fecha de fin no puede ser mayor que la fecha de fin de la campaña.');
                }
            }
        }
    }



    public function validarTareaConsultor($attribute, $params)
    {
        $query = self::find()
            ->where(['idtarea' => $this->idtarea])
            ->andWhere(['<>', 'estado', self::ESTADO_FINALIZADO]); // solo bloquea si ya hay no finalizadas

        if ($this->idproyecto) {
            $query->andWhere(['idproyecto' => $this->idproyecto]);
        } elseif ($this->idcampania) {
            $query->andWhere(['idcampania' => $this->idcampania]);
        }

        if (!$this->isNewRecord) {
            $query->andWhere(['not', ['idasignartarea' => $this->idasignartarea]]);
        }

        if ($query->exists()) {
            $this->addError(
                $attribute,
                'Ya existe una asignación activa de esta tarea en esta campaña o proyecto. Solo puede volver a asignarse cuando la anterior esté finalizada.'
            );
        }
    }





    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios['create'] = ['idtarea', 'idconsultor', 'prioridad', 'fechainicio', 'fechafin', 'descripcion']; // sin comentario
        $scenarios['update'] = ['idtarea', 'idconsultor', 'prioridad', 'fechainicio', 'fechafin', 'comentario', 'descripcion']; // con comentario

        return $scenarios;
    }

    private function validarFormatoFecha($fecha)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        return $d && $d->format('Y-m-d') === $fecha;
    }

    public function getConsultor()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idconsultor']);
    }

    public function getGestor()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idgestor']);
    }

    public function getRecursos()
    {
        return $this->hasMany(Recursos::class, ['idasignartarea' => 'idasignartarea']);
    }

    public function puedeFinalizar()
    {
        foreach ($this->recursos as $recurso) {
            if ($recurso->estado != self::ESTADO_FINALIZADO) { // 2 = aprobado
                return false;
            }
        }
        return true;
    }

    public function getCliente()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idcliente']);
    }

    public function getCliented()
    {
        return $this->proyecto->cliente ?? null;
    }
    public function getProyecto()
    {
        return $this->hasOne(Proyectos::class, ['idproyecto' => 'idproyecto']);
    }

    public function getCampania()
    {
        return $this->hasOne(Campanias::class, ['idcampania' => 'idcampania']);
    }

// Devuelve la fecha de inicio formateada
    public function getFechaInicioFormateada()
    {
        if (empty($this->fechainicio)) return '-';
        return date("d/m/Y", strtotime($this->fechainicio));
    }

// Devuelve la fecha de fin formateada
    public function getFechaFinFormateada()
    {
        if (empty($this->fechafin)) return '-';
        return date("d/m/Y", strtotime($this->fechafin));
    }

// Devuelve el rango completo como string listo para mostrar
    public function getRangoFechasAsignacion()
    {
        return "<span class='consultoresasi'><i class='fa-solid fa-calendar'></i>  
        <strong>Del </strong> {$this->fechaInicioFormateada} 
        <strong> hasta el </strong> {$this->fechaFinFormateada}</span>";
    }



    public function tieneHistorialtareas()
    {
        return Recursos::find()
            ->where(['idasignartarea' => $this->idasignartarea])
            ->exists();
    }



    public static function obtenerEnProgresoPorCampania($idCampania)
    {
        return self::find()
            ->where(['idcampania' => $idCampania, 'estado' => '2'])
            ->with('recursos') // evitar N+1
            ->all();
    }

    public static function obtenerFinalizadosPorCampania($idCampania)
    {
        return self::find()
            ->where(['idcampania' => $idCampania, 'estado' => '3'])
            ->with('recursos') // evitar N+1
            ->all();
    }

    public static function obtenerEnProgresoPorProyecto($idProyecto)
    {
        return self::find()
            ->where(['idproyecto' => $idProyecto, 'estado' => '2'])
            ->with('recursos') // evitar N+1
            ->all();
    }

    public static function obtenerFinalizadosPorProyecto($idProyecto)
    {
        return self::find()
            ->where(['idproyecto' => $idProyecto, 'estado' => '3'])
            ->with('recursos') // evitar N+1
            ->all();
    }

    public function getTareas()
    {
        return $this->hasOne(Tareas::className(), ['idtarea' => 'idtarea']);
    }
    public function getTarea()
    {
        return $this->hasOne(Tareas::className(), ['idtarea' => 'idtarea']);
    }
    /*
     * seccion de relaciones entre tablas
     */
    public function getCategorias()
    {
        return $this->hasMany(Asignacion::className(), ['idpadre' => 'idcategoria']);
    }


    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            // Limpia el contenido del campo descripcion para evitar XSS
            if (!empty($this->descripcion)) {
                $this->descripcion = HtmlPurifier::process($this->descripcion);
            }

            return true;
        }
        return false;
    }



    public function obtenerNombreEstado()
    {
        $estados = [
            self::ESTADO_PENDIENTE => 'Por hacer',
            self::ESTADO_EN_PROCESO => 'Haciendo',
            self::ESTADO_FINALIZADO => 'Finalizado',
        ];

        return $estados[$this->estado] ?? 'Desconocido';
    }

    public static function obtenerPorHacerSinMonitoreo($idCampania)
    {
        return self::find()
            ->joinWith('tarea')
            ->where(['asignartareas.idcampania' => $idCampania, 'asignartareas.estado' => '2'])
            ->andWhere(['<>', new Expression('LOWER(tareas.titulo)'), 'monitoreo y ajustes'])
            ->all();
    }
//
    public static function obtenerMonitoreoPorCampania($idcampania)
    {
        return self::find()
            ->alias('a')
            ->joinWith('tarea t') // solo la relación con la tarea
            ->where(['a.estado' => '2', 'a.idcampania' => $idcampania])
            ->andWhere(['t.titulo' => 'Monitoreo y Ajustes'])
            ->all();
    }





}

<?php

namespace app\models;

use yii;

/**
 * This is the model class for table "recursos".
 *
 * @property integer $idrecurso
 * @property string $titulo
 * @property string $descripcion
 * @property string $fechaentrega
 * @property integer $idasignartarea
 * @property integer $idusuario
 * @property string $estado
 * @property string $fecha_registro
 */
class HistoriaRecursos extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */


    public static function tableName()
    {
        return 'historial_recurso';
    }

    /**
     * @inheritdoc
     */
    public $archivoUpload;

    public function rules()
    {


        /* Regla para múltiples archivos */


        return [
            [['idrecurso', 'idusuario', 'tipo', 'tipomensaje'], 'integer'],
            [['tipo'], 'in', 'range' => [1, 2]],
            [['comentario'], 'string'],
            [['archivo'], 'file',
                'extensions' => ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar'],
                'maxSize' => 1024 * 1024 * 50,
                'maxFiles' => 10,
                'tooBig' => 'El archivo no puede superar los 50MB.',
                'wrongExtension' => 'Tipo de archivo no permitido.',
                'skipOnEmpty' => true
            ],

            [['comentario'], 'validarContenido'],
        ];
    }


    public function validarContenido($attribute, $params)
    {
        if (empty($this->comentario) && empty($this->archivo)) {
            $this->addError($attribute, 'Debe ingresar un comentario o adjuntar un archivo.');
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'idhistorial' => 'ID',
            'idrecurso' => 'Nombre del Recurso',
            'comentario' => 'Comentarios',
            'idasignartarea' => 'Tarea',
            'idusuario' => 'Quien mando',
            'fecha_registro' => 'Fecha del comentario',
        ];
    }


    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->fecha_registro = date('Y-m-d H:i:s');
            }
            return true;
        }
        return false;
    }

    public function getRecurso()
    {
        return $this->hasOne(Recursos::class, ['idrecurso' => 'idrecurso']);
    }


    public function getNombreTipo()
    {
        return $this->tipo == 1 ? 'Consultor' : 'Cliente';
    }

    public function getAsignacion()
    {
        return $this->hasOne(Asignacion::class, ['idasignartarea' => 'idasignartarea']);
    }

    public function getUsuario()
    {
        return $this->hasOne(Usuarios::class, ['idusuario' => 'idusuario']);
    }


    public function subirArchivo($archivo, $recurso = null)
    {
        if ($archivo) {
            $this->archivoUpload = $archivo;

            // Valor por defecto
            $carpeta = 'imagen/materiales/desconocido';

            if ($recurso && $recurso->asignacion) {
                $cliente = null;
                $prefijo = 'proyecto';

                if ($recurso->asignacion->proyecto && $recurso->asignacion->proyecto->cliente) {
                    $cliente = $recurso->asignacion->proyecto->cliente;
                    $prefijo = 'proyecto';
                } elseif ($recurso->asignacion->campania && $recurso->asignacion->campania->cliente) {
                    $cliente = $recurso->asignacion->campania->cliente;
                    $prefijo = 'campania';
                }

                if ($cliente) {
                    $idCliente = $cliente->idusuario;
                    $empresa = $cliente->empresa ?: $cliente->nombrecompleto;
                    $empresaSegura = preg_replace('/[^a-zA-Z0-9_-]/', '_', $empresa);
                    $carpeta = "imagen/materiales/{$prefijo}_cliente{$idCliente}_{$empresaSegura}"; // ✅
                }
            }

            // Crear la carpeta si no existe
            $rutaCarpeta = Yii::getAlias('@webroot/' . $carpeta); //

            if (!is_dir($rutaCarpeta)) {
                mkdir($rutaCarpeta, 0775, true);
            }

            // Generar nombre de archivo seguro y único
            $nombreOriginal = $archivo->baseName;
            $nombreSeguro = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $nombreOriginal);
            $timestamp = date('Ymd_His');
            $nombreFinal = $nombreSeguro . '_' . $timestamp . '.' . $archivo->extension;

            $rutaFinal = $rutaCarpeta . '/' . $nombreFinal;

            if ($archivo->saveAs($rutaFinal)) {
                return $carpeta . '/' . $nombreFinal; // Ruta relativa para guardar en DB
            }
        }

        return false;
    }


}

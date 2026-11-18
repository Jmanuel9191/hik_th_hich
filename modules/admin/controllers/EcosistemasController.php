<?php

namespace app\modules\admin\controllers;

use app\components\InterfaceEcosistemas;
use app\components\InterfacePermisosService;
use app\models\Ecosistema;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Ecosistemas (las 10 áreas del Grupo).
 * Permisos y "solo mis ecosistemas" del gerente: los controla EcosistemaService.
 */
class EcosistemasController extends Controller
{
    private $ecosistemas;
    private $permisos;

    public function __construct($id, $module, InterfaceEcosistemas $ecosistemas, InterfacePermisosService $permisos, $config = [])
    {
        $this->ecosistemas = $ecosistemas;
        $this->permisos = $permisos;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'estado' => ['POST'],
                    'galeria-subir' => ['POST'],
                    'galeria-borrar' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'dataProvider' => $this->ecosistemas->listar(Yii::$app->request->queryParams),
            'filtro' => Yii::$app->request->get('f', []),
            'puedeCrear' => $this->permisos->tienePermiso('ecosistemas_crear'),
            'puedeEstado' => $this->permisos->tienePermiso('ecosistemas_estado'),
            'puedeGaleria' => $this->permisos->tienePermiso('ecosistemas_galeria'),
            'puedeEliminar' => $this->permisos->tienePermiso('ecosistemas_eliminar'),
        ]);
    }

    public function actionView($id)
    {
        $model = $this->ecosistemas->buscar($id);
        return $this->render('view', [
            'model' => $model,
            'historial' => $this->ecosistemas->historial($model->idecosistema),
            'puedeEditar' => $this->ecosistemas->puedeGestionar($model),
            'puedeEstado' => $this->permisos->tienePermiso('ecosistemas_estado'),
        ]);
    }

    public function actionCreate()
    {
        $model = new Ecosistema();
        $this->permisos->verificarPermiso('ecosistemas_crear');
        if (Yii::$app->request->isPost) {
            $r = $this->ecosistemas->guardar($model, Yii::$app->request->post());
            $this->avisar($r);
            if ($r['exito']) {
                return $this->redirect(['view', 'id' => $model->idecosistema]);
            }
            if (!$model->isNewRecord) {
                return $this->redirect(['update', 'id' => $model->idecosistema]);
            }
        }
        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->ecosistemas->buscar($id, 'ecosistemas_editar');
        if (Yii::$app->request->isPost) {
            $r = $this->ecosistemas->guardar($model, Yii::$app->request->post());
            $this->avisar($r);
            if ($r['exito']) {
                return $this->redirect(['view', 'id' => $model->idecosistema]);
            }
        }
        return $this->render('update', ['model' => $model]);
    }

    public function actionEstado($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->ecosistemas->cambiarEstado($id);
    }

    /** Eliminar (solo si no tiene proyectos asignados). Responde JSON. */
    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->ecosistemas->eliminar($id);
    }

    public function actionGaleria($id)
    {
        $model = $this->ecosistemas->buscar($id, 'ecosistemas_galeria');
        return $this->render('galeria', ['model' => $model]);
    }

    /** Kartik FileInput: respuesta JSON vacía = subida correcta. */
    public function actionGaleriaSubir($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->ecosistemas->subirGaleria($id);
    }

    public function actionGaleriaBorrar()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->ecosistemas->borrarImagenGaleria(Yii::$app->request->post('key'));
    }

    private function avisar(array $r)
    {
        Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'warning', 'message' => $r['mensaje']]);
    }
}

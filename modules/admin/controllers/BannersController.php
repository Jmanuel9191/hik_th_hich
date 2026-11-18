<?php

namespace app\modules\admin\controllers;

use app\components\InterfaceBanners;
use app\components\InterfacePermisosService;
use app\models\Banners;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Banners de la portada. Permisos banners_* (Superadmin y Editor de contenido por defecto).
 * Estado, orden y eliminar responden JSON (huk-listado.js).
 */
class BannersController extends Controller
{
    private $banners;
    private $permisos;

    public function __construct($id, $module, InterfaceBanners $banners, InterfacePermisosService $permisos, $config = [])
    {
        $this->banners = $banners;
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
                'actions' => ['estado' => ['POST'], 'mover' => ['POST'], 'delete' => ['POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->permisos->verificarPermiso('banners_listar');
        return $this->render('index', $this->banners->listar(Yii::$app->request->queryParams) + $this->puede());
    }

    public function actionView($id)
    {
        $this->permisos->verificarPermiso('banners_ver');
        return $this->render('view', [
            'model' => $this->banners->buscar($id),
            'historial' => $this->permisos->tienePermiso('banners_historial') ? $this->banners->historial($id) : null,
        ] + $this->puede());
    }

    public function actionCreate()
    {
        $this->permisos->verificarPermiso('banners_crear');
        return $this->formulario(new Banners(), 'create');
    }

    public function actionUpdate($id)
    {
        $this->permisos->verificarPermiso('banners_editar');
        return $this->formulario($this->banners->buscar($id), 'update');
    }

    public function actionEstado($id)
    {
        $this->permisos->verificarPermiso('banners_estado');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->banners->cambiarEstado($id);
    }

    public function actionMover($id, $direccion)
    {
        $this->permisos->verificarPermiso('banners_editar');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->banners->mover($id, $direccion);
    }

    public function actionDelete($id)
    {
        $this->permisos->verificarPermiso('banners_eliminar');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->banners->eliminar($id);
    }

    private function formulario(Banners $model, $vista)
    {
        if (Yii::$app->request->isPost) {
            $r = $this->banners->guardar($model, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect(['index']);
            }
        }
        return $this->render($vista, ['model' => $model]);
    }

    private function puede()
    {
        $p = $this->permisos;
        return ['puede' => [
            'crear' => $p->tienePermiso('banners_crear'),
            'editar' => $p->tienePermiso('banners_editar'),
            'estado' => $p->tienePermiso('banners_estado'),
            'eliminar' => $p->tienePermiso('banners_eliminar'),
        ]];
    }
}

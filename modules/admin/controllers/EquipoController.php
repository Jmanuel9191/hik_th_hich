<?php

namespace app\modules\admin\controllers;

use app\components\InterfaceEquipo;
use app\components\InterfacePermisosService;
use app\models\Equipo;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Equipo que se muestra en la web. Permisos equipo_* (Superadmin y Editor de contenido por defecto).
 * Estado, orden y eliminar responden JSON (huk-listado.js).
 */
class EquipoController extends Controller
{
    private $equipo;
    private $permisos;

    public function __construct($id, $module, InterfaceEquipo $equipo, InterfacePermisosService $permisos, $config = [])
    {
        $this->equipo = $equipo;
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
        $this->permisos->verificarPermiso('equipo_listar');
        return $this->render('index', $this->equipo->listar(Yii::$app->request->queryParams) + $this->puede());
    }

    public function actionView($id)
    {
        $this->permisos->verificarPermiso('equipo_ver');
        return $this->render('view', [
            'model' => $this->equipo->buscar($id),
            'historial' => $this->permisos->tienePermiso('equipo_historial') ? $this->equipo->historial($id) : null,
        ] + $this->puede());
    }

    public function actionCreate()
    {
        $this->permisos->verificarPermiso('equipo_crear');
        return $this->formulario(new Equipo(), 'create');
    }

    public function actionUpdate($id)
    {
        $this->permisos->verificarPermiso('equipo_editar');
        return $this->formulario($this->equipo->buscar($id), 'update');
    }

    public function actionEstado($id)
    {
        $this->permisos->verificarPermiso('equipo_estado');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->equipo->cambiarEstado($id);
    }

    public function actionMover($id, $direccion)
    {
        $this->permisos->verificarPermiso('equipo_editar');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->equipo->mover($id, $direccion);
    }

    public function actionDelete($id)
    {
        $this->permisos->verificarPermiso('equipo_eliminar');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->equipo->eliminar($id);
    }

    private function formulario(Equipo $model, $vista)
    {
        if (Yii::$app->request->isPost) {
            $r = $this->equipo->guardar($model, Yii::$app->request->post());
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
            'crear' => $p->tienePermiso('equipo_crear'),
            'editar' => $p->tienePermiso('equipo_editar'),
            'estado' => $p->tienePermiso('equipo_estado'),
            'eliminar' => $p->tienePermiso('equipo_eliminar'),
        ]];
    }
}

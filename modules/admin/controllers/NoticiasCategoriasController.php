<?php

namespace app\modules\admin\controllers;

use app\components\InterfaceNoticiaCategorias;
use app\components\InterfacePermisosService;
use app\models\NoticiaCategoria;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Blog → Categorías (permiso noticias_categorias). Lista con alta rápida arriba; estado, orden y eliminar en JSON (huk-listado.js).
 */
class NoticiasCategoriasController extends Controller
{
    private InterfaceNoticiaCategorias $categorias;
    private InterfacePermisosService $permisos;

    public function __construct($id, $module, InterfaceNoticiaCategorias $categorias, InterfacePermisosService $permisos, $config = [])
    {
        $this->categorias = $categorias;
        $this->permisos = $permisos;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['estado' => ['POST'], 'mover' => ['POST'], 'delete' => ['POST']]],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $this->permisos->verificarPermiso('noticias_categorias');
        return true;
    }

    /** Listado + formulario de alta. */
    public function actionIndex()
    {
        $model = new NoticiaCategoria();
        if (Yii::$app->request->isPost) {
            $r = $this->categorias->guardar($model, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect(['index']);
            }
        }
        return $this->render('index', $this->categorias->listar() + [
            'model' => $model,
            'historial' => $this->permisos->tienePermiso('noticias_historial') ? $this->categorias->historial() : null,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->categorias->buscar($id);
        if (Yii::$app->request->isPost) {
            $r = $this->categorias->guardar($model, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect(['index']);
            }
        }
        return $this->render('update', ['model' => $model]);
    }

    public function actionEstado($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->categorias->cambiarEstado($id);
    }

    public function actionMover($id, $direccion)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->categorias->mover($id, $direccion);
    }

    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->categorias->eliminar($id);
    }
}

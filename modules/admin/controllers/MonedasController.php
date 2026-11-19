<?php

namespace app\modules\admin\controllers;

use app\components\InterfaceMonedas;
use app\components\InterfacePermisosService;
use app\models\Moneda;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Configuración → Monedas de los proyectos. Permiso monedas_editar (Superadmin por defecto).
 */
class MonedasController extends Controller
{
    private $monedas;
    private $permisos;

    public function __construct($id, $module, InterfaceMonedas $monedas, InterfacePermisosService $permisos, $config = [])
    {
        $this->monedas = $monedas;
        $this->permisos = $permisos;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['estado' => ['POST'], 'delete' => ['POST']]],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $this->permisos->verificarPermiso('monedas_editar');
        return true;
    }

    public function actionIndex()
    {
        return $this->render('index', $this->monedas->listar());
    }

    public function actionCreate()
    {
        return $this->formulario(new Moneda());
    }

    public function actionUpdate($id)
    {
        return $this->formulario($this->monedas->buscar($id));
    }

    public function actionEstado($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->monedas->cambiarEstado($id);
    }

    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->monedas->eliminar($id);
    }

    private function formulario(Moneda $m)
    {
        if (Yii::$app->request->isPost) {
            $r = $this->monedas->guardar($m, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect(['index']);
            }
        }
        return $this->render('form', ['model' => $m]);
    }
}

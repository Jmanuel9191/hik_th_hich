<?php

namespace app\modules\admin\controllers;

use app\components\InterfaceConfiguracion;
use app\components\InterfacePermisosService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Configuración → Datos generales (título, contacto, redes, mapa).
 * Permiso: configuracion_editar (Superadmin y Editor de contenido).
 */
class DatosGeneralesController extends Controller
{
    private $configuracion;
    private $permisos;

    public function __construct($id, $module, InterfaceConfiguracion $configuracion, InterfacePermisosService $permisos, $config = [])
    {
        $this->configuracion = $configuracion;
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
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $this->permisos->verificarPermiso('configuracion_editar');
        return true;
    }

    public function actionIndex()
    {
        $form = $this->configuracion->formulario();
        if (Yii::$app->request->isPost) {
            if ($this->configuracion->guardar($form, Yii::$app->request->post())) {
                Yii::$app->session->setFlash('flash', ['type' => 'success', 'message' => 'Datos generales guardados.']);
                return $this->redirect(['index']);
            }
            Yii::$app->session->setFlash('flash', ['type' => 'error', 'message' => 'Revisá los campos marcados en rojo.']);
        }
        return $this->render('index', [
            'model' => $form,
            'historial' => $this->configuracion->historial(),
        ]);
    }
}

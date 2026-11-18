<?php

namespace app\modules\admin\controllers;

use app\components\InterfacePermisosService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Roles y permisos: tabla de casillas rol × permiso. SOLO el Superadmin entra.
 */
class RolesController extends Controller
{
    private $permisos;

    public function __construct($id, $module, InterfacePermisosService $permisos, $config = [])
    {
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
        $this->permisos->verificarAdministrador(); // cualquier otro rol vuelve al inicio
        return true;
    }

    public function actionIndex()
    {
        if (Yii::$app->request->isPost) {
            $n = $this->permisos->guardarMatriz((array) Yii::$app->request->post('p', []));
            Yii::$app->session->setFlash('flash', [
                'type' => $n ? 'success' : 'info',
                'message' => $n ? "Se guardaron {$n} cambio(s) de permisos." : 'No había cambios para guardar.',
            ]);
            return $this->redirect(['index']);
        }

        return $this->render('index', $this->permisos->matrizPermisos() + [
            'historial' => $this->permisos->historialPermisos(),
        ]);
    }
}

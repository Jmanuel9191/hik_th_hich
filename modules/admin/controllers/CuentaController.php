<?php

namespace app\modules\admin\controllers;
use app\components\InterfaceCuenta;
use app\models\CambioPasswordObligatorioForm;
use app\models\Forget;
use Yii;
use yii\web\Controller;
class CuentaController extends Controller
{

    private $cuentaService;
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],

        ];
    }



    public function __construct($id, $module, InterfaceCuenta $cuentaService, $config = [])
    {
        $this->cuentaService = $cuentaService;
        parent::__construct($id, $module, $config);
    }


    public function actionForget()
    {
        $this->layout = "login";
        $model = new Forget();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $resultadoRecuperacion = $this->cuentaService->recuperacionCuenta($model->email);
            Yii::$app->session->setFlash('success', ['message' => $resultadoRecuperacion['mensaje'], 'type' => 'success']);
            return $this->refresh();
        }

        return $this->render('forget', [
            'model' => $model,
        ]);
    }

    /**
     * Abre el enlace que llegó por correo y deja crear una contraseña nueva.
     * Estados de la vista: 'form' (pide la clave), 'listo' (ya cambió), 'invalido' (enlace vencido o usado).
     */
    public function actionRestablecer($token = null)
    {
        $this->layout = "login";

        $usuario = $this->cuentaService->buscarUsuarioPorTokenRecuperacion($token);
        if ($usuario === null) {
            return $this->render('restablecer', ['estado' => 'invalido', 'model' => null]);
        }

        $model = new CambioPasswordObligatorioForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $this->cuentaService->restablecerContrasena($usuario, $model->nueva_contrasena);
            return $this->render('restablecer', ['estado' => 'listo', 'model' => null]);
        }

        $model->nueva_contrasena = $model->confirmar_contrasena = null; // nunca devolver la clave al HTML
        return $this->render('restablecer', ['estado' => 'form', 'model' => $model]);
    }

}

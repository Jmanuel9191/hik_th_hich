<?php

namespace app\modules\cuenta\controllers;

use app\components\InterfaceInversores;
use app\components\InterfaceNda;
use app\components\Rol;
use app\models\NdaAceptacion;
use app\models\Proyecto;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * Panel del inversor: estado de la solicitud y acuerdo de confidencialidad.
 * Sin aprobación no se pasa de "en revisión"; aprobado sin aceptar la versión vigente del NDA → se le pide aceptarla.
 * (El banco de proyectos para el inversor se suma en el paquete de la web.)
 */
class PanelController extends Controller
{
    private $inversores;
    private $nda;

    public function __construct($id, $module, InterfaceInversores $inversores, InterfaceNda $nda, $config = [])
    {
        $this->inversores = $inversores;
        $this->nda = $nda;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['nda' => ['GET', 'POST']]],
        ];
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $u = Yii::$app->user->identity;
        if ((int) $u->idrol !== Rol::INVERSOR || $this->inversores->motivoSinAcceso($u) !== null) {
            Yii::$app->user->logout();
            $this->redirect(['/cuenta/acceso/ingresar']);
            return false;
        }
        return true;
    }

    public function actionIndex()
    {
        $this->view->title = 'Mi cuenta';
        $inv = $this->inversores->perfil(Yii::$app->user->id);
        if ($inv->estado_solicitud === 'aprobado' && !NdaAceptacion::aceptoVigente($inv->idusuario)) {
            return $this->redirect(['nda']);
        }
        $proyectos = [];
        if ($inv->estado_solicitud === 'aprobado') {
            foreach ($inv->areas as $area) {
                $proyectos[$area->idecosistema] = (int) Proyecto::publicados([$area->idecosistema])->count();
            }
        }
        return $this->render('index', ['inversor' => $inv, 'proyectos' => $proyectos]);
    }

    public function actionNda()
    {
        $this->view->title = 'Acuerdo de confidencialidad';
        $inv = $this->inversores->perfil(Yii::$app->user->id);
        if ($inv->estado_solicitud !== 'aprobado') {
            return $this->redirect(['index']);
        }
        $vigente = $this->nda->vigente();
        if ($vigente === null || NdaAceptacion::aceptoVigente($inv->idusuario)) {
            return $this->redirect(['index']);
        }
        $error = null;
        if (Yii::$app->request->isPost) {
            $r = $this->nda->aceptar($inv->idusuario, Yii::$app->request->post('firma', ''), Yii::$app->request->post('acepto') === '1');
            if ($r['exito']) {
                Yii::$app->session->setFlash('cuenta', ['success', $r['mensaje']]);
                return $this->redirect(['index']);
            }
            $error = $r['mensaje'];
        }
        $anterior = NdaAceptacion::find()->where(['idusuario' => $inv->idusuario])->exists();
        return $this->render('nda', ['version' => $vigente, 'inversor' => $inv, 'error' => $error, 'anterior' => $anterior]);
    }
}

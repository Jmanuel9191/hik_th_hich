<?php

namespace app\modules\cuenta\controllers;

use app\components\InterfaceInteresInversion;
use app\components\InterfaceInversores;
use app\components\InterfaceNda;
use app\components\InterfacePerfilInversor;
use app\components\InterfaceProyectosWeb;
use app\components\Rol;
use app\models\ContrasenaInversorForm;
use app\models\NdaAceptacion;
use app\models\PerfilInversorForm;
use app\models\QuieroInvertirForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Mi cuenta del inversor: perfil (foto, datos, contraseña), estado de la solicitud, áreas, inversiones
 * y acuerdo de confidencialidad. Aprobado sin aceptar la versión vigente del NDA → se le pide aceptarla.
 * Todo actúa sobre el usuario logueado (nunca recibe el id de otro inversor). La foto y los comprobantes
 * son privados: se sirven desde acá. Los proyectos se ven en /proyectos (ProyectosController).
 */
class PanelController extends Controller
{
    private $inversores;
    private $nda;
    private $perfil;
    private $proyectosWeb;
    private $interes;

    public function __construct($id, $module, InterfaceInversores $inversores, InterfaceNda $nda, InterfacePerfilInversor $perfil,
        InterfaceProyectosWeb $proyectosWeb, InterfaceInteresInversion $interes, $config = [])
    {
        $this->proyectosWeb = $proyectosWeb;
        $this->interes = $interes;
        $this->inversores = $inversores;
        $this->nda = $nda;
        $this->perfil = $perfil;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'nda' => ['GET', 'POST'], 'datos' => ['POST'], 'contrasena' => ['POST'], 'foto-subir' => ['POST'],
                'foto-quitar' => ['POST'], 'areas' => ['POST'], 'invertir' => ['POST'],
            ]],
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

    const PESTANAS = ['resumen', 'datos', 'inversiones', 'seguridad'];

    public function actionIndex($tab = 'resumen')
    {
        $inv = $this->inversores->perfil(Yii::$app->user->id);
        if ($inv->estado_solicitud === 'aprobado' && !NdaAceptacion::aceptoVigente($inv->idusuario)) {
            return $this->redirect(['nda']);
        }
        return $this->mostrar(in_array($tab, self::PESTANAS, true) ? $tab : 'resumen');
    }

    /** Mis datos: solo los campos que permite PerfilInversorForm. */
    public function actionDatos()
    {
        $inv = $this->inversores->perfil(Yii::$app->user->id);
        $form = PerfilInversorForm::desde($inv);
        $r = $this->perfil->guardarDatos($inv, $form, Yii::$app->request->post());
        return $r['exito'] ? $this->volver($r, 'datos') : $this->mostrar('datos', $form, null, $r['mensaje']);
    }

    public function actionContrasena()
    {
        $inv = $this->inversores->perfil(Yii::$app->user->id);
        $form = new ContrasenaInversorForm();
        $r = $this->perfil->cambiarContrasena($inv, $form, Yii::$app->request->post());
        if (!$r['exito']) {
            $form->actual = $form->nueva = $form->nueva2 = null; // nunca se devuelven contraseñas al navegador
            return $this->mostrar('seguridad', null, $form, $r['mensaje']);
        }
        Yii::$app->user->switchIdentity($inv->usuario); // la sesión actual sigue abierta con la clave nueva
        return $this->volver($r, 'seguridad');
    }

    public function actionFotoSubir()
    {
        return $this->volver($this->perfil->subirFoto($this->inversores->perfil(Yii::$app->user->id)));
    }

    public function actionFotoQuitar()
    {
        return $this->volver($this->perfil->quitarFoto($this->inversores->perfil(Yii::$app->user->id)));
    }

    /** Su foto (privada). */
    public function actionFoto()
    {
        $ruta = $this->perfil->archivoFoto((int) Yii::$app->user->id);
        if ($ruta === null) {
            throw new NotFoundHttpException('Sin foto.');
        }
        Yii::$app->response->headers->set('Cache-Control', 'private, max-age=86400');
        Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
        return Yii::$app->response->sendFile($ruta, 'foto.webp', ['inline' => true, 'mimeType' => 'image/webp']);
    }

    public function actionAreas()
    {
        $inv = $this->inversores->perfil(Yii::$app->user->id);
        return $this->volver($this->perfil->pedirAreas($inv, (array) Yii::$app->request->post('areas', [])), 'resumen');
    }

    /**
     * "Quiero invertir" desde la página del proyecto (lo manda el modal por AJAX). Responde JSON:
     * exito, mensaje, errores (id del campo => texto) y, si salió bien, el bloque nuevo para la página.
     */
    public function actionInvertir($slug)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $acceso = $this->proyectosWeb->acceso();
        if ($acceso['estado'] !== InterfaceProyectosWeb::OK) {
            return ['exito' => false, 'mensaje' => 'Para enviar su interés, primero acepte el acuerdo de confidencialidad.'];
        }
        $p = $this->proyectosWeb->porSlug($acceso, (string) $slug); // 404 si no es de sus áreas o no está publicado
        $form = new QuieroInvertirForm();
        $r = $this->interes->enviar($acceso['inversor'], $p, $form, Yii::$app->request->post());
        $errores = [];
        foreach ($form->getFirstErrors() as $campo => $texto) {
            $errores['quieroinvertirform-' . $campo] = $texto;
        }
        $zona = fn($lugar) => $this->renderPartial('@app/views/proyectos/_interes', [
            'proyecto' => $p, 'previo' => $r['interes'], 'puede' => $this->interes->puedeEnviar($p, $r['interes']), 'lugar' => $lugar]);
        return ['exito' => $r['exito'], 'mensaje' => $r['mensaje'], 'errores' => $errores,
            'zonas' => $r['exito'] ? ['hero' => $zona('hero'), 'pie' => $zona('pie')] : null];
    }

    /** Comprobante de un pago suyo (privado, se ve en el navegador). */
    public function actionComprobante($id)
    {
        $a = $this->perfil->archivoComprobante((int) Yii::$app->user->id, (int) $id);
        Yii::$app->response->headers->set('Cache-Control', 'private, no-store');
        Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
        return Yii::$app->response->sendFile($a['ruta'], $a['nombre'], array_filter(['inline' => true, 'mimeType' => $a['mime']]));
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
                return $this->redirect(Yii::$app->user->getReturnUrl(['index'])); // si venía de un proyecto, vuelve ahí
            }
            $error = $r['mensaje'];
        }
        $anterior = NdaAceptacion::find()->where(['idusuario' => $inv->idusuario])->exists();
        return $this->render('nda', ['version' => $vigente, 'inversor' => $inv, 'error' => $error, 'anterior' => $anterior]);
    }

    /* ---------------- Ayudas ---------------- */

    /** Mi cuenta con la pestaña $tab abierta (y los formularios con errores, si vienen). */
    private function mostrar($tab = 'resumen', ?PerfilInversorForm $datos = null, ?ContrasenaInversorForm $clave = null, $error = null)
    {
        $this->view->title = 'Mi cuenta';
        $r = $this->perfil->resumen((int) Yii::$app->user->id);
        if ($error !== null) {
            Yii::$app->session->setFlash('cuenta', ['danger', $error]);
        }
        return $this->render('index', $r + [
            'tab' => $tab,
            'formDatos' => $datos ?? PerfilInversorForm::desde($r['inversor']),
            'formClave' => $clave ?? new ContrasenaInversorForm(),
            'intereses' => $this->interes->deInversor((int) Yii::$app->user->id),
        ]);
    }

    /** Post/Redirect/Get con el aviso y la pestaña. */
    private function volver(array $r, $tab = 'resumen')
    {
        Yii::$app->session->setFlash('cuenta', [$r['exito'] ? 'success' : 'danger', $r['mensaje']]);
        return $this->redirect(['index', 'tab' => $tab]);
    }
}

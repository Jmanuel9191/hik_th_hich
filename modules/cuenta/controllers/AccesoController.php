<?php

namespace app\modules\cuenta\controllers;

use app\components\InterfaceAuth;
use app\components\InterfaceCorreos;
use app\components\InterfaceInversores;
use app\components\Rol;
use app\models\Ecosistema;
use app\models\LoginForm;
use app\models\SolicitudInversorForm;
use Yii;
use yii\base\DynamicModel;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * Acceso del inversor: registro (solicitud), confirmar correo, ingresar, salir, olvidé mi contraseña
 * y crear contraseña (recuperación o activación de una cuenta cargada desde el panel).
 */
class AccesoController extends Controller
{
    private $inversores;
    private $auth;
    private $correos;

    public function __construct($id, $module, InterfaceInversores $inversores, InterfaceAuth $auth, InterfaceCorreos $correos, $config = [])
    {
        $this->inversores = $inversores;
        $this->auth = $auth;
        $this->correos = $correos;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return ['verbs' => ['class' => VerbFilter::class, 'actions' => ['salir' => ['POST'], 'reenviar' => ['POST']]]];
    }

    public function actionRegistro()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/cuenta/panel/index']);
        }
        $this->view->title = 'Quiero ser socio';
        $form = new SolicitudInversorForm(['scenario' => 'web']);
        if ($form->load(Yii::$app->request->post())) {
            $r = $this->inversores->registrar($form);
            if ($r['exito']) {
                return $this->mensaje('envelope-open-text', 'Revisá tu correo',
                    'Te enviamos un enlace a <strong>' . \yii\helpers\Html::encode($form->email) . '</strong> para confirmar tu correo. '
                    . 'Cuando lo confirmes, tu solicitud pasa a revisión y nuestro equipo se va a contactar con vos.',
                    'Si no lo ves en unos minutos, revisá la carpeta de spam.', true, $form->email);
            }
            if (!$form->hasErrors()) {
                Yii::$app->session->setFlash('cuenta', ['danger', $r['mensaje']]);
            }
        }
        $form->contrasena = $form->contrasena2 = null;
        return $this->render('registro', ['model' => $form, 'ecosistemas' => Ecosistema::lista(true)]);
    }

    public function actionVerificar($token = null)
    {
        $this->view->title = 'Confirmar correo';
        $u = $this->inversores->verificarCorreo($token);
        if ($u === null) {
            return $this->mensaje('circle-exclamation', 'El enlace no es válido',
                'Puede que haya vencido (dura 24 horas) o que ya lo hayas usado. Pedí uno nuevo con tu correo.', null, true);
        }
        return $this->mensaje('circle-check', '¡Correo confirmado!',
            'Gracias, <strong>' . \yii\helpers\Html::encode($u->nombrecompleto) . '</strong>. Tu solicitud ya está <strong>en revisión</strong>: '
            . 'nuestro equipo se va a comunicar con vos para conocer tu perfil. Te avisamos por correo cuando esté aprobada.',
            null, false, null, ['Ingresar a mi cuenta', ['/cuenta/acceso/ingresar']]);
    }

    public function actionReenviar()
    {
        $this->view->title = 'Reenviar confirmación';
        $texto = $this->inversores->reenviarVerificacion(Yii::$app->request->post('email', ''));
        return $this->mensaje('envelope', 'Listo', $texto, null, false, null, ['Ingresar', ['/cuenta/acceso/ingresar']]);
    }

    public function actionIngresar()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(['/cuenta/panel/index']);
        }
        $this->view->title = 'Acceso inversores';
        $model = new LoginForm(['rememberMe' => false]);
        $sinConfirmar = null;
        if ($model->load(Yii::$app->request->post())) {
            if ($this->auth->login($model, $this->correos, [Rol::INVERSOR])) {
                $motivo = $this->inversores->motivoSinAcceso(Yii::$app->user->identity);
                if ($motivo === null) {
                    Yii::$app->session->regenerateID(true);
                    return $this->redirect(Yii::$app->user->getReturnUrl(['/cuenta/panel/index']));
                }
                $sinConfirmar = Yii::$app->user->identity->email;
                Yii::$app->user->logout(false);
                $model->addError('password', $motivo);
            }
        }
        $model->password = null;
        return $this->render('ingresar', ['model' => $model, 'sinConfirmar' => $sinConfirmar]);
    }

    public function actionSalir()
    {
        Yii::$app->user->logout();
        return $this->redirect(['/cuenta/acceso/ingresar']);
    }

    public function actionOlvide()
    {
        $this->view->title = 'Recuperar contraseña';
        $model = new DynamicModel(['email']);
        $model->addRule('email', 'required', ['message' => 'Escribí tu correo.'])->addRule('email', 'email');
        $model->setAttributeLabels(['email' => 'Correo electrónico']);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            return $this->mensaje('envelope', 'Revisá tu correo', $this->inversores->pedirRecuperacion($model->email),
                'Si no lo ves, revisá la carpeta de spam.');
        }
        return $this->render('olvide', ['model' => $model]);
    }

    public function actionRestablecer($token = null, $activar = 0)
    {
        $activar = (int) $activar === 1;
        $this->view->title = $activar ? 'Activá tu cuenta' : 'Nueva contraseña';
        $u = $this->inversores->usuarioPorToken($token);
        if ($u === null) {
            return $this->mensaje('circle-exclamation', 'El enlace no es válido',
                'Puede que haya vencido o que ya lo hayas usado. Pedí uno nuevo desde «¿Olvidaste tu contraseña?».', null, false, null,
                ['¿Olvidaste tu contraseña?', ['/cuenta/acceso/olvide']]);
        }
        $model = new DynamicModel(['contrasena', 'contrasena2']);
        $model->addRule(['contrasena', 'contrasena2'], 'required')
            ->addRule('contrasena', 'string', ['max' => 72])
            ->addRule('contrasena', 'match', ['pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/',
                'message' => 'Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.'])
            ->addRule('contrasena2', 'compare', ['compareAttribute' => 'contrasena', 'message' => 'Las contraseñas no coinciden.']);
        $model->setAttributeLabels(['contrasena' => 'Contraseña nueva', 'contrasena2' => 'Repetila']);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $this->inversores->guardarContrasena($u, $model->contrasena);
            Yii::$app->session->setFlash('cuenta', ['success', $activar ? 'Tu cuenta está activa. Ya podés ingresar.' : 'Contraseña cambiada. Ya podés ingresar.']);
            return $this->redirect(['/cuenta/acceso/ingresar']);
        }
        $model->contrasena = $model->contrasena2 = null;
        return $this->render('restablecer', ['model' => $model, 'activar' => $activar, 'nombre' => $u->nombrecompleto]);
    }

    /** Página de aviso (correo enviado, enlace inválido, etc.). */
    private function mensaje($icono, $titulo, $html, $nota = null, $reenviar = false, $email = null, $boton = null)
    {
        return $this->render('mensaje', compact('icono', 'titulo', 'html', 'nota', 'reenviar', 'email', 'boton'));
    }
}

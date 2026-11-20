<?php

namespace app\modules\cuenta\controllers;

use app\components\InterfaceAuth;
use app\components\InterfaceBanners;
use app\components\InterfaceConfiguracion;
use app\components\InterfaceCorreos;
use app\components\InterfaceInversores;
use app\components\InterfaceTerminos;
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
 * Ingreso y registro llevan el banner de Panel → Banners (pestañas "Acceso inversores" y "Quiero ser socio")
 * y los datos de contacto de Panel → Configuración (teléfono, WhatsApp y correo).
 */
class AccesoController extends Controller
{
    private $inversores;
    private $auth;
    private $correos;
    private InterfaceBanners $banners;
    private InterfaceConfiguracion $configuracion;
    private InterfaceTerminos $terminos;

    /** Clave de sesión con los datos para la pantalla "Solicitud enviada" (después de redirigir). */
    const SESION_ENVIADA = 'cuenta_solicitud_enviada';

    public function __construct($id, $module, InterfaceInversores $inversores, InterfaceAuth $auth, InterfaceCorreos $correos,
        InterfaceBanners $banners, InterfaceConfiguracion $configuracion, InterfaceTerminos $terminos, $config = [])
    {
        $this->inversores = $inversores;
        $this->auth = $auth;
        $this->correos = $correos;
        $this->banners = $banners;
        $this->configuracion = $configuracion;
        $this->terminos = $terminos;
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
        // Al salir del campo Correo se revisa ya mismo si tiene cuenta (validación AJAX solo de ese campo)
        if (Yii::$app->request->isAjax && Yii::$app->request->post('ajax') === 'registro-form') {
            $form->load(Yii::$app->request->post());
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($form, ['email']);
        }
        if ($form->load(Yii::$app->request->post())) {
            $r = $this->inversores->registrar($form);
            if ($r['exito']) {
                // Post/Redirect/Get: al recargar la pantalla de "enviada" no se manda la solicitud de nuevo
                Yii::$app->session->set(self::SESION_ENVIADA, ['nombre' => (string) $form->nombre, 'email' => (string) $form->email]);
                return $this->redirect(['enviada']);
            }
            if (!$form->hasErrors()) {
                Yii::$app->session->setFlash('cuenta', ['danger', $r['mensaje']]);
            }
        }
        $form->contrasena = $form->contrasena2 = null;
        return $this->render('registro', [
            'model' => $form,
            'ecosistemas' => Ecosistema::lista(true),
            'encabezado' => $this->banners->encabezado('cuenta-registro'),
            'contacto' => $this->contacto(),
            'monedas' => \app\models\Moneda::find()->select(['codigo', 'simbolo', 'nombre', 'idmoneda'])->where(['estado' => '1'])
                ->orderBy(['orden' => SORT_ASC, 'codigo' => SORT_ASC])->indexBy('idmoneda')->asArray()->all(),
            'terminos' => $this->terminos->vigente(),
        ]);
    }

    /** "¡Solicitud enviada!": qué sigue (confirmar el correo, revisión, aviso de aprobación) y cómo comunicarse. */
    public function actionEnviada()
    {
        $datos = Yii::$app->session->get(self::SESION_ENVIADA);
        // Ya entró o ya confirmó el correo: la pantalla de "solicitud enviada" no tiene más sentido.
        if (!Yii::$app->user->isGuest) {
            Yii::$app->session->remove(self::SESION_ENVIADA);
            return $this->redirect(['/cuenta/panel/index']);
        }
        if (!is_array($datos)) {
            return $this->redirect(['registro']);
        }
        if ($this->inversores->correoConfirmado($datos['email'] ?? '')) {
            Yii::$app->session->remove(self::SESION_ENVIADA);
            Yii::$app->session->setFlash('cuenta', ['info', 'Su correo ya está confirmado. Ingrese con su cuenta para ver el estado de su solicitud.']);
            return $this->redirect(['ingresar']);
        }
        $this->view->title = 'Solicitud enviada';
        return $this->render('enviada', $datos + [
            'encabezado' => $this->banners->encabezado('cuenta-registro'),
            'contacto' => $this->contacto(),
        ]);
    }

    public function actionVerificar($token = null)
    {
        $this->view->title = 'Confirmar correo';
        $u = $this->inversores->verificarCorreo($token);
        Yii::$app->session->remove(self::SESION_ENVIADA);
        if ($u === null) {
            return $this->mensaje('circle-exclamation', 'El enlace no es válido',
                'Puede que haya vencido (dura 24 horas) o que ya lo haya usado. Pida uno nuevo con su correo.', null, true);
        }
        return $this->mensaje('circle-check', '¡Correo confirmado!',
            'Gracias, <strong>' . \yii\helpers\Html::encode($u->nombrecompleto) . '</strong>. Su solicitud ya está <strong>en revisión</strong>: '
            . 'nuestro equipo se comunicará con usted para conocer su perfil. Le avisaremos por correo cuando esté aprobada.',
            null, false, null, ['Volver al inicio', ['/site/index']]);
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
                $estado = $this->inversores->estadoSinAcceso(Yii::$app->user->identity);
                $sinConfirmar = Yii::$app->user->identity->email;
                Yii::$app->user->logout(false);
                if ($estado !== null) { // en revisión o rechazado: su estado y cómo comunicarse (sin entrar)
                    $this->view->title = $estado['estado'] === 'rechazado' ? 'Solicitud rechazada' : 'Solicitud en revisión';
                    return $this->render('estado', $estado + ['contacto' => $this->contacto()]);
                }
                $model->addError('password', $motivo);
            }
        }
        $model->password = null;
        return $this->render('ingresar', [
            'model' => $model,
            'sinConfirmar' => $sinConfirmar,
            'encabezado' => $this->banners->encabezado('cuenta-ingreso'),
        ]);
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
        $model->addRule('email', 'required', ['message' => 'Escriba su correo.'])->addRule('email', 'email');
        $model->setAttributeLabels(['email' => 'Correo electrónico']);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            return $this->mensaje('envelope', 'Revise su correo', $this->inversores->pedirRecuperacion($model->email),
                'Si no lo ve, revise la carpeta de spam.');
        }
        return $this->render('olvide', ['model' => $model]);
    }

    public function actionRestablecer($token = null, $activar = 0)
    {
        $activar = (int) $activar === 1;
        $this->view->title = $activar ? 'Active su cuenta' : 'Nueva contraseña';
        $u = $this->inversores->usuarioPorToken($token);
        if ($u === null) {
            return $this->mensaje('circle-exclamation', 'El enlace no es válido',
                'Puede que haya vencido o que ya lo haya usado. Pida uno nuevo desde «¿Olvidó su contraseña?».', null, false, null,
                ['¿Olvidó su contraseña?', ['/cuenta/acceso/olvide']]);
        }
        $model = new DynamicModel(['contrasena', 'contrasena2']);
        $model->addRule(['contrasena', 'contrasena2'], 'required')
            ->addRule('contrasena', 'string', ['max' => 72])
            ->addRule('contrasena', 'match', ['pattern' => '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/',
                'message' => 'Mínimo 8 caracteres, con mayúscula, minúscula, número y símbolo.'])
            ->addRule('contrasena2', 'compare', ['compareAttribute' => 'contrasena', 'message' => 'Las contraseñas no coinciden.']);
        $model->setAttributeLabels(['contrasena' => 'Contraseña nueva', 'contrasena2' => 'Repítala']);
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $this->inversores->guardarContrasena($u, $model->contrasena);
            Yii::$app->session->setFlash('cuenta', ['success', $activar ? 'Su cuenta está activa. Ya puede ingresar.' : 'Contraseña cambiada. Ya puede ingresar.']);
            return $this->redirect(['/cuenta/acceso/ingresar']);
        }
        $model->contrasena = $model->contrasena2 = null;
        return $this->render('restablecer', ['model' => $model, 'activar' => $activar, 'nombre' => $u->nombrecompleto]);
    }

    /**
     * Teléfono, WhatsApp y correo de Panel → Configuración, con sus enlaces (vacío = no se muestra).
     * El WhatsApp se guarda en la columna fax_empresa; si está vacía se usa el teléfono.
     * @return array [['icono', 'texto', 'valor', 'url'], …]
     */
    private function contacto(): array
    {
        $d = $this->configuracion->paraWeb()['datos'];
        $numeros = static fn(string $t): string => preg_replace('/[^0-9+]/', '', $t);
        $tel = $d['telefono_empresa'] ?? '';
        $wa = ($d['fax_empresa'] ?? '') !== '' ? $d['fax_empresa'] : $tel;
        $email = $d['email'] ?? '';
        return array_values(array_filter([
            $wa !== '' ? ['fa-brands fa-whatsapp', 'WhatsApp', 'Escríbanos', 'https://wa.me/' . ltrim($numeros($wa), '+')] : null,
            $tel !== '' ? ['fa-solid fa-phone', 'Llámenos', $tel, 'tel:' . $numeros($tel)] : null,
            $email !== '' ? ['fa-solid fa-envelope', 'Correo', $email, 'mailto:' . $email] : null,
        ]));
    }

    /** Página de aviso (correo enviado, enlace inválido, etc.). */
    private function mensaje($icono, $titulo, $html, $nota = null, $reenviar = false, $email = null, $boton = null)
    {
        return $this->render('mensaje', compact('icono', 'titulo', 'html', 'nota', 'reenviar', 'email', 'boton'));
    }
}

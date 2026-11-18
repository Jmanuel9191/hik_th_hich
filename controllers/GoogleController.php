<?php

namespace app\controllers;
use app\components\AuthService;
use app\components\InterfaceAdmin;
use app\components\InterfaceCuenta;
use app\components\InterfaceGoogle;
use app\components\InterfaceLib;
use app\models\LoginWeb;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\components\InterfaceComponents;

class GoogleController extends Controller
{
    private $googlecalendar;
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],

        ];
    }

    public function __construct($id, $module, InterfaceGoogle $googlecalendar,$config = [])
    {
        $this->googlecalendar = $googlecalendar;
        parent::__construct($id, $module, $config);
    }




    public function actionAuth()
    {

        $this->googlecalendar->verificarUsuarioLogueado();
        Yii::$app->session->set('google_user_id', Yii::$app->user->id);
        return $this->redirect($this->googlecalendar->getAuthUrl());
    }



    public function actionCallback()
    {
        // Verificamos si el usuario está logueado usando tu método
        $this->googlecalendar->verificarUsuarioLogueado();

        // Intentamos recuperar el ID guardado en sesión (por si la sesión se perdió durante la redirección de Google)
        $usuario = Yii::$app->user->identity;

        if (!$usuario) {
            $userId = Yii::$app->session->get('google_user_id');
            if ($userId) {
                $usuario = \app\models\Usuarios::findOne($userId);
                if ($usuario) {
                    Yii::$app->user->login($usuario);
                }
            }
        }

        // Si todavía no hay usuario, redirigimos al login
        if (!$usuario) {
            Yii::$app->session->setFlash('error', 'No se pudo recuperar la sesión del usuario. Intente nuevamente.');
            return $this->redirect(['site/login']);
        }

        // Ya tenemos al usuario autenticado, procesamos el token
        $code = Yii::$app->request->get('code');

        if ($code) {
            $token = $this->googlecalendar->fetchAccessToken($code);
            // 🔁 Este chequeo es CLAVE para que Google te permita renovar el token más adelante
            if (!isset($token['refresh_token'])) {
                // Si no vino, intentá preservar el anterior (si ya estaba guardado)
                $anterior = json_decode($usuario->google_token, true);
                if (isset($anterior['refresh_token'])) {
                    $token['refresh_token'] = $anterior['refresh_token'];
                }
            }

            $usuario->google_token = json_encode($token);

            if ($usuario->save()) {
                Yii::$app->session->setFlash('flash', [
                    'type' => 'success',
                    'message' => 'Cuenta de Google vinculada correctamente.'
                ]);
            } else {
                Yii::$app->session->setFlash('flash', [
                    'type' => 'error',
                    'message' => 'Ocurrió un error al guardar el token.'
                ]);
            }
        } else {
            Yii::$app->session->setFlash('flash', [
                'type' => 'error',
                'message' => 'No se recibió el código de autorización'
            ]);
        }

        // Eliminamos el ID temporal de sesión si existe
        Yii::$app->session->remove('google_user_id');

        // Redirigimos según tipo de usuario
        return match ($usuario->idrol) {
            1 => $this->redirect(['/administrador/cuenta']),
            2 => $this->redirect(['/gestor/cuenta']),
            3 => $this->redirect(['/consultor/cuenta']),
            4 => $this->redirect(['/cliente/cuenta']),
            default => $this->redirect(['site/index']),
        };
    }


    public function actionCrearMeet()
    {
        $usuario = Yii::$app->user->identity;
        $titulo = 'Reunión con cliente';
        $ahora = new \DateTime('now', new \DateTimeZone($usuario->zona_horaria ?? 'America/La_Paz'));
        $fin = clone $ahora;
        $fin->modify('+1 hour');

        $link = $this->googlecalendar->crearEventoConMeet($usuario, $titulo, '', $ahora->format(\DateTime::RFC3339), $fin->format(DateTime::RFC3339));

        return $this->asJson(['link' => $link]);
    }






    public function actionDesvincular()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('flash', [
                'type' => 'error',
                'message' => 'Debes iniciar sesión para desvincular tu cuenta de Google.'
            ]);
            return $this->redirect(['site/login']);
        }

        $usuario = Yii::$app->user->identity;

        if ($usuario) {
            $usuario->google_token = null;

            if ($usuario->save()) {

                Yii::$app->session->setFlash('flash', [
                    'type' => 'success',
                    'message' => 'Tu cuenta de Google fue desvinculada correctamente.'
                ]);


            } else {
                Yii::$app->session->setFlash('flash', [
                    'type' => 'error',
                    'message' => 'Ocurrió un error al desvincular la cuenta'
                ]);
            }
        }

        return match ($usuario->idrol) {
            1 => $this->redirect(['/administrador/cuenta']),
            2 => $this->redirect(['/gestor/cuenta']),
            3 => $this->redirect(['/consultor/cuenta']),
            4 => $this->redirect(['/cliente/cuenta']),
            default => $this->redirect(['site/index']),
        };
    }




}




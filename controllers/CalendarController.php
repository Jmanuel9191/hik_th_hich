<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Url;
use yii\web\Response;
use app\components\InterfaceCalendario;
use app\components\InterfaceGoogle;
use app\components\InterfacePermisosService;
use app\models\Usuarios;

class CalendarController extends Controller
{
    private $calendarioService;
    private $googleCalendarService;
    private $permisosService;

    public function __construct(
        $id,
        $module,
        InterfaceCalendario $calendarioService,
        InterfacePermisosService $permisosService,
        InterfaceGoogle $googleCalendarService,
        $config = []
    ) {
        $this->calendarioService = $calendarioService;
        $this->permisosService = $permisosService;
        $this->googleCalendarService = $googleCalendarService;
        parent::__construct($id, $module, $config);
    }

    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', [
            'render' => 'index',
        ]);
    }

    public function actionListgeneral()
    {
        $model = $this->permisosService->verificarRol(['administrador', 'gestor']);
        return $this->render('index', [
            'model' => $model,
            'render' => 'listgeneral',
        ]);
    }

    public function actionMicalendario()
    {
        $usuario = $this->permisosService->verificarRol(['consultor', 'cliente']);
        $eventos = $this->calendarioService->obtenerEventosPorUsuario($usuario);

        $hashesActuales = [];
        foreach ($eventos as $e) {
            $hashesActuales[$e['title'] . '|' . $e['start']] = true;
        }

        $sincronizados = $this->googleCalendarService->obtenerEventosSincronizados($usuario);
        $nuevos = array_diff_key($hashesActuales, $sincronizados);

        $this->calendarioService->limpiarFlashes();

        return $this->render('index', [
            'hayEventosNuevos' => !empty($nuevos),
            'model' => $usuario,
            'render' => 'micalendario',
            'usuario' => $usuario,
        ]);
    }

    public function actionEventosMios()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->calendarioService->obtenerEventosPorUsuario(Yii::$app->user->identity);
    }

    public function actionEventos()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->calendarioService->obtenerEventosGlobales(); // Podés definir este método si necesitás
    }

    public function actionSincronizarGoogle()
    {
        $usuario = Yii::$app->user->identity;

        if (empty($usuario->google_token)) {
            Yii::$app->session->setFlash('error', 'Primero debes vincular tu cuenta de Google.');
            return $this->redirect(['micalendario']);
        }

        $resultado = $this->googleCalendarService->sincronizarEventos($usuario);

        Yii::$app->session->setFlash($resultado['flash_tipo'], $resultado['mensaje']);
        if (isset($resultado['cantidad'])) {
            Yii::$app->session->setFlash('flashdone', $resultado['cantidad']);
        }

        return $this->redirect(['micalendario']);
    }

}

<?php

namespace app\modules\admin\controllers;
use app\controllers\BaseController;
use app\models\LoginForm;
use app\models\Ordenes;
use app\models\OrdenDetalle;
use app\models\OrdenPagos;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
/**
 * Default controller for the `admin` module
 */
class DefaultController extends BaseController
{
    private $authService;
    private $correoService;
    /**
     * Renders the index view for the module
     * @return string
     */
      public function init()
    {
        parent::init();
        $this->authService = Yii::$container->get('app\components\InterfaceAuth');
        $this->correoService = Yii::$container->get('app\components\InterfaceCorreos'); // <- esto sí va en la otra propiedad
    }
    
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'index'],
                'rules' => [

                    [
                        'allow' => true,
                        'actions' => ['index', 'logout'],
                        'roles' => ['@'],
                    ],
                ],
            ],

           'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
  public function actionIndex()
    {
        date_default_timezone_set('America/La_Paz');
        $hoy      = date('Y-m-d');
        $mesDesde = date('Y-m-01');
        $meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',
    4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre',
    10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];
        $mesActual = $meses[(int)date('n')] . ' ' . date('Y');

        $queryBase = Ordenes::find()->where(['tipo_orden' => 'orden']);
        $alertasInventario = \app\models\InventarioLote::alertasGlobales(10);
$materialesStockBajo = \app\models\InventarioLote::stockBajo(10);
        // Generales
        $totalOrdenesGeneral   = (clone $queryBase)->count();
        $totalAnalisisGeneral  = OrdenDetalle::find()
            ->joinWith('orden')
            ->where(['ordenes.tipo_orden' => 'orden'])
            ->count();

        // NUEVO: "Recaudado" se cuenta el día en que el dinero ENTRÓ
        // (orden_pagos.fecha_pago), no el día en que se creó la orden.
        // Si hoy pagan Bs. 30 de una orden de Bs. 100 y mañana pagan
        // los Bs. 70 restantes, esos 70 tienen que sumar al recaudado
        // de mañana, no al de hoy — antes, al sumar monto_pagado
        // filtrando por fecha_registro de la orden, los 70 se contaban
        // como si hubieran entrado el día en que se creó la orden.
        $queryPagosBase = OrdenPagos::find()
            ->joinWith('orden')
            ->where(['ordenes.tipo_orden' => 'orden']);

        $totalRecaudadoGeneral = (clone $queryPagosBase)->sum('orden_pagos.monto') ?? 0;

        // Hoy
        $queryHoy = (clone $queryBase)->andWhere(['DATE(fecha_registro)' => $hoy]);

        $totalOrdenesHoy   = (clone $queryHoy)->count();
        $totalRecaudadoHoy = (clone $queryPagosBase)
            ->andWhere(['DATE(orden_pagos.fecha_pago)' => $hoy])
            ->sum('orden_pagos.monto') ?? 0;
        $totalAnalisisHoy  = OrdenDetalle::find()
            ->joinWith('orden')
            ->where(['ordenes.tipo_orden' => 'orden'])
            ->andWhere(['DATE(ordenes.fecha_registro)' => $hoy])
            ->count();

        // Mes
        $queryMes = (clone $queryBase)->andWhere(['>=', 'DATE(fecha_registro)', $mesDesde]);

        $totalOrdenesMes   = (clone $queryMes)->count();
        $totalRecaudadoMes = (clone $queryPagosBase)
            ->andWhere(['>=', 'DATE(orden_pagos.fecha_pago)', $mesDesde])
            ->sum('orden_pagos.monto') ?? 0;
        $totalAnalisisMes  = OrdenDetalle::find()
            ->joinWith('orden')
            ->where(['ordenes.tipo_orden' => 'orden'])
            ->andWhere(['>=', 'DATE(ordenes.fecha_registro)', $mesDesde])
            ->count();

        // Pendientes
        $pendientes = Ordenes::find()
            ->where(['tipo_orden' => 'orden', 'estado_pago' => 'pendiente'])
            ->orderBy(['fecha_registro' => SORT_DESC])
            ->limit(5)
            ->all();

        $totalSaldoPendiente = Ordenes::find()
            ->where(['tipo_orden' => 'orden', 'estado_pago' => 'pendiente'])
            ->sum('saldo') ?? 0;

        // Últimas órdenes
        $ultimasOrdenes = Ordenes::find()
            ->with(['doctor'])
            ->where(['tipo_orden' => 'orden'])
            ->orderBy(['fecha_registro' => SORT_DESC])
            ->limit(8)
            ->all();

        return $this->render('index', [
    'hoy'                  => $hoy,
    'mesActual' => $mesActual,  
    'totalOrdenesGeneral'  => $totalOrdenesGeneral,
    'totalRecaudadoGeneral'=> $totalRecaudadoGeneral,
    'totalAnalisisGeneral' => $totalAnalisisGeneral,
    'totalOrdenesHoy'      => $totalOrdenesHoy,
    'totalRecaudadoHoy'    => $totalRecaudadoHoy,
    'totalAnalisisHoy'     => $totalAnalisisHoy,
    'totalOrdenesMes'      => $totalOrdenesMes,
    'totalRecaudadoMes'    => $totalRecaudadoMes,
    'totalAnalisisMes'     => $totalAnalisisMes,
    'pendientes'           => $pendientes,
    'totalSaldoPendiente'  => $totalSaldoPendiente,
    'ultimasOrdenes'       => $ultimasOrdenes,
    'alertasInventario' => $alertasInventario,
    'materialesStockBajo' => $materialesStockBajo,
]);
    }

    /**
     * Login action.
     *
     * @return string
     */
    public function actionLogin()
    {
        $this->layout = "login";
        if (!Yii::$app->user->isGuest) {
            return $this->redirect(
                $this->authService->getRedirectRoute(Yii::$app->user->identity)
            );
        }            
        $model = new LoginForm();     
        if ($model->load(Yii::$app->request->post())) {
            if ($this->authService->login($model, $this->correoService)) {
                return $this->redirect($this->authService->getRedirectRoute(Yii::$app->user->identity));
            } else {
               // Yii::error("Login falló. Errores: " . json_encode($model->getErrors()), 'loginDebug');
            }
        }
        return $this->render('login', ['model' => $model]);
   }

    /**
     * Logout action.
     *
     * @return string
     */
   

      public function actionLogout()
    {
        $this->authService->logout();
        return $this->redirect(['login']);
    }


}
<?php
namespace app\controllers;
use app\components\InterfaceTarea;
use app\components\InterfaceBanners;
use app\components\InterfacePaginas;
use app\models\LoginWeb;
use Yii;
use yii\web\Controller;
class SiteController extends BaseController
{
    private $authService;
    private $correoService;
    public function init()
    {
        parent::init();
        $this->authService = Yii::$container->get('app\components\InterfaceAuth');
        $this->correoService = Yii::$container->get('app\components\InterfaceCorreos'); // <- esto sí va en la otra propiedad
    }

    public function behaviors()
    {
        return [];
    }
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
        // Banners del Inicio: los que se muestran hoy (Panel → Banners)
        $banners = Yii::$container->get(InterfaceBanners::class)->paraWeb();
        // Tarjetas Conectar · Transformar · Multiplicar (Panel → Páginas → Nosotros → Quiénes somos)
        $paginas = Yii::$container->get(InterfacePaginas::class);
        $tarjetas = $paginas->tarjetasInicio();
        // Resumen de Quiénes somos (+ destacados y cifras), mismo panel
        $quienes = $paginas->quienesInicio();
        return $this->render('index', ['banners' => $banners, 'tarjetas' => $tarjetas, 'quienes' => $quienes]);
    }
    /**
     * Login action.
     *
     * @return \yii\web\Response
     */
    public function actionLogin()
    {

        if (!Yii::$app->user->isGuest) {
            return $this->redirect(
                $this->authService->getRedirectRoute(Yii::$app->user->identity)
            );
        }


        $this->layout = "login";
        $model = new LoginWeb();

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
     * @return \yii\web\Response
     */
    public function actionLogout()
    {
        $this->authService->logout();
        return $this->redirect(['login']);
    }


}




<?php

namespace app\modules\admin\controllers;
use app\controllers\BaseController;
use app\models\LoginForm;
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
    /** Inicio: bienvenida y accesos a los módulos que el usuario puede usar. */
    public function actionIndex()
    {
        $permisos = Yii::$container->get(\app\components\InterfacePermisosService::class);
        $superadmin = (int) (Yii::$app->user->identity->idrol ?? 0) === \app\components\Rol::SUPERADMIN;
        $accesos = array_values(array_filter([
            $permisos->tienePermiso('inversores_listar') ? ['Inversores', 'handshake-o', ['/admin/inversores/index'], 'Solicitudes de la web, aprobación con áreas y acuerdo de confidencialidad.'] : null,
            $permisos->tienePermiso('inversiones_listar') ? ['Inversiones', 'money', ['/admin/inversiones/index'], 'Pagos de cada inversor en cada proyecto, comprobantes y % de la ronda.'] : null,
            $permisos->tienePermiso('proyectos_listar') ? ['Proyectos', 'briefcase', ['/admin/banco-proyectos/index'], 'El banco de proyectos que ven los inversores de cada área.'] : null,
            $permisos->tienePermiso('ecosistemas_listar') ? ['Ecosistemas', 'th-large', ['/admin/ecosistemas/index'], 'Contenido, imágenes y galería de cada ecosistema.'] : null,
            $permisos->tienePermiso('banners_listar') ? ['Banners', 'picture-o', ['/admin/banners/index'], 'Las imágenes grandes de la portada, con su texto y botón.'] : null,
            $permisos->tienePermiso('equipo_listar') ? ['Equipo', 'id-badge', ['/admin/equipo/index'], 'Las personas que se muestran en la página, con foto y biografía.'] : null,
            $permisos->tienePermiso('paginas_ver') ? ['Páginas', 'files-o', ['/admin/paginas/index', 'pagina' => 'nosotros'], 'Textos de Nosotros (y del Inicio), Estrategia y Gobierno y ESG.'] : null,
            $permisos->tienePermiso('configuracion_editar') ? ['Datos generales', 'globe', ['/admin/datos-generales/index'], 'Título, contacto, redes sociales y mapa de la página.'] : null,
            $superadmin ? ['Usuarios', 'users', ['/admin/usuarios/index'], 'Altas, bloqueos e historial del personal del panel.'] : null,
            $superadmin ? ['Roles y permisos', 'key', ['/admin/roles/index'], 'Qué puede hacer cada tipo de usuario.'] : null,
            ['Mi perfil', 'user-circle', ['/admin/usuarios/cuenta'], 'Tus datos y tu contraseña.'],
        ]));
        return $this->render('index', ['accesos' => $accesos, 'usuario' => Yii::$app->user->identity]);
    }

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
<?php
namespace app\controllers;

use app\components\InterfaceBanners;
use app\components\InterfaceConfiguracion;
use app\components\InterfaceContacto;
use app\components\InterfaceEcosistemas;
use app\components\InterfaceNoticias;
use app\components\InterfacePaginas;
use app\components\InterfaceProyectosWeb;
use app\models\ContactoForm;
use Yii;
use yii\web\Controller;

/**
 * Web pública. Cada página pide sus datos a los servicios y la vista solo los muestra.
 * Nosotros y sus subpáginas: NosotrosController · Ecosistemas: EcosistemasController · Blog: NoticiasController.
 */
class SiteController extends Controller
{
    private InterfaceBanners $banners;
    private InterfacePaginas $paginas;
    private InterfaceConfiguracion $configuracion;
    private InterfaceContacto $contacto;
    private InterfaceEcosistemas $ecosistemas;
    private InterfaceProyectosWeb $proyectos;
    private InterfaceNoticias $noticias;

    public function __construct($id, $module, InterfaceBanners $banners, InterfacePaginas $paginas,
        InterfaceConfiguracion $configuracion, InterfaceContacto $contacto, InterfaceEcosistemas $ecosistemas,
        InterfaceProyectosWeb $proyectos, InterfaceNoticias $noticias, $config = [])
    {
        $this->ecosistemas = $ecosistemas;
        $this->proyectos = $proyectos;
        $this->noticias = $noticias;
        $this->banners = $banners;
        $this->paginas = $paginas;
        $this->configuracion = $configuracion;
        $this->contacto = $contacto;
        parent::__construct($id, $module, $config);
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        $estrategia = $this->paginas->estrategiaWeb();
        return $this->render('index', [
            'banners' => $this->banners->paraWeb('inicio'),       // Panel → Banners → Inicio (carrusel)
            'tarjetas' => $this->paginas->tarjetasInicio(),       // Conectar · Transformar · Multiplicar
            'quienes' => $this->paginas->quienesInicio(),         // resumen de Quiénes somos (+ destacados y cifras)
            'ceo' => $this->paginas->nosotrosWeb()['ceo'],         // Panel → Páginas → Quiénes somos → Mensaje del CEO
            'ecosistemas' => $this->ecosistemas->paraMenu(),      // Panel → Ecosistemas (activos, por número)
            'proyectos' => $this->proyectos->destacadosInicio(),  // solo inversor aprobado con NDA; si no, null → llamado
            'modelo' => $estrategia['modelo'], 'propuesta' => $estrategia['propuesta'],   // Panel → Páginas → Ejes estratégicos
            'expansion' => $estrategia['expansion'], 'fases' => $estrategia['fases'],
            'noticias' => $this->noticias->destacadas(9),         // Panel → Blog → destacadas
        ]);
    }

    /**
     * Contacto: datos de Datos generales (izquierda), formulario (derecha) y el mapa abajo.
     * Al enviar: si sale bien, vuelve a la página vacía con el aviso (así recargar no lo manda dos veces).
     */
    public function actionContacto()
    {
        $form = new ContactoForm();
        $request = Yii::$app->request;
        if ($request->isPost && $form->load($request->post())) {
            $resultado = $this->contacto->enviar($form, $request->post('g-recaptcha-response'), $request->userIP);
            if ($resultado['exito']) {
                Yii::$app->session->setFlash('contacto-ok', $resultado['mensaje']);
                return $this->redirect(['contacto', '#' => 'formulario']);
            }
            Yii::$app->session->setFlash('contacto-error', $resultado['mensaje']);
        }
        return $this->render('contacto', [
            'form' => $form,
            'encabezado' => $this->banners->encabezado('contacto'), // Panel → Banners → Contacto
            'sitio' => $this->configuracion->paraWeb(),            // Panel → Configuración → Datos generales
            'recaptcha' => $this->contacto->recaptchaSiteKey(),
        ]);
    }
}

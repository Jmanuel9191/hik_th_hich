<?php

namespace app\modules\admin\controllers;

use app\components\InterfacePaginaProyecto;
use app\components\InterfaceProyectosWeb;
use app\components\MediosProyecto;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Editor de la página web de los proyectos y de las plantillas (lo dibuja paginas-proyecto/_editor.php, que va
 * en la pestaña "Página web" del proyecto y en Plantillas → Editar). Todo pasa por PaginaProyectoService.
 *  - Formularios (sección, bloque): GET devuelve el HTML del modal; POST guarda y responde JSON {exito, mensaje, errores}.
 *  - Botones (mover, duplicar, ocultar, eliminar): POST → JSON. Los usa huk-pagina-proyecto.js.
 *  - $tipo: proyecto | plantilla; $id: el del proyecto o la plantilla.
 */
class PaginasProyectoController extends Controller
{
    private $pagina;

    public function __construct($id, $module, InterfacePaginaProyecto $pagina, $config = [])
    {
        $this->pagina = $pagina;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        $post = array_fill_keys([
            'banner', 'seccion-guardar', 'seccion-mover', 'seccion-duplicar', 'seccion-visible', 'seccion-eliminar',
            'bloque-guardar', 'bloque-mover', 'bloque-duplicar', 'bloque-visible', 'bloque-eliminar',
            'aplicar-plantilla', 'guardar-como-plantilla',
        ], ['POST']);
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => $post],
        ];
    }

    public function beforeAction($action)
    {
        $json = ['seccion-guardar', 'seccion-mover', 'seccion-duplicar', 'seccion-visible', 'seccion-eliminar',
            'bloque-guardar', 'bloque-mover', 'bloque-duplicar', 'bloque-visible', 'bloque-eliminar', 'aplicar-plantilla', 'guardar-como-plantilla'];
        if (in_array($action->id, $json, true)) {
            Yii::$app->response->format = Response::FORMAT_JSON;
        }
        return parent::beforeAction($action);
    }

    /* ---------- Banner del proyecto (formulario normal) ---------- */

    public function actionBanner($id)
    {
        $r = $this->pagina->guardarBanner($id, Yii::$app->request->post());
        Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
        return $this->redirect(['/admin/banco-proyectos/view', 'id' => $id, 'tab' => 'pagina', '#' => 'pagina-banner']);
    }

    /* ---------- Secciones ---------- */

    public function actionSeccionForm($tipo, $id, $idseccion = null)
    {
        return $this->renderPartial('_seccion_form', $this->pagina->formularioSeccion((string) $tipo, (int) $id, $idseccion));
    }

    public function actionSeccionGuardar($tipo, $id, $idseccion = null)
    {
        return $this->pagina->guardarSeccion((string) $tipo, (int) $id, $idseccion, Yii::$app->request->post());
    }

    public function actionSeccionMover($id, $direccion)
    {
        return $this->pagina->moverSeccion($id, $direccion);
    }

    public function actionSeccionDuplicar($id)
    {
        return $this->pagina->duplicarSeccion($id);
    }

    public function actionSeccionVisible($id)
    {
        return $this->pagina->cambiarVisibilidadSeccion($id);
    }

    public function actionSeccionEliminar($id)
    {
        return $this->pagina->eliminarSeccion($id);
    }

    /* ---------- Bloques ---------- */

    /** Formulario de un bloque nuevo (idseccion + tipo) o existente (id). */
    public function actionBloqueForm($idseccion = null, $tipo = null, $id = null)
    {
        return $this->renderPartial('_bloque_form', $this->pagina->formularioBloque($idseccion, $tipo, $id));
    }

    public function actionBloqueGuardar($idseccion = null, $tipo = null, $id = null)
    {
        return $this->pagina->guardarBloque($idseccion, $tipo, $id, Yii::$app->request->post());
    }

    public function actionBloqueMover($id, $direccion)
    {
        return $this->pagina->moverBloque($id, $direccion);
    }

    public function actionBloqueDuplicar($id)
    {
        return $this->pagina->duplicarBloque($id);
    }

    public function actionBloqueVisible($id)
    {
        return $this->pagina->cambiarVisibilidadBloque($id);
    }

    public function actionBloqueEliminar($id)
    {
        return $this->pagina->eliminarBloque($id);
    }

    /* ---------- Plantillas ---------- */

    public function actionAplicarPlantilla($id)
    {
        $post = Yii::$app->request->post();
        return $this->pagina->aplicarPlantilla($id, $post['idplantilla'] ?? null, !empty($post['reemplazar']));
    }

    public function actionGuardarComoPlantilla($id)
    {
        return $this->pagina->guardarComoPlantilla($id, Yii::$app->request->post());
    }

    /* ---------- Vista previa y fotos ---------- */

    /** La página tal cual la ve el inversor (con el diseño de la web), aunque esté en borrador. */
    public function actionVistaPrevia($id)
    {
        $p = $this->pagina->proyecto($id, true);
        $this->layout = '@app/views/layouts/main';
        $this->view->registerMetaTag(['name' => 'robots', 'content' => 'noindex, nofollow']);
        $web = Yii::$container->get(InterfaceProyectosWeb::class);
        return $this->render('@app/views/proyectos/ver', $web->armarPagina($p) + [
            'proyecto' => $p,
            'medios' => MediosProyecto::panel('proyecto', (int) $p->idproyecto),
            'vistaPrevia' => true,
        ]);
    }

    public function actionImagen($tipo, $id, $carpeta, $archivo, $w = 960)
    {
        $f = $this->pagina->archivoImagen((string) $tipo, (int) $id, (string) $carpeta, (string) $archivo, (int) $w);
        Yii::$app->session->close();
        Yii::$app->response->headers->set('Cache-Control', 'private, max-age=2592000, immutable');
        Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
        return Yii::$app->response->sendFile($f['ruta'], basename($f['ruta']), ['inline' => true, 'mimeType' => $f['mime']]);
    }
}

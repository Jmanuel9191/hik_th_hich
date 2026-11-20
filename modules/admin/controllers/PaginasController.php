<?php

namespace app\modules\admin\controllers;

use app\components\InterfacePaginas;
use app\components\InterfacePermisosService;
use app\models\PaginaItem;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Páginas institucionales (Inicio, Nosotros, Estrategia, Gobierno y ESG). Pantalla con pestañas = pestañas de la web.
 * Permisos paginas_ver / paginas_editar / paginas_historial (Superadmin y Editor de contenido por defecto).
 * Estado, orden y eliminar responden JSON (huk-listado.js).
 */
class PaginasController extends Controller
{
    private $paginas;
    private $permisos;

    public function __construct($id, $module, InterfacePaginas $paginas, InterfacePermisosService $permisos, $config = [])
    {
        $this->paginas = $paginas;
        $this->permisos = $permisos;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [['allow' => true, 'roles' => ['@']]],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'seccion-estado' => ['POST'],
                    'item-estado' => ['POST'],
                    'item-mover' => ['POST'],
                    'item-eliminar' => ['POST'],
                ],
            ],
        ];
    }

    /** Una pestaña de la página: sus bloques con sus listas, e historial. */
    public function actionIndex($pagina = 'quienes', $tab = null)
    {
        $this->permisos->verificarPermiso('paginas_ver');
        $config = $this->paginas->pagina($pagina);
        $tab = isset($config['pestanas'][$tab]) ? $tab : array_key_first($config['pestanas']);
        $bloques = $this->paginas->bloques($pagina, $tab);
        $listas = [];
        foreach ($bloques as $clave => $bloque) {
            if (!empty($bloque->config()['lista'])) {
                $listas[$clave] = $this->paginas->items($bloque);
            }
        }
        return $this->render('index', [
            'pagina' => $pagina,
            'config' => $config,
            'tab' => $tab,
            'bloques' => $bloques,
            'listas' => $listas,
            'historial' => $this->permisos->tienePermiso('paginas_historial') ? $this->paginas->historial($pagina) : null,
            'puedeEditar' => $this->permisos->tienePermiso('paginas_editar'),
        ]);
    }

    public function actionSeccion($id)
    {
        $this->permisos->verificarPermiso('paginas_editar');
        $seccion = $this->paginas->buscarSeccion($id);
        $volver = ['index', 'pagina' => $seccion->pagina, 'tab' => $seccion->config()['pestana']];
        if (Yii::$app->request->isPost) {
            $r = $this->paginas->guardarSeccion($seccion, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect($volver);
            }
        }
        return $this->render('seccion', ['model' => $seccion, 'volver' => $volver]);
    }

    public function actionSeccionEstado($id)
    {
        $this->permisos->verificarPermiso('paginas_editar');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->paginas->cambiarEstadoSeccion($id);
    }

    public function actionItemCrear($seccion)
    {
        $this->permisos->verificarPermiso('paginas_editar');
        return $this->formularioItem($this->paginas->nuevoItem($seccion));
    }

    public function actionItemEditar($id)
    {
        $this->permisos->verificarPermiso('paginas_editar');
        return $this->formularioItem($this->paginas->buscarItem($id));
    }

    public function actionItemEstado($id)
    {
        $this->permisos->verificarPermiso('paginas_editar');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->paginas->cambiarEstadoItem($id);
    }

    public function actionItemMover($id, $direccion)
    {
        $this->permisos->verificarPermiso('paginas_editar');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->paginas->moverItem($id, $direccion);
    }

    public function actionItemEliminar($id)
    {
        $this->permisos->verificarPermiso('paginas_editar');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->paginas->eliminarItem($id);
    }

    private function formularioItem(PaginaItem $item)
    {
        $volver = ['index', 'pagina' => $item->seccion->pagina, 'tab' => $item->seccion->config()['pestana'], '#' => 'bloque-' . $item->seccion->clave];
        if (Yii::$app->request->isPost) {
            $r = $this->paginas->guardarItem($item, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect($volver);
            }
        }
        return $this->render('item', ['model' => $item, 'volver' => $volver]);
    }
}

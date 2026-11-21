<?php

namespace app\modules\admin\controllers;

use app\components\InterfaceAvances;
use app\components\InterfaceInteresInversion;
use app\components\InterfaceInversiones;
use app\components\InterfacePaginaProyecto;
use app\components\InterfacePermisosService;
use app\components\InterfaceProyecto;
use app\components\SubidaArchivo;
use app\models\Proyecto;
use app\models\ProyectoAvance;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Banco de proyectos (admin). URL: /admin/banco-proyectos (el controlador "proyectos" es del sistema anterior).
 * Permisos proyectos_* y alcance por área: ProyectoService. Las acciones de botón responden JSON.
 * Las imágenes, videos y documentos se sirven desde acá (están en privado/, sin URL pública).
 * Pestaña "Página web": el editor de secciones y bloques (PaginasProyectoController hace las acciones).
 * El Editor de contenido (proyectos_pagina sin proyectos_ver) solo ve esa pestaña: nada de montos ni inversores.
 */
class BancoProyectosController extends Controller
{
    const PESTANAS = ['ficha' => 'Ficha', 'pagina' => 'Página web', 'fotos' => 'Fotos', 'videos' => 'Videos', 'documentos' => 'Documentos', 'avances' => 'Avances', 'interesados' => 'Interesados', 'inversiones' => 'Inversiones', 'historial' => 'Historial'];

    private $proyectos;
    private $avances;
    private $permisos;
    private $inversiones;
    private $interes;
    private $pagina;

    public function __construct($id, $module, InterfaceProyecto $proyectos, InterfaceAvances $avances, InterfacePermisosService $permisos,
        InterfaceInversiones $inversiones, InterfacePaginaProyecto $pagina, InterfaceInteresInversion $interes, $config = [])
    {
        $this->interes = $interes;
        $this->proyectos = $proyectos;
        $this->avances = $avances;
        $this->permisos = $permisos;
        $this->inversiones = $inversiones;
        $this->pagina = $pagina;
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
                    'estado' => ['POST'], 'delete' => ['POST'],
                    'galeria-subir' => ['POST'], 'galeria-borrar' => ['POST'],
                    'video-agregar' => ['POST'], 'video-mover' => ['POST'], 'video-eliminar' => ['POST'],
                    'documento-agregar' => ['POST'], 'documento-visible' => ['POST'], 'documento-eliminar' => ['POST'],
                    'avance-estado' => ['POST'], 'avance-eliminar' => ['POST'], 'avance-archivo-agregar' => ['POST'], 'avance-archivo-eliminar' => ['POST'],
                    'avance-archivo-leyenda' => ['POST'], 'interes-seguimiento' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', $this->proyectos->listar(Yii::$app->request->queryParams) + [
            'puedeCrear' => $this->permisos->tienePermiso('proyectos_crear'),
            'puedeExportar' => $this->permisos->tienePermiso('proyectos_exportar'),
            'verMontos' => $this->permisos->tienePermiso('proyectos_ver'),
        ]);
    }

    /** Exporta el listado (con los filtros que tenga) a xlsx, csv o pdf. */
    public function actionExportar($formato = 'xlsx')
    {
        if (!isset(\app\components\Exportador::FORMATOS[$formato])) {
            throw new \yii\web\BadRequestHttpException('Formato no válido.');
        }
        $d = $this->proyectos->exportar(Yii::$app->request->queryParams);
        Yii::info('Exportación de proyectos (' . $formato . ', ' . count($d['modelos']) . ' registros) por #' . Yii::$app->user->id, 'exportar');
        return \app\components\Exportador::enviar($d['titulo'], $d['columnas'], $d['modelos'], $formato, $d['filtros']);
    }

    public function actionView($id, $tab = 'ficha')
    {
        $soloPagina = !$this->permisos->tienePermiso('proyectos_ver');
        if ($soloPagina) { // Editor de contenido: solo la página web
            $p = $this->pagina->proyecto($id);
            $pestanas = ['pagina' => self::PESTANAS['pagina']];
        } else {
            $p = $this->proyectos->buscar($id);
            $pestanas = self::PESTANAS;
            if (!$this->permisos->tienePermiso('proyectos_historial')) {
                unset($pestanas['historial']);
            }
            if (!$this->permisos->tienePermiso('inversiones_listar')) {
                unset($pestanas['inversiones']);
            }
            if (!$this->permisos->tienePermiso('interesados_ver')) {
                unset($pestanas['interesados']);
            }
        }
        $tab = isset($pestanas[$tab]) ? $tab : array_key_first($pestanas);
        return $this->render('view', [
            'model' => $p,
            'tab' => $tab,
            'pestanas' => $pestanas,
            'puedeEditar' => !$soloPagina && $this->proyectos->puedeEditar($p),
            'puedeEliminar' => $this->proyectos->puedeEliminar($p),
            'transiciones' => $this->proyectos->transiciones($p),
            'historial' => $tab === 'historial' ? $this->proyectos->historial($p->idproyecto) : null,
            'descargas' => $tab === 'documentos' ? $this->proyectos->descargasPorDocumento($p->idproyecto) : [],
            'avances' => $tab === 'avances' ? $this->avances->listar($p) : [],
            'inversiones' => $tab === 'inversiones' ? $this->inversiones->resumenProyecto($p, Yii::$app->request->queryParams) : null,
            'interesados' => $tab === 'interesados' ? $this->interes->deProyecto($p) : [],
            'pagados' => $tab === 'interesados' ? $this->interes->pagados($p) : [],
            'gestionarInteres' => $tab === 'interesados' && $this->interes->puedeGestionar(),
            'registrarPago' => $this->permisos->tienePermiso('inversiones_registrar'),
            'limiteServidor' => SubidaArchivo::limiteServidor(),
            'editor' => $tab === 'pagina' ? $this->pagina->editor('proyecto', (int) $p->idproyecto, true) : null,
        ]);
    }

    public function actionCreate()
    {
        $this->permisos->verificarPermiso('proyectos_crear');
        return $this->formulario($this->proyectos->nuevo(), 'create');
    }

    public function actionUpdate($id)
    {
        $p = $this->proyectos->buscar($id, 'proyectos_editar');
        return $this->formulario($p, 'update');
    }

    public function actionEstado($id, $estado)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->proyectos->cambiarEstado($id, $estado);
    }

    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->proyectos->eliminar($id);
    }

    /* ---------- Galería (Kartik FileInput) ---------- */

    public function actionGaleriaSubir($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->proyectos->subirGaleria($id);
    }

    public function actionGaleriaBorrar()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->proyectos->borrarImagenGaleria(Yii::$app->request->post('key'));
    }

    /* ---------- Videos y documentos (huk-proyectos.js / huk-listado.js) ---------- */

    public function actionVideoAgregar($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->proyectos->agregarVideo($id, Yii::$app->request->post());
    }

    public function actionVideoMover($id, $direccion)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->proyectos->moverVideo($id, $direccion);
    }

    public function actionVideoEliminar($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->proyectos->eliminarVideo($id);
    }

    public function actionDocumentoAgregar($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->proyectos->agregarDocumento($id, Yii::$app->request->post());
    }

    public function actionDocumentoVisible($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->proyectos->cambiarVisibilidadDocumento($id);
    }

    public function actionDocumentoEliminar($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->proyectos->eliminarDocumento($id);
    }

    /* ---------- Avances ---------- */

    public function actionAvanceCrear($id)
    {
        return $this->formularioAvance($this->avances->nuevo($id));
    }

    public function actionAvanceEditar($id)
    {
        return $this->formularioAvance($this->avances->buscar($id, true));
    }

    /** Interesados ("Quiero invertir"): cambiar el estado y/o anotar el seguimiento. */
    public function actionInteresSeguimiento($id)
    {
        $r = $this->interes->seguimiento((int) $id, Yii::$app->request->post());
        Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
        return $this->redirect(['view', 'id' => $r['idproyecto'], 'tab' => 'interesados', '#' => 'interes-' . (int) $id]);
    }

    public function actionAvanceEstado($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->avances->cambiarEstado($id);
    }

    public function actionAvanceEliminar($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->avances->eliminar($id);
    }

    public function actionAvanceArchivoAgregar($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->avances->agregarArchivo($id, Yii::$app->request->post());
    }

    public function actionAvanceArchivoEliminar($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->avances->eliminarArchivo($id);
    }

    public function actionAvanceArchivoLeyenda($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->avances->cambiarLeyenda($id, (string) Yii::$app->request->post('leyenda', ''));
    }

    public function actionAvanceArchivo($id)
    {
        $a = $this->avances->archivo($id);
        return $this->enviar($a, $a['inline']);
    }

    private function formularioAvance(ProyectoAvance $a)
    {
        $volver = ['view', 'id' => $a->idproyecto, 'tab' => 'avances'];
        if (Yii::$app->request->isPost) {
            $r = $this->avances->guardar($a, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect($volver + ['#' => 'avance-' . $a->idavance]);
            }
        }
        return $this->render('avance', ['model' => $a, 'volver' => $volver]);
    }

    /* ---------- Archivos protegidos ---------- */

    public function actionImagen($id, $campo)
    {
        return $this->enviar($this->proyectos->archivoImagen($id, $campo), true);
    }

    public function actionGaleriaImagen($id)
    {
        return $this->enviar($this->proyectos->archivoGaleria($id), true);
    }

    /** Video con soporte de "Range" (se puede adelantar sin bajar todo). */
    public function actionVideo($id)
    {
        return $this->enviar($this->proyectos->archivoVideo($id), true);
    }

    public function actionDocumento($id)
    {
        return $this->enviar($this->proyectos->archivoDocumento($id), false);
    }

    private function enviar(array $a, $inline)
    {
        Yii::$app->response->headers->set('Cache-Control', 'private, max-age=3600');
        Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
        return Yii::$app->response->sendFile($a['ruta'], $a['nombre'], array_filter(['inline' => $inline, 'mimeType' => $a['mime']]));
    }

    private function formulario(Proyecto $p, $vista)
    {
        if (Yii::$app->request->isPost) {
            $r = $this->proyectos->guardar($p, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect(['view', 'id' => $p->idproyecto]);
            }
        }
        return $this->render($vista, [
            'model' => $p,
            'ecosistemas' => $this->proyectos->ecosistemasPermitidos(),
            'gerentes' => $this->proyectos->gerentes(),
        ]);
    }
}

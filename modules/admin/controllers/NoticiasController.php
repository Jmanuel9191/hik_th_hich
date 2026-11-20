<?php

namespace app\modules\admin\controllers;

use app\components\InterfaceNoticias;
use app\components\InterfacePermisosService;
use app\models\Noticia;
use app\models\NoticiaCategoria;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Blog → Noticias. Permisos noticias_* (Superadmin y Editor de contenido por defecto).
 * Estado, destacado y eliminar responden JSON (huk-listado.js); la galería usa Kartik FileInput.
 */
class NoticiasController extends Controller
{
    private InterfaceNoticias $noticias;
    private InterfacePermisosService $permisos;

    public function __construct($id, $module, InterfaceNoticias $noticias, InterfacePermisosService $permisos, $config = [])
    {
        $this->noticias = $noticias;
        $this->permisos = $permisos;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => ['estado' => ['POST'], 'destacar' => ['POST'], 'delete' => ['POST'], 'galeria-subir' => ['POST'], 'galeria-borrar' => ['POST']],
            ],
        ];
    }

    public function actionIndex()
    {
        $this->permisos->verificarPermiso('noticias_listar');
        return $this->render('index', $this->noticias->listar(Yii::$app->request->queryParams) + $this->puede());
    }

    public function actionView($id)
    {
        $this->permisos->verificarPermiso('noticias_ver');
        return $this->render('view', [
            'model' => $this->noticias->buscar($id),
            'historial' => $this->permisos->tienePermiso('noticias_historial') ? $this->noticias->historial($id) : null,
        ] + $this->puede());
    }

    public function actionCreate()
    {
        $this->permisos->verificarPermiso('noticias_crear');
        return $this->formulario(new Noticia(), 'create');
    }

    public function actionUpdate($id)
    {
        $this->permisos->verificarPermiso('noticias_editar');
        return $this->formulario($this->noticias->buscar($id), 'update');
    }

    public function actionEstado($id)
    {
        $this->permisos->verificarPermiso('noticias_estado');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->noticias->cambiarEstado($id);
    }

    public function actionDestacar($id)
    {
        $this->permisos->verificarPermiso('noticias_editar');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->noticias->cambiarDestacado($id);
    }

    public function actionDelete($id)
    {
        $this->permisos->verificarPermiso('noticias_eliminar');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->noticias->eliminar($id);
    }

    public function actionGaleria($id)
    {
        $this->permisos->verificarPermiso('noticias_galeria');
        return $this->render('galeria', ['model' => $this->noticias->buscar($id)]);
    }

    /** Kartik FileInput: respuesta JSON vacía = subida correcta. */
    public function actionGaleriaSubir($id)
    {
        $this->permisos->verificarPermiso('noticias_galeria');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->noticias->subirGaleria($id);
    }

    public function actionGaleriaBorrar()
    {
        $this->permisos->verificarPermiso('noticias_galeria');
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->noticias->borrarImagenGaleria(Yii::$app->request->post('key'));
    }

    private function formulario(Noticia $model, $vista)
    {
        if (Yii::$app->request->isPost) {
            $r = $this->noticias->guardar($model, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect(['view', 'id' => $model->idnoticia]);
            }
        }
        return $this->render($vista, [
            'model' => $model,
            'categorias' => NoticiaCategoria::lista(),
            'puedeCategorias' => $this->permisos->tienePermiso('noticias_categorias'),
        ]);
    }

    private function puede()
    {
        $p = $this->permisos;
        return ['puede' => [
            'crear' => $p->tienePermiso('noticias_crear'),
            'editar' => $p->tienePermiso('noticias_editar'),
            'estado' => $p->tienePermiso('noticias_estado'),
            'galeria' => $p->tienePermiso('noticias_galeria'),
            'eliminar' => $p->tienePermiso('noticias_eliminar'),
        ]];
    }
}

<?php

namespace app\modules\admin\controllers;

use app\components\InterfacePaginaProyecto;
use app\components\InterfacePlantillas;
use app\models\Ecosistema;
use app\models\ProyectoPlantilla;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Plantillas de página de proyecto (Panel → Proyectos → Plantillas). Permiso: plantillas_editar.
 * Datos de la plantilla acá; sus secciones y bloques con el mismo editor del proyecto (acción editar).
 */
class PlantillasProyectoController extends Controller
{
    private $plantillas;
    private $pagina;

    public function __construct($id, $module, InterfacePlantillas $plantillas, InterfacePaginaProyecto $pagina, $config = [])
    {
        $this->plantillas = $plantillas;
        $this->pagina = $pagina;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['delete' => ['POST'], 'duplicar' => ['POST'], 'mover' => ['POST']]],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', $this->plantillas->listar());
    }

    public function actionCreate()
    {
        return $this->formulario($this->plantillas->nueva(), 'create');
    }

    public function actionUpdate($id)
    {
        return $this->formulario($this->plantillas->buscar($id), 'update');
    }

    /** Secciones y bloques de la plantilla (mismo editor que la página del proyecto). */
    public function actionEditar($id)
    {
        $t = $this->plantillas->buscar($id);
        return $this->render('editar', ['model' => $t, 'editor' => $this->pagina->editor('plantilla', (int) $t->idplantilla)]);
    }

    public function actionDuplicar($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->plantillas->duplicar($id);
    }

    public function actionMover($id, $direccion)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->plantillas->mover($id, $direccion);
    }

    public function actionDelete($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->plantillas->eliminar($id);
    }

    private function formulario(ProyectoPlantilla $t, $vista)
    {
        if (Yii::$app->request->isPost) {
            $nueva = $t->isNewRecord;
            $r = $this->plantillas->guardar($t, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect($nueva ? ['editar', 'id' => $t->idplantilla] : ['index']);
            }
        }
        return $this->render($vista, ['model' => $t, 'ecosistemas' => Ecosistema::lista(false)]);
    }
}

<?php

namespace app\modules\admin\controllers;

use app\components\InterfaceNda;
use app\components\InterfacePermisosService;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Configuración → Acuerdo de confidencialidad (NDA). Ver: inversores_ver o nda_editar. Publicar: nda_editar (Superadmin).
 */
class NdaController extends Controller
{
    private $nda;
    private $permisos;

    public function __construct($id, $module, InterfaceNda $nda, InterfacePermisosService $permisos, $config = [])
    {
        $this->nda = $nda;
        $this->permisos = $permisos;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return ['access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]]];
    }

    public function actionIndex($ver = null)
    {
        $puedeEditar = $this->permisos->tienePermiso('nda_editar');
        if (!$puedeEditar) {
            $this->permisos->verificarPermiso('inversores_ver');
        }
        $borrador = $this->nda->borrador();
        if ($puedeEditar && Yii::$app->request->isPost) {
            $r = $this->nda->publicar($borrador, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect(['index']);
            }
        }
        $resumen = $this->nda->resumen();
        $vista = null;
        foreach ($resumen['versiones'] as $v) {
            if ((string) $v->numero === (string) $ver) {
                $vista = $v;
            }
        }
        return $this->render('index', $resumen + [
            'vigente' => $this->nda->vigente(),
            'borrador' => $borrador,
            'vista' => $vista,
            'puedeEditar' => $puedeEditar,
        ]);
    }
}

<?php

namespace app\modules\admin\controllers;

use app\components\InterfacePermisosService;
use app\components\InterfaceTerminos;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;

/**
 * Configuración → Términos y privacidad (los del registro de inversores). Ver: inversores_ver o terminos_editar.
 * Publicar una versión nueva: terminos_editar (Superadmin).
 */
class TerminosController extends Controller
{
    private $terminos;
    private $permisos;

    public function __construct($id, $module, InterfaceTerminos $terminos, InterfacePermisosService $permisos, $config = [])
    {
        $this->terminos = $terminos;
        $this->permisos = $permisos;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return ['access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]]];
    }

    public function actionIndex($ver = null)
    {
        $puedeEditar = $this->permisos->tienePermiso('terminos_editar');
        if (!$puedeEditar) {
            $this->permisos->verificarPermiso('inversores_ver');
        }
        $borrador = $this->terminos->borrador();
        if ($puedeEditar && Yii::$app->request->isPost) {
            $r = $this->terminos->publicar($borrador, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect(['index']);
            }
        }
        $resumen = $this->terminos->resumen();
        $vista = null;
        foreach ($resumen['versiones'] as $v) {
            if ((string) $v->numero === (string) $ver) {
                $vista = $v;
            }
        }
        return $this->render('index', $resumen + [
            'vigente' => $this->terminos->vigente(),
            'borrador' => $borrador,
            'vista' => $vista,
            'puedeEditar' => $puedeEditar,
        ]);
    }
}

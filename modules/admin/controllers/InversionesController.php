<?php

namespace app\modules\admin\controllers;

use app\components\Exportador;
use app\components\InterfaceInversiones;
use app\components\InterfacePermisosService;
use app\models\InversionPago;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;

/**
 * Inversiones: pagos de los inversores en los proyectos. Permisos inversiones_*; alcance del gerente: InversionService.
 * Anular y subir comprobante responden JSON. El comprobante (privado) se sirve desde acá.
 */
class InversionesController extends Controller
{
    private $inversiones;
    private $permisos;

    public function __construct($id, $module, InterfaceInversiones $inversiones, InterfacePermisosService $permisos, $config = [])
    {
        $this->inversiones = $inversiones;
        $this->permisos = $permisos;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => ['anular' => ['POST'], 'comprobante-subir' => ['POST']]],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index', $this->inversiones->listar(Yii::$app->request->queryParams) + [
            'puede' => $this->puede(),
        ]);
    }

    /**
     * Registrar un pago. ?idproyecto= / ?idinversor= lo dejan elegido; ?volver=proyecto|inversor vuelve a esa ficha.
     */
    public function actionCreate($idproyecto = null, $idinversor = null, $volver = null)
    {
        $pago = $this->inversiones->nuevo($idproyecto, $idinversor);
        if (Yii::$app->request->isPost) {
            $r = $this->inversiones->registrar($pago, Yii::$app->request->post());
            if ($r['exito']) {
                Yii::$app->session->setFlash('flash', ['type' => 'success', 'message' => $r['mensaje']]);
                if ($volver === 'proyecto' || $volver === 'interesados') {
                    return $this->redirect(['banco-proyectos/view', 'id' => $pago->idproyecto, 'tab' => $volver === 'interesados' ? 'interesados' : 'inversiones']);
                }
                if ($volver === 'inversor') {
                    return $this->redirect(['inversores/view', 'id' => $pago->idusuario]);
                }
                return $this->redirect(['index']);
            }
        }
        return $this->render('create', [
            'model' => $pago,
            'proyectos' => $this->inversiones->proyectosParaPagar(),
            'datosProyectos' => $this->inversiones->datosProyectos(),
            'inversores' => $this->inversiones->inversoresAprobados(),
            'interesados' => $this->inversiones->interesadosParaPagar(), // van primero en la lista, marcados con ★
            'volver' => in_array($volver, ['proyecto', 'inversor', 'interesados'], true) ? $volver : null,
        ]);
    }

    public function actionAnular($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->inversiones->anular($id, Yii::$app->request->post('motivo', ''));
    }

    public function actionComprobanteSubir($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->inversiones->subirComprobante($id);
    }

    /** Comprobante (privado): se ve en el navegador. */
    public function actionComprobante($id)
    {
        $a = $this->inversiones->archivoComprobante($id);
        Yii::$app->response->headers->set('Cache-Control', 'private, no-store');
        Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
        return Yii::$app->response->sendFile($a['ruta'], $a['nombre'], array_filter(['inline' => true, 'mimeType' => $a['mime']]));
    }

    public function actionExportar($formato = 'xlsx')
    {
        if (!isset(Exportador::FORMATOS[$formato])) {
            throw new BadRequestHttpException('Formato no válido.');
        }
        $d = $this->inversiones->exportar(Yii::$app->request->queryParams);
        Yii::info('Exportación de inversiones (' . $formato . ', ' . count($d['modelos']) . ' registros) por #' . Yii::$app->user->id, 'exportar');
        return Exportador::enviar($d['titulo'], $d['columnas'], $d['modelos'], $formato, $d['filtros']);
    }

    private function puede()
    {
        return [
            'registrar' => $this->permisos->tienePermiso('inversiones_registrar'),
            'anular' => $this->permisos->tienePermiso('inversiones_anular'),
            'exportar' => $this->permisos->tienePermiso('inversiones_exportar'),
        ];
    }
}

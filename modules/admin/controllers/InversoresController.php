<?php

namespace app\modules\admin\controllers;

use app\components\InterfaceInversiones;
use app\components\InterfaceInversores;
use app\components\InterfaceNda;
use app\components\InterfacePerfilInversor;
use app\components\InterfacePermisosService;
use app\models\Ecosistema;
use app\models\SolicitudInversorForm;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;

/**
 * Inversores (rol 5): solicitudes de la web y del panel, aprobación con áreas, bloqueo, NDA e historial.
 * Permisos inversores_*; alcance por área del gerente: InversorService.
 * Rechazar, bloquear/activar y reenviar correo responden JSON (huk-inversores.js).
 */
class InversoresController extends Controller
{
    const PESTANAS = [
        'todos' => 'Todos',
        'pendiente' => 'Pendientes',
        'aprobado' => 'Aprobados',
        'rechazado' => 'Rechazados',
        'sin_verificar' => 'Sin confirmar correo',
        'bloqueado' => 'Bloqueados',
    ];

    private $inversores;
    private $nda;
    private $permisos;
    private $inversiones;
    private $perfil;

    public function __construct($id, $module, InterfaceInversores $inversores, InterfaceNda $nda, InterfacePermisosService $permisos,
        InterfaceInversiones $inversiones, InterfacePerfilInversor $perfil, $config = [])
    {
        $this->perfil = $perfil;
        $this->inversores = $inversores;
        $this->nda = $nda;
        $this->permisos = $permisos;
        $this->inversiones = $inversiones;
        parent::__construct($id, $module, $config);
    }

    public function behaviors()
    {
        return [
            'access' => ['class' => AccessControl::class, 'rules' => [['allow' => true, 'roles' => ['@']]]],
            'verbs' => ['class' => VerbFilter::class, 'actions' => [
                'aprobar' => ['POST'], 'areas' => ['POST'], 'rechazar' => ['POST'], 'estado' => ['POST'],
                'reenviar' => ['POST'], 'documento-subir' => ['POST'], 'desbloquear' => ['POST'],
            ]],
        ];
    }

    public function actionIndex($tab = 'todos')
    {
        $tab = isset(self::PESTANAS[$tab]) ? $tab : 'todos';
        return $this->render('index', $this->inversores->listar(Yii::$app->request->queryParams, $tab) + [
            'tab' => $tab,
            'pestanas' => self::PESTANAS,
            'puedeCrear' => $this->permisos->tienePermiso('inversores_crear'),
            'puedeExportar' => $this->permisos->tienePermiso('inversores_exportar'),
        ]);
    }

    public function actionView($id)
    {
        $inv = $this->inversores->buscar($id);
        return $this->render('view', [
            'model' => $inv,
            'aceptaciones' => $this->nda->aceptacionesDe($inv->idusuario),
            'vigente' => $this->nda->vigente(),
            'historial' => $this->permisos->tienePermiso('inversores_historial') ? $this->inversores->historial($inv->idusuario) : null,
            'ecosistemas' => Ecosistema::lista(false),
            'tieneFoto' => $this->perfil->archivoFoto((int) $inv->idusuario) !== null,
            'pedidas' => \app\models\Inversor::idsPedidos($inv->idusuario),
            'inversiones' => $this->permisos->tienePermiso('inversiones_listar') ? $this->inversiones->resumenInversor($inv->idusuario) : null,
            'puede' => [
                'registrarPago' => $this->permisos->tienePermiso('inversiones_registrar'),
                'anularPago' => $this->permisos->tienePermiso('inversiones_anular'),
                'editar' => $this->permisos->tienePermiso('inversores_editar'),
                'aprobar' => $this->permisos->tienePermiso('inversores_aprobar'),
                'estado' => $this->permisos->tienePermiso('inversores_estado'),
                'desbloquear' => $this->permisos->tienePermiso('inversores_desbloquear'),
            ],
        ]);
    }

    public function actionCreate()
    {
        $this->permisos->verificarPermiso('inversores_crear');
        $form = new SolicitudInversorForm(['scenario' => 'admin']);
        if (Yii::$app->request->isPost) {
            $r = $this->inversores->crearDesdePanel($form, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect(['view', 'id' => $r['id']]);
            }
        }
        return $this->render('create', ['model' => $form, 'ecosistemas' => $this->ecosistemasFormulario(),
            'puedeAprobar' => $this->permisos->tienePermiso('inversores_aprobar')]);
    }

    public function actionUpdate($id)
    {
        $inv = $this->inversores->buscar($id, 'inversores_editar');
        $form = SolicitudInversorForm::desde($inv);
        if (Yii::$app->request->isPost) {
            $r = $this->inversores->editar($inv, $form, Yii::$app->request->post());
            Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
            if ($r['exito']) {
                return $this->redirect(['view', 'id' => $inv->idusuario]);
            }
        }
        return $this->render('update', ['model' => $form, 'inversor' => $inv, 'ecosistemas' => Ecosistema::lista(false), 'puedeAprobar' => false]);
    }

    public function actionAprobar($id)
    {
        return $this->conFlash($this->inversores->aprobar($id, (array) Yii::$app->request->post('areas', [])), $id);
    }

    public function actionAreas($id)
    {
        return $this->conFlash($this->inversores->guardarAreas($id, (array) Yii::$app->request->post('areas', [])), $id);
    }

    public function actionRechazar($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->inversores->rechazar($id, Yii::$app->request->post('motivo', ''));
    }

    public function actionEstado($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->inversores->cambiarEstadoCuenta($id, Yii::$app->request->post('motivo', ''));
    }

    /** Quita el bloqueo por intentos fallidos (Superadmin / Gerente del área). */
    public function actionDesbloquear($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->inversores->desbloquearIntentos($id);
    }

    public function actionReenviar($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        return $this->inversores->reenviarCorreo($id);
    }

    public function actionDocumentoSubir($id)
    {
        return $this->conFlash($this->inversores->subirDocumento($id), $id);
    }

    /** Documento de identidad (privado): se ve en el navegador. */
    public function actionDocumento($id)
    {
        $a = $this->inversores->archivoDocumento($id);
        Yii::$app->response->headers->set('Cache-Control', 'private, no-store');
        Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
        return Yii::$app->response->sendFile($a['ruta'], $a['nombre'], array_filter(['inline' => true, 'mimeType' => $a['mime']]));
    }

    /** Foto de perfil del inversor (privada): solo con permiso para ver a ese inversor. */
    public function actionFoto($id)
    {
        $inv = $this->inversores->buscar($id);
        $ruta = $this->perfil->archivoFoto((int) $inv->idusuario);
        if ($ruta === null) {
            throw new \yii\web\NotFoundHttpException('Sin foto.');
        }
        Yii::$app->response->headers->set('Cache-Control', 'private, max-age=3600');
        Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
        return Yii::$app->response->sendFile($ruta, 'foto.webp', ['inline' => true, 'mimeType' => 'image/webp']);
    }

    /** Exporta el listado (con los filtros que tenga) a xlsx, csv o pdf. */
    public function actionExportar($formato = 'xlsx', $tab = 'todos')
    {
        if (!isset(\app\components\Exportador::FORMATOS[$formato])) {
            throw new \yii\web\BadRequestHttpException('Formato no válido.');
        }
        $d = $this->inversores->exportar(Yii::$app->request->queryParams, isset(self::PESTANAS[$tab]) ? $tab : 'todos');
        Yii::info('Exportación de inversores (' . $formato . ', ' . count($d['modelos']) . ' registros) por #' . Yii::$app->user->id, 'exportar');
        return \app\components\Exportador::enviar($d['titulo'], $d['columnas'], $d['modelos'], $formato, $d['filtros']);
    }

    private function conFlash(array $r, $id)
    {
        Yii::$app->session->setFlash('flash', ['type' => $r['exito'] ? 'success' : 'error', 'message' => $r['mensaje']]);
        return $this->redirect(['view', 'id' => $id]);
    }

    /** El gerente solo elige entre sus áreas. */
    private function ecosistemasFormulario()
    {
        $lista = Ecosistema::lista(false);
        if ((int) (Yii::$app->user->identity->idrol ?? 0) === \app\components\Rol::GERENTE) {
            $ids = (new \yii\db\Query())->select('idecosistema')->from('usuario_ecosistema')->where(['idusuario' => Yii::$app->user->id])->column();
            $lista = array_intersect_key($lista, array_flip($ids));
        }
        return $lista;
    }
}

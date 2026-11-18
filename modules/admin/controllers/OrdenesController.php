<?php
namespace app\modules\admin\controllers;

use app\components\InterfaceOrdenes;
use app\components\InterfacePermisosService;
use app\models\Ordenes;
use Mpdf\Mpdf;
use app\models\OrdenDetalle;
use Yii;

class OrdenesController extends BaseController
{
    private $ordenesService;
    private $permisosService;
private $resultadosService;
public function __construct($id, $module, InterfaceOrdenes $ordenesService, InterfacePermisosService $permisosService, \app\components\InterfaceResultados $resultadosService, $config = [])
{
    $this->ordenesService   = $ordenesService;
    $this->permisosService  = $permisosService;
    $this->resultadosService = $resultadosService;
    parent::__construct($id, $module, $config);
}


public function behaviors()
{
    return [
        'access' => [
            'class' => \yii\filters\AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'], // solo usuarios autenticados
                ],
            ],
        ],
    ];
}

  public function actionIndex()
{
    //$this->permisosService->verificarAdministrador();
    $searchModel  = new \app\models\OrdenesSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams, false); // false = no forzar nada
    return $this->render('index', [
        'searchModel'  => $searchModel,
        'dataProvider' => $dataProvider,
    ]);
}

    public function actionCotizaciones()
    {
        //$this->permisosService->verificarAdministrador();
        $resultado = $this->ordenesService->listOrdenes(Yii::$app->request->queryParams, false);
        $resultado['searchModel']->tipo_orden = 'cotizacion';
        return $this->render('index', [
            'searchModel'    => $resultado['searchModel'],
            'dataProvider'   => $resultado['dataProvider'],
            'esCotizaciones' => true,
        ]);
    }

    public function actionCarrito()
    {
        //$this->permisosService->verificarAdministrador();
        if (Yii::$app->request->get('nueva')) {
            $this->limpiarSessionCarrito();
        }
        return $this->render('carrito', [
            'model'         => new Ordenes(),
            'itemsActuales' => Yii::$app->session->get('carrito_items', []),
        ]);
    }

    // Guarda datos del paciente en session (AJAX)
    public function actionGuardarDatos()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $post = Yii::$app->request->post();

        $items = Yii::$app->session->get('carrito_items', []);
        $items['_paciente']    = $post['paciente']    ?? '';
        $items['_edad']        = $post['edad']        ?? '';
        $items['_telefono']    = $post['telefono']    ?? '';
        $items['_solicitante'] = $post['solicitante'] ?? '';
        $items['_iddoctor']    = $post['iddoctor']    ?? null;

        Yii::$app->session->set('carrito_items', $items);
        return ['exito' => true];
    }

    // Guarda directo como COTIZACIÓN sin pasar por el formulario de pago
    public function actionCotizar()
    {
        //$this->permisosService->verificarAdministrador();

        $items    = Yii::$app->session->get('carrito_items', []);
        $detalles = array_values(array_filter($items, fn($k) => is_int($k), ARRAY_FILTER_USE_KEY));

        if (empty($detalles)) {
            Yii::$app->session->setFlash('error', 'No hay análisis en la cotización.');
            return $this->redirect(['carrito']);
        }

        $paciente = $items['_paciente'] ?? '';
        if (empty($paciente)) {
            Yii::$app->session->setFlash('error', 'El nombre del paciente es obligatorio.');
            return $this->redirect(['carrito']);
        }

        $total = array_sum(array_column($detalles, 'precio'));

        $model = new Ordenes();
        $model->paciente_nombre    = $paciente;
        $model->paciente_edad      = $items['_edad']        ?? null;
        $model->paciente_telefono  = $items['_telefono']    ?? null;
        $model->solicitante_nombre = $items['_solicitante'] ?? null;
        $model->iddoctor           = $items['_iddoctor']    ?? null;
        $model->idusuario_registro = Yii::$app->user->id;
        $model->fecha_registro     = date('Y-m-d H:i:s');
        $model->monto_total        = $total;
        $model->monto_pagado       = 0;
        $model->saldo              = $total;
        $model->tipo_pago          = 'efectivo'; // no importa en cotización
        $model->estado_pago        = 'cotizacion';
        $model->tipo_orden         = 'cotizacion';

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save()) {
                throw new \Exception('Error al guardar la cotización.');
            }
            foreach ($detalles as $d) {
                $det             = new OrdenDetalle();
                $det->idorden    = $model->idorden;
                $det->idproducto = (int)$d['idproducto'];
                $det->precio     = (float)$d['precio'];
                $det->subtotal   = (float)$d['precio'];
                if (!$det->save()) {
                    throw new \Exception('Error al guardar detalle.');
                }
            }
            $transaction->commit();
            $this->limpiarSessionCarrito();
            return $this->redirect(['ver-cotizacion', 'id' => $model->idorden]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            Yii::$app->session->setFlash('error', 'Error al guardar. Inténtelo de nuevo.');
            return $this->redirect(['carrito']);
        }
    }

    // Ver cotización imprimible
    public function actionVerCotizacion($id)
    {
        //$this->permisosService->verificarAdministrador();
        $model = $this->ordenesService->findModel($id);
        return $this->render('cotizacion_view', [
            'model'    => $model,
            'detalles' => $model->detalles,
        ]);
    }


    

    // Convierte una cotización en orden real — lleva al formulario de pago con datos prellenados
    public function actionConvertirOrden($id)
    {
        //$this->permisosService->verificarAdministrador();
        $model = $this->ordenesService->findModel($id);

        if ($model->tipo_orden !== 'cotizacion') {
            return $this->redirect(['view', 'id' => $id]);
        }

        // Cargar los detalles de la cotización en session para reusar el flujo normal
        $this->limpiarSessionCarrito();
        $items = [
            '_paciente'    => $model->paciente_nombre,
            '_edad'        => $model->paciente_edad,
            '_telefono'    => $model->paciente_telefono,
            '_solicitante' => $model->solicitante_nombre,
            '_iddoctor'    => $model->iddoctor,
            '_tipo_orden'  => 'orden',
            '_cotizacion_origen' => $id, // para referencia
            '_idusuario_registro' => $model->idusuario_registro,
        ];

        foreach ($model->detalles as $d) {
            $items[] = [
                'idproducto' => $d->idproducto,
                'titulo'     => $d->producto->titulo ?? '',
                'precio'     => (float)$d->precio,
                'categoria'  => $d->producto->categoria->nombre ?? '',
            ];
        }

        Yii::$app->session->set('carrito_items', $items);
        return $this->redirect(['create']);
    }

    // Formulario de pago (orden real)
    public function actionCreate()
{
    //$this->permisosService->verificarAdministrador();

    $items = Yii::$app->session->get('carrito_items', []);

    // Si viene de una cotización, usamos el usuario original; si no, el actual
    $idusuarioRegistro = $items['_idusuario_registro'] ?? Yii::$app->user->id;

    if (Yii::$app->request->isPost) {
        $resultado = $this->ordenesService->nuevaOrden(
            Yii::$app->request->post(),
            $idusuarioRegistro  // ← ya no es siempre el usuario actual
        );
        if ($resultado['exito']) {
            $this->limpiarSessionCarrito();
            return $this->redirect(['view', 'id' => $resultado['idorden'], 'status' => 'created']);
        }
        $detalles = array_values(array_filter($items, fn($k) => is_int($k), ARRAY_FILTER_USE_KEY));
        return $this->render('create', [
            'model'    => $resultado['model'],
            'detalles' => $detalles,
        ]);
    }

    $detalles = array_values(array_filter($items, fn($k) => is_int($k), ARRAY_FILTER_USE_KEY));

    if (empty($detalles)) {
        return $this->redirect(['carrito']);
    }

    $total = array_sum(array_column($detalles, 'precio'));
    $model = new Ordenes();
    $model->paciente_nombre    = $items['_paciente']    ?? '';
    $model->paciente_edad      = $items['_edad']        ?? '';
    $model->paciente_telefono  = $items['_telefono']    ?? '';
    $model->solicitante_nombre = $items['_solicitante'] ?? '';
    $model->iddoctor           = $items['_iddoctor']    ?? null;
    $model->tipo_orden         = 'orden';
    $model->monto_total        = $total;

    return $this->render('create', [
        'model'    => $model,
        'detalles' => $detalles,
    ]);
}

    public function actionAgregarItem()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
       // $this->permisosService->verificarAdministrador();

        $idproducto = (int)Yii::$app->request->post('idproducto');
        $titulo     = Yii::$app->request->post('titulo', '');
        $precio     = (float)Yii::$app->request->post('precio', 0);
        $categoria  = Yii::$app->request->post('categoria', 'Sin categoría');

        if ($idproducto <= 0 || $precio <= 0) {
            return ['exito' => false, 'mensaje' => 'Producto inválido.'];
        }

        $items     = Yii::$app->session->get('carrito_items', []);
        $soloItems = array_filter($items, fn($k) => is_int($k), ARRAY_FILTER_USE_KEY);

        foreach ($soloItems as $item) {
            if ((int)$item['idproducto'] === $idproducto) {
                return ['exito' => false, 'mensaje' => 'Este análisis ya está en la orden.'];
            }
        }

        $items[] = [
            'idproducto' => $idproducto,
            'titulo'     => $titulo,
            'precio'     => $precio,
            'categoria'  => $categoria,
        ];

        Yii::$app->session->set('carrito_items', $items);

        $soloItems = array_values(array_filter($items, fn($k) => is_int($k), ARRAY_FILTER_USE_KEY));
        $total     = array_sum(array_column($soloItems, 'precio'));

        return [
            'exito' => true,
            'items' => $soloItems,
            'total' => number_format($total, 2),
            'count' => count($soloItems),
        ];
    }

    public function actionQuitarItem()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $idproducto    = (int)Yii::$app->request->post('idproducto');
        $items         = Yii::$app->session->get('carrito_items', []);
        $datosPaciente = array_filter($items, fn($k) => is_string($k), ARRAY_FILTER_USE_KEY);
        $soloItems     = array_values(array_filter(
            array_filter($items, fn($k) => is_int($k), ARRAY_FILTER_USE_KEY),
            fn($i) => (int)$i['idproducto'] !== $idproducto
        ));

        Yii::$app->session->set('carrito_items', array_merge($datosPaciente, $soloItems));

        $total = array_sum(array_column($soloItems, 'precio'));
        return [
            'exito' => true,
            'items' => $soloItems,
            'total' => number_format($total, 2),
            'count' => count($soloItems),
        ];
    }

    public function actionView($id)
    {
        //$this->permisosService->verificarAdministrador();
        $model = $this->ordenesService->findModel($id);
        $pagos = $this->ordenesService->getPagosDeOrden($id);
          // Evita que el navegador reintente un POST anterior al refrescar esta pantalla
    Yii::$app->response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    Yii::$app->response->headers->set('Pragma', 'no-cache');
        return $this->render('view', [
            'model'    => $model,
            'detalles' => $model->detalles,
            'pagos'    => $pagos,
        ]);
    }

    public function actionPago($id)
    {
        //$this->permisosService->verificarAdministrador();
        $model = $this->ordenesService->findModel($id);
        if (Yii::$app->request->isPost) {
            $resultado = $this->ordenesService->actualizarPago($model, Yii::$app->request->post());
            if ($resultado['exito']) {
                return $this->redirect(['view', 'id' => $model->idorden, 'status' => 'updated']);
            }
        }
        return $this->render('pago', ['model' => $model]);
    }

    // Editar el ÚLTIMO pago de una orden — SOLO ADMIN. El service
    // (editarPago) es el que realmente valida que sea el último; acá
    // solo se resuelve el permiso y se arma la vista.
    public function actionEditarPago($idpago)
    {
        $this->permisosService->verificarAdministrador();

        $pago = \app\models\OrdenPagos::findOne($idpago);
        if (!$pago) {
            throw new \yii\web\NotFoundHttpException('El pago solicitado no existe.');
        }

        if (Yii::$app->request->isPost) {
            $resultado = $this->ordenesService->editarPago($idpago, Yii::$app->request->post());
            if ($resultado['exito']) {
                return $this->redirect(['view', 'id' => $resultado['idorden'], 'status' => 'updated']);
            }
        }

        $model = $this->ordenesService->findModel($pago->idorden);
        return $this->render('pago_editar', ['pago' => $pago, 'model' => $model]);
    }

    // Editar una orden ya creada — SOLO ADMIN. Permite tocar datos del
    // paciente/doctor, agregar o quitar análisis, y la forma de pago.
    public function actionUpdate($id)
    {
        $this->permisosService->verificarAdministrador();

        $model = $this->ordenesService->findModel($id);

        if ($model->esCotizacion()) {
            // Las cotizaciones no pasan por acá, tienen su propio flujo
            return $this->redirect(['view', 'id' => $id]);
        }

        if (Yii::$app->request->isPost) {
            $resultado = $this->ordenesService->actualizarOrden($model, Yii::$app->request->post());
            if ($resultado['exito']) {
                return $this->redirect(['view', 'id' => $model->idorden, 'status' => 'updated']);
            }
            $model = $resultado['model'];
        }

        // Detalles actuales, en el mismo formato que usa carrito.php/create.php
        $detallesActuales = [];
        foreach ($model->detalles as $d) {
            $detallesActuales[] = [
                'idproducto' => $d->idproducto,
                'titulo'     => $d->producto->titulo ?? '',
                'precio'     => (float)$d->precio,
                'categoria'  => $d->producto->categoria->nombre ?? 'Sin categoría',
            ];
        }

        $pagos = $this->ordenesService->getPagosDeOrden($id);

        return $this->render('update', [
            'model'    => $model,
            'detalles' => $detallesActuales,
            'pagos'    => $pagos,
        ]);
    }

    public function actionDelete($id)
    {
        $this->permisosService->verificarAdministrador();
        $resultado = $this->ordenesService->eliminarOrden($id);
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $resultado;
    }

    private function limpiarSessionCarrito()
    {
        Yii::$app->session->remove('carrito_items');
    }

public function actionResultados($id)
{
    if (Yii::$app->user->isGuest) {
        return $this->redirect(['default/login']);
    }

    $model     = $this->ordenesService->findModel($id);
    $secciones = $this->resultadosService->getSeccionesDeOrden($id);
    $parametrosDisponibles = $this->resultadosService->getParametrosDisponibles($id);

    // IDs de parámetro que YA tienen resultado guardado en alguna
    // sección existente de esta orden.
    $idsParametrosConResultado = [];
    foreach ($secciones as $sec) {
        foreach ($sec->resultados as $res) {
            if ($res->idparametro) {
                $idsParametrosConResultado[(int)$res->idparametro] = true;
            }
        }
    }

    // Arma secciones "para completar" — funciona en los dos casos:
    // 1) la orden nunca tuvo resultados (antes solo pasaba en ese caso)
    // 2) la orden YA tenía resultados, pero se le agregó un análisis
    //    nuevo por "Editar Orden" — ese análisis nuevo no tiene ningún
    //    parámetro con resultado todavía, así que también se le arma
    //    su sección, sin tocar las que ya estaban completas.
    $seccionesPreCargadas = [];
    $categoriasProcesadas = [];

    foreach ($model->detalles as $detalle) {
        $producto = $detalle->producto;
        if (!$producto) continue;

        $parametros = \app\models\ProductoParametro::find()
            ->where(['idproducto' => $producto->idproducto])
            ->orderBy('orden')
            ->with('parametro')
            ->all();

        if (empty($parametros)) continue;

        // ¿Este producto ya tiene algún resultado cargado? Si tiene
        // aunque sea uno, lo dejamos como está (no lo duplicamos).
        $tieneAlgunResultado = false;
        foreach ($parametros as $pp) {
            if (isset($idsParametrosConResultado[(int)$pp->idparametro])) {
                $tieneAlgunResultado = true;
                break;
            }
        }
        if ($tieneAlgunResultado) continue;

        $idcategoria     = $producto->idcategoria ?? 0;
        $nombreCategoria = $producto->categoria->nombre ?? '';

        if (!isset($categoriasProcesadas[$idcategoria])) {
            $categoriasProcesadas[$idcategoria] = count($seccionesPreCargadas);
            $seccionesPreCargadas[] = [
                'titulo'           => '', // se completa después, sigue siendo editable
                'titulo_categoria' => $nombreCategoria,
                'productos'        => [],
            ];
        }

        $idx = $categoriasProcesadas[$idcategoria];
        $seccionesPreCargadas[$idx]['productos'][] = [
            'nombre'     => strtoupper($producto->titulo),
            'parametros' => array_filter(
                array_map(fn($v) => $v->parametro, $parametros)
            ),
        ];
    }

    // Título automático: un solo examen → su nombre exacto (ej: "HEMOGRAMA
    // COMPLETO"); varios exámenes de la misma categoría → nombre de la
    // categoría (ej: "HEMATOLOGIA"). Siempre editable.
    foreach ($seccionesPreCargadas as &$sec) {
        if (count($sec['productos']) === 1) {
            $sec['titulo'] = $sec['productos'][0]['nombre'];
        } else {
            $sec['titulo'] = $sec['titulo_categoria'] ?: 'ANÁLISIS';
        }
        unset($sec['titulo_categoria']);
    }
    unset($sec);

    if (Yii::$app->request->isPost) {
        $resultado = $this->resultadosService->guardarResultados($id, Yii::$app->request->post());
        if ($resultado['exito']) {
            $this->actionGenerarPdf($id);
            return $this->redirect(['resultados', 'id' => $id, 'status' => 'updated']);
        }
    }

    return $this->render('resultados', [
        'model'                 => $model,
        'secciones'             => $secciones,
        'seccionesPreCargadas'  => $seccionesPreCargadas,
        'parametrosDisponibles' => $parametrosDisponibles,
    ]);
}

public function actionResultadosv2($id)
{
    //$this->permisosService->verificarAdministrador();
     if (Yii::$app->user->isGuest) {
        return $this->redirect(['default/login']);
    }

    $model    = $this->ordenesService->findModel($id);
    $secciones = $this->resultadosService->getSeccionesDeOrden($id);
    $parametrosDisponibles = $this->resultadosService->getParametrosDisponibles($id);

    if (Yii::$app->request->isPost) {
        $resultado = $this->resultadosService->guardarResultados($id, Yii::$app->request->post());
        if ($resultado['exito']) {
            return $this->redirect(['resultados', 'id' => $id, 'status' => 'updated']);
        }
    }

    return $this->render('resultados', [
        'model'                 => $model,
        'secciones'             => $secciones,
        'parametrosDisponibles' => $parametrosDisponibles,
    ]);
}


public function actionGenerarPdf($id)
{
    $model     = $this->ordenesService->findModel($id);
    $secciones = $this->resultadosService->getSeccionesDeOrden($id);

    $htmlHeader = $this->renderPartial('resultados_pdf_header', [
        'model' => $model,
        'secciones' => $secciones,
    ]);
    $htmlFooter = $this->renderPartial('resultados_pdf_footer', [
        'model' => $model,
    ]);
    $htmlBody = $this->renderPartial('resultados_pdf_body', [
        'model'     => $model,
        'secciones' => $secciones,
    ]);

    $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
    $fontDirs = $defaultConfig['fontDir'];

    $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
    $fontData = $defaultFontConfig['fontdata'];

    $mpdf = new Mpdf([
        'format'        => 'Letter',
        'margin_top'    => 66,
        'margin_bottom' => 28,
        'margin_header' => 2.8,
        'margin_footer' => 12,
        'margin_left'   => 15,
        'margin_right'  => 15,
        'fontDir' => array_merge($fontDirs, [
            Yii::getAlias('@webroot') . '/assets_b/fonts',
        ]),
        'fontdata' => $fontData + [
            'arimo' => [
                'R' => 'Arimo-Regular.ttf',
                'B' => 'Arimo-Bold.ttf',
            ]
        ],
        'default_font' => 'arimo',
    ]);

    $mpdf->simpleTables = true  ;
    $mpdf->SetHTMLHeader($htmlHeader);
    $mpdf->SetHTMLFooter($htmlFooter);
    $mpdf->setAutoTopMargin = 'stretch';
    $mpdf->setAutoBottomMargin = 'stretch';

    // El header/footer de mPDF se renderiza de forma diferida durante Output(),
    // asi que la proteccion tiene que cubrir TODO el bloque, no solo WriteHTML
    set_error_handler(function () {
        return true;
    }, E_WARNING | E_NOTICE | E_DEPRECATED);

    $mpdf->WriteHTML($htmlBody);

    $nombrePacienteSlug = $this->limpiarNombreCarpeta($model->paciente_nombre);
    $nombreCarpetaOrden = 'PACIENTE_' . $nombrePacienteSlug . '_ORDEN_' . $model->idorden;

    $carpeta = 'C:/xampp/htdocs/LABORATORIOOSINAGA/imagen/resultados/' . $nombreCarpetaOrden;
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0775, true);
    }

    $nombreArchivo = 'RESULTADO_ORDEN_N' . $model->idorden . '.pdf';
    $rutaCompleta  = $carpeta . '/' . $nombreArchivo;

    $mpdf->Output($rutaCompleta, \Mpdf\Output\Destination::FILE);

    restore_error_handler();

    return $rutaCompleta;
}
private function limpiarNombreCarpeta($texto)
{
    $texto = trim($texto);
    $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    $texto = preg_replace('/[^A-Za-z0-9]+/', '_', $texto);
    $texto = trim(preg_replace('/_+/', '_', $texto), '_');
    return strtoupper($texto);
}
    

public function actionDescargarPdf($id)
{
    $model = $this->ordenesService->findModel($id);

    $nombrePacienteSlug = $this->limpiarNombreCarpeta($model->paciente_nombre);
    $nombreCarpetaOrden = 'PACIENTE_' . $nombrePacienteSlug . '_ORDEN_' . $model->idorden;
    $nombreArchivo      = 'RESULTADO_ORDEN_N' . $model->idorden . '.pdf';

    $rutaCompleta = 'C:/xampp/htdocs/LABORATORIOOSINAGA/imagen/resultados/' . $nombreCarpetaOrden . '/' . $nombreArchivo;

    if (!file_exists($rutaCompleta)) {
        throw new \yii\web\NotFoundHttpException('El PDF todavía no fue generado.');
    }

    return Yii::$app->response->sendFile($rutaCompleta, $nombreArchivo);
}
}
<?php

namespace app\modules\admin\controllers;

use app\models\Inventario;
use app\models\ProductoCategoria;
use app\models\InventarioLoteMovimiento;
use app\models\Productos;
use app\models\ProductosGaleria;
use app\models\ProductosSearch;
use app\models\ProductosTallas;
use app\models\Promociones;
use app\components\InterfaceGestionProyecto;
use app\components\InterfacePermisosService;
use app\models\InventarioLote;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * ProductosController implements the CRUD actions for Productos model.
 */
class ProductosController extends Controller
{
    private $gestionProyectAService;
    private $permisosService;
    /**
     * @inheritdoc
     */

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }


      public function __construct($id, $module, InterfaceGestionProyecto $gestionProyectAService, InterfacePermisosService $permisosService, $config = [])
    {
        $this->gestionProyectAService = $gestionProyectAService;
        $this->permisosService = $permisosService;
        parent::__construct($id, $module, $config);
    }


    public function actionIndex()
    {
        $this->permisosService->verificarAdministrador();
        $searchModel = new ProductosSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams, null, 'productosg');
        $dataProvider->setSort(['defaultOrder' => ['idproducto' => SORT_DESC]]);
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }


    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }


     public function actionCreate()
    {
        $this->permisosService->verificarAdministrador();
        $resultado = $this->gestionProyectAService->nuevoProyecto(Yii::$app->request->post());
        if ($resultado && $resultado['exito']) {
            return $this->redirect(['index']);

       }

        $this->gestionProyectAService->limpiarFlashes();
        return $this->render('create', [
            'model' => $resultado['model'],

        ]);
    }


     public function actionUpdate($id)
    {
       $this->permisosService->verificarAdministrador();
        $resultado = $this->gestionProyectAService->actualizarProyecto(Yii::$app->request->post(), $id);
        if ($resultado && $resultado['exito']) {
            return $this->redirect(['index']);
        }
        $this->gestionProyectAService->limpiarFlashes();
        $model = $resultado['model'];
        return $this->render('update', [
            'model' => $resultado['model'],

        ]);
    }

    public function actionDelete($id)
    {
         $this->permisosService->verificarAdministrador();
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Productos::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }


    public function actionPromocion($id)
    {
        $model = Promociones::findOne(['idproducto' => $id]);
        if (empty($model))
            $model = new Promociones();

        $model->idproducto = $id;
        $model->fecha_registro = date('Y-m-d H:i:s');
        $model->estado = '1';
        if ($model->load(Yii::$app->request->post())) {
            if ($model->save()) {
                return $this->redirect(['index', 'id' => Yii::$app->request->post('id')]);
            }
        }
        return $this->render('promociones', ['id' => $id, 'model' => $model]);

    }

    public function actionInventario($id)
    {
        $this->permisosService->verificarAdministrador();
        date_default_timezone_set('America/La_Paz');
        $query = Inventario::find();
        $lista = new ActiveDataProvider([
            'query' => $query,
        ]);
        $producto = Productos::findOne(['idproducto' => $id]);
        $query->orderBy(['fecha_registro' => SORT_DESC, 'idtalla' => SORT_DESC])->andWhere(['idproducto' => $id])->all();
        $model = new Inventario();
        $model->idproducto = $id;
        $model->fecha_registro = date('Y-m-d H:i:s');
        $model->detalle = "Movimiento de Administracion";
        $saldo = 0;

        if ($model->load(Yii::$app->request->post())) {
            $fechaVencimiento = Yii::$app->request->post('fecha_vencimiento');

            if ($model->estado == 1) {
                // ENTRADA
                $model->setSaldo();
                if ($model->save()) {
                    InventarioLote::crearLote($id, $model->movimiento, $fechaVencimiento, $model->idinventario);
                }
                return $this->redirect(['inventario', 'id' => $id]);
            } else {
                // SALIDA (estado == 0)
                $cantidadSalida = $model->movimiento;
                $detalle = InventarioLote::descontarFEFO($id, $cantidadSalida);
                if ($detalle === false) {
                    Yii::$app->session->setFlash('error', 'No hay stock suficiente en los lotes activos para esta salida.');
                } else {
                    $model->setSaldo();
                    if ($model->save()) {
                        InventarioLoteMovimiento::registrar($model->idinventario, $detalle);
                    }
                }
                return $this->redirect(['inventario', 'id' => $id]);
            }
        } else {
            if ($producto->tallas) {
                $saldo = array();
                foreach ($producto->tallas as $val) {
                    $last = Inventario::find()->orderBy(['fecha_registro' => SORT_DESC])->where(['idproducto' => $id, 'idtalla' => $val['idtalla']])->one();
                    if (!empty($last)) {
                        $saldo[$val['valor']] = $last->saldo;
                    } else {
                        $saldo[$val['valor']] = 0;
                    }
                }
            } else {
                $last = Inventario::find()->orderBy(['fecha_registro' => SORT_DESC])->where(['idproducto' => $id])->one();
                if (!empty($last)) {
                    $saldo = $last->saldo;
                }
            }
        }

        $lotes = InventarioLote::lotesActivos($id);
        $stockTotal = InventarioLote::stockTotal($id);

        return $this->render('inventario', [
            'lista' => $lista,
            'model' => $model,
            'producto' => Productos::findOne($id),
            'saldo' => $saldo,
            'lotes' => $lotes,
            'stockTotal' => $stockTotal,
        ]);
    }

    public function actionGaleria($id)
    {
        $model = new ProductosGaleria();
        $model->idproducto = $id;
        $galeria = $model->findAll(['idproducto' => $id]);
        return $this->render('galeria', ['id' => $id, 'model' => $model, 'galeria' => $galeria]);
    }

    public function actionUpload()
    {
        $model = new ProductosGaleria();

        $model->file = UploadedFile::getInstance($model, 'file');
        $name = Yii::$app->security->generateRandomString();
        if ($model->upload($name)) {
            $model->archivo = $name . '.' . $model->file->extension;
            $model->fecha_registro = date('Y-m-d H:i:s');
            $model->idproducto = Yii::$app->request->post('id');
            if ($model->save(false)) {
                echo Json::encode([]);
                return $this->redirect(['galeria', 'id' => Yii::$app->request->post('id')]);
            }
        }
        var_dump($model->getErrors());
        die();
        return false;
    }

    public function actionErase()
    {
        $model = new ProductosGaleria();
        $id = Yii::$app->request->post('key');
        $model = $model->findOne(['idgaleria' => $id]);
        if (empty($model->attributes)) {
            return false;
        }
        $idp = $model->idproducto;
        $url = "/imagen/productos/" . $model->archivo;
        $url = Yii::$app->basePath . $url;
        if ($model->delete()) {
            unlink($url);
            return $this->redirect(['galeria', 'id' => $idp]);
        }
        return false;
    }

    public function actionUp($id)
    {
        if (!Yii::$app->request->isAjax)
            $this->redirect(['index']);
        $model = new Productos();
        $model2 = new Productos();
        $model = $model->findOne(['idproducto' => $id]);
        $model2 = $model2->findOne(['idproducto' => $id - 1]);
        $model->idproducto = 0;
        $model->save();
        $model->idproducto = $model2->idproducto;
        $model2->idproducto = $id;
        $model2->save();
        $model->save();
    }

    public function actionDown($id)
    {
        if (!Yii::$app->request->isAjax)
            $this->redirect(['index']);
        $model = new Productos();
        $model2 = new Productos();
        $model = $model->findOne(['idproducto' => $id]);
        $model2 = $model2->findOne(['idproducto' => $id + 1]);
        $model->idproducto = $model2->idproducto;
        $model2->idproducto = $id;
        $model->save();
    }

    public function actionEstado($id)
    {
        if (!Yii::$app->request->isAjax)
            $this->redirect(['index']);
        $model = new Productos();
        $model = $model->findOne(['idproducto' => $id]);
        $model->estado = (string)!$model->estado;
        $model->save();
    }


    public function actionEstade($id)
    {
        if (!Yii::$app->request->isAjax)
            $this->redirect(['index']);
        $model = new Productos();
        $model = $model->findOne(['idproducto' => $id]);
        $model->estade = (string)!$model->estade;
        $model->save();
    }

    public function actionDestacado($id)
    {
        if (!Yii::$app->request->isAjax)
            $this->redirect(['index']);
        $model = new Productos();
        $model = $model->findOne(['idproducto' => $id]);
        $model->destacado = (string)!$model->destacado;
        $model->save();
    }

    public function actionExport()
    {
        \moonland\phpexcel\Excel::export([
            'models' => Productos::find()->all(),
            'columns' => ['idusuario', 'name', 'apellido', 'phone', 'email', 'ciudad'],
            'headers' => ['idusuario' => 'idusuario', 'Nombre' => 'name', 'Apellido' => 'apellido', 'Telefono' => 'phone', 'Email' => 'email', 'Ciudad' => 'ciudad'],
        ]);
    }


    public function actionAdd_talla()
    {
        if (Yii::$app->request->isAjax && isset(Yii::$app->request->isGet)) {
            $detalle = new ProductosTallas();
            return $this->renderAjax('_newRowRedes', array(
                'model' => $detalle,
                'index' => Yii::$app->request->get('index'),
            ));
        }
    }




    /***/

    public function actionMateriales($id)
{
    $this->permisosService->verificarAdministrador();
    $model = Productos::findOne($id);
    if (!$model) {
        throw new NotFoundHttpException('Producto no encontrado.');
    }

    $vinculos = \app\models\ProductoMaterial::find()
        ->where(['idproducto_servicio' => $id])
        ->with('material')
        ->all();

    // Materiales disponibles: solo productos de categoría "productosg" (los insumos)
    $materialesDisponibles = Productos::find()
        ->innerJoinWith('categoria')
        ->where(['categorias_servicio.alias' => 'productosg'])
        ->orderBy('productos.titulo')
        ->all();

    return $this->render('materiales', [
        'model' => $model,
        'vinculos' => $vinculos,
        'materialesDisponibles' => $materialesDisponibles,
    ]);
}

public function actionMaterialesCrear($id)
{
    $this->permisosService->verificarAdministrador();
    $post = Yii::$app->request->post();

    $idmaterial = (int)($post['idproducto_material'] ?? 0);
    $cantidad   = (float)($post['cantidad'] ?? 1);

    if ($idmaterial <= 0) {
        Yii::$app->session->setFlash('error', 'Elegí un material válido.');
        return $this->redirect(['materiales', 'id' => $id]);
    }

    // Evitar duplicados: si ya existe el vínculo, actualizamos la cantidad en vez de duplicar
    $vinculo = \app\models\ProductoMaterial::findOne([
        'idproducto_servicio' => $id,
        'idproducto_material' => $idmaterial,
    ]);

    if (!$vinculo) {
        $vinculo = new \app\models\ProductoMaterial();
        $vinculo->idproducto_servicio = $id;
        $vinculo->idproducto_material = $idmaterial;
    }
    $vinculo->cantidad = $cantidad > 0 ? $cantidad : 1;
    $vinculo->save();

    return $this->redirect(['materiales', 'id' => $id]);
}

public function actionMaterialesEditar($id, $idvinculo)
{
    $this->permisosService->verificarAdministrador();
    $vinculo = \app\models\ProductoMaterial::findOne($idvinculo);
    if ($vinculo) {
        $vinculo->cantidad = (float)(Yii::$app->request->post('cantidad') ?? 1);
        $vinculo->save();
    }
    return $this->redirect(['materiales', 'id' => $id]);
}

public function actionMaterialesQuitar($id, $idvinculo)
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    $vinculo = \app\models\ProductoMaterial::findOne($idvinculo);
    if (!$vinculo) {
        return ['exito' => false, 'mensaje' => 'No encontrado.'];
    }
    $vinculo->delete();
    return ['exito' => true];
}
}
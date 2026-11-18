<?php

namespace app\controllers;

use app\components\InterfaceCatalogoService;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\components\InterfacePermisosService;

class CatalogosController extends Controller
{
    private $catalogoService;
    private $permisosService;

    public function __construct($id, $module, InterfaceCatalogoService $catalogoService, InterfacePermisosService $permisosService, $config = [])
    {
        $this->catalogoService = $catalogoService;
        $this->permisosService = $permisosService;
        parent::__construct($id, $module, $config);
    }

    public function actions(): array
    {
        return [
            'error' => ['class' => 'yii\web\ErrorAction'],
        ];
    }

    // ----------------- LISTAR -----------------
    public function actionSucursalListar()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->listar('sucursal', Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $resultado['searchModel'],
            'dataProvider' => $resultado['dataProvider'],
            'render' => 'listasucursal',
        ]);
    }

    public function actionSectorListar()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->listar('sector', Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $resultado['searchModel'],
            'dataProvider' => $resultado['dataProvider'],
            'render' => 'listasector',
        ]);
    }

    // Repetir igual para categoria_servicio, audiencia, canal_marketing, tipo_tarea, rol...

    // ----------------- CREATE -----------------
    public function actionSucursalCreate()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);

        if (Yii::$app->request->isGet) {
            $this->catalogoService->limpiarFlashes();
        }

        $resultado = $this->catalogoService->crear('sucursal', Yii::$app->request->post());

        if ($resultado['exito']) {
            Yii::$app->session->setFlash('success', 'Sucursal creada exitosamente.');
            return $this->redirect(['sucursal-listar']);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'createsucursal',
        ]);
    }


    public function actionSectorCreate()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->crear('sector', Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['sector-listar']);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'createsector',
        ]);
    }

    // ----------------- VIEW -----------------
    public function actionSucursalView($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $model = $this->catalogoService->obtener('sucursal', $id);

        return $this->render('index', [
            'model' => $model,
            'render' => 'viewsucursal',
        ]);
    }

    public function actionSectorView($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $model = $this->catalogoService->obtener('sector', $id);

        return $this->render('index', [
            'model' => $model,
            'render' => 'viewsector',
        ]);
    }

    // ----------------- UPDATE -----------------
    public function actionSucursalUpdate($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->actualizar('sucursal', $id, Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['sucursal-view', 'id' => $id]);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'updatesucursal',
        ]);
    }

    public function actionSectorUpdate($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->actualizar('sector', $id, Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['sector-view', 'id' => $id]);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'updatesector',
        ]);
    }

    // ----------------- DELETE -----------------
    public function actionSucursalDelete($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $this->catalogoService->eliminar('sucursal', $id);
        return $this->redirect(['sucursal-listar']);
    }

    public function actionSectorDelete($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $this->catalogoService->eliminar('sector', $id);
        return $this->redirect(['sector-listar']);
    }


    public function actionCategoriaServicioListar()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->listar('categoria_servicio', Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $resultado['searchModel'],
            'dataProvider' => $resultado['dataProvider'],
            'render' => 'listacategoriaservicio',
        ]);
    }

    public function actionAudienciaListar()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->listar('audiencia', Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $resultado['searchModel'],
            'dataProvider' => $resultado['dataProvider'],
            'render' => 'listaaudiencia',
        ]);
    }

    public function actionCanalMarketingListar()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->listar('canal_marketing', Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $resultado['searchModel'],
            'dataProvider' => $resultado['dataProvider'],
            'render' => 'listacanalmarketing',
        ]);
    }

    public function actionTipoTareaListar()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->listar('tipo_tarea', Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $resultado['searchModel'],
            'dataProvider' => $resultado['dataProvider'],
            'render' => 'listatipotarea',
        ]);
    }

    public function actionRolListar()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->listar('rol', Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $resultado['searchModel'],
            'dataProvider' => $resultado['dataProvider'],
            'render' => 'listarol',
        ]);
    }

// ----------------- CREATE -----------------
    public function actionCategoriaServicioCreate()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->crear('categoria_servicio', Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['categoria-servicio-listar']);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'createcategoriaservicio',
        ]);
    }

    public function actionAudienciaCreate()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->crear('audiencia', Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['audiencia-listar']);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'createaudiencia',
        ]);
    }

    public function actionCanalMarketingCreate()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->crear('canal_marketing', Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['canal-marketing-listar']);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'createcanalmarketing',
        ]);
    }

    public function actionTipoTareaCreate()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->crear('tipo_tarea', Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['tipo-tarea-listar']);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'createtipotarea',
        ]);
    }

    public function actionRolCreate()
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->crear('rol', Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['rol-listar']);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'createrol',
        ]);
    }

// ----------------- VIEW -----------------
    public function actionCategoriaServicioView($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $model = $this->catalogoService->obtener('categoria_servicio', $id);

        return $this->render('index', [
            'model' => $model,
            'render' => 'viewcategoriaservicio',
        ]);
    }

    public function actionAudienciaView($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $model = $this->catalogoService->obtener('audiencia', $id);

        return $this->render('index', [
            'model' => $model,
            'render' => 'viewaudiencia',
        ]);
    }

    public function actionCanalMarketingView($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $model = $this->catalogoService->obtener('canal_marketing', $id);

        return $this->render('index', [
            'model' => $model,
            'render' => 'viewcanalmarketing',
        ]);
    }

    public function actionTipoTareaView($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $model = $this->catalogoService->obtener('tipo_tarea', $id);

        return $this->render('index', [
            'model' => $model,
            'render' => 'viewtipotarea',
        ]);
    }

    public function actionRolView($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $model = $this->catalogoService->obtener('rol', $id);

        return $this->render('index', [
            'model' => $model,
            'render' => 'viewrol',
        ]);
    }

// ----------------- UPDATE -----------------
    public function actionCategoriaServicioUpdate($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->actualizar('categoria_servicio', $id, Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['categoria-servicio-view', 'id' => $id]);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'updatecategoriaservicio',
        ]);
    }

    public function actionAudienciaUpdate($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->actualizar('audiencia', $id, Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['audiencia-view', 'id' => $id]);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'updateaudiencia',
        ]);
    }

    public function actionCanalMarketingUpdate($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->actualizar('canal_marketing', $id, Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['canal-marketing-view', 'id' => $id]);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'updatecanalmarketing',
        ]);
    }

    public function actionTipoTareaUpdate($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->actualizar('tipo_tarea', $id, Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['tipo-tarea-view', 'id' => $id]);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'updatetipotarea',
        ]);
    }

    public function actionRolUpdate($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $resultado = $this->catalogoService->actualizar('rol', $id, Yii::$app->request->post());

        if ($resultado['exito']) {
            return $this->redirect(['rol-view', 'id' => $id]);
        }

        return $this->render('index', [
            'model' => $resultado['model'],
            'render' => 'updaterol',
        ]);
    }

// ----------------- DELETE -----------------
    public function actionCategoriaServicioDelete($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $this->catalogoService->eliminar('categoria_servicio', $id);
        return $this->redirect(['categoria-servicio-listar']);
    }

    public function actionAudienciaDelete($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $this->catalogoService->eliminar('audiencia', $id);
        return $this->redirect(['audiencia-listar']);
    }

    public function actionCanalMarketingDelete($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $this->catalogoService->eliminar('canal_marketing', $id);
        return $this->redirect(['canal-marketing-listar']);
    }

    public function actionTipoTareaDelete($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $this->catalogoService->eliminar('tipo_tarea', $id);
        return $this->redirect(['tipo-tarea-listar']);
    }

    public function actionRolDelete($id)
    {
        $this->permisosService->verificarRol(['administrador', 'gestor']);
        $this->catalogoService->eliminar('rol', $id);
        return $this->redirect(['rol-listar']);
    }





    // ----- Repetir igual para: categoria_servicio, audiencia, canal_marketing, tipo_tarea, rol -----
}

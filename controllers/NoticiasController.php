<?php

namespace app\controllers;

use app\components\InterfaceBanners;
use app\components\InterfaceNoticias;
use Yii;
use yii\web\Controller;

/**
 * Web pública · Blog (menú "Blog"): /noticias, /noticias/categoria/<slug>, /noticias/<slug>.
 * Contenido: Panel → Blog; encabezado: Panel → Banners → Blog. Reglas de URL en config/web.php.
 */
class NoticiasController extends Controller
{
    private InterfaceNoticias $noticias;
    private InterfaceBanners $banners;

    public function __construct($id, $module, InterfaceNoticias $noticias, InterfaceBanners $banners, $config = [])
    {
        $this->noticias = $noticias;
        $this->banners = $banners;
        parent::__construct($id, $module, $config);
    }

    /** Listado: 12 por página, búsqueda (?q=) y categoría. */
    public function actionIndex($categoria = null)
    {
        return $this->render('index', $this->noticias->paraWeb(Yii::$app->request->get('q'), $categoria) + [
            'encabezado' => $this->banners->encabezado('noticias'),
            'lateral' => $this->noticias->barraLateral(),
        ]);
    }

    public function actionVer($slug)
    {
        return $this->render('ver', [
            'noticia' => $this->noticias->porSlug($slug),
            'encabezado' => $this->banners->encabezado('noticias'),
            'lateral' => $this->noticias->barraLateral(),
        ]);
    }
}

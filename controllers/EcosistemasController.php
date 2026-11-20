<?php

namespace app\controllers;

use app\components\InterfaceBanners;
use app\components\InterfaceEcosistemas;
use app\components\InterfacePaginas;
use yii\web\Controller;

/**
 * Web pública · Ecosistemas: /ecosistemas (listado) y /ecosistemas/<slug> (página de cada uno).
 * Contenido: Panel → Ecosistemas; Modelo de valor y países: Panel → Páginas → Ejes estratégicos;
 * encabezado del listado: Panel → Banners → Ecosistemas. Reglas de URL en config/web.php.
 */
class EcosistemasController extends Controller
{
    private InterfaceEcosistemas $ecosistemas;
    private InterfacePaginas $paginas;
    private InterfaceBanners $banners;

    public function __construct($id, $module, InterfaceEcosistemas $ecosistemas, InterfacePaginas $paginas, InterfaceBanners $banners, $config = [])
    {
        $this->ecosistemas = $ecosistemas;
        $this->paginas = $paginas;
        $this->banners = $banners;
        parent::__construct($id, $module, $config);
    }

    /** Los activos de a 4 por número + Modelo de creación de valor + llamado a ver proyectos + prefooter. */
    public function actionIndex()
    {
        $estrategia = $this->paginas->estrategiaWeb();
        return $this->render('index', [
            'ecosistemas' => $this->ecosistemas->paraMenu(),
            'encabezado' => $this->banners->encabezado('ecosistemas'),
            'modelo' => $estrategia['modelo'],
            'propuesta' => $estrategia['propuesta'],
        ]);
    }

    /** Página de un ecosistema, con el anterior y el siguiente (en orden de número, dando la vuelta). */
    public function actionVer($slug)
    {
        $ecosistema = $this->ecosistemas->porSlug($slug);
        $todos = $this->ecosistemas->paraMenu();
        $pos = 0;
        foreach ($todos as $i => $e) {
            if ($e->idecosistema === $ecosistema->idecosistema) {
                $pos = $i;
            }
        }
        $total = count($todos);
        return $this->render('ver', [
            'ecosistema' => $ecosistema,
            'todos' => $todos,
            'posicion' => $pos + 1,
            'anterior' => $total > 1 ? $todos[($pos - 1 + $total) % $total] : null,
            'siguiente' => $total > 1 ? $todos[($pos + 1) % $total] : null,
            'encabezado' => $this->banners->encabezado('ecosistemas'), // fondo si el ecosistema no tiene foto de banner
            'modelo' => $this->paginas->estrategiaWeb()['modelo'],
            'paises' => $this->paginas->paisesExpansion(),
        ]);
    }
}

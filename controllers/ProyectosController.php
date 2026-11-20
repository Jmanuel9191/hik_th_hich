<?php

namespace app\controllers;

use app\components\InterfaceBanners;
use app\components\InterfaceInteresInversion;
use app\components\InterfaceProyectosWeb;
use app\components\MediosProyecto;
use app\models\QuieroInvertirForm;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Web pública · Proyectos (solo inversores habilitados): /proyectos (listado), /proyectos/<slug> (página de cada uno),
 * y las fotos, videos y documentos protegidos. Quién puede ver qué: ProyectoWebService. Reglas de URL en config/web.php.
 *  - Sin sesión → al ingreso de inversores (y vuelve acá después).
 *  - Aprobado sin NDA → a aceptar el NDA. En revisión o bloqueado → a Mi cuenta.
 */
class ProyectosController extends Controller
{
    private InterfaceProyectosWeb $proyectos;
    private InterfaceBanners $banners;
    private InterfaceInteresInversion $interes;

    public function __construct($id, $module, InterfaceProyectosWeb $proyectos, InterfaceBanners $banners, InterfaceInteresInversion $interes, $config = [])
    {
        $this->interes = $interes;
        $this->proyectos = $proyectos;
        $this->banners = $banners;
        parent::__construct($id, $module, $config);
    }

    /** Listado: barra lateral (buscar y ecosistemas de sus áreas) y tarjetas de a 3. */
    public function actionIndex($ecosistema = null)
    {
        $acceso = $this->exigirAcceso();
        if ($acceso instanceof Response) {
            return $acceso;
        }
        $this->noIndexar();
        return $this->render('index', $this->proyectos->listado($acceso, Yii::$app->request->get('q'), $ecosistema) + [
            'encabezado' => $this->banners->encabezado('proyectos'),
            'lateral' => $this->proyectos->barraLateral($acceso),
            'inversor' => $acceso['inversor'],
        ]);
    }

    /** Página de un proyecto: banner, menú de anclas y secciones. */
    public function actionVer($slug)
    {
        $acceso = $this->exigirAcceso();
        if ($acceso instanceof Response) {
            return $acceso;
        }
        $this->noIndexar();
        $p = $this->proyectos->porSlug($acceso, (string) $slug);
        $previo = $this->interes->dePara((int) $acceso['inversor']->idusuario, (int) $p->idproyecto);
        return $this->render('ver', $this->proyectos->armarPagina($p) + [
            'proyecto' => $p,
            'medios' => MediosProyecto::web((int) $p->idproyecto),
            'vistaPrevia' => false,
            'interes' => [ // "Quiero invertir"
                'previo' => $previo,
                'puede' => $this->interes->puedeEnviar($p, $previo),
                'form' => QuieroInvertirForm::para($acceso['inversor'], $previo),
            ],
        ]);
    }

    /** Foto protegida, achicada al ancho pedido. El navegador la guarda 30 días (privada: no la guardan proxies). */
    public function actionImagen($id, $carpeta, $archivo, $w = 960)
    {
        $acceso = $this->proyectos->acceso();
        if ($acceso['estado'] !== InterfaceProyectosWeb::OK) {
            throw new NotFoundHttpException();
        }
        Yii::$app->session->close(); // libera la sesión: las fotos se bajan en paralelo
        $f = $this->proyectos->archivoImagen($acceso, (int) $id, (string) $carpeta, (string) $archivo, (int) $w);
        return $this->enviarImagen($f['ruta'], $f['mime']);
    }

    public function actionDocumento($id)
    {
        $acceso = $this->proyectos->acceso();
        if ($acceso['estado'] !== InterfaceProyectosWeb::OK) {
            throw new NotFoundHttpException();
        }
        $d = $this->proyectos->archivoDocumento($acceso, (int) $id);
        Yii::$app->response->headers->set('Cache-Control', 'private, no-store');
        Yii::$app->response->headers->set('X-Content-Type-Options', 'nosniff');
        Yii::$app->response->headers->set('X-Robots-Tag', 'noindex');
        // PDF y fotos se abren en el navegador; Word, Excel y el resto se descargan
        return Yii::$app->response->sendFile($d['ruta'], $d['nombre'], array_filter(['inline' => $d['inline'], 'mimeType' => $d['mime']]));
    }

    /**
     * Video subido, para verlo en la página (no tiene botón de descarga). Soporta "Range": se puede adelantar
     * sin bajarlo entero, y el navegador pide de a partes.
     */
    public function actionVideo($id)
    {
        $acceso = $this->proyectos->acceso();
        if ($acceso['estado'] !== InterfaceProyectosWeb::OK) {
            throw new NotFoundHttpException();
        }
        Yii::$app->session->close(); // el navegador pide el video de a partes, en paralelo con el resto
        $v = $this->proyectos->archivoVideo($acceso, (int) $id);
        $r = Yii::$app->response;
        $r->headers->set('Cache-Control', 'private, max-age=86400');
        $r->headers->set('X-Content-Type-Options', 'nosniff');
        $r->headers->set('X-Robots-Tag', 'noindex');
        $r->headers->set('Accept-Ranges', 'bytes');
        return $r->sendFile($v['ruta'], basename($v['ruta']), ['inline' => true, 'mimeType' => $v['mime']]);
    }

    /** Acceso del inversor; si no puede ver proyectos, la redirección que corresponde. */
    private function exigirAcceso()
    {
        $acceso = $this->proyectos->acceso();
        switch ($acceso['estado']) {
            case InterfaceProyectosWeb::OK:
                return $acceso;
            case InterfaceProyectosWeb::VISITANTE:
                \app\modules\cuenta\Module::sesion()->setReturnUrl(Yii::$app->request->url);
                Yii::$app->session->setFlash('cuenta', ['info', 'Ingrese con su cuenta de inversor para ver los proyectos.']);
                return $this->redirect(['/cuenta/acceso/ingresar']);
            case InterfaceProyectosWeb::NDA:
                return $this->redirect(['/cuenta/panel/nda']);
            default:
                return $this->redirect(['/cuenta/panel/index']);
        }
    }

    /** Páginas confidenciales: que no las guarden los buscadores. */
    private function noIndexar(): void
    {
        $this->view->registerMetaTag(['name' => 'robots', 'content' => 'noindex, nofollow']);
        Yii::$app->response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        Yii::$app->response->headers->set('Cache-Control', 'private, no-cache');
    }

    private function enviarImagen(string $ruta, string $mime)
    {
        $etag = '"' . md5($ruta . filemtime($ruta) . filesize($ruta)) . '"';
        $r = Yii::$app->response;
        $r->headers->set('Cache-Control', 'private, max-age=2592000, immutable');
        $r->headers->set('ETag', $etag);
        $r->headers->set('X-Content-Type-Options', 'nosniff');
        $r->headers->set('X-Robots-Tag', 'noindex');
        if (trim((string) Yii::$app->request->headers->get('If-None-Match')) === $etag) {
            $r->statusCode = 304;
            return $r;
        }
        return $r->sendFile($ruta, basename($ruta), ['inline' => true, 'mimeType' => $mime]);
    }
}

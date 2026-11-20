<?php

namespace app\controllers;

use app\components\InterfaceBanners;
use app\components\InterfaceEcosistemas;
use app\components\InterfaceEquipo;
use app\components\InterfacePaginas;
use app\models\PaginaSeccion;
use yii\web\Controller;

/**
 * Web pública · Nosotros y sus subpáginas (menú "Nosotros ▾"). Contenido: Panel → Páginas → Quiénes somos;
 * encabezado: Panel → Banners → pestaña de cada subpágina; Liderazgo: Panel → Equipo;
 * Ejes estratégicos: Panel → Ecosistemas (destacados) + Panel → Páginas → Ejes estratégicos.
 * Título y pestaña de banner de cada acción: PaginaSeccion::NOSOTROS_WEB (la clave es el id de la acción y de la vista).
 * Ruta: /nosotros (regla en config/web.php) y /nosotros/<subpágina>.
 */
class NosotrosController extends Controller
{
    private InterfaceBanners $banners;
    private InterfacePaginas $paginas;
    private InterfaceEquipo $equipo;
    private InterfaceEcosistemas $ecosistemas;

    public function __construct($id, $module, InterfaceBanners $banners, InterfacePaginas $paginas, InterfaceEquipo $equipo, InterfaceEcosistemas $ecosistemas, $config = [])
    {
        $this->banners = $banners;
        $this->paginas = $paginas;
        $this->equipo = $equipo;
        $this->ecosistemas = $ecosistemas;
        parent::__construct($id, $module, $config);
    }

    /** Quiénes somos: tarjetas sobre el encabezado, texto, mensaje del CEO y bloques adicionales. */
    public function actionIndex()
    {
        $n = $this->paginas->nosotrosWeb();
        return $this->pagina(['quienes' => $n['quienes'], 'tarjetas' => $n['tarjetas'], 'ceo' => $n['ceo'], 'bloques' => $n['bloques']]);
    }

    /** Propósito, Visión y Misión + Valores y Principios + tarjetas (Conectar · Transformar · Multiplicar). */
    public function actionPropositoVisionMision()
    {
        $n = $this->paginas->nosotrosWeb();
        return $this->pagina([
            'proposito' => $n['proposito'], 'vision' => $n['vision'], 'mision' => $n['mision'],
            'valores' => $n['valores'], 'principios' => $n['principios'],
            'tarjetas' => $n['tarjetas'],
        ]);
    }

    /** Liderazgo (Panel → Equipo) + tarjetas (Conectar · Transformar · Multiplicar). */
    public function actionLiderazgo()
    {
        return $this->pagina(['equipo' => $this->equipo->paraWeb(), 'tarjetas' => $this->paginas->nosotrosWeb()['tarjetas']]);
    }

    /** Ejes (Ecosistemas destacados) + Modelo de creación de valor + Visión de expansión. */
    public function actionEjesEstrategicos()
    {
        return $this->pagina($this->paginas->estrategiaWeb() + ['ecosistemas' => $this->ecosistemas->destacadosWeb()]);
    }

    /** Gobierno corporativo y ESG + Propuesta para socios + ¿Por qué asociarse? */
    public function actionGobiernoYSocios()
    {
        return $this->pagina($this->paginas->gobiernoWeb());
    }

    /** Dibuja la vista de la acción con su título y el banner de su pestaña. */
    private function pagina(array $datos): string
    {
        $accion = $this->action->id;
        [$titulo, $ubicacion] = PaginaSeccion::NOSOTROS_WEB[$accion];
        return $this->render($accion, $datos + [
            'titulo' => $titulo,
            'encabezado' => $this->banners->encabezado($ubicacion),
        ]);
    }
}

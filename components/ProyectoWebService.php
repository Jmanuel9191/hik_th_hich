<?php

namespace app\components;

use app\models\bloques\Bloque;
use app\models\Ecosistema;
use app\models\Inversor;
use app\models\NdaAceptacion;
use app\models\Proyecto;
use app\models\ProyectoDescarga;
use app\models\ProyectoDocumento;
use app\models\ProyectoImagen;
use app\models\ProyectoVideo;
use app\models\ProyectoSeccion;
use app\modules\cuenta\Module as Cuenta;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * Proyectos en la web (ver InterfaceProyectosWeb). Usa la sesión del inversor (Cuenta::sesion()),
 * no la del panel. Si no puede ver algo responde 404: no se revela qué proyectos existen.
 */
class ProyectoWebService implements InterfaceProyectosWeb
{
    const POR_PAGINA = 9;
    /** Carpetas de fotos que se sirven (en videos/, solo las miniaturas .jpg: el patrón no deja pasar otra cosa). */
    const CARPETAS = ['imagenes', 'bloques', 'galeria', 'videos'];

    private $inversores;
    /** Acceso ya calculado en este pedido (las fotos de una página lo piden muchas veces). */
    private static array $accesos = [];

    public function __construct(InterfaceInversores $inversores)
    {
        $this->inversores = $inversores;
    }

    public function acceso(): array
    {
        $sesion = Cuenta::sesion();
        if ($sesion->isGuest) {
            return ['estado' => self::VISITANTE, 'inversor' => null, 'areas' => []];
        }
        $u = $sesion->identity;
        return self::$accesos[$u->idusuario] ??= $this->calcularAcceso($u);
    }

    private function calcularAcceso($u): array
    {
        $sin = fn($estado, $inv = null) => ['estado' => $estado, 'inversor' => $inv, 'areas' => []];
        if ((int) $u->idrol !== Rol::INVERSOR || $u->estado !== '1' || $this->inversores->motivoSinAcceso($u) !== null) {
            return $sin(self::SIN_ACCESO);
        }
        $inv = Inversor::find()->with(['areas', 'usuario'])->where(['idusuario' => $u->idusuario])->one();
        if ($inv === null) {
            return $sin(self::SIN_ACCESO);
        }
        if ($inv->estado_solicitud !== 'aprobado') {
            return $sin(self::PENDIENTE, $inv);
        }
        if (!NdaAceptacion::aceptoVigente($inv->idusuario)) {
            return $sin(self::NDA, $inv);
        }
        return ['estado' => self::OK, 'inversor' => $inv, 'areas' => array_map(fn($a) => (int) $a->idecosistema, $inv->areas)];
    }

    /* ---------- Listado ---------- */

    public function listado(array $acceso, ?string $busqueda, ?string $ecosistema): array
    {
        $eco = null;
        if ($ecosistema !== null && $ecosistema !== '') {
            $eco = Ecosistema::find()->where(['slug' => $ecosistema, 'idecosistema' => $acceso['areas'] ?: [0]])->one()
                ?? throw new NotFoundHttpException('Ese ecosistema no está entre sus áreas.');
        }
        $busqueda = mb_substr(trim((string) $busqueda), 0, 100);
        $q = Proyecto::publicados($eco ? [$eco->idecosistema] : $acceso['areas'])->with('ecosistema');
        if ($busqueda !== '') {
            $q->andWhere(['or', ['like', 'titulo', $busqueda], ['like', 'resumen', $busqueda], ['like', 'ciudad', $busqueda], ['like', 'pais', $busqueda]]);
        }
        return [
            'dataProvider' => new ActiveDataProvider([
                'query' => $q,
                'pagination' => ['pageSize' => self::POR_PAGINA, 'forcePageParam' => false],
                'sort' => false,
            ]),
            'ecosistema' => $eco,
            'busqueda' => $busqueda,
        ];
    }

    public function barraLateral(array $acceso): array
    {
        $cantidades = $this->cantidadesPorArea($acceso['areas']);
        $lista = [];
        foreach (Ecosistema::find()->where(['idecosistema' => $acceso['areas'] ?: [0]])->orderBy('numero')->all() as $e) {
            $lista[] = [$e, $cantidades[$e->idecosistema] ?? 0];
        }
        return $lista;
    }

    public function cantidadesPorArea(array $areas): array
    {
        return array_map('intval', Proyecto::publicados($areas)->select(['COUNT(*)', 'idecosistema'])->orderBy([])
            ->groupBy('idecosistema')->indexBy('idecosistema')->column());
    }

    /* ---------- Proyecto ---------- */

    public function porSlug(array $acceso, string $slug): Proyecto
    {
        return Proyecto::publicados($acceso['areas'])->with(['ecosistema', 'moneda'])->andWhere(['slug' => $slug])->one()
            ?? throw new NotFoundHttpException('No encontramos ese proyecto.');
    }

    public function armarPagina(Proyecto $p): array
    {
        $secciones = ProyectoSeccion::find()->with('bloques')->where(['idproyecto' => $p->idproyecto, 'visible' => '1'])
            ->orderBy(['orden' => SORT_ASC, 'idseccion' => SORT_ASC])->all();
        $armadas = [];
        $menu = [];
        $numero = 0;
        $usa = [];
        foreach ($secciones as $s) {
            $bloques = array_values(array_filter($s->bloques, fn($b) => $b->visible === '1' && $b->contenido() !== null));
            if (!$bloques && !$s->tieneEncabezado()) {
                continue;
            }
            $s->numero = $s->numerada === '1' ? ++$numero : null;
            if ($s->en_menu === '1' && $s->nombreMenu() !== '') {
                $menu[$s->ancla] = $s->nombreMenu();
            }
            foreach ($bloques as $b) {
                $usa[$b->tipo] = true;
            }
            $armadas[] = ['seccion' => $s, 'filas' => ProyectoSeccion::filas($bloques)];
        }
        // Ficha automática: primera en la página y en el menú (se puede apagar desde el banner)
        $ficha = $p->mostrar_ficha !== '0' ? $p->datosFicha() : null;
        if ($ficha !== null && ($ficha['datos'] || $ficha['avance'] !== null || $ficha['ronda'] !== null)) {
            $menu = ['ficha' => 'Ficha'] + $menu;
        } else {
            $ficha = null;
        }
        return [
            'ficha' => $ficha,
            'secciones' => $armadas,
            'menu' => $menu,
            'biblioteca' => self::biblioteca((int) $p->idproyecto, $usa),
        ];
    }

    /**
     * Fotos, videos y documentos del proyecto para los bloques que los muestran. Solo se consulta lo que algún
     * bloque usa ($usa[tipo] = true). Los documentos: solo los marcados "lo ven los inversores".
     */
    public static function biblioteca(int $idproyecto, array $usa): array
    {
        return [
            'fotos' => isset($usa['galeria_proyecto']) ? ProyectoImagen::find()->where(['idproyecto' => $idproyecto])
                ->orderBy(['orden' => SORT_ASC, 'idimagen' => SORT_ASC])->all() : [],
            'videos' => isset($usa['videos']) ? ProyectoVideo::find()->where(['idproyecto' => $idproyecto])
                ->orderBy(['orden' => SORT_ASC, 'idvideo' => SORT_ASC])->all() : [],
            'documentos' => isset($usa['documentos']) ? ProyectoDocumento::find()->where(['idproyecto' => $idproyecto, 'visible_inversor' => '1'])
                ->orderBy(['iddocumento' => SORT_ASC])->all() : [],
        ];
    }

    public function carrusel(int $idecosistema): ?array
    {
        $acceso = $this->acceso();
        if ($acceso['estado'] !== self::OK || !in_array($idecosistema, $acceso['areas'], true)) {
            return null;
        }
        $base = Proyecto::publicados([$idecosistema])->with('ecosistema');
        $total = (int) (clone $base)->count();
        if ($total === 0) {
            return null;
        }
        $destacados = (clone $base)->andWhere(['destacado' => '1'])->limit(8)->all();
        return ['proyectos' => $destacados ?: (clone $base)->limit(1)->all(), 'total' => $total];
    }

    public function destacadosInicio(): ?array
    {
        $acceso = $this->acceso();
        if ($acceso['estado'] !== self::OK || !$acceso['areas']) {
            return null;
        }
        $base = Proyecto::publicados($acceso['areas'])->with('ecosistema');
        $total = (int) (clone $base)->count();
        if ($total === 0) {
            return null;
        }
        $destacados = (clone $base)->andWhere(['destacado' => '1'])->limit(9)->all();
        return ['proyectos' => $destacados ?: (clone $base)->limit(6)->all(), 'total' => $total];
    }

    /* ---------- Archivos ---------- */

    public function archivoImagen(array $acceso, int $idproyecto, string $carpeta, string $archivo, int $ancho): array
    {
        $visible = Proyecto::publicados($acceso['areas'])->andWhere(['idproyecto' => $idproyecto])->exists();
        if (!$visible || !in_array($carpeta, self::CARPETAS, true) || !preg_match(Bloque::PATRON_IMAGEN, $archivo)) {
            throw new NotFoundHttpException();
        }
        $ruta = Proyecto::carpetaDe($idproyecto, $carpeta) . '/' . $archivo;
        if (!is_file($ruta)) {
            throw new NotFoundHttpException();
        }
        return ImagenVariante::obtener($ruta, $ancho);
    }

    public function archivoDocumento(array $acceso, int $iddocumento): array
    {
        $d = ProyectoDocumento::find()->where(['iddocumento' => $iddocumento, 'visible_inversor' => '1'])->one();
        if ($d === null || !Proyecto::publicados($acceso['areas'])->andWhere(['idproyecto' => $d->idproyecto])->exists()) {
            throw new NotFoundHttpException('No encontramos ese documento.');
        }
        $ruta = Proyecto::carpetaDe($d->idproyecto, 'documentos') . '/' . basename($d->archivo);
        if (!is_file($ruta)) {
            throw new NotFoundHttpException('El documento ya no está disponible.');
        }
        ProyectoDescarga::registrar($d, (int) $acceso['inversor']->idusuario);
        return ['ruta' => $ruta, 'nombre' => $d->nombre_original, 'mime' => $d->mime, 'inline' => $d->seVe()];
    }

    public function archivoVideo(array $acceso, int $idvideo): array
    {
        $v = ProyectoVideo::find()->where(['idvideo' => $idvideo, 'tipo' => 'archivo'])->one();
        if ($v === null || !Proyecto::publicados($acceso['areas'])->andWhere(['idproyecto' => $v->idproyecto])->exists()) {
            throw new NotFoundHttpException('No encontramos ese video.');
        }
        $ruta = Proyecto::carpetaDe($v->idproyecto, 'videos') . '/' . basename($v->archivo);
        if (!is_file($ruta)) {
            throw new NotFoundHttpException('El video ya no está disponible.');
        }
        return ['ruta' => $ruta, 'mime' => ProyectoVideo::mimeReproduccion($v->mime)];
    }
}

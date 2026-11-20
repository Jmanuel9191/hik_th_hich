<?php

namespace app\components;

use app\models\PaginaHistorial;
use app\models\PaginaItem;
use app\models\PaginaSeccion;
use app\widgets\MapaPaises;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

/**
 * Páginas institucionales: Inicio, Nosotros, Estrategia, Gobierno y ESG. Los permisos los revisa PaginasController.
 * Bloques fijos (pagina_seccion) + listas (pagina_item). Imágenes en imagenes/paginas/ (SubidaImagen);
 * textos con formato limpios con HtmlEditor.
 */
class PaginasService implements InterfacePaginas
{
    public function pagina($pagina): array
    {
        if (!isset(PaginaSeccion::PAGINAS[$pagina]['pestanas'])) { // las de "enlace" (Liderazgo) no tienen bloques
            throw new NotFoundHttpException('Página no encontrada.');
        }
        return PaginaSeccion::PAGINAS[$pagina];
    }

    public function tarjetasInicio(): array
    {
        $seccion = PaginaSeccion::findOne(['pagina' => 'quienes', 'clave' => 'historia']);
        return $seccion ? $seccion->getItemsActivos()->all() : [];
    }

    public function quienesInicio(): array
    {
        $bloques = PaginaSeccion::find()->where(['pagina' => 'quienes', 'clave' => ['historia', 'destacados', 'cifras'], 'estado' => '1'])
            ->indexBy('clave')->all();
        return [
            'historia' => $bloques['historia'] ?? null,
            'destacados' => isset($bloques['destacados']) ? $bloques['destacados']->getItemsActivos()->all() : [],
            'cifras' => isset($bloques['cifras']) ? $bloques['cifras']->getItemsActivos()->all() : [],
        ];
    }

    public function nosotrosWeb(): array
    {
        $b = PaginaSeccion::dePagina(['quienes', 'pvm']); // bloques visibles, con sus ítems activos
        $items = static fn ($clave) => isset($b[$clave]) ? $b[$clave]->itemsActivos : [];
        $datos = [
            'quienes' => ['historia' => $b['historia'] ?? null, 'destacados' => $items('destacados'), 'cifras' => $items('cifras')],
            'tarjetas' => $items('historia'),
            'bloques' => $items('bloques'),
        ];
        foreach (['proposito', 'vision', 'mision', 'valores', 'principios', 'ceo'] as $clave) {
            $datos[$clave] = $b[$clave] ?? null;
        }
        return $datos;
    }

    public function estrategiaWeb(): array
    {
        $b = PaginaSeccion::dePagina('ejes');
        return [
            'ejes' => $b['ejes'] ?? null, 'modelo' => $b['modelo'] ?? null, 'propuesta' => $b['propuesta_valor'] ?? null,
            'expansion' => $b['expansion'] ?? null, 'fases' => $b['fases'] ?? null,
        ];
    }

    public function gobiernoWeb(): array
    {
        $b = PaginaSeccion::dePagina('gobierno');
        return ['gobierno' => $b['gobierno'] ?? null, 'impacto' => $b['impacto'] ?? null, 'socios' => $b['socios'] ?? null, 'razones' => $b['razones'] ?? null];
    }

    public function paisesExpansion(): array
    {
        $seccion = PaginaSeccion::findOne(['pagina' => 'ejes', 'clave' => 'expansion']);
        $paises = [];
        foreach ($seccion ? $seccion->itemsActivos : [] as $item) {
            if ($item->codigo) {
                $paises[$item->codigo] = (string) $item->titulo;
            }
        }
        return $paises;
    }

    public function pieWeb(): ?PaginaSeccion
    {
        return PaginaSeccion::findOne(['pagina' => 'general', 'clave' => 'pie', 'estado' => '1']);
    }

    public function bloques($pagina, $pestana): array
    {
        $config = $this->pagina($pagina)['pestanas'][$pestana] ?? null;
        if ($config === null) {
            throw new NotFoundHttpException('Pestaña no encontrada.');
        }
        $filas = PaginaSeccion::find()->where(['pagina' => $pagina, 'clave' => array_keys($config['bloques'])])->indexBy('clave')->all();
        $bloques = [];
        foreach (array_keys($config['bloques']) as $clave) {
            if (!isset($filas[$clave])) {
                throw new ServerErrorHttpException('Falta el bloque "' . $clave . '" en la base de datos. Ejecute la migración de páginas.');
            }
            $bloques[$clave] = $filas[$clave];
        }
        return $bloques;
    }

    public function items(PaginaSeccion $seccion): array
    {
        $items = $seccion->getItems()->all();
        $ordenes = array_map(fn($i) => (int) $i->orden, $items);
        return ['items' => $items, 'extremos' => $ordenes ? [min($ordenes), max($ordenes)] : [0, 0]];
    }

    public function buscarSeccion($id): PaginaSeccion
    {
        $model = PaginaSeccion::findOne((int) $id);
        if ($model === null || $model->config()['pestana'] === null) {
            throw new NotFoundHttpException('Bloque no encontrado.');
        }
        return $model;
    }

    public function guardarSeccion(PaginaSeccion $seccion, array $post)
    {
        $antes = $seccion->getAttributes();
        $datos = $post['PaginaSeccion'] ?? [];
        foreach (PaginaSeccion::CAMPOS as $campo) {
            if ($seccion->usa($campo) && array_key_exists($campo, $datos)) {
                $seccion->$campo = $campo === 'texto' ? $this->texto($datos[$campo], $seccion->conEditor()) : $datos[$campo];
            }
        }

        // Imágenes del bloque (foto, firma…): solo las que usa este bloque
        $subidas = [];
        foreach (PaginaSeccion::IMAGENES as $campo) {
            $f = $seccion->usa($campo) ? UploadedFile::getInstance($seccion, $campo) : null;
            if ($f !== null && $f->error !== UPLOAD_ERR_NO_FILE) {
                $subidas[$campo] = $f;
            }
        }
        if (!$seccion->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }

        $borrar = [];
        $cambiosImagen = [];
        $nuevas = [];
        foreach (PaginaSeccion::IMAGENES as $campo) {
            if (isset($subidas[$campo])) {
                $r = SubidaImagen::guardar($subidas[$campo], PaginaSeccion::carpeta(), 5);
                if (!$r['ok']) {
                    foreach ($nuevas as $archivo) {
                        SubidaImagen::borrar(PaginaSeccion::carpeta(), $archivo); // no dejar huérfanas
                    }
                    $seccion->addError($campo, $r['error']);
                    return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
                }
                $nuevas[] = $r['archivo'];
                if ($seccion->$campo) {
                    $borrar[] = $seccion->$campo;
                }
                $cambiosImagen[$campo] = $seccion->$campo ? 'reemplazada' : 'subida';
                $seccion->$campo = $r['archivo'];
            } elseif ($seccion->usa($campo) && !empty($datos['quitar'][$campo]) && $seccion->$campo) {
                $borrar[] = $seccion->$campo;
                $cambiosImagen[$campo] = 'quitada';
                $seccion->$campo = null;
            }
        }

        $seccion->idusuario_actualizacion = Yii::$app->user->id;
        $seccion->fecha_actualizacion = date('Y-m-d H:i:s');
        $seccion->save(false);
        foreach ($borrar as $archivo) {
            SubidaImagen::borrar(PaginaSeccion::carpeta(), $archivo);
        }

        $cambios = [];
        foreach (PaginaSeccion::CAMPOS as $campo) {
            if ($seccion->usa($campo)) {
                $this->diff($cambios, $seccion->getAttributeLabel($campo), $antes[$campo], $seccion->$campo, $campo === 'texto');
            }
        }
        foreach ($cambiosImagen as $campo => $cambio) {
            $cambios[] = $seccion->getAttributeLabel($campo) . ': ' . $cambio;
        }
        if ($cambios) {
            PaginaHistorial::registrar($seccion->pagina, 'Edición', $this->nombre($seccion) . "\n" . implode("\n", $cambios));
        }
        return ['exito' => true, 'mensaje' => $cambios ? 'Cambios guardados.' : 'No había cambios para guardar.'];
    }

    public function cambiarEstadoSeccion($id)
    {
        $seccion = $this->buscarSeccion($id);
        $activar = $seccion->estado !== '1';
        $seccion->updateAttributes(['estado' => $activar ? '1' : '0', 'fecha_actualizacion' => date('Y-m-d H:i:s'), 'idusuario_actualizacion' => Yii::$app->user->id]);
        PaginaHistorial::registrar($seccion->pagina, $activar ? 'Activación' : 'Desactivación',
            $this->nombre($seccion) . "\n" . ($activar ? 'Estado: Oculto → Visible' : 'Estado: Visible → Oculto (no se muestra en la web)'));
        return ['exito' => true, 'mensaje' => $activar ? 'Bloque visible en la web.' : 'Bloque oculto.'];
    }

    public function buscarItem($id): PaginaItem
    {
        $item = PaginaItem::find()->with('seccion')->where(['iditem' => (int) $id])->one();
        if ($item === null || $item->seccion === null || empty($item->seccion->config()['lista'])) {
            throw new NotFoundHttpException('Elemento no encontrado.');
        }
        return $item;
    }

    public function nuevoItem($idseccion): PaginaItem
    {
        $seccion = $this->buscarSeccion($idseccion);
        if (empty($seccion->config()['lista'])) {
            throw new NotFoundHttpException('Este bloque no tiene lista.');
        }
        $item = new PaginaItem(['idseccion' => $seccion->idseccion]);
        $item->populateRelation('seccion', $seccion);
        return $item;
    }

    public function guardarItem(PaginaItem $item, array $post)
    {
        $nuevo = $item->isNewRecord;
        $antes = $item->getAttributes();
        $datos = $post['PaginaItem'] ?? [];
        if (array_key_exists('titulo', $datos)) {
            $item->titulo = $datos['titulo'];
        }
        if ($item->usa('texto') && array_key_exists('texto', $datos)) {
            $item->texto = $this->texto($datos['texto'], $item->conEditor());
        }
        foreach (['cifra', 'codigo'] as $campo) {
            if ($item->usa($campo) && array_key_exists($campo, $datos)) {
                $item->$campo = $datos[$campo];
            }
        }

        $subidas = [];
        foreach (PaginaItem::IMAGENES as $campo) {
            if ($item->usa($campo)) {
                $f = UploadedFile::getInstance($item, $campo);
                if ($f !== null && $f->error !== UPLOAD_ERR_NO_FILE) {
                    $subidas[$campo] = $f;
                }
            }
        }
        if (!$item->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }

        $guardadas = [];
        $borrar = [];
        $cambiosImg = [];
        foreach ($subidas as $campo => $f) {
            $r = SubidaImagen::guardar($f, PaginaSeccion::carpeta(), $campo === 'icono' ? 2 : 5, $campo === 'icono');
            if (!$r['ok']) {
                foreach ($guardadas as $archivo) {
                    SubidaImagen::borrar(PaginaSeccion::carpeta(), $archivo); // no dejar huérfanas
                }
                $item->addError($campo, $r['error']);
                return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
            }
            $guardadas[] = $r['archivo'];
            if ($item->$campo) {
                $borrar[] = $item->$campo;
            }
            $cambiosImg[] = $item->getAttributeLabel($campo) . ': ' . ($item->$campo ? 'reemplazada' : 'subida');
            $item->$campo = $r['archivo'];
        }
        foreach (PaginaItem::IMAGENES as $campo) {
            if (!isset($subidas[$campo]) && $item->usa($campo) && !empty($datos['quitar'][$campo]) && $item->$campo) {
                $borrar[] = $item->$campo;
                $cambiosImg[] = $item->getAttributeLabel($campo) . ': quitada';
                $item->$campo = null;
            }
        }

        if ($nuevo) {
            $item->estado = '1';
            $item->orden = (int) PaginaItem::find()->where(['idseccion' => $item->idseccion])->max('orden') + 1;
            $item->idusuario_registro = Yii::$app->user->id;
            $item->fecha_registro = date('Y-m-d H:i:s');
        } else {
            $item->fecha_actualizacion = date('Y-m-d H:i:s');
        }
        $item->save(false);
        foreach ($borrar as $archivo) {
            SubidaImagen::borrar(PaginaSeccion::carpeta(), $archivo);
        }

        $donde = $this->nombre($item->seccion);
        if ($nuevo) {
            PaginaHistorial::registrar($item->seccion->pagina, 'Creación', $donde . "\nSe agregó: " . $item->titulo);
        } else {
            $cambios = [];
            $this->diff($cambios, $item->getAttributeLabel('titulo'), $antes['titulo'], $item->titulo, false);
            if ($item->usa('texto')) {
                $this->diff($cambios, $item->getAttributeLabel('texto'), $antes['texto'], $item->texto, $item->conEditor());
            }
            if ($item->usa('cifra')) {
                $this->diff($cambios, $item->getAttributeLabel('cifra'), $antes['cifra'], $item->cifra, false);
            }
            if ($item->usa('codigo')) {
                $this->diff($cambios, $item->getAttributeLabel('codigo'), $antes['codigo'] ? MapaPaises::nombre($antes['codigo']) : '', $item->pais(), false);
            }
            $cambios = array_merge($cambios, $cambiosImg);
            if ($cambios) {
                PaginaHistorial::registrar($item->seccion->pagina, 'Edición', $donde . ' · ' . $item->titulo . "\n" . implode("\n", $cambios));
            }
        }
        return ['exito' => true, 'mensaje' => $nuevo ? 'Se agregó «' . $item->titulo . '».' : 'Cambios guardados.'];
    }

    public function cambiarEstadoItem($id)
    {
        $item = $this->buscarItem($id);
        $activar = $item->estado !== '1';
        $item->updateAttributes(['estado' => $activar ? '1' : '0', 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        PaginaHistorial::registrar($item->seccion->pagina, $activar ? 'Activación' : 'Desactivación',
            $this->nombre($item->seccion) . ' · ' . $item->titulo . "\n" . ($activar ? 'Estado: Inactivo → Activo' : 'Estado: Activo → Inactivo (no se muestra en la web)'));
        return ['exito' => true, 'mensaje' => $activar ? 'Activado.' : 'Desactivado.'];
    }

    public function moverItem($id, $direccion)
    {
        $item = $this->buscarItem($id);
        $subir = (int) $direccion < 0;
        $vecino = PaginaItem::find()
            ->where(['idseccion' => $item->idseccion])
            ->andWhere([$subir ? '<' : '>', 'orden', $item->orden])
            ->orderBy(['orden' => $subir ? SORT_DESC : SORT_ASC])
            ->one();
        if ($vecino === null) {
            return ['exito' => false, 'mensaje' => $subir ? 'Ya es el primero.' : 'Ya es el último.'];
        }
        $tx = Yii::$app->db->beginTransaction();
        $orden = $item->orden;
        $item->updateAttributes(['orden' => $vecino->orden]);
        $vecino->updateAttributes(['orden' => $orden]);
        $tx->commit();
        PaginaHistorial::registrar($item->seccion->pagina, 'Orden', $this->nombre($item->seccion) . ' · ' . $item->titulo . ': ' . ($subir ? 'subió' : 'bajó') . ' un lugar.');
        return ['exito' => true, 'mensaje' => ''];
    }

    public function eliminarItem($id)
    {
        $item = $this->buscarItem($id);
        $archivos = array_filter([$item->icono, $item->imagen]);
        $pagina = $item->seccion->pagina;
        $texto = $this->nombre($item->seccion) . "\nSe eliminó: " . $item->titulo;
        $item->delete();
        foreach ($archivos as $archivo) {
            SubidaImagen::borrar(PaginaSeccion::carpeta(), $archivo);
        }
        PaginaHistorial::registrar($pagina, 'Eliminación', $texto);
        return ['exito' => true, 'mensaje' => 'Eliminado.'];
    }

    public function historial($pagina)
    {
        return new ActiveDataProvider([
            'query' => PaginaHistorial::find()->with('usuarioActor')->where(['pagina' => $pagina])->orderBy(['idhistorial' => SORT_DESC]),
            'pagination' => ['pageSize' => 10, 'pageParam' => 'hpage'],
        ]);
    }

    /* ---------- Ayudas internas ---------- */

    /** Texto del editor: limpio; un editor "vacío" deja <p>&nbsp;</p> → null. Texto simple: recortado. */
    private function texto($valor, $conEditor)
    {
        if (!$conEditor) {
            $valor = trim(str_replace("\r\n", "\n", (string) $valor));
            return $valor === '' ? null : $valor;
        }
        $html = HtmlEditor::limpiar($valor);
        $plano = preg_replace('/[\s\x{00A0}]+/u', '', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        return $plano === '' ? null : $html;
    }

    private function diff(array &$cambios, $etiqueta, $antes, $despues, $largo)
    {
        $a = (string) $antes;
        $d = (string) $despues;
        if ($a === $d) {
            return;
        }
        if ($largo) {
            // el editor reacomoda espacios y saltos entre etiquetas: eso no es un cambio
            $normal = fn($v) => preg_replace(['/>\s+</', '/\s+/u'], ['><', ' '], trim($v));
            if ($normal($a) === $normal($d)) {
                return;
            }
            $cambios[] = $etiqueta . ': ' . ($d === '' ? 'quitado' : ($a === '' ? 'agregado' : 'modificado'));
            return;
        }
        $corto = fn($v) => $v === '' ? '(vacío)' : mb_strimwidth($v, 0, 120, '…');
        $cambios[] = $etiqueta . ': ' . $corto($a) . ' → ' . $corto($d);
    }

    private function nombre(PaginaSeccion $seccion)
    {
        return $seccion->config()['nombre'];
    }
}

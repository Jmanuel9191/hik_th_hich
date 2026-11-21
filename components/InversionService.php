<?php

namespace app\components;

use app\models\InteresInversion;
use app\models\InteresInversionNota;
use app\models\Inversor;
use app\models\InversorHistorial;
use app\models\InversionPago;
use app\models\InversionSearch;
use app\models\Moneda;
use app\models\Proyecto;
use app\models\ProyectoHistorial;
use app\models\Usuarios;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Inversiones (pagos). Cada pago queda en el historial del proyecto y en el del inversor.
 * Comprobantes en privado/inversiones/<idproyecto>/ (se sirven desde el controlador).
 */
class InversionService implements InterfaceInversiones
{
    private $permisos;

    public function __construct(InterfacePermisosService $permisos)
    {
        $this->permisos = $permisos;
    }

    /** null = todos; gerente: sus ecosistemas. */
    private function alcance()
    {
        if ((int) (Yii::$app->user->identity->idrol ?? 0) !== Rol::GERENTE) {
            return null;
        }
        return array_map('intval', (new \yii\db\Query())->select('idecosistema')->from('usuario_ecosistema')
            ->where(['idusuario' => Yii::$app->user->id])->column());
    }

    private function enAlcance($idecosistema)
    {
        $ids = $this->alcance();
        return $ids === null || in_array((int) $idecosistema, $ids, true);
    }

    public function listar($params)
    {
        $this->permisos->verificarPermiso('inversiones_listar');
        $search = new InversionSearch();
        $dp = $search->search($params, $this->alcance());
        return [
            'searchModel' => $search,
            'dataProvider' => $dp,
            'totales' => $this->totalesDe($dp->query),
            'proyectos' => $this->proyectosConPagos(),
            'monedas' => Moneda::find()->select(['codigo', 'idmoneda'])->orderBy(['orden' => SORT_ASC])->indexBy('idmoneda')->column(),
        ];
    }

    /** Totales vigentes (por moneda) de una consulta de pagos ya filtrada. */
    private function totalesDe($query)
    {
        $q = clone $query;
        $q->with = [];
        $filas = $q->select(['ip.idmoneda', 'total' => 'SUM(ip.monto)', 'pagos' => 'COUNT(*)'])
            ->andWhere(['ip.estado' => 'vigente'])->groupBy('ip.idmoneda')->orderBy([])->asArray()->all();
        $monedas = Moneda::find()->where(['idmoneda' => array_column($filas, 'idmoneda')])->indexBy('idmoneda')->all();
        return array_map(fn($f) => ['moneda' => $monedas[$f['idmoneda']] ?? null, 'total' => (float) $f['total'], 'pagos' => (int) $f['pagos']], $filas);
    }

    public function buscar($id, $permiso = 'inversiones_listar'): InversionPago
    {
        $this->permisos->verificarPermiso($permiso);
        $pago = InversionPago::find()->with(['proyecto', 'moneda', 'monedaProyecto', 'inversor'])->where(['idpago' => (int) $id])->one();
        if ($pago === null) {
            throw new NotFoundHttpException('El pago no existe.');
        }
        if (!$this->enAlcance($pago->proyecto->idecosistema)) {
            throw new ForbiddenHttpException('Este pago es de un proyecto de un área que no tiene asignada.');
        }
        return $pago;
    }

    public function nuevo($idproyecto = null, $idinversor = null): InversionPago
    {
        $this->permisos->verificarPermiso('inversiones_registrar');
        $pago = new InversionPago(['fecha_pago' => date('Y-m-d'), 'tipo_pago' => 'transferencia']);
        if ($idproyecto && isset($this->proyectosParaPagar()[(int) $idproyecto])) {
            $pago->idproyecto = (int) $idproyecto;
            $pago->idmoneda = $pago->proyecto->idmoneda;
        }
        if ($idinversor && isset($this->inversoresAprobados()[(int) $idinversor])) {
            $pago->idusuario = (int) $idinversor;
        }
        // Viene de un interesado: el monto y la moneda que estimó arrancan cargados (se corrigen si cambió).
        if ($pago->idproyecto && $pago->idusuario) {
            $i = InteresInversion::findOne(['idproyecto' => $pago->idproyecto, 'idusuario' => $pago->idusuario]);
            if ($i && $i->monto !== null && $i->estado !== 'descartado') {
                $pago->monto = 0 + $i->monto;
                $pago->idmoneda = $i->idmoneda ?: $pago->idmoneda;
            }
        }
        return $pago;
    }

    public function registrar(InversionPago $pago, array $post)
    {
        $this->permisos->verificarPermiso('inversiones_registrar');
        $pago->load($post);
        if (!$pago->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los datos marcados.'];
        }
        $p = Proyecto::findOne((int) $pago->idproyecto);
        if ($p === null || !isset($this->proyectosParaPagar()[$p->idproyecto])) {
            $pago->addError('idproyecto', 'Elija un proyecto publicado (o con la inversión completa) de sus áreas.');
            return ['exito' => false, 'mensaje' => 'Revise los datos marcados.'];
        }
        $inv = Inversor::find()->with(['usuario', 'areas'])->where(['idusuario' => (int) $pago->idusuario])->one();
        if ($inv === null || $inv->estado_solicitud !== 'aprobado' || $inv->usuario->estado !== '1') {
            $pago->addError('idusuario', 'Elija un inversor aprobado y con la cuenta activa.');
            return ['exito' => false, 'mensaje' => 'Revise los datos marcados.'];
        }
        if (!in_array((int) $p->idecosistema, array_map('intval', array_column($inv->areas, 'idecosistema')), true)) {
            $pago->addError('idusuario', $inv->nombreVisible() . ' no tiene habilitada el área ' . $p->ecosistema->nombre
                . '. Habilítela desde su ficha de inversor y vuelva a intentar.');
            return ['exito' => false, 'mensaje' => 'Revise los datos marcados.'];
        }

        $pago->idmoneda_proyecto = $p->idmoneda;
        if (!$pago->otraMoneda()) {
            $pago->monto_equivalente = $pago->monto;
        }
        $pago->estado = 'vigente';
        $pago->idusuario_registro = Yii::$app->user->id;
        $pago->fecha_registro = date('Y-m-d H:i:s');
        $archivo = $pago->comprobante;

        $tx = Yii::$app->db->beginTransaction();
        try {
            $pago->save(false);
            if ($archivo) {
                $r = SubidaArchivo::guardar($archivo, InversionPago::carpetaDe($p->idproyecto), InversionPago::FORMATOS_COMPROBANTE, InversionPago::MAX_COMPROBANTE_MB);
                if (!$r['ok']) {
                    $tx->rollBack();
                    $pago->addError('comprobante', $r['error']);
                    $pago->setIsNewRecord(true);
                    $pago->idpago = null;
                    return ['exito' => false, 'mensaje' => 'Revise los datos marcados.'];
                }
                $pago->updateAttributes(['comprobante_archivo' => $r['archivo'], 'comprobante_nombre' => mb_substr($archivo->name, 0, 255)]);
            }
            $pago->refresh();
            $texto = $this->descripcion($pago);
            ProyectoHistorial::registrar($p->idproyecto, 'Inversión', 'Pago registrado de ' . $inv->nombreVisible() . ': ' . $texto);
            InversorHistorial::registrar($inv->idusuario, 'Inversión', 'Pago registrado en ' . $p->codigo . ' · ' . $p->titulo . ': ' . $texto);
            $this->concretarInteres($p, (int) $inv->idusuario, $pago);
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
        return ['exito' => true, 'mensaje' => 'Pago registrado: ' . $pago->montoTexto() . ' de ' . $inv->nombreVisible() . '.', 'id' => $pago->idpago];
    }

    private function descripcion(InversionPago $pago)
    {
        return $pago->montoTexto() . ($pago->otraMoneda() ? ' (equivale a ' . $pago->equivalenteTexto() . ')' : '')
            . ' · ' . $pago->tipoPagoTexto() . ($pago->referencia ? ' (' . $pago->referencia . ')' : '')
            . ' · fecha ' . date('d/m/Y', strtotime($pago->fecha_pago))
            . ($pago->comprobante_archivo ? ' · con comprobante' : '');
    }

    public function anular($id, $motivo)
    {
        $pago = $this->buscar($id, 'inversiones_anular');
        $motivo = trim((string) $motivo);
        if (mb_strlen($motivo) < 5 || mb_strlen($motivo) > 500) {
            return ['exito' => false, 'mensaje' => 'Escriba el motivo (entre 5 y 500 caracteres).'];
        }
        if ($pago->esAnulado()) {
            return ['exito' => false, 'mensaje' => 'Este pago ya estaba anulado.'];
        }
        $pago->updateAttributes(['estado' => 'anulado', 'motivo_anulacion' => $motivo, 'idusuario_anulacion' => Yii::$app->user->id,
            'fecha_anulacion' => date('Y-m-d H:i:s')]);
        $texto = $this->descripcion($pago) . ' · Motivo: ' . $motivo;
        $nombre = $pago->fichaInversor ? $pago->fichaInversor->nombreVisible() : $pago->inversor->nombrecompleto;
        ProyectoHistorial::registrar($pago->idproyecto, 'Inversión anulada', 'Pago anulado de ' . $nombre . ': ' . $texto);
        InversorHistorial::registrar($pago->idusuario, 'Inversión anulada', 'Pago anulado en ' . $pago->proyecto->codigo . ': ' . $texto);
        return ['exito' => true, 'mensaje' => 'Pago anulado. Ya no suma en los totales.'];
    }

    public function subirComprobante($id)
    {
        $pago = $this->buscar($id, 'inversiones_registrar');
        if ($pago->esAnulado()) {
            return ['exito' => false, 'mensaje' => 'El pago está anulado.'];
        }
        $f = \yii\web\UploadedFile::getInstanceByName('comprobante');
        if ($f === null || $f->error === UPLOAD_ERR_NO_FILE) {
            return ['exito' => false, 'mensaje' => 'Elija el archivo.'];
        }
        $r = SubidaArchivo::guardar($f, InversionPago::carpetaDe($pago->idproyecto), InversionPago::FORMATOS_COMPROBANTE, InversionPago::MAX_COMPROBANTE_MB);
        if (!$r['ok']) {
            return ['exito' => false, 'mensaje' => $r['error']];
        }
        $anterior = $pago->comprobante_archivo;
        $pago->updateAttributes(['comprobante_archivo' => $r['archivo'], 'comprobante_nombre' => mb_substr($f->name, 0, 255)]);
        if ($anterior) {
            SubidaArchivo::borrar(InversionPago::carpetaDe($pago->idproyecto), $anterior);
        }
        $que = 'Comprobante ' . ($anterior ? 'reemplazado' : 'agregado') . ' al pago de ' . $pago->montoTexto() . ' del ' . date('d/m/Y', strtotime($pago->fecha_pago)) . '.';
        ProyectoHistorial::registrar($pago->idproyecto, 'Inversión', $que);
        InversorHistorial::registrar($pago->idusuario, 'Inversión', $que);
        return ['exito' => true, 'mensaje' => 'Comprobante guardado.'];
    }

    public function archivoComprobante($id): array
    {
        $pago = $this->buscar($id);
        $ruta = InversionPago::carpetaDe($pago->idproyecto) . '/' . basename((string) $pago->comprobante_archivo);
        if (!$pago->comprobante_archivo || !is_file($ruta)) {
            throw new NotFoundHttpException('Este pago no tiene comprobante.');
        }
        $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
        return ['ruta' => $ruta, 'nombre' => $pago->comprobante_nombre ?: 'comprobante.' . $ext, 'mime' => InversionPago::FORMATOS_COMPROBANTE[$ext][0] ?? null];
    }

    /* ---------------- Listas ---------------- */

    private function consultaProyectos()
    {
        $q = Proyecto::find()->with('moneda')->where(['estado' => InversionPago::ESTADOS_PROYECTO])->andWhere(['not', ['idmoneda' => null]])
            ->orderBy(['codigo' => SORT_ASC]);
        if (($ids = $this->alcance()) !== null) {
            $q->andWhere(['idecosistema' => $ids ?: [0]]);
        }
        return $q;
    }

    public function proyectosParaPagar(): array
    {
        $lista = [];
        foreach ($this->consultaProyectos()->all() as $p) {
            $lista[$p->idproyecto] = $p->codigo . ' · ' . $p->titulo . ' (' . ($p->moneda->codigo ?? '') . ')';
        }
        return $lista;
    }

    public function datosProyectos(): array
    {
        $datos = [];
        foreach ($this->consultaProyectos()->all() as $p) {
            $datos[$p->idproyecto] = ['moneda' => (int) $p->idmoneda, 'codigo' => $p->moneda->codigo ?? ''];
        }
        return $datos;
    }

    /** Para el filtro del listado: proyectos (de su alcance) que tienen pagos. */
    private function proyectosConPagos()
    {
        $q = Proyecto::find()->select(['codigo', 'idproyecto'])
            ->where(['idproyecto' => InversionPago::find()->select('idproyecto')->distinct()])->orderBy(['codigo' => SORT_ASC])->indexBy('idproyecto');
        if (($ids = $this->alcance()) !== null) {
            $q->andWhere(['idecosistema' => $ids ?: [0]]);
        }
        return $q->column();
    }

    /** Si el inversor había enviado "Quiero invertir" en este proyecto: pasa a Concretado y queda anotado el pago. */
    private function concretarInteres(Proyecto $p, int $idusuario, InversionPago $pago): void
    {
        $i = InteresInversion::findOne(['idproyecto' => $p->idproyecto, 'idusuario' => $idusuario]);
        if ($i === null) {
            return;
        }
        $texto = 'Se registró un pago de ' . $pago->montoTexto() . '.';
        if ($i->estado !== 'concretado') {
            $texto .= ' Estado: ' . $i->estadoNombre() . ' → ' . InteresInversion::ESTADOS['concretado'][0] . '.';
            $i->updateAttributes(['estado' => 'concretado', 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        }
        InteresInversionNota::registrar($i->idinteres, 'estado', $texto, (int) Yii::$app->user->id);
    }

    public function interesadosParaPagar(): array
    {
        $ids = array_keys($this->proyectosParaPagar());
        $res = [];
        foreach (InteresInversion::find()->with('moneda')->where(['idproyecto' => $ids ?: [0]])->andWhere(['<>', 'estado', 'descartado'])->all() as $i) {
            $res[$i->idproyecto][$i->idusuario] = [
                'texto' => $i->monto === null ? 'a conversar' : $i->montoTexto() . ' estimado',
                'monto' => $i->monto === null ? null : (string) (0 + $i->monto),
                'idmoneda' => $i->idmoneda,
            ];
        }
        return $res;
    }

    public function inversoresAprobados(): array
    {
        $lista = [];
        $q = Inversor::find()->alias('i')->innerJoinWith('usuario u')->with('areas')
            ->where(['i.estado_solicitud' => 'aprobado', 'u.estado' => '1'])->orderBy(['u.nombrecompleto' => SORT_ASC]);
        if (($ids = $this->alcance()) !== null) {
            $q->andWhere(['exists', (new \yii\db\Query())->from('inversor_area a')->where('a.idusuario = i.idusuario')->andWhere(['a.idecosistema' => $ids ?: [0]])]);
        }
        foreach ($q->all() as $inv) {
            $lista[$inv->idusuario] = $inv->nombreVisible() . ' (' . implode(', ', array_column($inv->areas, 'nombre')) . ')';
        }
        return $lista;
    }

    /* ---------------- Resúmenes ---------------- */

    public function resumenProyecto(Proyecto $p, $params)
    {
        $search = new InversionSearch();
        $dp = $search->search($params, null, ['ip.idproyecto' => $p->idproyecto]);
        $recaudado = $p->recaudado();

        $filas = InversionPago::find()->select(['idusuario', 'idmoneda', 'total' => 'SUM(monto)', 'equiv' => 'SUM(monto_equivalente)', 'pagos' => 'COUNT(*)'])
            ->where(['idproyecto' => $p->idproyecto, 'estado' => 'vigente'])->groupBy(['idusuario', 'idmoneda'])->asArray()->all();
        $monedas = Moneda::find()->indexBy('idmoneda')->all();
        $inversores = Inversor::find()->with('usuario')->where(['idusuario' => array_unique(array_column($filas, 'idusuario'))])->indexBy('idusuario')->all();
        $porInversor = [];
        foreach ($filas as $f) {
            $id = (int) $f['idusuario'];
            $porInversor[$id] ??= ['inversor' => $inversores[$id] ?? null, 'totales' => [], 'equivalente' => 0.0, 'pagos' => 0];
            $porInversor[$id]['totales'][] = ['moneda' => $monedas[$f['idmoneda']] ?? null, 'total' => (float) $f['total']];
            $porInversor[$id]['equivalente'] += (float) $f['equiv'];
            $porInversor[$id]['pagos'] += (int) $f['pagos'];
        }
        uasort($porInversor, fn($a, $b) => $b['equivalente'] <=> $a['equivalente']);
        return [
            'searchModel' => $search,
            'dataProvider' => $dp,
            'recaudado' => $recaudado,
            'porcentaje' => $p->porcentajeRonda($recaudado),
            'porInversor' => $porInversor,
            'totales' => InversionPago::totalesPorMoneda(['idproyecto' => $p->idproyecto]),
            'anulados' => (int) InversionPago::find()->where(['idproyecto' => $p->idproyecto, 'estado' => 'anulado'])->count(),
            'puedeRegistrar' => $this->permisos->tienePermiso('inversiones_registrar') && in_array($p->estado, InversionPago::ESTADOS_PROYECTO, true) && $p->idmoneda,
            'puedeAnular' => $this->permisos->tienePermiso('inversiones_anular'),
        ];
    }

    public function resumenInversor($idusuario): array
    {
        $ids = InversionPago::find()->select('idproyecto')->distinct()->where(['idusuario' => (int) $idusuario])->column();
        $intereses = InteresInversion::find()->where(['idusuario' => (int) $idusuario])->indexBy('idproyecto')->all();
        $ids = array_unique(array_merge($ids, array_keys($intereses)));   // también los que solo tocó "Quiero invertir"
        $q = Proyecto::find()->with(['moneda', 'ecosistema'])->where(['idproyecto' => $ids ?: [0]])->orderBy(['codigo' => SORT_ASC]);
        if (($alc = $this->alcance()) !== null) {
            $q->andWhere(['idecosistema' => $alc ?: [0]]);
        }
        $res = [];
        foreach ($q->all() as $p) {
            $cond = ['idproyecto' => $p->idproyecto, 'idusuario' => (int) $idusuario];
            $res[] = [
                'proyecto' => $p,
                'totales' => InversionPago::totalesPorMoneda($cond),
                'equivalente' => (float) InversionPago::find()->where($cond + ['estado' => 'vigente'])->sum('monto_equivalente'),
                'anulados' => (int) InversionPago::find()->where($cond + ['estado' => 'anulado'])->count(),
                'interes' => $intereses[$p->idproyecto] ?? null,
                'pagos' => new \yii\data\ArrayDataProvider([
                    'allModels' => InversionPago::find()->with(['proyecto', 'moneda', 'monedaProyecto', 'registradoPor', 'anuladoPor'])->where($cond)
                        ->orderBy(['fecha_pago' => SORT_DESC, 'idpago' => SORT_DESC])->all(),
                    'pagination' => false,
                ]),
            ];
        }
        return $res;
    }

    /* ---------------- Exportar ---------------- */

    public function exportar($params)
    {
        $this->permisos->verificarPermiso('inversiones_exportar');
        $search = new InversionSearch();
        $dp = $search->search($params, $this->alcance());
        $modelos = $dp->query->all();
        $nombre = fn(InversionPago $m) => $m->fichaInversor ? $m->fichaInversor->nombreVisible() : ($m->inversor->nombrecompleto ?? '');
        $columnas = [
            'Fecha del pago' => fn($m) => date('d/m/Y', strtotime($m->fecha_pago)),
            'Proyecto' => fn($m) => $m->proyecto->codigo . ' · ' . $m->proyecto->titulo,
            'Inversor' => $nombre,
            'Email' => fn($m) => $m->inversor->email ?? '',
            'Moneda' => fn($m) => $m->moneda->codigo ?? '',
            'Monto' => fn($m) => number_format((float) $m->monto, 2, '.', ''),
            'Moneda del proyecto' => fn($m) => $m->monedaProyecto->codigo ?? '',
            'Equivalente' => fn($m) => number_format((float) $m->monto_equivalente, 2, '.', ''),
            'Tipo de pago' => fn($m) => $m->tipoPagoTexto(),
            'Referencia' => fn($m) => $m->referencia,
            'Comprobante' => fn($m) => $m->comprobante_archivo ? 'Sí' : 'No',
            'Estado' => fn($m) => InversionPago::ESTADOS[$m->estado] ?? $m->estado,
            'Motivo de anulación' => fn($m) => $m->motivo_anulacion,
            'Registrado por' => fn($m) => $m->registradoPor->nombrecompleto ?? '',
            'Fecha de registro' => fn($m) => date('d/m/Y H:i', strtotime($m->fecha_registro)),
            'Nota' => fn($m) => $m->nota,
        ];
        $filtros = [];
        if ((string) $search->texto !== '') {
            $filtros[] = 'Búsqueda: ' . $search->texto;
        }
        if ($search->idproyecto && ($p = Proyecto::findOne((int) $search->idproyecto))) {
            $filtros[] = 'Proyecto: ' . $p->codigo;
        }
        if ($search->tipo_pago) {
            $filtros[] = 'Tipo de pago: ' . InversionPago::TIPOS_PAGO[$search->tipo_pago];
        }
        if ($search->idmoneda && ($mo = Moneda::findOne((int) $search->idmoneda))) {
            $filtros[] = 'Moneda: ' . $mo->codigo;
        }
        if ($search->estado) {
            $filtros[] = 'Estado: ' . InversionPago::ESTADOS[$search->estado];
        }
        if ($search->desde || $search->hasta) {
            $filtros[] = 'Fechas: ' . ($search->desde ? date('d/m/Y', strtotime($search->desde)) : '…') . ' al ' . ($search->hasta ? date('d/m/Y', strtotime($search->hasta)) : '…');
        }
        $filtros[] = 'Totales vigentes: ' . InversionPago::totalesTexto($this->totalesDe($dp->query));
        return ['titulo' => 'Inversiones', 'columnas' => $columnas, 'modelos' => $modelos, 'filtros' => $filtros];
    }
}

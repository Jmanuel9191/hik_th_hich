<?php

namespace app\components;

use app\models\Configuracion;
use app\models\InversionPago;
use app\models\InteresInversion;
use app\models\InteresInversionNota;
use app\models\Inversor;
use app\models\InversorHistorial;
use app\models\Proyecto;
use app\models\ProyectoHistorial;
use app\models\QuieroInvertirForm;
use app\models\Usuarios;
use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;

/**
 * "Quiero invertir" (ver InterfaceInteresInversion).
 * Avisos al equipo: gerente responsable del proyecto, gerentes del área, Superadmin y correo del sistema.
 * Si el inversor actualiza su interés, se vuelve a avisar como mucho una vez cada MINUTOS_ENTRE_AVISOS.
 */
class InteresInversionService implements InterfaceInteresInversion
{
    const MINUTOS_ENTRE_AVISOS = 10;

    private $correos;
    private $permisos;
    private $proyectos;

    public function __construct(InterfaceCorreos $correos, InterfacePermisosService $permisos, InterfaceProyecto $proyectos)
    {
        $this->correos = $correos;
        $this->permisos = $permisos;
        $this->proyectos = $proyectos;
    }

    /* ================= Web ================= */

    public function dePara(int $idusuario, int $idproyecto): ?InteresInversion
    {
        return InteresInversion::find()->with('moneda')->where(['idusuario' => $idusuario, 'idproyecto' => $idproyecto])->one();
    }

    public function puedeEnviar(Proyecto $p, ?InteresInversion $previo): bool
    {
        if ($previo !== null && $previo->activo()) {
            return true; // puede actualizarlo
        }
        return $p->estado === 'publicado'; // "Inversión completa" (cerrado): ya no recibe
    }

    public function enviar(Inversor $inv, Proyecto $p, QuieroInvertirForm $f, array $post): array
    {
        $previo = $this->dePara((int) $inv->idusuario, (int) $p->idproyecto);
        if (!$this->puedeEnviar($p, $previo)) {
            return ['exito' => false, 'mensaje' => 'Este proyecto ya completó su ronda de inversión.', 'interes' => $previo];
        }
        if (!$f->load($post) || !$f->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.', 'interes' => $previo];
        }
        $ahora = date('Y-m-d H:i:s');
        $nuevo = $previo === null;
        $reabre = !$nuevo && !$previo->activo();
        $i = $previo ?? new InteresInversion(['idproyecto' => $p->idproyecto, 'idusuario' => $inv->idusuario, 'fecha_registro' => $ahora]);
        $ultimoAviso = $nuevo ? null : $i->fecha_actualizacion;
        $i->setAttributes([
            'monto' => $f->tipo_monto === 'monto' ? $f->monto : null,
            'idmoneda' => $p->idmoneda,
            'participacion' => $f->participacion ?: null,
            'plazo' => $f->plazo ?: null,
            'contacto_medio' => $f->contacto_medio,
            'contacto_horario' => $f->contacto_horario ?: null,
            'comentario' => $f->comentario ?: null,
            'ip' => Yii::$app->request->userIP,
            'fecha_actualizacion' => $ahora,
        ], false);
        if ($nuevo || $reabre) {
            $i->estado = 'nuevo';
            $i->fecha_registro = $ahora;
        }
        if (!$i->save(false)) {
            return ['exito' => false, 'mensaje' => 'No se pudo enviar. Intente de nuevo.', 'interes' => $previo];
        }
        $i->refresh();
        $que = ($nuevo ? 'Envió' : ($reabre ? 'Volvió a enviar' : 'Actualizó')) . ' su interés en invertir: ' . $i->montoTexto() . '.';
        InteresInversionNota::registrar($i->idinteres, 'inversor', $que, (int) $inv->idusuario);
        InversorHistorial::registrar($inv->idusuario, 'Quiero invertir', $que . ' Proyecto: ' . $p->titulo . '.');
        ProyectoHistorial::registrar($p->idproyecto, 'Quiero invertir', $inv->nombreVisible() . ': ' . mb_strtolower(mb_substr($que, 0, 1)) . mb_substr($que, 1));

        $avisar = $nuevo || $reabre || !$ultimoAviso || strtotime($ultimoAviso) < time() - self::MINUTOS_ENTRE_AVISOS * 60;
        if ($avisar) {
            $this->avisarEquipo($i, $inv, $p, $nuevo || $reabre);
        }
        if ($nuevo || $reabre) {
            $this->intentar(fn() => $this->correos->enviarCorreoInteresInversor($inv->usuario, $p, $i, $this->quienContacta($p)));
        }
        return ['exito' => true, 'interes' => $i, 'mensaje' => $nuevo || $reabre
            ? '¡Recibimos su interés! ' . $this->quienContacta($p) . ' se comunicará con usted en las próximas ' . InteresInversion::HORAS_CONTACTO . ' horas hábiles.'
            : 'Actualizamos su interés. Nuestro equipo verá los cambios.'];
    }

    public function deInversor(int $idusuario): array
    {
        return InteresInversion::find()->with(['proyecto.ecosistema', 'moneda'])->where(['idusuario' => $idusuario])
            ->orderBy(['fecha_actualizacion' => SORT_DESC])->all();
    }

    /* ================= Panel ================= */

    public function deProyecto(Proyecto $p): array
    {
        if (!$this->permisos->tienePermiso('interesados_ver')) {
            return [];
        }
        $orden = "FIELD(estado, 'nuevo', 'contacto', 'negociacion', 'concretado', 'descartado')";
        return InteresInversion::find()->with(['usuario', 'inversor', 'moneda', 'notas.usuario'])->where(['idproyecto' => $p->idproyecto])
            ->orderBy(new \yii\db\Expression($orden . ', fecha_actualizacion DESC'))->all();
    }

    public function pagados(Proyecto $p): array
    {
        $ids = InteresInversion::find()->select('idusuario')->where(['idproyecto' => $p->idproyecto])->column();
        $res = [];
        foreach ($ids as $id) {
            $totales = InversionPago::totalesPorMoneda(['idproyecto' => $p->idproyecto, 'idusuario' => (int) $id]);
            if ($totales) {
                $res[(int) $id] = InversionPago::totalesTexto($totales);
            }
        }
        return $res;
    }

    public function puedeGestionar(): bool
    {
        return (bool) $this->permisos->tienePermiso('interesados_gestionar');
    }

    public function seguimiento(int $idinteres, array $post): array
    {
        $i = InteresInversion::find()->with(['proyecto', 'usuario'])->where(['idinteres' => $idinteres])->one();
        if ($i === null) {
            throw new NotFoundHttpException('No existe ese interesado.');
        }
        $this->proyectos->buscar($i->idproyecto, 'interesados_gestionar'); // permiso + gerente solo de sus áreas
        $estado = (string) ($post['estado'] ?? $i->estado);
        $nota = trim((string) ($post['nota'] ?? ''));
        if (!isset(InteresInversion::ESTADOS[$estado])) {
            return ['exito' => false, 'mensaje' => 'Estado no válido.', 'idproyecto' => $i->idproyecto];
        }
        if (mb_strlen($nota) > 1000) {
            return ['exito' => false, 'mensaje' => 'La nota puede tener hasta 1000 caracteres.', 'idproyecto' => $i->idproyecto];
        }
        if ($estado === $i->estado && $nota === '') {
            return ['exito' => false, 'mensaje' => 'No hubo cambios: elija otro estado o escriba una nota.', 'idproyecto' => $i->idproyecto];
        }
        $yo = (int) Yii::$app->user->id;
        if ($estado !== $i->estado) {
            $texto = 'Estado: ' . $i->estadoNombre() . ' → ' . InteresInversion::ESTADOS[$estado][0] . '.';
            $i->updateAttributes(['estado' => $estado]);
            InteresInversionNota::registrar($i->idinteres, 'estado', $texto, $yo);
            InversorHistorial::registrar($i->idusuario, 'Quiero invertir', $i->proyecto->titulo . ' · ' . $texto);
        }
        if ($nota !== '') {
            InteresInversionNota::registrar($i->idinteres, 'nota', $nota, $yo);
        }
        return ['exito' => true, 'mensaje' => 'Seguimiento guardado.', 'idproyecto' => $i->idproyecto];
    }

    /* ================= Ayudas ================= */

    /** "Juan Pérez, gerente del proyecto," o "Nuestro equipo". */
    private function quienContacta(Proyecto $p): string
    {
        $g = $p->idgerente ? Usuarios::find()->select(['nombrecompleto'])->where(['idusuario' => $p->idgerente, 'estado' => '1'])->scalar() : null;
        return $g ? trim($g) . ', gerente del proyecto,' : 'Nuestro equipo';
    }

    private function avisarEquipo(InteresInversion $i, Inversor $inv, Proyecto $p, bool $nuevo): void
    {
        $gerentes = (new \yii\db\Query())->select('u.email')->from(['ue' => 'usuario_ecosistema'])
            ->innerJoin(['u' => 'usuarios'], 'u.idusuario = ue.idusuario')
            ->where(['ue.idecosistema' => $p->idecosistema, 'u.idrol' => Rol::GERENTE, 'u.estado' => '1'])->column();
        $responsable = $p->idgerente ? Usuarios::find()->select('email')->where(['idusuario' => $p->idgerente, 'estado' => '1'])->column() : [];
        $super = Usuarios::find()->select('email')->where(['idrol' => Rol::SUPERADMIN, 'estado' => '1'])->column();
        $corporativo = (string) Configuracion::find()->select('email')->scalar();
        $emails = array_values(array_unique(array_filter(array_map('trim', array_merge($responsable, $gerentes, $super, [$corporativo])))));
        if (!$emails) {
            return;
        }
        $u = $inv->usuario;
        $resumen = [
            'Inversor' => $inv->nombreVisible(),
            'Proyecto' => $p->titulo . ' (' . $p->codigo . ')',
            'Monto estimado' => $i->montoTexto(),
            'Participación' => Inversor::PARTICIPACIONES[$i->participacion] ?? null,
            'Plazo' => Inversor::PLAZOS[$i->plazo] ?? null,
            'Prefiere' => $i->medioTexto() . ($i->contacto_horario ? ' · ' . $i->contacto_horario : ''),
            'Celular' => $u->movil ?: $u->telefono,
            'Correo' => $u->email,
            'Comentario' => $i->comentario,
        ];
        $enlace = Url::to(['/admin/banco-proyectos/view', 'id' => $p->idproyecto, 'tab' => 'interesados'], true);
        $this->intentar(fn() => $this->correos->enviarCorreoInteresEquipo($emails, $inv->usuario, $p, array_filter($resumen), $enlace, $nuevo));
    }

    private function intentar(callable $enviar)
    {
        try {
            return (bool) $enviar();
        } catch (\Throwable $e) {
            Yii::error('No se pudo enviar un correo de "Quiero invertir": ' . $e->getMessage(), __METHOD__);
            return false;
        }
    }
}

<?php

namespace app\components;

use app\models\ConfiguracionHistorial;
use app\models\InversorHistorial;
use app\models\NdaAceptacion;
use app\models\NdaVersion;
use Yii;

/**
 * Acuerdo de confidencialidad. Las versiones no se editan ni se borran (son la prueba de qué aceptó cada uno).
 * Publicar queda en el historial de configuración; cada aceptación, en el historial del inversor.
 */
class NdaService implements InterfaceNda
{
    private $permisos;

    public function __construct(InterfacePermisosService $permisos)
    {
        $this->permisos = $permisos;
    }

    public function vigente(): ?NdaVersion
    {
        return NdaVersion::vigente();
    }

    public function resumen(): array
    {
        $vigente = $this->vigente();
        $aprobados = (new \yii\db\Query())->from('inversor i')->innerJoin('usuarios u', 'u.idusuario = i.idusuario')
            ->where(['i.estado_solicitud' => 'aprobado', 'u.estado' => '1']);
        $pendientes = $vigente ? (int) (clone $aprobados)->andWhere(['not exists',
            (new \yii\db\Query())->from('nda_aceptacion a')->where('a.idusuario = i.idusuario')->andWhere(['a.idversion' => $vigente->idversion])])->count() : 0;
        return [
            'versiones' => NdaVersion::find()->with('usuario')->orderBy(['numero' => SORT_DESC])->all(),
            'aceptaciones' => array_map('intval', NdaAceptacion::find()->select(['COUNT(*)', 'idversion'])->groupBy('idversion')->indexBy('idversion')->column()),
            'pendientes' => $pendientes,
        ];
    }

    public function borrador(): NdaVersion
    {
        $vigente = $this->vigente();
        return new NdaVersion([
            'numero' => ($vigente->numero ?? 0) + 1,
            'titulo' => $vigente->titulo ?? 'Acuerdo de confidencialidad',
            'texto' => $vigente->texto ?? '',
        ]);
    }

    public function publicar(NdaVersion $v, array $post)
    {
        $this->permisos->verificarPermiso('nda_editar');
        $datos = $post['NdaVersion'] ?? [];
        $v->titulo = $datos['titulo'] ?? '';
        $v->nota = $datos['nota'] ?? '';
        $html = HtmlEditor::limpiar($datos['texto'] ?? '');
        $plano = trim(preg_replace('/[\s\x{00A0}]+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $v->texto = $plano === '' ? '' : $html;
        if (!$v->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        $vigente = $this->vigente();
        if ($vigente && $vigente->texto === $v->texto && $vigente->titulo === $v->titulo) {
            $v->addError('texto', 'El texto es igual al de la versión vigente: no hace falta publicar otra.');
            return ['exito' => false, 'mensaje' => 'No hay cambios para publicar.'];
        }
        $v->numero = ($vigente->numero ?? 0) + 1;
        $v->idusuario = Yii::$app->user->id;
        $v->ip = Yii::$app->request->userIP;
        $v->fecha_publicacion = date('Y-m-d H:i:s');
        $v->nota = $v->nota === '' ? null : $v->nota;
        $v->save(false);
        ConfiguracionHistorial::registrar('Acuerdo de confidencialidad: publicada la versión ' . $v->numero . ($v->nota ? ' (' . $v->nota . ')' : '')
            . '. Los inversores la aceptan al volver a entrar.');
        return ['exito' => true, 'mensaje' => 'Versión ' . $v->numero . ' publicada. Los inversores la van a aceptar la próxima vez que entren.'];
    }

    public function aceptar($idusuario, $firma, $acepto)
    {
        $vigente = $this->vigente();
        if ($vigente === null) {
            return ['exito' => true, 'mensaje' => ''];
        }
        $firma = trim(preg_replace('/\s+/u', ' ', (string) $firma));
        if (!$acepto) {
            return ['exito' => false, 'mensaje' => 'Marque la casilla "Leí y acepto" para continuar.'];
        }
        if (mb_strlen($firma) < 5 || mb_strlen($firma) > 150) {
            return ['exito' => false, 'mensaje' => 'Escriba su nombre completo como firma.'];
        }
        if (NdaAceptacion::find()->where(['idusuario' => (int) $idusuario, 'idversion' => $vigente->idversion])->exists()) {
            return ['exito' => true, 'mensaje' => 'Ya lo habías aceptado.'];
        }
        $a = new NdaAceptacion([
            'idusuario' => (int) $idusuario,
            'idversion' => $vigente->idversion,
            'firma' => $firma,
            'ip' => Yii::$app->request->userIP,
            'navegador' => mb_substr((string) Yii::$app->request->userAgent, 0, 255),
            'fecha' => date('Y-m-d H:i:s'),
        ]);
        $a->save(false);
        InversorHistorial::registrar((int) $idusuario, 'NDA', 'Aceptó el acuerdo de confidencialidad, versión ' . $vigente->numero
            . "\nFirma: " . $firma . "\nIP: " . $a->ip);
        return ['exito' => true, 'mensaje' => 'Gracias. Ya puede ver los proyectos de sus áreas.'];
    }

    public function aceptacionesDe($idusuario): array
    {
        return NdaAceptacion::find()->with('version')->where(['idusuario' => (int) $idusuario])->orderBy(['fecha' => SORT_DESC])->all();
    }
}

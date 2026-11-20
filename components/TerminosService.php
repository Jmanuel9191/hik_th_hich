<?php

namespace app\components;

use app\models\ConfiguracionHistorial;
use app\models\TerminosVersion;
use Yii;

/**
 * Términos de uso y política de privacidad. Las versiones no se editan ni se borran (son la prueba de qué aceptó
 * cada inversor al registrarse: inversor.terminos_version). Publicar queda en el historial de configuración.
 * A diferencia del NDA, publicar una versión nueva no les pide nada a los inversores que ya están.
 */
class TerminosService implements InterfaceTerminos
{
    private $permisos;

    public function __construct(InterfacePermisosService $permisos)
    {
        $this->permisos = $permisos;
    }

    public function vigente(): ?TerminosVersion
    {
        return TerminosVersion::vigente();
    }

    public function resumen(): array
    {
        return [
            'versiones' => TerminosVersion::find()->with('usuario')->orderBy(['numero' => SORT_DESC])->all(),
            'aceptaciones' => array_map('intval', (new \yii\db\Query())->select(['COUNT(*)', 'terminos_version'])->from('inversor')
                ->where(['not', ['terminos_version' => null]])->groupBy('terminos_version')->indexBy('terminos_version')->column()),
        ];
    }

    public function borrador(): TerminosVersion
    {
        $vigente = $this->vigente();
        return new TerminosVersion([
            'numero' => ($vigente->numero ?? 0) + 1,
            'titulo' => $vigente->titulo ?? 'Términos de uso y política de privacidad',
            'texto' => $vigente->texto ?? '',
        ]);
    }

    public function publicar(TerminosVersion $v, array $post)
    {
        $this->permisos->verificarPermiso('terminos_editar');
        $datos = $post['TerminosVersion'] ?? [];
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
        ConfiguracionHistorial::registrar('Términos y privacidad: publicada la versión ' . $v->numero . ($v->nota ? ' (' . $v->nota . ')' : '')
            . '. Desde ahora es la que aceptan los inversores nuevos.');
        return ['exito' => true, 'mensaje' => 'Versión ' . $v->numero . ' publicada. Es la que ven y aceptan los que se registran desde ahora.'];
    }
}

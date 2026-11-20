<?php

namespace app\components;

use app\models\ProyectoPlantilla;
use app\models\ProyectoSeccion;
use Yii;
use yii\helpers\FileHelper;
use yii\web\NotFoundHttpException;

/**
 * Plantillas de página de proyecto (ver InterfacePlantillas). Al aplicarse a un proyecto se copian
 * (PaginaProyectoService::aplicarPlantilla): cambiar una plantilla no toca los proyectos que ya la usaron.
 */
class PlantillaService implements InterfacePlantillas
{
    private $permisos;

    public function __construct(InterfacePermisosService $permisos)
    {
        $this->permisos = $permisos;
    }

    public function listar(): array
    {
        $this->permisos->verificarPermiso('plantillas_editar');
        return [
            'plantillas' => ProyectoPlantilla::find()->with('ecosistema')->orderBy(['orden' => SORT_ASC, 'nombre' => SORT_ASC])->all(),
            'secciones' => array_map('intval', ProyectoSeccion::find()->select(['COUNT(*)', 'idplantilla'])
                ->where(['not', ['idplantilla' => null]])->groupBy('idplantilla')->indexBy('idplantilla')->column()),
        ];
    }

    public function buscar($id): ProyectoPlantilla
    {
        $this->permisos->verificarPermiso('plantillas_editar');
        return ProyectoPlantilla::findOne((int) $id) ?? throw new NotFoundHttpException('La plantilla no existe.');
    }

    public function nueva(): ProyectoPlantilla
    {
        $this->permisos->verificarPermiso('plantillas_editar');
        return new ProyectoPlantilla(['estado' => '1']);
    }

    public function guardar(ProyectoPlantilla $t, array $post): array
    {
        $this->permisos->verificarPermiso('plantillas_editar');
        $datos = $post['ProyectoPlantilla'] ?? [];
        $t->setAttributes(array_intersect_key($datos, array_flip(['nombre', 'descripcion', 'idecosistema'])), false);
        $t->estado = !empty($datos['estado']) ? '1' : '0';
        if (!$t->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }
        $nueva = $t->isNewRecord;
        $ahora = date('Y-m-d H:i:s');
        if ($nueva) {
            $t->orden = (int) ProyectoPlantilla::find()->max('orden') + 1;
            $t->idusuario_registro = Yii::$app->user->id;
            $t->fecha_registro = $ahora;
        }
        $t->fecha_actualizacion = $ahora;
        $t->save(false);
        return ['exito' => true, 'mensaje' => $nueva ? 'Plantilla creada. Ahora arme sus secciones.' : 'Plantilla guardada.'];
    }

    public function duplicar($id): array
    {
        $t = $this->buscar($id);
        $copia = new ProyectoPlantilla([
            'nombre' => $this->nombreLibre($t->nombre . ' (copia)'),
            'descripcion' => $t->descripcion, 'idecosistema' => $t->idecosistema, 'estado' => '0',
            'orden' => (int) ProyectoPlantilla::find()->max('orden') + 1,
            'idusuario_registro' => Yii::$app->user->id, 'fecha_registro' => date('Y-m-d H:i:s'),
        ]);
        $tx = Yii::$app->db->beginTransaction();
        $copia->save(false);
        $origen = ProyectoPlantilla::carpetaDe($t->idplantilla, 'bloques');
        $destino = ProyectoPlantilla::carpetaDe($copia->idplantilla, 'bloques');
        foreach ($t->getSecciones()->with('bloques')->all() as $s) {
            CopiaPagina::seccion($s, ['idplantilla' => $copia->idplantilla], [], $origen, $destino);
        }
        $tx->commit();
        return ['exito' => true, 'mensaje' => 'Copia creada (queda oculta hasta que la actives).'];
    }

    public function mover($id, $direccion): array
    {
        $t = $this->buscar($id);
        $subir = (int) $direccion < 0;
        $vecino = ProyectoPlantilla::find()->andWhere([$subir ? '<' : '>', 'orden', $t->orden])
            ->orderBy(['orden' => $subir ? SORT_DESC : SORT_ASC])->one();
        if ($vecino === null) {
            return ['exito' => false, 'mensaje' => $subir ? 'Ya está primera.' : 'Ya está última.'];
        }
        $orden = $t->orden;
        $t->updateAttributes(['orden' => $vecino->orden]);
        $vecino->updateAttributes(['orden' => $orden]);
        return ['exito' => true, 'mensaje' => ''];
    }

    public function eliminar($id): array
    {
        $t = $this->buscar($id);
        $t->delete(); // secciones y bloques en cascada
        FileHelper::removeDirectory(ProyectoPlantilla::carpetaDe($t->idplantilla));
        return ['exito' => true, 'mensaje' => 'Plantilla eliminada. Los proyectos que la usaron no cambian.'];
    }

    private function nombreLibre(string $base): string
    {
        $nombre = mb_substr($base, 0, 100);
        for ($i = 2; ProyectoPlantilla::find()->where(['nombre' => $nombre])->exists(); $i++) {
            $nombre = mb_substr($base, 0, 95) . ' ' . $i;
        }
        return $nombre;
    }
}

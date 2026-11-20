<?php

namespace app\components;

use app\models\Equipo;
use app\models\EquipoHistorial;
use app\models\EquipoSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * Equipo que se muestra en la web. Los permisos los revisa EquipoController.
 * Fotos en imagenes/equipo/ (SubidaImagen); biografía limpia con HtmlEditor.
 */
class EquipoService implements InterfaceEquipo
{
    const CAMPOS = ['nombre', 'puesto', 'telefono', 'email', 'linkedin', 'facebook', 'instagram', 'twitter'];

    public function listar($params)
    {
        $search = new EquipoSearch();
        return [
            'searchModel' => $search,
            'dataProvider' => $search->search($params),
            'extremos' => [(int) Equipo::find()->min('orden'), (int) Equipo::find()->max('orden')],
        ];
    }

    public function buscar($id): Equipo
    {
        $model = Equipo::findOne((int) $id);
        if ($model === null) {
            throw new NotFoundHttpException('Miembro del equipo no encontrado.');
        }
        return $model;
    }

    public function guardar(Equipo $model, array $post)
    {
        $nuevo = $model->isNewRecord;
        $antes = $model->getAttributes();
        $datos = $post['Equipo'] ?? [];
        $model->setAttributes(array_intersect_key($datos, array_flip(self::CAMPOS)), false);
        if (array_key_exists('biografia', $datos)) {
            $bio = HtmlEditor::limpiar($datos['biografia']);
            $model->biografia = Equipo::textoPlano($bio) === '' ? null : $bio; // un editor "vacío" deja <p>&nbsp;</p>
        }

        $foto = UploadedFile::getInstance($model, 'foto');
        if ($foto !== null && $foto->error === UPLOAD_ERR_NO_FILE) {
            $foto = null;
        }
        if (!$model->validate()) {
            return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
        }

        $cambioFoto = null;
        $borrar = null;
        if ($foto !== null) {
            $r = SubidaImagen::guardar($foto, Equipo::carpeta(), 5);
            if (!$r['ok']) {
                $model->addError('foto', $r['error']);
                return ['exito' => false, 'mensaje' => 'Revise los campos marcados.'];
            }
            $borrar = $model->foto;
            $cambioFoto = $model->foto ? 'reemplazada' : 'subida';
            $model->foto = $r['archivo'];
        } elseif (!empty($datos['quitar']['foto']) && $model->foto) {
            $borrar = $model->foto;
            $cambioFoto = 'quitada';
            $model->foto = null;
        }

        if ($nuevo) {
            $model->estado = '1';
            $model->orden = (int) Equipo::find()->max('orden') + 1;
            $model->idusuario_registro = Yii::$app->user->id;
            $model->fecha_registro = date('Y-m-d H:i:s');
        } else {
            $model->fecha_actualizacion = date('Y-m-d H:i:s');
        }
        $model->save(false);
        if ($borrar) {
            SubidaImagen::borrar(Equipo::carpeta(), $borrar);
        }

        if ($nuevo) {
            EquipoHistorial::registrar($model->idmiembro, 'Creación', 'Agregado al equipo: ' . $model->nombre . ' (' . $model->puesto . ')');
        } else {
            $cambios = [];
            foreach (self::CAMPOS as $campo) {
                $a = (string) $antes[$campo];
                $d = (string) $model->$campo;
                if ($a !== $d) {
                    $cambios[] = $model->getAttributeLabel($campo) . ': ' . ($a === '' ? '(vacío)' : $a) . ' → ' . ($d === '' ? '(vacío)' : $d);
                }
            }
            if ((string) $antes['biografia'] !== (string) $model->biografia) {
                $cambios[] = 'Biografía: ' . ($model->biografia === null ? 'quitada' : ($antes['biografia'] ? 'modificada' : 'agregada'));
            }
            if ($cambioFoto) {
                $cambios[] = 'Foto: ' . $cambioFoto;
            }
            if ($cambios) {
                EquipoHistorial::registrar($model->idmiembro, 'Edición', implode("\n", $cambios));
            }
        }
        return ['exito' => true, 'mensaje' => $nuevo ? 'Miembro agregado al equipo.' : 'Cambios guardados.'];
    }

    public function cambiarEstado($id)
    {
        $model = $this->buscar($id);
        $activar = $model->estado !== '1';
        $model->updateAttributes(['estado' => $activar ? '1' : '0', 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        EquipoHistorial::registrar($model->idmiembro, $activar ? 'Activación' : 'Desactivación',
            $activar ? 'Estado: Inactivo → Activo' : 'Estado: Activo → Inactivo (no se muestra en la web)');
        return ['exito' => true, 'mensaje' => $activar ? 'Activado.' : 'Desactivado.'];
    }

    public function cambiarDestacado($id)
    {
        $model = $this->buscar($id);
        $destacar = $model->destacado !== '1';
        $model->updateAttributes(['destacado' => $destacar ? '1' : '0', 'fecha_actualizacion' => date('Y-m-d H:i:s')]);
        EquipoHistorial::registrar($model->idmiembro, 'Destacado',
            $destacar ? 'Marcado como destacado (sale en el Inicio y primero en Liderazgo)' : 'Ya no es destacado');
        return ['exito' => true, 'mensaje' => $destacar ? 'Marcado como destacado.' : 'Ya no es destacado.'];
    }

    public function mover($id, $direccion)
    {
        $model = $this->buscar($id);
        $subir = (int) $direccion < 0;
        $vecino = Equipo::find()
            ->where([$subir ? '<' : '>', 'orden', $model->orden])
            ->orderBy(['orden' => $subir ? SORT_DESC : SORT_ASC])
            ->one();
        if ($vecino === null) {
            return ['exito' => false, 'mensaje' => $subir ? 'Ya es el primero.' : 'Ya es el último.'];
        }
        $tx = Yii::$app->db->beginTransaction();
        $orden = $model->orden;
        $model->updateAttributes(['orden' => $vecino->orden]);
        $vecino->updateAttributes(['orden' => $orden]);
        $tx->commit();
        EquipoHistorial::registrar($model->idmiembro, 'Orden', 'Posición: ' . ($subir ? 'subió' : 'bajó') . ' un lugar.');
        return ['exito' => true, 'mensaje' => ''];
    }

    public function eliminar($id)
    {
        $model = $this->buscar($id);
        $nombre = $model->nombre;
        $foto = $model->foto;
        $model->delete();
        SubidaImagen::borrar(Equipo::carpeta(), $foto);
        EquipoHistorial::registrar((int) $id, 'Eliminación', 'Eliminado del equipo: ' . $nombre);
        return ['exito' => true, 'mensaje' => 'Eliminado del equipo.'];
    }

    public function historial($id)
    {
        return new ActiveDataProvider([
            'query' => EquipoHistorial::find()->with('usuarioActor')->where(['idmiembro' => (int) $id])->orderBy(['idhistorial' => SORT_DESC]),
            'pagination' => ['pageSize' => 10, 'pageParam' => 'hpage'],
        ]);
    }

    public function exportar($params)
    {
        $search = new EquipoSearch();
        $modelos = $search->search($params)->query->all();
        $columnas = [
            'Orden' => fn(Equipo $m) => $m->orden,
            'Nombre' => fn($m) => $m->nombre,
            'Puesto' => fn($m) => $m->puesto,
            'Email' => fn($m) => $m->email,
            'Teléfono' => fn($m) => $m->telefono,
            'LinkedIn' => fn($m) => $m->linkedin,
            'Facebook' => fn($m) => $m->facebook,
            'Instagram' => fn($m) => $m->instagram,
            'X / Twitter' => fn($m) => $m->twitter,
            'Destacado' => fn($m) => $m->destacado === '1' ? 'Sí' : 'No',
            'Visible en la web' => fn($m) => $m->estado === '1' ? 'Sí' : 'No',
            'Resumen' => fn($m) => Equipo::textoPlano($m->biografia),
            'Fecha de registro' => fn($m) => $m->fecha_registro ? date('d/m/Y', strtotime($m->fecha_registro)) : '',
        ];
        $filtros = [];
        foreach (['nombre' => 'Nombre', 'puesto' => 'Puesto'] as $c => $e) {
            if ((string) $search->$c !== '') {
                $filtros[] = $e . ': ' . $search->$c;
            }
        }
        if ((string) $search->estado !== '') {
            $filtros[] = 'Visible: ' . ($search->estado === '1' ? 'sí' : 'no');
        }
        if ((string) $search->destacado !== '') {
            $filtros[] = 'Destacado: ' . ($search->destacado === '1' ? 'sí' : 'no');
        }
        return ['titulo' => 'Equipo', 'columnas' => $columnas, 'modelos' => $modelos, 'filtros' => $filtros];
    }

    public function paraWeb(): array
    {
        return Equipo::activos()->all();
    }
}

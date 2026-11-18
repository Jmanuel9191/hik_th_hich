<?php

namespace app\components;

use Yii;
use yii\helpers\Url;
use app\models\Asignacion;
use app\models\Proyectos;
use app\models\Campanias;
use app\models\Recursos;
use app\models\Usuarios;

class CalendarioService implements InterfaceCalendario
{
    public function obtenerEventosPorUsuario(Usuarios $usuario): array
    {
        $eventos = [];

        if ($usuario->esConsultor()) {
            $eventos = array_merge(
                $this->eventosAsignacionesConsultor($usuario),
                $this->eventosProyectosConsultor($usuario),
                $this->eventosCampaniasConsultor($usuario)
            );
        }

        if ($usuario->esCliente()) {
            $eventos = array_merge(
                $this->eventosRecursosCliente($usuario),
                $this->eventosProyectosCliente($usuario),
                $this->eventosCampaniasCliente($usuario)
            );
        }

        return $eventos;
    }

    //  MÉTODOS PRIVADOS

    private function eventosAsignacionesConsultor($usuario): array
    {
        $eventos = [];
        $asignaciones = Asignacion::find()->where(['idconsultor' => $usuario->idusuario])->all();

        foreach ($asignaciones as $a) {
            $titulo = $a->tarea->titulo ?? 'Sin título';

            if ($a->fechainicio && $a->fechafin) {
                $eventos[] = [
                    'title' => $titulo,
                    'start' => $a->fechainicio,
                    'end'   => $a->fechafin,
                    'color' => '#ffc107',
                    'tipo'  => 'tarea'
                ];
            }

            if ($a->fechaestadofinal) {
                $eventos[] = [
                    'title' => '✅ Tarea Finalizada: ' . $titulo,
                    'start' => $a->fechaestadofinal,
                    'color' => '#c69500',
                    'tipo'  => 'tarea'
                ];
            }

            $eventos = array_merge($eventos, $this->eventosRecursosAsignacion($a));
        }

        return $eventos;
    }

    private function eventosRecursosAsignacion(Asignacion $a): array
    {
        $eventos = [];
        $recursos = Recursos::find()->where(['idasignartarea' => $a->idasignartarea])->all();

        foreach ($recursos as $r) {
            $url = null;
            $origen = '';

            if ($a->idproyecto) {
                $url = Url::to(['/proyectos/historialmaterial', 'idrecurso' => $r->idrecurso], true);
                $origen = 'Proyecto';
            } elseif ($a->idcampania) {
                $url = Url::to(['/campanias/historialmaterial', 'idrecurso' => $r->idrecurso], true);
                $origen = 'Campaña';
            }

            $eventos[] = [
                'title' => '📦 Fecha límite: ' . $r->titulo,
                'start' => $r->fechaentrega,
                'color' => '#dc3545',
                'tipo' => 'recurso',
                'url' => $url,
                'extendedProps' => ['origen' => $origen],
            ];
        }

        return $eventos;
    }

    private function eventosProyectosConsultor($usuario): array
    {
        $eventos = [];
        $ids = Asignacion::find()
            ->select('idproyecto')
            ->distinct()
            ->where(['idconsultor' => $usuario->idusuario])
            ->andWhere(['IS NOT', 'idproyecto', null])
            ->column();

        $proyectos = Proyectos::find()->where(['idproyecto' => $ids])->all();

        foreach ($proyectos as $p) {
            if ($p->fecha_inicio && $p->fecha_fin) {
                $eventos[] = [
                    'title' => $p->titulo,
                    'start' => $p->fecha_inicio,
                    'end'   => $p->fecha_fin,
                    'color' => '#007bff',
                    'tipo'  => 'proyecto',
                    'url'   => Url::to(['/proyectos/viewproyect', 'id' => $p->idproyecto], true)
                ];
            }

            if ($p->fechafinalizacion) {
                $eventos[] = [
                    'title' => '✅ Proyecto Finalizado: ' . $p->titulo,
                    'start' => $p->fechafinalizacion,
                    'color' => '#0056b3',
                    'tipo'  => 'proyecto',
                    'url'   => Url::to(['/proyectos/viewproyect', 'id' => $p->idproyecto], true)
                ];
            }
        }

        return $eventos;
    }

    private function eventosCampaniasConsultor($usuario): array
    {
        $eventos = [];
        $ids = Asignacion::find()
            ->select('idcampania')
            ->distinct()
            ->where(['idconsultor' => $usuario->idusuario])
            ->andWhere(['IS NOT', 'idcampania', null])
            ->column();

        $campanias = Campanias::find()->where(['idcampania' => $ids])->all();

        foreach ($campanias as $c) {
            if ($c->fecha_inicio && $c->fecha_fin) {
                $eventos[] = [
                    'title' => $c->titulo,
                    'start' => $c->fecha_inicio,
                    'end'   => $c->fecha_fin,
                    'color' => '#28a745',
                    'tipo'  => 'campania',
                    'url'   => Url::to(['/campanias/viewproyect', 'id' => $c->idcampania], true)
                ];
            }

            if ($c->fechafinalizacion) {
                $eventos[] = [
                    'title' => '✅ Campaña Finalizada: ' . $c->titulo,
                    'start' => $c->fechafinalizacion,
                    'color' => '#1b6d2f',
                    'tipo'  => 'campania',
                    'url'   => Url::to(['/campanias/viewproyect', 'id' => $c->idcampania], true)
                ];
            }
        }

        return $eventos;
    }
    private function eventosRecursosCliente(Usuarios $usuario): array
    {
        $eventos = [];
        $asignaciones = Asignacion::find()
            ->joinWith(['proyecto', 'campania'])  // ✅ Agregas el JOIN a campanias
            ->where(['proyectos.idcliente' => $usuario->idusuario])
            ->orWhere(['campanias.idcliente' => $usuario->idusuario])
            ->all();

        foreach ($asignaciones as $a) {
            $eventos = array_merge($eventos, $this->eventosRecursosAsignacion($a));
        }

        return $eventos;
    }


    private function eventosProyectosCliente($usuario): array
    {
        $eventos = [];
        $proyectos = Proyectos::find()->where(['idcliente' => $usuario->idusuario])->all();

        foreach ($proyectos as $p) {
            if ($p->fecha_inicio && $p->fecha_fin) {
                $eventos[] = [
                    'title' => $p->titulo,
                    'start' => $p->fecha_inicio,
                    'end'   => $p->fecha_fin,
                    'color' => '#007bff',
                    'tipo'  => 'proyecto',
                    'url'   => Url::to(['/proyectos/viewproyect', 'id' => $p->idproyecto], true)
                ];
            }

            if ($p->fechafinalizacion) {
                $eventos[] = [
                    'title' => '✅ Proyecto Finalizado: ' . $p->titulo,
                    'start' => $p->fechafinalizacion,
                    'color' => '#0056b3',
                    'tipo'  => 'proyecto',
                    'url'   => Url::to(['/proyectos/viewproyect', 'id' => $p->idproyecto], true)
                ];
            }
        }

        return $eventos;
    }

    private function eventosCampaniasCliente($usuario): array
    {
        $eventos = [];
        $campanias = Campanias::find()->where(['idcliente' => $usuario->idusuario])->all();

        foreach ($campanias as $c) {
            if ($c->fecha_inicio && $c->fecha_fin) {
                $eventos[] = [
                    'title' => $c->titulo,
                    'start' => $c->fecha_inicio,
                    'end'   => $c->fecha_fin,
                    'color' => '#28a745',
                    'tipo'  => 'campania',
                    'url'   => Url::to(['/campanias/viewproyect', 'id' => $c->idcampania], true)
                ];
            }

            if ($c->fechafinalizacion) {
                $eventos[] = [
                    'title' => '✅ Campaña Finalizada: ' . $c->titulo,
                    'start' => $c->fechafinalizacion,
                    'color' => '#1b6d2f',
                    'tipo'  => 'campania',
                    'url'   => Url::to(['/campanias/viewproyect', 'id' => $c->idcampania], true)
                ];
            }
        }

        return $eventos;
    }


    public function obtenerEventosGlobales(): array
    {
        $eventos = [];

        // PROYECTOS
        foreach (\app\models\Proyectos::find()->all() as $p) {
            if (!empty($p->fecha_inicio) && !empty($p->fecha_fin)) {
                $eventos[] = [
                    'title' => $p->titulo,
                    'start' => $p->fecha_inicio,
                    'end'   => $p->fecha_fin,
                    'color' => '#007bff',
                    'tipo'  => 'proyecto',
                    'url'   => \yii\helpers\Url::to(['/proyectos/view', 'id' => $p->idproyecto], true),
                ];
            }
            if (!empty($p->fechafinalizacion)) {
                $eventos[] = [
                    'title' => '✅ Proyecto Finalizado: ' . $p->titulo,
                    'start' => $p->fechafinalizacion,
                    'color' => '#0056b3',
                    'tipo'  => 'proyecto',
                    'url'   => \yii\helpers\Url::to(['/proyectos/view', 'id' => $p->idproyecto], true),
                ];
            }
        }

        // CAMPAÑAS
        foreach (\app\models\Campanias::find()->all() as $c) {
            if (!empty($c->fecha_inicio) && !empty($c->fecha_fin)) {
                $eventos[] = [
                    'title' => $c->titulo,
                    'start' => $c->fecha_inicio,
                    'end'   => $c->fecha_fin,
                    'color' => '#28a745',
                    'tipo'  => 'campania',
                    'url'   => \yii\helpers\Url::to(['/campanias/view', 'id' => $c->idcampania], true),
                ];
            }
            if (!empty($c->fechafinalizacion)) {
                $eventos[] = [
                    'title' => '✅ Campaña Finalizada: ' . $c->titulo,
                    'start' => $c->fechafinalizacion,
                    'color' => '#1b6d2f',
                    'tipo'  => 'campania',
                    'url'   => \yii\helpers\Url::to(['/campanias/view', 'id' => $c->idcampania], true),
                ];
            }
        }

        // TAREAS (ASIGNACIONES)
        foreach (\app\models\Asignacion::find()->all() as $a) {
            $titulo = $a->tarea->titulo ?? 'Sin título';

            if (!empty($a->fechainicio) && !empty($a->fechafin)) {
                $eventos[] = [
                    'title' => $titulo,
                    'start' => $a->fechainicio,
                    'end'   => $a->fechafin,
                    'color' => '#ffc107',
                    'tipo'  => 'tarea'
                ];
            }

            if (!empty($a->fechaestadofinal)) {
                $eventos[] = [
                    'title' => '✅ Tarea Finalizada: ' . $titulo,
                    'start' => $a->fechaestadofinal,
                    'color' => '#c69500',
                    'tipo'  => 'tarea'
                ];
            }

            // RECURSOS de esa asignación
            foreach (\app\models\Recursos::find()->where(['idasignartarea' => $a->idasignartarea])->all() as $r) {
                $url = null;
                $origen = '';

                if ($a->idproyecto) {
                    $url = \yii\helpers\Url::to(['/proyectos/historialmaterial', 'idrecurso' => $r->idrecurso], true);
                    $origen = 'Proyecto';
                } elseif ($a->idcampania) {
                    $url = \yii\helpers\Url::to(['/campanias/historialmaterial', 'idrecurso' => $r->idrecurso], true);
                    $origen = 'Campaña';
                }

                $eventos[] = [
                    'title' => '📦 Fecha límite: ' . $r->titulo,
                    'start' => $r->fechaentrega,
                    'color' => '#dc3545',
                    'tipo'  => 'recurso',
                    'url'   => $url,
                    'extendedProps' => ['origen' => $origen],
                ];
            }
        }

        return $eventos;
    }


    public function limpiarFlashes()
    {
        Yii::$app->session->removeFlash('flash');
        Yii::$app->session->removeFlash('error');
        Yii::$app->session->removeFlash('info');
        Yii::$app->session->removeFlash('success');
    }
}

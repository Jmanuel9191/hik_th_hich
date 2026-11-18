<?php

namespace app\components;

use app\models\Permisos;
use app\models\Roles;
use app\models\RolPermisoHistorial;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\ForbiddenHttpException;

class PermisosService implements InterfacePermisosService
{
    /**
     * Devuelve el usuario logueado actual.
     */
    public function getUsuario()
    {
        return Yii::$app->user->identity;
    }

    /**
     * Verifica si el usuario está logueado.
     */
    public function estaLogueado()
    {
        return !Yii::$app->user->isGuest;
    }

    public function verificarLogin()
    {
        if (Yii::$app->user->isGuest) {
            Yii::$app->response->redirect(['site/login'])->send();
            Yii::$app->end();
        }
        return Yii::$app->user->identity;
    }



    /**
     * Verifica si el usuario tiene un rol específico (por alias).
     */
    public function tieneRol($aliasRol)
    {
        $usuario = $this->getUsuario();
        return $usuario && $usuario->rol && $usuario->rol->alias === $aliasRol;
    }

    /**
     * Verifica si el usuario tiene un permiso específico (por alias).
     */
    public function tienePermiso($aliasPermiso)
    {
        $usuario = $this->getUsuario();
        if (!$usuario || !$usuario->rol || !$usuario->rol->rolPermisos) {
            return false;
        }

        foreach ($usuario->rol->rolPermisos as $relacion) {
            if ($relacion->permiso->alias === $aliasPermiso) {
                return true;
            }
        }

        return false;
    }

    /**
     * Lanza excepción si no tiene el permiso.
     */
    public function verificarPermiso($aliasPermiso)
    {
        if (!$this->tienePermiso($aliasPermiso)) {
            throw new ForbiddenHttpException('No tienes permiso para realizar esta acción.');
        }
    }






    public function verificarAdministrador()
    {
        $usuario = $this->getUsuario();
        if (!$usuario || (int) $usuario->idrol !== Rol::SUPERADMIN) {
            $this->redirigirSegunRol($usuario);
        }

        return $usuario;
    }


    public function verificarGestor()
    {
        $usuario = $this->getUsuario();
        if (!$usuario || !$usuario->rol || $usuario->rol->alias !== 'gestor') {
            $this->redirigirSegunRol($usuario);
        }
        return $usuario;
    }

    public function verificarConsultor()
    {
        $usuario = $this->getUsuario();
        if (!$usuario || !$usuario->rol || $usuario->rol->alias !== 'consultor') {
            $this->redirigirSegunRol($usuario);
        }
        return $usuario;
    }

    public function verificarCliente()
    {
        $usuario = $this->getUsuario();
        if (!$usuario || !$usuario->rol || $usuario->rol->alias !== 'cliente') {
            $this->redirigirSegunRol($usuario);
        }
        return $usuario;
    }

    public function verificarRol(array $rolesPermitidos)
    {
        $usuario = $this->getUsuario();
        $rolAlias = $usuario?->rol?->alias;

        if (!$usuario || !$rolAlias || !in_array($rolAlias, $rolesPermitidos)) {
            $this->redirigirSegunRol($usuario);
        }

        return $usuario;
    }

    public function verificarAcceso(array $roles, string $permiso)
    {
        $this->verificarRol($roles);
        $this->verificarPermiso($permiso);
    }






    private function redirigirSegunRol($usuario)
    {
        $alias = $usuario->rol->alias ?? '';

        $rutas = [
            'administrador' => ['admin/default/index'],
            'doctor'        => ['admin/default/index'],
  
        ];

        $ruta = $rutas[$alias] ?? ['admin/default/index'];
        Yii::$app->response->redirect($ruta)->send();
        Yii::$app->end();
    }


    /* ================= Pantalla "Roles y permisos" (solo Superadmin) ================= */

    /** Nombre visible de cada módulo (prefijo del alias del permiso: "ecosistemas_editar" → ecosistemas). */
    const NOMBRES_MODULO = ['usuarios' => 'Usuarios', 'ecosistemas' => 'Ecosistemas', 'configuracion' => 'Datos generales', 'banners' => 'Banners', 'equipo' => 'Equipo'];

    /** Módulos cuyas pantallas exigen ser Superadmin: sus permisos no se reparten a otros roles. */
    const MODULOS_SOLO_SUPERADMIN = ['usuarios'];

    /**
     * Datos para la tabla de casillas.
     * @return array ['roles' => Roles[], 'grupos' => [modulo => ['nombre','fijo','permisos' => Permisos[]]], 'asignados' => ["idrol-idpermiso" => true]]
     */
    public function matrizPermisos()
    {
        $roles = Roles::find()->where(['idrol' => Rol::PANEL])->orderBy('idrol')->all();
        $grupos = [];
        foreach (Permisos::find()->where(['estado' => '1'])->orderBy(['alias' => SORT_ASC])->all() as $p) {
            $modulo = strstr($p->alias, '_', true) ?: $p->alias;
            $grupos[$modulo]['nombre'] = self::NOMBRES_MODULO[$modulo] ?? ucfirst($modulo);
            $grupos[$modulo]['fijo'] = in_array($modulo, self::MODULOS_SOLO_SUPERADMIN, true);
            $grupos[$modulo]['permisos'][] = $p;
        }
        $asignados = [];
        foreach ((new \yii\db\Query())->from('rol_permiso')->where(['idrol' => Rol::PANEL])->all() as $f) {
            $asignados[$f['idrol'] . '-' . $f['idpermiso']] = true;
        }
        return ['roles' => $roles, 'grupos' => $grupos, 'asignados' => $asignados];
    }

    /**
     * Guarda las casillas. $marcados[idrol] = [idpermiso, ...] (lo que vino tildado).
     * - Superadmin: siempre todos los permisos (no se puede quitar).
     * - Módulos solo-Superadmin: no se tocan para los demás roles.
     * @return int cantidad de cambios
     */
    public function guardarMatriz(array $marcados)
    {
        $this->verificarAdministrador();
        $m = $this->matrizPermisos();
        $cambios = [];
        foreach ($m['roles'] as $rol) {
            $idrol = (int) $rol->idrol;
            $tildados = array_map('intval', (array) ($marcados[$idrol] ?? []));
            foreach ($m['grupos'] as $grupo) {
                foreach ($grupo['permisos'] as $p) {
                    $idp = (int) $p->idpermiso;
                    $tiene = isset($m['asignados'][$idrol . '-' . $idp]);
                    if ($idrol === Rol::SUPERADMIN) {
                        $quiere = true;
                    } elseif ($grupo['fijo']) {
                        $quiere = $tiene;
                    } else {
                        $quiere = in_array($idp, $tildados, true);
                    }
                    if ($quiere !== $tiene) {
                        $cambios[] = [$idrol, $idp, $quiere];
                    }
                }
            }
        }

        if (!$cambios) {
            return 0;
        }
        $db = Yii::$app->db;
        $tx = $db->beginTransaction();
        try {
            $ahora = date('Y-m-d H:i:s');
            $ip = Yii::$app->request instanceof \yii\web\Request ? Yii::$app->request->userIP : null;
            foreach ($cambios as [$idrol, $idp, $dar]) {
                if ($dar) {
                    $db->createCommand()->insert('rol_permiso', ['idrol' => $idrol, 'idpermiso' => $idp])->execute();
                } else {
                    $db->createCommand()->delete('rol_permiso', ['idrol' => $idrol, 'idpermiso' => $idp])->execute();
                }
                $db->createCommand()->insert('rol_permiso_historial', [
                    'idrol' => $idrol, 'idpermiso' => $idp, 'accion' => $dar ? 'Asignado' : 'Quitado',
                    'idusuario_actor' => Yii::$app->user->id, 'ip' => $ip, 'fecha' => $ahora,
                ])->execute();
            }
            $tx->commit();
        } catch (\Throwable $e) {
            $tx->rollBack();
            throw $e;
        }
        return count($cambios);
    }

    /** Últimos cambios de permisos. */
    public function historialPermisos()
    {
        return new ActiveDataProvider([
            'query' => RolPermisoHistorial::find()->with(['rol', 'permiso', 'usuarioActor'])
                ->orderBy(['fecha' => SORT_DESC, 'idhistorial' => SORT_DESC]),
            'pagination' => ['pageSize' => 15, 'pageParam' => 'hpage'],
            'sort' => false,
        ]);
    }
}

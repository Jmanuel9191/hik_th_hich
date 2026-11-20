<?php

namespace app\components;

use app\models\ContrasenaInversorForm;
use app\models\Inversor;
use app\models\PerfilInversorForm;

/**
 * Mi cuenta del inversor: su perfil, foto, datos que puede cambiar, contraseña, pedido de más áreas
 * y sus inversiones (pagos y comprobantes). Todo sobre el inversor logueado: nunca recibe ids de otro usuario
 * salvo archivoFoto(), que el panel usa después de comprobar el permiso.
 */
interface InterfacePerfilInversor
{
    /** Todo lo que muestra Mi cuenta. @return array inversor, nda, terminos, proyectos, pendientes, disponibles, inversiones */
    public function resumen(int $idusuario): array;

    /** Guarda solo los campos permitidos. @return array ['exito', 'mensaje'] */
    public function guardarDatos(Inversor $inv, PerfilInversorForm $f, array $post): array;

    /** Cambia la contraseña comprobando la actual. @return array ['exito', 'mensaje'] */
    public function cambiarContrasena(Inversor $inv, ContrasenaInversorForm $f, array $post): array;

    /** Foto de perfil desde $_FILES['foto'] (se recorta cuadrada y se guarda en WEBP). @return array ['exito', 'mensaje'] */
    public function subirFoto(Inversor $inv): array;

    /** @return array ['exito', 'mensaje'] */
    public function quitarFoto(Inversor $inv): array;

    /** Ruta del archivo de la foto, o null. */
    public function archivoFoto(int $idusuario): ?string;

    /** Pide acceso a más áreas (solo aprobados). @return array ['exito', 'mensaje'] */
    public function pedirAreas(Inversor $inv, array $ids): array;

    /** Comprobante de un pago SUYO (vigente). @return array ['ruta', 'nombre', 'mime'] */
    public function archivoComprobante(int $idusuario, int $idpago): array;
}

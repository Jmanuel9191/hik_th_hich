<?php

namespace app\models;

use yii\base\Model;

/**
 * Datos del banner de la página web del proyecto (pestaña "Página web"):
 * antetítulo de la tarjeta, texto del banner, sello, elementos (Au 79…), cifras grandes y "destacado".
 * Los guarda PaginaProyectoService en las columnas del proyecto (elementos y cifras en JSON).
 */
class BannerProyectoForm extends Model
{
    const MAX_ELEMENTOS = 8;
    const MAX_CIFRAS = 4;
    const SELLO = 'CONFIDENCIAL – PARA DISCUSIÓN Y EVALUACIÓN';

    public $rubro;
    public $bajada;
    public $sello;
    public $destacado = false;
    /** Mostrar la Ficha (datos de la inversión) al principio de la página. */
    public $mostrar_ficha = true;
    /** [['numero' => '79', 'simbolo' => 'Au', 'nombre' => 'Oro'], …] */
    public $elementos = [];
    /** [['valor' => '7', 'texto' => 'Concesiones mineras', 'contar' => true], …] */
    public $cifras = [];

    public static function desdeProyecto(Proyecto $p): self
    {
        return new self([
            'rubro' => $p->rubro,
            'bajada' => $p->bajada,
            'sello' => $p->sello ?? self::SELLO,
            'destacado' => $p->destacado === '1',
            'mostrar_ficha' => $p->mostrar_ficha !== '0',
            'elementos' => $p->listaElementos(),
            'cifras' => $p->listaCifras(),
        ]);
    }

    public function rules()
    {
        return [
            [['rubro', 'bajada', 'sello'], 'filter', 'filter' => fn($v) => trim((string) $v)],
            [['rubro'], 'string', 'max' => 80],
            [['bajada'], 'string', 'max' => 400],
            [['sello'], 'string', 'max' => 80],
            [['destacado', 'mostrar_ficha'], 'boolean'],
            [['elementos'], 'validarElementos'],
            [['cifras'], 'validarCifras'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'rubro' => 'Antetítulo de la tarjeta',
            'bajada' => 'Texto del banner',
            'sello' => 'Sello del banner',
            'destacado' => 'Proyecto destacado (aparece en el carrusel de su ecosistema)',
            'mostrar_ficha' => 'Mostrar la Ficha al principio (montos, plazo, ROI y avance)',
            'elementos' => 'Elementos',
            'cifras' => 'Cifras del banner',
        ];
    }

    public function validarElementos($a)
    {
        $lista = [];
        foreach ((array) $this->$a as $f) {
            $simbolo = trim((string) ($f['simbolo'] ?? ''));
            $numero = trim((string) ($f['numero'] ?? ''));
            $nombre = trim((string) ($f['nombre'] ?? ''));
            if ($simbolo === '' && $numero === '' && $nombre === '') {
                continue;
            }
            if ($simbolo === '' || mb_strlen($simbolo) > 4) {
                $this->addError($a, 'Cada elemento necesita su símbolo (hasta 4 letras, ej. Au).');
                continue;
            }
            if ($numero !== '' && (!ctype_digit($numero) || (int) $numero > 999)) {
                $this->addError($a, '«' . $simbolo . '»: el número atómico va sin letras (ej. 79).');
                continue;
            }
            $lista[] = ['numero' => $numero, 'simbolo' => $simbolo, 'nombre' => mb_substr($nombre, 0, 30)];
        }
        if (count($lista) > self::MAX_ELEMENTOS) {
            $this->addError($a, 'Hasta ' . self::MAX_ELEMENTOS . ' elementos.');
        }
        $this->$a = $lista;
    }

    public function validarCifras($a)
    {
        $lista = [];
        foreach ((array) $this->$a as $f) {
            $valor = trim((string) ($f['valor'] ?? ''));
            $texto = trim((string) ($f['texto'] ?? ''));
            if ($valor === '' && $texto === '') {
                continue;
            }
            if ($valor === '' || $texto === '') {
                $this->addError($a, 'Cada cifra necesita el número y su texto.');
                continue;
            }
            if (mb_strlen($valor) > 16 || mb_strlen($texto) > 60) {
                $this->addError($a, 'Cifra «' . $valor . '»: el número hasta 16 caracteres y el texto hasta 60.');
                continue;
            }
            $lista[] = ['valor' => $valor, 'texto' => $texto, 'contar' => !empty($f['contar'])];
        }
        if (count($lista) > self::MAX_CIFRAS) {
            $this->addError($a, 'Hasta ' . self::MAX_CIFRAS . ' cifras.');
        }
        $this->$a = $lista;
    }
}

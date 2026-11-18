<?php

namespace app\components;

use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\helpers\Inflector;

/**
 * Exporta un listado a Excel (.xlsx), CSV o PDF. Sirve para cualquier módulo:
 *
 *   return Exportador::enviar('Usuarios', [
 *       'Nombre' => fn($m) => $m->nombrecompleto,
 *       'Email'  => fn($m) => $m->email,
 *   ], $modelos, 'xlsx', ['Estado: Activo']);
 *
 * - Excel: se arma directo (sin librerías). Encabezado fijo, filtros de Excel y columnas anchas.
 * - CSV: UTF-8 con BOM y separador ";" (Excel en español lo abre bien, con tildes).
 *   Las celdas que empiezan con = + - @ se escapan para que Excel no las tome como fórmula.
 * - PDF: mPDF (ya está en el proyecto), A4 horizontal con logo, fecha, quién exportó y filtros.
 */
class Exportador
{
    const FORMATOS = ['xlsx' => 'Excel (.xlsx)', 'csv' => 'CSV', 'pdf' => 'PDF'];

    /**
     * @param string     $titulo   Título del documento y del nombre de archivo
     * @param callable[] $columnas Encabezado => function($modelo): string
     * @param iterable   $modelos
     * @param string     $formato  xlsx | csv | pdf
     * @param string[]   $filtros  Textos de los filtros aplicados (van en el PDF)
     * @return \yii\web\Response
     */
    public static function enviar($titulo, array $columnas, $modelos, $formato, array $filtros = [])
    {
        $encabezados = array_keys($columnas);
        $filas = [];
        foreach ($modelos as $m) {
            $fila = [];
            foreach ($columnas as $fn) {
                $v = $fn($m);
                $fila[] = $v === null ? '' : (string) $v;
            }
            $filas[] = $fila;
        }

        $nombre = Inflector::slug($titulo, '_', false) . '_' . date('Y-m-d_His');
        switch ($formato) {
            case 'csv':
                return Yii::$app->response->sendContentAsFile(self::csv($encabezados, $filas), $nombre . '.csv',
                    ['mimeType' => 'text/csv; charset=UTF-8']);
            case 'pdf':
                return Yii::$app->response->sendContentAsFile(self::pdf($titulo, $encabezados, $filas, $filtros), $nombre . '.pdf',
                    ['mimeType' => 'application/pdf']);
            default:
                return Yii::$app->response->sendContentAsFile(self::xlsx($titulo, $encabezados, $filas), $nombre . '.xlsx',
                    ['mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        }
    }

    /* ------------------------------ CSV ------------------------------ */

    public static function csv(array $encabezados, array $filas)
    {
        $h = fopen('php://temp', 'r+');
        fwrite($h, "\xEF\xBB\xBF"); // BOM: Excel reconoce UTF-8 (tildes, ñ)
        fputcsv($h, $encabezados, ';', '"', '');
        foreach ($filas as $fila) {
            fputcsv($h, array_map([self::class, 'sinFormula'], $fila), ';', '"', '');
        }
        rewind($h);
        $csv = stream_get_contents($h);
        fclose($h);
        return $csv;
    }

    /** Evita que Excel ejecute como fórmula un texto que empieza con = + - @ (inyección CSV). */
    public static function sinFormula($v)
    {
        return ($v !== '' && strpbrk($v[0], "=+-@\t\r") !== false) ? "'" . $v : $v;
    }

    /* ------------------------------ XLSX ----------------------------- */

    public static function xlsx($titulo, array $encabezados, array $filas)
    {
        $x = fn($s) => htmlspecialchars((string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $col = function ($i) {
            $s = '';
            for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
                $s = chr(65 + ($i - 1) % 26) . $s;
            }
            return $s;
        };
        $limpiar = fn($s) => preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', (string) $s);

        // Ancho de columnas según el contenido (entre 10 y 50)
        $anchos = array_map(fn($h) => mb_strlen($h), $encabezados);
        foreach ($filas as $fila) {
            foreach ($fila as $i => $v) {
                $anchos[$i] = max($anchos[$i], min(50, mb_strlen($v)));
            }
        }
        $cols = '';
        foreach ($anchos as $i => $w) {
            $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . max(10, $w + 2) . '" customWidth="1"/>';
        }

        $filasXml = '';
        $todas = array_merge([$encabezados], $filas);
        foreach ($todas as $r => $fila) {
            $celdas = '';
            foreach ($fila as $c => $v) {
                $estilo = $r === 0 ? ' s="1"' : '';
                $celdas .= '<c r="' . $col($c) . ($r + 1) . '" t="inlineStr"' . $estilo . '><is><t xml:space="preserve">'
                    . $x($limpiar($v)) . '</t></is></c>';
            }
            $filasXml .= '<row r="' . ($r + 1) . '">' . $celdas . '</row>';
        }
        $ultima = $col(max(0, count($encabezados) - 1)) . max(1, count($todas));

        $hoja = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>' . $cols . '</cols><sheetData>' . $filasXml . '</sheetData>'
            . '<autoFilter ref="A1:' . $ultima . '"/></worksheet>';

        $nombreHoja = $x(mb_substr(preg_replace('/[\\\\\/?*\[\]:]/', '', $titulo), 0, 31) ?: 'Hoja1');
        $archivos = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                . '<Default Extension="xml" ContentType="application/xml"/>'
                . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                . '</Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                . '</Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                . '<sheets><sheet name="' . $nombreHoja . '" sheetId="1" r:id="rId1"/></sheets>'
                . '<definedNames><definedName name="_xlnm._FilterDatabase" localSheetId="0" hidden="1">\'' . $nombreHoja . '\'!$A$1:$' . preg_replace('/(\d+)$/', '\$$1', $ultima) . '</definedName></definedNames>'
                . '</workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
                . '</Relationships>',
            'xl/styles.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font></fonts>'
                . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
                . '<fill><patternFill patternType="solid"><fgColor rgb="FFBD944F"/><bgColor indexed="64"/></patternFill></fill></fills>'
                . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
                . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
                . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
                . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
                . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
                . '</styleSheet>',
            'xl/worksheets/sheet1.xml' => $hoja,
        ];

        $tmp = Yii::getAlias('@runtime/export');
        FileHelper::createDirectory($tmp, 0775, true);
        $ruta = tempnam($tmp, 'xlsx');
        $zip = new \ZipArchive();
        $zip->open($ruta, \ZipArchive::OVERWRITE);
        foreach ($archivos as $nombre => $contenido) {
            $zip->addFromString($nombre, $contenido);
        }
        $zip->close();
        $binario = file_get_contents($ruta);
        @unlink($ruta);
        return $binario;
    }

    /* ------------------------------ PDF ------------------------------ */

    public static function pdf($titulo, array $encabezados, array $filas, array $filtros = [])
    {
        $tmp = Yii::getAlias('@runtime/mpdf');
        FileHelper::createDirectory($tmp, 0775, true);

        $logo = Yii::getAlias('@webroot/assets_b/img/logo.png');
        $quien = Yii::$app->user->identity->usuario ?? '';
        $cabecera = '<table width="100%" style="border-bottom:2px solid #bd944f"><tr>'
            . '<td style="background:#0b0b0b;padding:6px 10px;width:170px">'
            . (is_file($logo) ? '<img src="' . $logo . '" height="28">' : '<b style="color:#bd944f">HUK-THE-ICHI</b>') . '</td>'
            . '<td style="padding-left:12px"><div style="font-size:15pt;font-weight:bold;color:#1d1b17">' . Html::encode($titulo) . '</div>'
            . '<div style="font-size:8pt;color:#6b665c">Exportado el ' . date('d/m/Y H:i') . ($quien !== '' ? ' por ' . Html::encode($quien) : '')
            . ' · ' . count($filas) . ' registro(s)' . ($filtros ? ' · Filtros: ' . Html::encode(implode(' · ', $filtros)) : ' · Sin filtros') . '</div></td>'
            . '</tr></table>';

        $th = '';
        foreach ($encabezados as $h) {
            $th .= '<th>' . Html::encode($h) . '</th>';
        }
        $tr = '';
        foreach ($filas as $i => $fila) {
            $tr .= '<tr' . ($i % 2 ? ' class="par"' : '') . '>';
            foreach ($fila as $v) {
                $tr .= '<td>' . nl2br(Html::encode($v)) . '</td>';
            }
            $tr .= '</tr>';
        }
        if ($tr === '') {
            $tr = '<tr><td colspan="' . count($encabezados) . '" style="text-align:center;color:#6b665c">Sin registros</td></tr>';
        }

        $css = 'body{font-family:dejavusans;font-size:7.5pt;color:#1d1b17}'
            . 'table.datos{width:100%;border-collapse:collapse;margin-top:10px}'
            . 'table.datos th{background:#bd944f;color:#fff;font-weight:bold;padding:4px 5px;text-align:left;border:0.3px solid #a8823f}'
            . 'table.datos td{padding:3px 5px;border:0.3px solid #e2dccf;vertical-align:top}'
            . 'table.datos tr.par td{background:#faf8f3}';

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 10, 'margin_right' => 10, 'margin_top' => 10, 'margin_bottom' => 14,
            'default_font' => 'dejavusans',
            'tempDir' => $tmp,
        ]);
        $mpdf->SetTitle($titulo);
        $mpdf->SetAuthor('HUK-THE-ICHI GLOBAL GROUP');
        $mpdf->showImageErrors = false;
        $mpdf->SetFooter('HUK-THE-ICHI GLOBAL GROUP · Confidencial||Página {PAGENO} de {nbpg}');
        $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($cabecera . '<table class="datos" repeat_header="1"><thead><tr>' . $th . '</tr></thead><tbody>' . $tr . '</tbody></table>',
            \Mpdf\HTMLParserMode::HTML_BODY);
        return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }
}

<?php

namespace App\Support;

use App\Models\Archivo;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;
use ZipArchive;

/**
 * Vista previa de un archivo del archivador.
 *
 * Se resuelve en el servidor con lo que el proyecto ya tiene (PhpSpreadsheet
 * y ZipArchive), sin convertidores externos: asi funciona igual en el XAMPP
 * del colegio que en el hosting, y sin depender de internet.
 *
 * Lo que el navegador sabe mostrar solo (PDF, imagenes, video, audio) se le
 * entrega tal cual; del resto se extrae el contenido y se arma un HTML propio,
 * siempre escapado.
 */
class VistaPreviaArchivo
{
    /** Hasta donde se lee una hoja de calculo, para no colgar el navegador. */
    private const MAX_FILAS = 300;
    private const MAX_COLUMNAS = 40;

    /** Tope de un archivo de texto plano (1 MB ya es muchisimo para leer). */
    private const MAX_TEXTO = 1048576;

    /**
     * @return array{modo:string, html?:string, aviso?:string}
     *         modo: nativo | html | texto | no
     */
    public static function generar(Archivo $archivo, string $rutaAbsoluta): array
    {
        $extension = strtolower((string) $archivo->extension);

        // El navegador lo muestra por su cuenta: solo hay que servirlo.
        if ($archivo->seVePorDentro()) {
            return ['modo' => 'nativo'];
        }

        try {
            return match (true) {
                in_array($extension, ['xlsx', 'xls', 'xlsm', 'ods', 'csv'], true)
                    => self::hojaDeCalculo($rutaAbsoluta, $extension),

                $extension === 'docx' => self::documentoWord($rutaAbsoluta),
                $extension === 'pptx' => self::presentacion($rutaAbsoluta),

                in_array($extension, ['txt', 'md', 'json', 'xml', 'log', 'ini', 'yml', 'yaml', 'sql'], true)
                    => self::textoPlano($rutaAbsoluta),

                in_array($extension, ['zip'], true) => self::comprimido($rutaAbsoluta),

                default => self::noSoportado($extension),
            };
        } catch (Throwable $e) {
            return [
                'modo'  => 'no',
                'aviso' => 'No se pudo leer el contenido de este archivo. Descargalo para abrirlo.',
            ];
        }
    }

    // ── Hojas de calculo ──────────────────────────────────────────────

    private static function hojaDeCalculo(string $ruta, string $extension): array
    {
        if ($extension === 'csv') {
            return self::csv($ruta);
        }

        $lector = IOFactory::createReaderForFile($ruta);
        $lector->setReadDataOnly(true);
        $libro = $lector->load($ruta);

        $html = '';

        foreach ($libro->getSheetNames() as $indice => $nombreHoja) {
            $hoja = $libro->getSheet($indice);
            $filas = $hoja->toArray(null, true, false, false);

            $html .= '<h4 class="arc-prev__hoja">' . e($nombreHoja) . '</h4>';

            if (!$filas) {
                $html .= '<p class="arc-prev__vacio">Hoja vacia.</p>';
                continue;
            }

            $html .= self::tabla(array_slice($filas, 0, self::MAX_FILAS));

            if (count($filas) > self::MAX_FILAS) {
                $html .= '<p class="arc-prev__corte">Se muestran las primeras '
                    . self::MAX_FILAS . ' filas de ' . count($filas) . '.</p>';
            }
        }

        return ['modo' => 'html', 'html' => $html];
    }

    private static function csv(string $ruta): array
    {
        $filas = [];
        $puntero = fopen($ruta, 'r');

        // El separador puede ser coma o punto y coma segun quien exporto.
        $primera = fgets($puntero) ?: '';
        $separador = substr_count($primera, ';') > substr_count($primera, ',') ? ';' : ',';
        rewind($puntero);

        while (count($filas) < self::MAX_FILAS && ($fila = fgetcsv($puntero, 0, $separador)) !== false) {
            $filas[] = $fila;
        }

        fclose($puntero);

        return [
            'modo' => 'html',
            'html' => $filas ? self::tabla($filas) : '<p class="arc-prev__vacio">Archivo vacio.</p>',
        ];
    }

    private static function tabla(array $filas): string
    {
        $html = '<div class="arc-prev__tabla-wrap"><table class="arc-prev__tabla">';

        foreach ($filas as $i => $fila) {
            $celdas = array_slice((array) $fila, 0, self::MAX_COLUMNAS);
            $etiqueta = $i === 0 ? 'th' : 'td';

            $html .= '<tr>';
            foreach ($celdas as $celda) {
                $html .= "<{$etiqueta}>" . e(trim((string) $celda)) . "</{$etiqueta}>";
            }
            $html .= '</tr>';
        }

        return $html . '</table></div>';
    }

    // ── Word ──────────────────────────────────────────────────────────

    /**
     * Un .docx es un zip con XML dentro: se leen los parrafos y las tablas de
     * word/document.xml. No se reproduce el diseno, pero si el contenido.
     */
    private static function documentoWord(string $ruta): array
    {
        $xml = self::leerDelZip($ruta, 'word/document.xml');

        if ($xml === null) {
            return self::noSoportado('docx');
        }

        $doc = new \DOMDocument();
        $doc->loadXML($xml, LIBXML_NOCDATA | LIBXML_NONET);
        $xpath = self::xpathOffice($doc);

        $html = '';

        // Se recorre el cuerpo en orden, para que las tablas salgan donde van.
        foreach ($xpath->query('//w:body/*') ?: [] as $nodo) {
            if ($nodo->localName === 'p') {
                $texto = trim(self::textoDe($xpath, $nodo));
                $html .= $texto === '' ? '<p class="arc-prev__salto"></p>' : '<p>' . e($texto) . '</p>';
                continue;
            }

            if ($nodo->localName === 'tbl') {
                $filas = [];

                foreach ($xpath->query('.//w:tr', $nodo) ?: [] as $tr) {
                    $celdas = [];
                    foreach ($xpath->query('.//w:tc', $tr) ?: [] as $tc) {
                        $celdas[] = trim(self::textoDe($xpath, $tc));
                    }
                    $filas[] = $celdas;
                }

                if ($filas) {
                    $html .= self::tabla($filas);
                }
            }
        }

        return $html === ''
            ? ['modo' => 'html', 'html' => '<p class="arc-prev__vacio">El documento no tiene texto.</p>']
            : ['modo' => 'html', 'html' => $html];
    }

    /**
     * Word y PowerPoint comparten la etiqueta de texto segun el caso, asi que
     * ambos prefijos se registran siempre: un prefijo sin registrar hace que
     * XPath falle.
     */
    private static function xpathOffice(\DOMDocument $doc): \DOMXPath
    {
        $xpath = new \DOMXPath($doc);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');

        return $xpath;
    }

    private static function textoDe(\DOMXPath $xpath, \DOMNode $nodo): string
    {
        $partes = [];

        foreach ($xpath->query('.//w:t|.//a:t', $nodo) ?: [] as $t) {
            $partes[] = $t->textContent;
        }

        return implode('', $partes);
    }

    // ── PowerPoint ────────────────────────────────────────────────────

    private static function presentacion(string $ruta): array
    {
        $zip = new ZipArchive();

        if ($zip->open($ruta) !== true) {
            return self::noSoportado('pptx');
        }

        // Las diapositivas van numeradas: slide1, slide2... y hay que
        // ordenarlas por numero, no alfabeticamente (slide10 antes que slide2).
        $slides = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nombre = $zip->getNameIndex($i);

            if (preg_match('#^ppt/slides/slide(\d+)\.xml$#', $nombre, $m)) {
                $slides[(int) $m[1]] = $zip->getFromName($nombre);
            }
        }

        $zip->close();
        ksort($slides);

        $html = '';

        foreach ($slides as $numero => $xml) {
            $doc = new \DOMDocument();
            @$doc->loadXML($xml, LIBXML_NOCDATA | LIBXML_NONET);
            $xpath = self::xpathOffice($doc);

            $lineas = [];
            foreach ($xpath->query('//a:p') ?: [] as $p) {
                $texto = trim(self::textoDe($xpath, $p));
                if ($texto !== '') {
                    $lineas[] = $texto;
                }
            }

            $html .= '<div class="arc-prev__slide"><h4>Diapositiva ' . $numero . '</h4>';
            $html .= $lineas
                ? '<p>' . implode('</p><p>', array_map('e', $lineas)) . '</p>'
                : '<p class="arc-prev__vacio">Sin texto.</p>';
            $html .= '</div>';
        }

        return $html === ''
            ? self::noSoportado('pptx')
            : ['modo' => 'html', 'html' => $html];
    }

    // ── Texto y comprimidos ───────────────────────────────────────────

    private static function textoPlano(string $ruta): array
    {
        $contenido = file_get_contents($ruta, false, null, 0, self::MAX_TEXTO);

        if ($contenido === false) {
            return self::noSoportado('texto');
        }

        // Puede venir en ANSI desde Windows: se pasa a UTF-8 si hace falta.
        if (!mb_check_encoding($contenido, 'UTF-8')) {
            $contenido = mb_convert_encoding($contenido, 'UTF-8', 'ISO-8859-1');
        }

        $aviso = filesize($ruta) > self::MAX_TEXTO
            ? 'Archivo muy largo: se muestra solo el principio.'
            : null;

        return array_filter([
            'modo'  => 'texto',
            'html'  => e($contenido),
            'aviso' => $aviso,
        ]);
    }

    private static function comprimido(string $ruta): array
    {
        $zip = new ZipArchive();

        if ($zip->open($ruta) !== true) {
            return self::noSoportado('zip');
        }

        $filas = [['Archivo', 'Tamano']];

        for ($i = 0; $i < min($zip->numFiles, self::MAX_FILAS); $i++) {
            $dato = $zip->statIndex($i);
            $filas[] = [$dato['name'], self::peso((int) $dato['size'])];
        }

        $total = $zip->numFiles;
        $zip->close();

        return [
            'modo' => 'html',
            'html' => '<p class="arc-prev__nota">Contenido del comprimido (' . $total . ' elementos):</p>'
                . self::tabla($filas),
        ];
    }

    private static function peso(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        foreach (['KB', 'MB', 'GB'] as $unidad) {
            $bytes /= 1024;

            if ($bytes < 1024) {
                return round($bytes, 1) . ' ' . $unidad;
            }
        }

        return round($bytes, 1) . ' TB';
    }

    private static function noSoportado(string $extension): array
    {
        return [
            'modo'  => 'no',
            'aviso' => 'Este tipo de archivo (.' . $extension . ') no se puede previsualizar. '
                . 'Descargalo para abrirlo con su programa.',
        ];
    }

    /** Saca un archivo concreto de dentro de un zip (docx, xlsx y pptx lo son). */
    private static function leerDelZip(string $ruta, string $interno): ?string
    {
        $zip = new ZipArchive();

        if ($zip->open($ruta) !== true) {
            return null;
        }

        $contenido = $zip->getFromName($interno);
        $zip->close();

        return $contenido === false ? null : $contenido;
    }
}

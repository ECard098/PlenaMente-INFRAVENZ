<?php
namespace App\Libraries;

/**
 * Graficos: generador de graficos SVG nativos para el panel de metricas.
 * No requiere librerias externas: produce SVG accesible (con <title> en cada
 * elemento), responsivo (viewBox) e imprimible.
 */
class Graficos {

    /**
     * Grafico de barras verticales agrupadas (una o mas series por categoria).
     * @param array $etiquetas Etiquetas de cada categoria (eje X)
     * @param array $series [['nombre'=>'Citas', 'color'=>'#7E57C2', 'valores'=>[...]], ...]
     * @param array $opciones ['ancho'=>680, 'alto'=>250, 'titulo'=>'...']
     */
    public static function barrasVerticales(array $etiquetas, array $series, array $opciones = []) {
        $ancho = (int)($opciones['ancho'] ?? 680);
        $alto = (int)($opciones['alto'] ?? 250);
        $margenIzq = 44;
        $margenDer = 10;
        $margenSup = 16;
        $margenInf = 36;
        $anchoUtil = $ancho - $margenIzq - $margenDer;
        $altoUtil = $alto - $margenSup - $margenInf;

        // Maximo real de los datos
        $maximoReal = 0;
        foreach ($series as $serie) {
            foreach ($serie['valores'] as $valor) {
                $maximoReal = max($maximoReal, (int)$valor);
            }
        }
        $maximo = self::escalarMaximo($maximoReal);

        $categorias = count($etiquetas);
        $cantidadSeries = max(1, count($series));
        $anchoGrupo = $categorias > 0 ? $anchoUtil / $categorias : $anchoUtil;
        $separacion = 3;
        $anchoBarra = max(3, ($anchoGrupo * 0.7) / $cantidadSeries - $separacion);
        $offsetGrupo = ($anchoGrupo - ($anchoBarra + $separacion) * $cantidadSeries + $separacion) / 2;

        $svg = [];
        $svg[] = '<svg class="grafico-svg" viewBox="0 0 ' . $ancho . ' ' . $alto . '" role="img" '
            . 'aria-label="' . self::escapar($opciones['titulo'] ?? 'Grafico de barras') . '" '
            . 'preserveAspectRatio="xMidYMid meet" style="width:100%;height:auto;">';

        // Fondo del area de trazado
        $svg[] = '<rect x="' . $margenIzq . '" y="' . $margenSup . '" width="' . $anchoUtil . '" height="' . $altoUtil . '" fill="#FAFAFA" rx="4"/>';

        // Lineas de referencia con su valor
        $lineas = 4;
        for ($i = 0; $i <= $lineas; $i++) {
            $valor = $maximo * $i / $lineas;
            $y = $margenSup + $altoUtil - ($altoUtil * $i / $lineas);
            $svg[] = '<line x1="' . $margenIzq . '" y1="' . round($y, 2) . '" x2="' . ($ancho - $margenDer) . '" y2="' . round($y, 2) . '" stroke="#E0E0E0" stroke-width="1"/>';
            $svg[] = '<text x="' . ($margenIzq - 7) . '" y="' . round($y + 4, 2) . '" text-anchor="end" font-size="10" fill="#757575">' . (int)round($valor) . '</text>';
        }

        // Barras
        foreach ($series as $indiceSerie => $serie) {
            $color = $serie['color'] ?? '#7E57C2';
            $nombreSerie = $serie['nombre'] ?? '';
            foreach ($etiquetas as $indiceCategoria => $etiqueta) {
                $valor = (int)($serie['valores'][$indiceCategoria] ?? 0);
                $altoBarra = $maximo > 0 ? ($valor / $maximo) * $altoUtil : 0;
                $x = $margenIzq + ($indiceCategoria * $anchoGrupo) + $offsetGrupo + ($indiceSerie * ($anchoBarra + $separacion));
                $y = $margenSup + $altoUtil - $altoBarra;

                if ($valor > 0 && $altoBarra > 0) {
                    $svg[] = '<rect x="' . round($x, 2) . '" y="' . round($y, 2) . '" width="' . round($anchoBarra, 2) . '" height="' . round($altoBarra, 2) . '" fill="' . self::escapar($color) . '" rx="2">'
                        . '<title>' . self::escapar($etiqueta . ' - ' . $nombreSerie . ': ' . $valor) . '</title>'
                        . '</rect>';
                }
            }
        }

        // Etiquetas del eje X
        foreach ($etiquetas as $indiceCategoria => $etiqueta) {
            $x = $margenIzq + ($indiceCategoria * $anchoGrupo) + ($anchoGrupo / 2);
            $svg[] = '<text x="' . round($x, 2) . '" y="' . ($margenSup + $altoUtil + 16) . '" text-anchor="middle" font-size="10" fill="#616161">' . self::escapar($etiqueta) . '</text>';
        }

        // Linea base
        $svg[] = '<line x1="' . $margenIzq . '" y1="' . ($margenSup + $altoUtil) . '" x2="' . ($ancho - $margenDer) . '" y2="' . ($margenSup + $altoUtil) . '" stroke="#9E9E9E" stroke-width="1"/>';
        $svg[] = '</svg>';

        return implode('', $svg);
    }

    /**
     * Grafico de barras horizontales (una serie categorica).
     * @param array $items [['etiqueta'=>'Activo', 'total'=>12, 'color'=>'#28a745'], ...]
     * @param array $opciones ['ancho'=>460, 'alto'=>180, 'margen_izq'=>120, 'titulo'=>'...']
     */
    public static function barrasHorizontales(array $items, array $opciones = []) {
        $ancho = (int)($opciones['ancho'] ?? 460);
        $alto = (int)($opciones['alto'] ?? 180);
        $margenIzq = (int)($opciones['margen_izq'] ?? 120);
        $margenDer = 56;
        $margenSup = 10;
        $margenInf = 8;
        $anchoUtil = $ancho - $margenIzq - $margenDer;
        $altoUtil = $alto - $margenSup - $margenInf;

        $maximo = 0;
        foreach ($items as $item) {
            $maximo = max($maximo, (int)($item['total'] ?? 0));
        }
        $maximo = $maximo > 0 ? $maximo : 1;

        $filas = max(1, count($items));
        $altoFila = $altoUtil / $filas;
        $altoBarra = max(10, min(26, $altoFila * 0.6));

        $svg = [];
        $svg[] = '<svg class="grafico-svg" viewBox="0 0 ' . $ancho . ' ' . $alto . '" role="img" '
            . 'aria-label="' . self::escapar($opciones['titulo'] ?? 'Grafico de barras horizontales') . '" '
            . 'preserveAspectRatio="xMidYMid meet" style="width:100%;height:auto;">';

        foreach ($items as $indice => $item) {
            $total = (int)($item['total'] ?? 0);
            $color = $item['color'] ?? '#7E57C2';
            $etiqueta = $item['etiqueta'] ?? '';
            $y = $margenSup + ($indice * $altoFila) + ($altoFila - $altoBarra) / 2;
            $anchoBarra = $maximo > 0 ? ($total / $maximo) * $anchoUtil : 0;

            $svg[] = '<text x="' . ($margenIzq - 10) . '" y="' . round($y + $altoBarra / 2 + 4, 2) . '" text-anchor="end" font-size="11" fill="#424242">' . self::escapar($etiqueta) . '</text>';
            $svg[] = '<rect x="' . $margenIzq . '" y="' . round($y, 2) . '" width="' . $anchoUtil . '" height="' . round($altoBarra, 2) . '" fill="#F5F5F5" rx="3"/>';

            if ($total > 0 && $anchoBarra > 0) {
                $svg[] = '<rect x="' . $margenIzq . '" y="' . round($y, 2) . '" width="' . round($anchoBarra, 2) . '" height="' . round($altoBarra, 2) . '" fill="' . self::escapar($color) . '" rx="3">'
                    . '<title>' . self::escapar($etiqueta . ': ' . $total) . '</title>'
                    . '</rect>';
            }

            $svg[] = '<text x="' . ($margenIzq + $anchoUtil + 8) . '" y="' . round($y + $altoBarra / 2 + 4, 2) . '" font-size="11" font-weight="bold" fill="#424242">' . $total . '</text>';
        }

        $svg[] = '</svg>';
        return implode('', $svg);
    }

    private static function escalarMaximo($maximo) {
        if ($maximo <= 0) {
            return 4;
        }
        $magnitud = pow(10, floor(log10($maximo)));
        $normalizado = $maximo / $magnitud;

        if ($normalizado <= 1) {
            $escalado = 1;
        } elseif ($normalizado <= 2) {
            $escalado = 2;
        } elseif ($normalizado <= 2.5) {
            $escalado = 2.5;
        } elseif ($normalizado <= 5) {
            $escalado = 5;
        } else {
            $escalado = 10;
        }

        return (int)ceil($escalado * $magnitud);
    }

    private static function escapar($texto) {
        return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    }
}
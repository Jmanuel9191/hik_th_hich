<?php

/**
 * Proyecto de ejemplo "Puerta del Sol" (lo carga m261120_020000_ejemplo_puerta_del_sol, en borrador).
 * Contenido de la referencia de diseño. Fotos en privado/proyectos/900/ (imagenes y bloques): cada foto se usa una sola vez.
 * Versión 2 (Paq B): tablas, gráfico, minerales, línea de tiempo, proceso, tarjetas… con sus bloques propios.
 * m261120_040000_puerta_del_sol_v2 rehace la página del proyecto 900 con estas secciones.
 *
 * Sección: antetitulo, titulo, menu, ancla, fondo, diseno, en_menu, numerada, bloques
 * Bloque:  [tipo, ancho, datos]
 */

$foto = fn(string $archivo, string $alt, string $pie, string $formato = 'original', bool $mapa = false) =>
    ['imagen' => $archivo, 'alt' => $alt, 'pie' => $pie, 'formato' => $formato, 'mapa' => $mapa];

return [
    'proyecto' => [
        'titulo' => 'Proyecto Minero Puerta del Sol S.R.L.',
        'slug' => 'puerta-del-sol',
        'tipo' => 'financiamiento',
        'ecosistema' => 'mineria-y-recursos-naturales',
        'pais' => 'Bolivia',
        'ciudad' => 'La Paz',
        'ubicacion' => 'Cuencas de Vilaque y Kokota, Cordillera Real',
        'etapa' => 'prefactibilidad',
        'porcentaje_avance' => 15,
        'resumen' => 'Siete concesiones en las cuencas de Vilaque y Kokota, con presencia reportada de oro, bismuto, estaño y wolframio. Fase I concluida en agosto de 2026; Fases II y III de validación técnica en preparación para un socio estratégico.',
        'rubro' => 'Proyecto minero',
        'bajada' => 'Exploración para la prospección y producción de las concesiones mineras. La Paz – Bolivia. Documento complementario al Perfil Ejecutivo de Inversión, noviembre de 2026.',
        'sello' => 'CONFIDENCIAL – PARA DISCUSIÓN Y EVALUACIÓN',
        'elementos' => [
            ['numero' => '79', 'simbolo' => 'Au', 'nombre' => 'Oro'],
            ['numero' => '83', 'simbolo' => 'Bi', 'nombre' => 'Bismuto'],
            ['numero' => '50', 'simbolo' => 'Sn', 'nombre' => 'Estaño'],
            ['numero' => '74', 'simbolo' => 'W', 'nombre' => 'Wolframio'],
        ],
        'cifras' => [
            ['valor' => '7', 'texto' => 'Concesiones mineras', 'contar' => true],
            ['valor' => '2', 'texto' => 'Sectores de trabajo', 'contar' => true],
            ['valor' => '2', 'texto' => 'Cuencas: Vilaque y Kokota', 'contar' => true],
            ['valor' => 'Ago 2026', 'texto' => 'Fase I concluida', 'contar' => false],
        ],
        'destacado' => '1',
        'imagen_banner' => 'pds-banner.jpg',
        'foto_principal' => 'pds-tarjeta.jpg',
    ],
    'secciones' => [
        [
            'antetitulo' => 'Síntesis ejecutiva', 'titulo' => 'El proyecto en una página', 'menu' => 'Síntesis', 'ancla' => 'sintesis', 'fondo' => 'blanco', 'diseno' => 'columna',
            'bloques' => [
                ['texto', 'izquierda', ['texto' => '<p>Puerta del Sol S.R.L. es una iniciativa minera ubicada en el entorno de las cuencas de Vilaque y Kokota, al noroeste de las ciudades de La Paz y El Alto, en el departamento de La Paz, Bolivia. El activo comprende siete concesiones mineras y se inserta en un contexto geológico andino asociado regionalmente al distrito Milluni–Vilaque, conocido por antecedentes de mineralización de oro, bismuto, estaño y wolframio.</p><p><strong>Puerta del Sol S.R.L. fue absorbida por HUK–THE–ICHI con el objetivo de capitalizar e industrializar la producción minera.</strong></p><p>La prospección Fase I realizada por HUK–THE–ICHI, concluida en agosto de 2026, identificó depósitos secundarios aluviales y glaciares/morrénicos, paleocanales, llanuras, terrazas y abanicos, y reportó la presencia de Au, Bi, Sn y W.</p><p>La recomendación técnica es avanzar mediante inversión escalonada: priorizar las llanuras y terrazas de las siete concesiones (las de mayor extensión son Marisa y Arbizu) para geofísica, muestreo volumétrico y pruebas metalúrgicas, y tomar decisiones de escalamiento cuando se obtengan datos verificados de volumen, ley, recuperación, costos, permisos y viabilidad económica.</p>']],
                ['recuadro', 'derecha', ['antetitulo' => 'Tesis de inversión', 'texto' => 'Un proyecto minero en etapa temprana con mineralización reportada, siete concesiones y blancos de exploración priorizados; la siguiente creación de valor depende de convertir indicios y antecedentes en datos técnicos independientes, reproducibles y económicamente demostrables.']],
                ['imagen', 'derecha', $foto('condoriri.jpg', 'Nevado Condoriri y laguna Tuni', 'Nevado Condoriri y laguna Tuni, Cordillera Real (La Paz).', '16-10')],
                ['nota', 'derecha', ['texto' => 'La Fase I no determina todavía espesores, leyes representativas, continuidad tridimensional, recursos ni reservas minerales.']],
            ],
        ],
        [
            'antetitulo' => 'Ubicación y contexto geológico regional', 'titulo' => 'Región Vilaque, Cordillera Real', 'menu' => 'Ubicación', 'ancla' => 'ubicacion', 'fondo' => 'gris', 'diseno' => 'columna',
            'bloques' => [
                ['texto', 'derecha', ['texto' => '<p>El área se sitúa al noroeste de La Paz y El Alto, dentro de las cuencas de los ríos Vilaque y Kokota. La mayor parte de las concesiones se ubica en la cuenca del río Vilaque, con una porción menor vinculada a la cuenca del río Kokota. Procesos glaciares y fluviales erosionaron materiales de origen primario y redistribuyeron minerales pesados hacia depósitos secundarios: morrenas, till, paleocanales, terrazas y llanuras aluviales.</p><p>Para el inversor, la ubicación ofrece proximidad a centros urbanos y servicios de La Paz y El Alto, antecedentes geológicos y mineros regionales, y sistemas fluviales y glaciares que permiten definir objetivos de exploración diferenciados.</p>']],
                ['datos', 'derecha', ['datos' => [
                    ['rotulo' => 'Región', 'valor' => 'Vilaque'],
                    ['rotulo' => 'Cuencas', 'valor' => 'Ríos Vilaque y Kokota'],
                    ['rotulo' => 'Contexto', 'valor' => 'Cordillera Real · Milluni–Vilaque'],
                ]]],
                ['tarjetas_foto', 'completo', ['por_fila' => '3', 'tarjetas' => [
                    ['imagen' => 'terrazas.jpg', 'alt' => 'Terrazas fluviales escalonadas', 'numero' => '', 'titulo' => 'Depósitos aluviales', 'texto' => 'Llanuras, terrazas y paleocanales, objetivos prioritarios por su potencial de concentración hidráulica de minerales pesados.', 'pie' => 'Imagen de referencia: terrazas fluviales (Fish River, Namibia).'],
                    ['imagen' => 'abanico.jpg', 'alt' => 'Abanico aluvial al pie de un cerro', 'numero' => '', 'titulo' => 'Abanicos aluviales', 'texto' => 'Ambientes de transición cuya geometría, granulometría y distribución mineral requieren caracterización.', 'pie' => 'Imagen de referencia: abanico aluvial (Ladakh, India).'],
                    ['imagen' => 'valle-glaciar.jpg', 'alt' => 'Valle de origen glaciar', 'numero' => '', 'titulo' => 'Depósitos glaciares y morrénicos', 'texto' => 'Morrenas frontales, laterales y de retroceso, además de till; requieren muestreo de gran volumen y evaluación de compactación y continuidad.', 'pie' => 'Imagen de referencia: valle de origen glaciar.'],
                ]]],
                ['nota', 'completo', ['texto' => 'Cartografía de referencia: hojas 5944 La Paz y 5954 Milluni a escala 1:100.000, Servicio Geológico de Bolivia, y visitas de campo.']],
            ],
        ],
        [
            'antetitulo' => 'Activo minero', 'titulo' => 'Siete concesiones en dos sectores', 'menu' => 'Concesiones', 'ancla' => 'concesiones', 'fondo' => 'blanco', 'diseno' => 'columna',
            'bloques' => [
                ['tarjetas', 'izquierda', ['por_fila' => '2', 'estilo' => 'linea', 'tarjetas' => [
                    ['antetitulo' => 'Sector 1 · 6 concesiones', 'titulo' => 'Olivia, Estrella de Oro, Demasías Arbizu, Arbizu, Marisa y Piera', 'texto' => 'Objetivos aluviales, terrazas y depósitos glaciares.', 'negra' => false],
                    ['antetitulo' => 'Sector 2 · 1 concesión', 'titulo' => 'Milano', 'texto' => 'Abanicos y depósitos aluviales; el potencial identificado está en el lecho del río. Requiere evaluación específica de ocupación, uso del suelo y accesos.', 'negra' => true],
                ]]],
                ['nota', 'izquierda', ['texto' => 'La situación legal, vigencia, titularidad, cargas, coordenadas, superposiciones y obligaciones de cada derecho minero deberán verificarse documentalmente ante las autoridades competentes antes del cierre de cualquier inversión.']],
                ['imagen', 'derecha', $foto('mapa-concesiones.jpg', 'Mapa de subcuencas, sectores de estudio y concesiones mineras', 'Figura 1. Ubicación de las subcuencas, sectores de estudio y concesiones mineras. Mapa original de la Fase I.', 'original', true)],
                ['tabla', 'completo', ['titulo' => '', 'tabla' => [
                    'columnas' => ['N.º', 'Concesión', 'Sector', 'Coordenadas', 'Mapa N.º', 'Escala nominal'],
                    'filas' => [
                        ['1', '**Piera**', 'Sector 1', '16 vértices', '04', '1 : 13.000'],
                        ['2', '**Olivia**', 'Sector 1', '4 vértices', '05', '1 : 3.000'],
                        ['3', '**Milano**', 'Sector 2', '6 vértices', '06', '1 : 8.000'],
                        ['4', '**Marisa** [*Blanco prioritario]', 'Sector 1', '14 vértices', '07', '1 : 16.000'],
                        ['5', '**Estrella de Oro**', 'Sector 1', '21 puntos', '08', '1 : 6.000'],
                        ['6', '**Demasías Arbizu**', 'Sector 1', '4 vértices', '09', '1 : 5.000'],
                        ['7', '**Arbizu** [*Blanco prioritario]', 'Sector 1', '16 vértices', '10', '1 : 15.000'],
                    ],
                ], 'nota' => 'Sistema de referencia: WGS 84 / UTM zona 19S · EPSG:32719 (cajetín de los mapas originales, julio de 2026).']],
            ],
        ],
        [
            'antetitulo' => 'Antecedentes y resultados', 'titulo' => 'Fase I concluida en agosto de 2026', 'menu' => 'Fase I', 'ancla' => 'fase-1', 'fondo' => 'gris', 'diseno' => 'columna',
            'bloques' => [
                ['introduccion', 'izquierda', ['texto' => 'Exploración, caracterización geológica y geofísica, identificación de minerales e identificación de concesiones.']],
                ['imagen', 'izquierda', $foto('trinchera.jpg', 'Registro de campo en una trinchera de exploración', 'Imagen de referencia: registro de campo en una trinchera de exploración (NPS, EE. UU.).', '16-10')],
                ['lista_numerada', 'derecha', ['titulo' => 'Trabajo ejecutado', 'estilo' => 'simple', 'inicio' => '1', 'items' => [
                    ['destacado' => '', 'texto' => 'Recopilación de información geológica, minera e histórica disponible.'],
                    ['destacado' => '', 'texto' => 'Interpretación de imágenes satelitales y análisis geológico y geomorfológico.'],
                    ['destacado' => '', 'texto' => 'Delimitación de subcuencas de aporte y reconocimiento de ambientes de acumulación.'],
                    ['destacado' => '', 'texto' => 'Identificación de áreas intervenidas y sectores aparentemente no trabajados.'],
                    ['destacado' => '', 'texto' => 'Reconocimiento de accesos y verificación de campo de morrenas, paleocanales, llanuras y terrazas.'],
                    ['destacado' => '', 'texto' => 'Delimitación de blancos de interés para orientar estudios geofísicos y muestreos posteriores.'],
                    ['destacado' => '', 'texto' => 'Visita de campo a las concesiones mineras.'],
                ]]],
                ['texto', 'completo', ['texto' => '<h3>Cuatro minerales reportados en depósitos secundarios</h3><p>La Fase I reporta oro, bismuto, estaño y wolframio, con observaciones de fragmentos de bismuto de tamaño significativo y partículas de oro grueso. Son indicios de mineralización: no sustituyen ensayos representativos ni permiten inferir una ley media.</p>']],
                ['minerales', 'completo', ['minerales' => [
                    ['imagen' => 'min-oro.jpg', 'numero' => '79', 'simbolo' => 'Au', 'nombre' => 'Oro', 'pie' => 'Pepita de oro nativo (Australia).'],
                    ['imagen' => 'min-bismuto.jpg', 'numero' => '83', 'simbolo' => 'Bi', 'nombre' => 'Bismuto', 'pie' => 'Cristal de bismuto (crecido en laboratorio).'],
                    ['imagen' => 'min-estano.jpg', 'numero' => '50', 'simbolo' => 'Sn', 'nombre' => 'Estaño', 'pie' => 'Casiterita (mineral de estaño).'],
                    ['imagen' => 'min-wolframio.jpg', 'numero' => '74', 'simbolo' => 'W', 'nombre' => 'Wolframio', 'pie' => 'Wolframita con cuarzo.'],
                ]]],
                ['grafico', 'izquierda', ['titulo' => 'Comercialización histórica de oro reportada · 2002–2007', 'sufijo' => 'kg', 'orientacion' => 'vertical', 'barras' => [
                    ['etiqueta' => '2002', 'valor' => '53'], ['etiqueta' => '2003', 'valor' => '128'], ['etiqueta' => '2004', 'valor' => '94'],
                    ['etiqueta' => '2005', 'valor' => '76'], ['etiqueta' => '2006', 'valor' => '104'], ['etiqueta' => '2007', 'valor' => '45'],
                ], 'total' => true, 'total_texto' => 'total acumulado reportado', 'nota' => 'Antecedentes históricos documentales atribuidos a Puerta del Sol S.R.L., sujetos a verificación con respaldos comerciales, contables y regulatorios. Producción totalmente artesanal; no representan producción actual ni permiten estimar recursos o reservas.']],
                ['imagen', 'derecha', $foto('oro-placer.jpg', 'Pepitas de oro de placer de origen fluvial', 'Imagen de referencia: pepitas de oro de placer de origen fluvial (Washington, EE. UU.).', '4-3')],
                ['texto', 'completo', ['texto' => '<h3>Qué se tiene y qué falta validar</h3>']],
                ['tarjetas', 'completo', ['por_fila' => '3', 'estilo' => 'borde', 'tarjetas' => [
                    ['antetitulo' => 'Indicios de mineralización', 'titulo' => 'Evidencia reportada', 'texto' => "- Presencia de Au, Bi, Sn y W en depósitos secundarios.\n- Fragmentos de bismuto de tamaño significativo.\n- Partículas de oro grueso.\n- Depósitos aluviales y glaciares/morrénicos, paleocanales, llanuras, terrazas y abanicos.", 'negra' => false],
                    ['antetitulo' => 'Por verificar con respaldos', 'titulo' => 'Antecedentes históricos', 'texto' => "- Comercialización histórica de oro atribuida a Puerta del Sol S.R.L., 2002–2007.\n- Total acumulado reportado de 500 kg.\n- Producción a la fecha totalmente artesanal.", 'negra' => false],
                    ['antetitulo' => 'Objeto de las Fases II y III', 'titulo' => 'Aún por validar', 'texto' => "- Espesor y continuidad lateral y vertical.\n- Ley media y tonelaje.\n- Recuperación metalúrgica.\n- Costos de producción.\n- Recursos, reservas, vida útil de mina y rentabilidad.", 'negra' => true],
                ]]],
                ['nota', 'completo', ['texto' => 'Los escenarios modelados por HUK–THE–ICHI sobre la base de la Fase I son solamente conceptuales y serán reemplazados por datos de exploración y prospección validados. Existe además un potencial a validar sobre el manejo de áridos en las concesiones.']],
            ],
        ],
        [
            'antetitulo' => 'Objetivos de exploración', 'titulo' => 'Dos blancos prioritarios: Marisa y Arbizu', 'menu' => 'Objetivos', 'ancla' => 'objetivos', 'fondo' => 'blanco', 'diseno' => 'arriba',
            'bloques' => [
                ['ficha_destacada', 'completo', ['antetitulo' => 'Blanco prioritario', 'titulo' => 'Marisa', 'cifras' => [
                    ['valor' => '3', 'texto' => 'cursos fluviales'],
                    ['valor' => '≈ 1 km', 'texto' => 'de llanura y terraza aluvial cada uno'],
                    ['valor' => '≈ 50%', 'texto' => 'del terreno con trabajo artesanal superficial'],
                ], 'subtitulo' => 'Tres blancos de investigación iniciales', 'texto' => '<p>La Fase I identifica en Marisa tres cursos fluviales con aproximadamente un kilómetro de llanura y terraza aluvial cada uno. Aproximadamente el 50% del terreno descrito presenta trabajos superficiales de carácter artesanal; esta condición deberá verificarse con levantamiento topográfico, inspección de campo, revisión de antecedentes y delimitación de áreas efectivamente disponibles.</p>']],
                ['ficha_destacada', 'completo', ['antetitulo' => 'Blanco prioritario', 'titulo' => 'Arbizu', 'cifras' => [
                    ['valor' => '> 500 m', 'texto' => 'de tramo fluvial señalado como no trabajado'],
                    ['valor' => 'Mayor', 'texto' => 'extensión disponible de terrazas y llanuras aluviales'],
                    ['valor' => 'Prioritario', 'texto' => 'para definir geometría, espesor y distribución'],
                ], 'subtitulo' => 'La mayor extensión aluvial del conjunto', 'texto' => '<p>Arbizu presenta más de 500 metros de tramo fluvial señalado como no trabajado y la mayor extensión disponible de terrazas y llanuras aluviales dentro del conjunto, según la interpretación de Fase I. Es un objetivo prioritario, siempre sujeto a verificación de accesos, situación social y autorizaciones.</p><p><strong>Resto del portafolio.</strong> Piera, Olivia, Estrella de Oro, Demasías Arbizu y Milano se incorporarán progresivamente según la evidencia de las primeras campañas, ponderando continuidad, espesor, leyes, granulometría, recuperación, restricciones ambientales, acceso y costos.</p>']],
                ['imagen', 'izquierda', $foto('mapa-arbizu-marisa.jpg', 'Límites de las concesiones Arbizu y Marisa con la vía de acceso', 'Figura 4. Límite de las concesiones Arbizu (magenta) y Marisa (naranja) en relación a la vía de acceso (amarillo).', 'original', true)],
                ['tabla', 'derecha', ['titulo' => 'Criterio de priorización sugerido', 'tabla' => [
                    'columnas' => ['Prioridad', 'Ambiente / concesión', 'Decisión requerida'],
                    'filas' => [
                        ['[*1]', '**Llanuras y terrazas – Marisa y Arbizu**', 'Geofísica, muestreo volumétrico y pruebas iniciales.'],
                        ['[2]', '**Otros depósitos aluviales y abanicos**', 'Ampliar si los resultados iniciales justifican continuidad.'],
                        ['[3]', '**Morrenas y till**', 'Caracterizar granulometría, compactación, espesor y mineralización.'],
                        ['[4]', '**Resto de concesiones**', 'Ordenar por evidencia técnica, riesgo, costo y factibilidad.'],
                    ],
                ], 'nota' => '']],
            ],
        ],
        [
            'antetitulo' => 'Programa técnico de validación', 'titulo' => 'Fases II y III, aprobadas por etapas', 'menu' => 'Fases II y III', 'ancla' => 'fases', 'fondo' => 'gris', 'diseno' => 'arriba',
            'bloques' => [
                ['introduccion', 'completo', ['texto' => 'La siguiente etapa se reestructura como un Programa de Preinversión y Due Diligence Técnico-Minero, con presupuesto por campaña, responsables definidos, protocolos de calidad y decisiones de continuidad vinculadas a resultados medibles.']],
                ['linea_tiempo', 'completo', ['pasos' => [
                    ['rotulo' => 'Concluida · Ago 2026', 'titulo' => 'Fase I', 'texto' => 'Exploración, caracterización geológica y geofísica, identificación de minerales y de concesiones.', 'estado' => 'hecho'],
                    ['rotulo' => 'Actividades 1 a 6', 'titulo' => 'Fase II', 'texto' => 'Estudio de prospección y cuantificación de reservas de los minerales identificados.', 'estado' => 'pendiente'],
                    ['rotulo' => 'Actividades 7 a 11', 'titulo' => 'Fase III', 'texto' => 'Exploración selectiva, metalurgia y modelo.', 'estado' => 'pendiente'],
                    ['rotulo' => 'Aprobación por etapas', 'titulo' => 'Decisión', 'texto' => 'Continuar, rediseñar o detener.', 'estado' => 'decision'],
                ]]],
                ['lista_numerada', 'izquierda', ['titulo' => 'Fase II · Prospección y cuantificación', 'estilo' => 'grande', 'inicio' => '1', 'items' => [
                    ['destacado' => 'Debida diligencia documental y legal:', 'texto' => 'verificar derechos mineros, titularidad, coordenadas, vigencia, obligaciones, accesos, servidumbres, acuerdos y restricciones.'],
                    ['destacado' => 'Estudio geológico regional y de detalle:', 'texto' => 'integrar cartografía, imágenes, antecedentes Milluni–Vilaque, geomorfología, drenajes, topografía y control estructural.'],
                    ['destacado' => 'Levantamiento topográfico y geomorfológico', 'texto' => 'georreferenciado de Marisa y Arbizu, con delimitación de llanuras, terrazas, paleocanales y sectores intervenidos.'],
                    ['destacado' => 'Geofísica', 'texto' => 'apropiada a los objetivos y al terreno para estimar espesores, contactos y geometría de depósitos.'],
                    ['destacado' => 'Calicatas, trincheras y muestreo volumétrico sistemático,', 'texto' => 'considerando granulometría, distribución de oro grueso y variabilidad vertical y lateral.'],
                    ['destacado' => 'Ensayos mineralógicos y químicos', 'texto' => 'para Au, Bi, Sn y W con laboratorios competentes, duplicados, blancos, estándares, cadena de custodia y trazabilidad.'],
                ]]],
                ['lista_numerada', 'derecha', ['titulo' => 'Fase III · Exploración selectiva, metalurgia y modelo', 'estilo' => 'grande', 'inicio' => '7', 'items' => [
                    ['destacado' => 'Perforación selectiva', 'texto' => 'únicamente donde la geofísica y el muestreo superficial lo justifiquen; definir diámetro, espaciamiento, recuperación y volumen de muestra.'],
                    ['destacado' => 'Pruebas metalúrgicas', 'texto' => 'por tipo de material: granulometría, liberación, concentración gravimétrica y caracterización de concentrados; Au, Bi, Sn y W por separado.'],
                    ['destacado' => 'Programa piloto controlado', 'texto' => 'para medir toneladas tratadas, leyes de cabeza, recuperación, pérdidas, calidad de concentrado, agua, energía, disponibilidad mecánica y costos.'],
                    ['destacado' => 'Modelo geológico tridimensional', 'texto' => 'y estimación de recursos por profesional calificado, cuando la densidad y calidad de datos lo permitan.'],
                    ['destacado' => 'Evaluación económica preliminar', 'texto' => 'y luego estudio técnico-económico de mayor detalle: CAPEX, OPEX, infraestructura, permisos, impuestos, logística, sensibilidad y escenarios.'],
                ]]],
                ['galeria', 'completo', ['por_fila' => '3', 'fotos' => [
                    ['imagen' => 'resistividad.jpg', 'alt' => 'Perfil de tomografía de resistividad eléctrica', 'pie' => 'Tomografía de resistividad eléctrica.'],
                    ['imagen' => 'batea.jpg', 'alt' => 'Muestreo de gravas aluviales con batea', 'pie' => 'Muestreo de gravas aluviales con batea.'],
                    ['imagen' => 'testigos.jpg', 'alt' => 'Testigos de perforación en cajas de archivo', 'pie' => 'Testigos de perforación.'],
                ]]],
                ['hitos', 'completo', ['antetitulo' => 'Disciplina de hitos', 'texto' => 'Cada campaña concluye con entregables verificables y una recomendación explícita.',
                    'etiquetas' => "Informe técnico\nBase de datos auditable\nConciliación de muestras\nInterpretación\nPresupuesto actualizado", 'opciones' => "CONTINUAR\nREDISEÑAR\nDETENER"]],
            ],
        ],
        [
            'antetitulo' => 'Concepto preliminar', 'titulo' => 'Procesamiento y operación', 'menu' => 'Procesamiento', 'ancla' => 'procesamiento', 'fondo' => 'blanco', 'diseno' => 'columna',
            'bloques' => [
                ['introduccion', 'derecha', ['texto' => 'Por tratarse de depósitos secundarios, la hipótesis inicial de beneficio considera clasificación granulométrica y concentración gravimétrica. Es una alternativa inicial de trabajo: el diseño de plantas definitivas, la selección de equipos y la capacidad deberán surgir de pruebas metalúrgicas representativas y pruebas piloto.']],
                ['proceso', 'completo', ['pasos' => [
                    ['grupo' => 'Clasificación granulométrica', 'titulo' => 'Excavación mecanizada', 'texto' => ''],
                    ['grupo' => 'Clasificación granulométrica', 'titulo' => 'Parrilla o grizzly', 'texto' => ''],
                    ['grupo' => 'Clasificación granulométrica', 'titulo' => 'Zarandeo', 'texto' => ''],
                    ['grupo' => 'Concentración gravimétrica', 'titulo' => 'Canaletas de concentración', 'texto' => ''],
                    ['grupo' => 'Concentración gravimétrica', 'titulo' => 'Separación final', 'texto' => 'Como mesas concentradoras.'],
                ]]],
                ['etiquetas', 'izquierda', ['titulo' => 'Parámetros a determinar antes del diseño definitivo', 'etiquetas' => "Liberación\nTamaño de partícula\nMinerales acompañantes\nRecuperación por fracción\nCalidad de concentrado\nManejo de colas\nRequerimientos de agua\nRequerimientos de energía"]],
                ['nota', 'izquierda', ['texto' => 'Cualquier compra industrial de gran escala considerará parámetros metalúrgicos y operativos comprobados.']],
                ['galeria', 'derecha', ['por_fila' => '2', 'fotos' => [
                    ['imagen' => 'mesas-estano.jpg', 'alt' => 'Mesas concentradoras en una planta de estaño', 'pie' => 'Mesas concentradoras (Geevor, Reino Unido).'],
                    ['imagen' => 'mesa-wilfley.jpg', 'alt' => 'Mesa Wilfley concentrando oro', 'pie' => 'Mesa Wilfley, ensayo gravimétrico.'],
                ]]],
            ],
        ],
        [
            'antetitulo' => 'Aspectos legales, ambientales y sociales', 'titulo' => 'Operar con permisos, cuidado y acuerdos', 'menu' => 'Legal y ambiental', 'ancla' => 'legal', 'fondo' => 'gris', 'diseno' => 'arriba',
            'bloques' => [
                ['tarjetas_foto', 'completo', ['por_fila' => '3', 'tarjetas' => [
                    ['imagen' => '', 'alt' => '', 'numero' => '8.1', 'titulo' => 'Seguridad jurídica y permisos', 'texto' => 'Antes de iniciar actividades de campo o explotación se verificará la situación jurídica de cada derecho minero, el alcance de las autorizaciones y el cumplimiento de la normativa boliviana. La licencia de prospección y exploración no debe confundirse con una autorización de explotación; cada fase contará con sus instrumentos ambientales y permisos sectoriales.', 'pie' => ''],
                    ['imagen' => 'bofedal.jpg', 'alt' => 'Bofedal altoandino', 'numero' => '8.2', 'titulo' => 'Gestión ambiental', 'texto' => 'Identificar cursos de agua, bofedales y ecosistemas sensibles; establecer línea base ambiental e hídrica; controlar sedimentos, combustibles, residuos y colas; recircular el agua y rehabilitar progresivamente las áreas intervenidas.', 'pie' => 'Bofedal altoandino: ecosistema sensible a identificar en la línea base.'],
                    ['imagen' => '', 'alt' => '', 'numero' => '8.3', 'titulo' => 'Relacionamiento comunitario y acceso', 'texto' => 'La Fase I registró actividad minera artesanal ilegal y controles improvisados en algunos accesos en agosto de 2026. La empresa contará con un plan de relacionamiento, mecanismo de quejas, acuerdos documentados y protocolos de seguridad y acceso, respetando la normativa y los derechos de terceros.', 'pie' => ''],
                ]]],
            ],
        ],
        [
            'antetitulo' => 'Oportunidad para el socio estratégico', 'titulo' => 'Una asociación de largo plazo, por etapas', 'menu' => 'Socio estratégico', 'ancla' => 'socio', 'fondo' => 'negro', 'diseno' => 'columna',
            'bloques' => [
                ['introduccion', 'derecha', ['texto' => 'Un socio estratégico puede participar en una plataforma minera con siete concesiones y objetivos polimetálicos en una región históricamente minera. HUK–THE–ICHI plantea una asociación de largo plazo, con desarrollo por etapas y preservación de una participación accionaria significativa, conforme al Perfil Ejecutivo de Inversión y los acuerdos definitivos.']],
                ['dos_aportes', 'completo', [
                    'a_titulo' => 'Aporta Puerta del Sol · HUK–THE–ICHI', 'a_items' => "Plataforma minera con siete concesiones\nObjetivos polimetálicos: Au, Bi, Sn y W\nRegión históricamente minera\nFase I concluida y blancos priorizados",
                    'b_titulo' => 'Aporta el socio estratégico', 'b_items' => "Experiencia técnica\nCapacidad financiera\nSistemas de gobernanza\nEstándares de reporte\nAcceso a mercados",
                ]],
                ['texto', 'completo', ['texto' => '<h3>Financiamiento por hitos</h3><p>Desembolsos contra programas, presupuestos, entregables y resultados auditables.</p>']],
                ['tarjetas', 'completo', ['por_fila' => '4', 'estilo' => 'borde', 'tarjetas' => [
                    ['antetitulo' => '', 'titulo' => 'Due diligence integral', 'texto' => 'Legal, societaria, técnica, ambiental, social, tributaria y financiera.', 'negra' => false],
                    ['antetitulo' => '', 'titulo' => 'Fase II', 'texto' => 'Estudio de prospección y cuantificación de reservas.', 'negra' => false],
                    ['antetitulo' => '', 'titulo' => 'Fase III', 'texto' => 'Exploración selectiva, metalurgia y modelo.', 'negra' => false],
                    ['antetitulo' => '', 'titulo' => 'Decisión de escalamiento', 'texto' => 'Inversión industrial condicionada a resultados satisfactorios.', 'negra' => true],
                ]]],
                ['tarjetas', 'completo', ['por_fila' => '3', 'estilo' => 'linea', 'tarjetas' => [
                    ['antetitulo' => '', 'titulo' => 'Valoración independiente', 'texto' => 'Una vez validados los datos, asignar valor económico a los recursos o reservas que lleguen a declararse conforme a los estándares aplicables.', 'negra' => false],
                    ['antetitulo' => '', 'titulo' => 'Financiamiento por hitos', 'texto' => 'Desembolsos contra programas, presupuestos, entregables y resultados auditables.', 'negra' => false],
                    ['antetitulo' => '', 'titulo' => 'Gobierno corporativo', 'texto' => 'Derechos de información, auditoría, decisiones reservadas, control presupuestario y resolución de controversias.', 'negra' => false],
                    ['antetitulo' => '', 'titulo' => 'Transparencia técnica', 'texto' => 'Data room geológico, protocolos QA/QC, cadena de custodia y reportes periódicos revisables por especialistas independientes.', 'negra' => false],
                    ['antetitulo' => '', 'titulo' => 'Decisión de escalamiento', 'texto' => 'Condicionar la inversión industrial a resultados metalúrgicos, ambientales, regulatorios y económicos satisfactorios.', 'negra' => false],
                ]]],
                ['sello', 'completo', ['rotulo' => 'NI 43-101 · CIM', 'texto' => 'Los estudios de las Fases I, II y III deben acompañarse de un reporte técnico NI 43-101 de recursos o reservas, firmado por una Persona Calificada (Qualified Person), observando los estándares de definición CIM cuando corresponda.']],
            ],
        ],
        [
            'antetitulo' => 'Riesgos principales', 'titulo' => 'Riesgos y medidas de gestión', 'menu' => 'Riesgos', 'ancla' => 'riesgos', 'fondo' => 'blanco', 'diseno' => 'arriba',
            'bloques' => [
                ['tabla', 'completo', ['titulo' => '', 'tabla' => [
                    'columnas' => ['#', 'Ámbito', 'Riesgo / incertidumbre', 'Medida de gestión propuesta'],
                    'filas' => [
                        ['01', '[Geológico]', '**Espesor, continuidad y volumen aún no determinados**', 'Geofísica, calicatas, muestreo volumétrico y modelo tridimensional.'],
                        ['02', '[Geológico]', '**Variabilidad de leyes y efecto de oro grueso**', 'Muestras de volumen adecuado, protocolo QA/QC, duplicados y ensayos de laboratorio.'],
                        ['03', '[Metalúrgico]', '**Recuperación de Au, Bi, Sn y W no demostrada**', 'Mineralogía, pruebas metalúrgicas y piloto por tipo de material.'],
                        ['04', '[Legal]', '**Situación legal y vigencia de derechos por verificar**', 'Auditoría legal minera y registral independiente antes del cierre.'],
                        ['05', '[Social]', '**Accesos, actividad de terceros y relacionamiento social**', 'Mapeo de actores, acuerdos, plan de acceso, seguridad y mecanismo de quejas.'],
                        ['06', '[Ambiental]', '**Agua, bofedales y sensibilidad ambiental**', 'Línea base, permisos, diseño de manejo hídrico y rehabilitación progresiva.'],
                        ['07', '[Económico]', '**CAPEX, OPEX y rentabilidad no establecidos**', 'Ingeniería por etapas, cotizaciones, piloto y modelo económico con sensibilidades.'],
                        ['08', '[*Complementario]', '**Potencial de áridos e industria de procesamiento de residuos aún no validado**', 'Evaluar como ingresos complementarios en las siete concesiones y como industria para procesar residuos de terceros y minerales, sujeto a estudios específicos.'],
                    ],
                ], 'nota' => 'Ámbito: agrupación visual de lectura, sin valoración de probabilidad ni impacto.']],
            ],
        ],
        [
            'antetitulo' => 'Conclusión ejecutiva', 'titulo' => 'Primero validar, luego demostrar y finalmente escalar', 'menu' => 'Conclusión', 'ancla' => 'conclusion', 'fondo' => 'negro', 'diseno' => 'columna',
            'bloques' => [
                ['introduccion', 'derecha', ['texto' => "Puerta del Sol S.R.L. reúne elementos que justifican continuar con una evaluación minera estructurada: siete concesiones en el entorno Vilaque–Kokota, contexto geológico favorable, ocurrencia reportada de Au, Bi, Sn y W, antecedentes de comercialización de oro, diversidad de depósitos secundarios y objetivos iniciales en Marisa y Arbizu.\n\nCon un socio que aporte conocimiento minero, financiamiento y gobernanza, HUK–THE–ICHI propone transformar el potencial geológico documentado en un proyecto técnicamente caracterizado, metalúrgicamente probado y económicamente evaluable."]],
                ['pasos_finales', 'completo', ['pasos' => [
                    ['titulo' => 'Validar', 'texto' => 'Ejecutar los estudios de las Fases II y III con estándares de calidad y validación independiente.'],
                    ['titulo' => 'Demostrar', 'texto' => 'Proyecto técnicamente caracterizado, metalúrgicamente probado y económicamente evaluable.'],
                    ['titulo' => 'Escalar', 'texto' => 'La inversión debe avanzar al ritmo de la evidencia.'],
                ]]],
                ['botones', 'completo', ['botones' => [
                    ['texto' => 'Solicitar el Perfil Ejecutivo', 'enlace' => '/contacto', 'estilo' => 'dorado'],
                    ['texto' => 'Volver a Minería y Recursos Naturales', 'enlace' => '/ecosistemas/mineria-y-recursos-naturales', 'estilo' => 'contorno'],
                ]]],
            ],
        ],
        require __DIR__ . '/seccion_galeria.php',
        [
            'antetitulo' => null, 'titulo' => null, 'menu' => 'Aviso', 'ancla' => 'aviso', 'fondo' => 'blanco', 'diseno' => 'arriba', 'en_menu' => false, 'numerada' => false,
            'bloques' => [
                ['aviso', 'completo', ['titulo' => 'Aviso importante', 'texto' => 'Este contenido es de carácter informativo y preliminar, elaborado para facilitar la evaluación de un potencial socio estratégico. Las referencias a ocurrencias minerales, antecedentes históricos y blancos de exploración se basan en la documentación citada y están sujetas a verificación. No constituye oferta pública de valores, recomendación de inversión, informe técnico independiente, estimación de recursos o reservas, estudio de factibilidad ni garantía de resultados.']],
                ['fuentes', 'completo', ['titulo' => 'Fuentes documentales', 'fuentes' => [
                    ['texto' => 'HUK–THE–ICHI. "Información Básica Puerta del Sol S.R.L.", octubre de 2026.', 'enlace' => ''],
                    ['texto' => 'HUK–THE–ICHI / Puerta del Sol S.R.L. "Prospección y Exploración de las Concesiones Mineras – Fase I", agosto de 2026.', 'enlace' => ''],
                    ['texto' => 'HUK–THE–ICHI / Puerta del Sol S.R.L. "Perfil Ejecutivo de Inversión – Proyecto Minero Puerta del Sol S.R.L. Propuesta para Socio Estratégico", octubre de 2026.', 'enlace' => ''],
                    ['texto' => 'Referencias para la debida diligencia: SERGEOMIN (idesig.sergeomin.gob.bo), Ley N.º 535 de Minería y Metalurgia, Reglamento Ambiental para Actividades Mineras (RAAM), CIM Definition Standards y NI 43-101.', 'enlace' => ''],
                ]]],
            ],
        ],
    ],
];

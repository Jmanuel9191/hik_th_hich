<?php

/**
 * Plantillas iniciales de la página de proyecto (las carga m261120_010000_pagina_proyecto).
 * Después se editan desde Panel → Proyectos → Plantillas. Los textos entre « » son guías para quien
 * carga el proyecto: mientras quede alguno, el proyecto no se puede publicar.
 *
 * Sección: [antetitulo, titulo, menu, ancla, fondo, diseno, en_menu, numerada, bloques]
 * Bloque:  [tipo, ancho, datos]  (ancho: completo | izquierda | derecha)
 */

$texto = fn(string $guia) => ['texto', 'completo', ['texto' => '<p>«' . $guia . '»</p>']];

/** Sección numerada con un texto guía. */
$s = function (string $ante, string $titulo, string $menu, string $ancla, string $fondo, string $guia, string $diseno = 'arriba') use ($texto) {
    $b = $texto($guia);
    if ($diseno === 'columna') {
        $b[1] = 'derecha';
    }
    return ['antetitulo' => $ante, 'titulo' => $titulo, 'menu' => $menu, 'ancla' => $ancla, 'fondo' => $fondo, 'diseno' => $diseno, 'bloques' => [$b]];
};

/** Síntesis: texto a la izquierda (con el título) y la tesis de inversión a la derecha. */
$sintesis = fn(string $guia) => [
    'antetitulo' => 'Síntesis ejecutiva', 'titulo' => 'El proyecto en una página', 'menu' => 'Síntesis', 'ancla' => 'sintesis',
    'fondo' => 'blanco', 'diseno' => 'columna', 'bloques' => [
        ['texto', 'izquierda', ['texto' => '<p>«' . $guia . '»</p>']],
        ['recuadro', 'derecha', ['antetitulo' => 'Tesis de inversión', 'texto' => '«En una o dos frases: por qué este proyecto es una buena oportunidad.»']],
    ],
];

$riesgos = fn(string $fondo = 'blanco') => ['antetitulo' => 'Riesgos principales', 'titulo' => 'Riesgos y medidas de gestión', 'menu' => 'Riesgos', 'ancla' => 'riesgos', 'fondo' => $fondo, 'diseno' => 'arriba', 'bloques' => [
    ['texto', 'completo', ['texto' => '<ul><li>«Riesgo 1: qué puede pasar y cómo se gestiona.»</li><li>«Riesgo 2…»</li></ul>']],
]];

$conclusion = ['antetitulo' => 'Conclusión ejecutiva', 'titulo' => '«Frase de cierre del proyecto»', 'menu' => 'Conclusión', 'ancla' => 'conclusion', 'fondo' => 'negro', 'diseno' => 'columna', 'bloques' => [
    ['introduccion', 'derecha', ['texto' => '«Resumen final: qué se propone y qué se le pide al inversor o socio.»']],
]];

/** Galería y documentos (fotos, videos y documentos de las pestañas del proyecto); después, aviso legal y fuentes: sin número y fuera del menú. */
$cierre = [
    require __DIR__ . '/seccion_galeria.php',
    ['antetitulo' => null, 'titulo' => null, 'menu' => 'Aviso', 'ancla' => 'aviso', 'fondo' => 'blanco', 'diseno' => 'arriba', 'en_menu' => false, 'numerada' => false, 'bloques' => [
        ['aviso', 'completo', ['titulo' => 'Aviso importante', 'texto' => 'Este contenido es de carácter informativo y preliminar, elaborado para facilitar la evaluación de un potencial socio o inversor. No constituye oferta pública de valores, recomendación de inversión, informe técnico independiente ni garantía de resultados.']],
        ['fuentes', 'completo', ['titulo' => 'Fuentes documentales', 'fuentes' => [['texto' => '«Documento, autor y fecha.»', 'enlace' => '']]]],
    ]],
];

$plantilla = fn(string $nombre, ?string $eco, string $desc, array $secciones) => [
    'nombre' => $nombre, 'ecosistema' => $eco, 'descripcion' => $desc, 'secciones' => array_merge($secciones, $cierre),
];

return [
    $plantilla('Minero', 'mineria-y-recursos-naturales', 'Proyectos mineros y de recursos naturales (estructura de Puerta del Sol).', [
        $sintesis('Qué es el proyecto, dónde está, qué activos tiene y cuál es la recomendación técnica.'),
        $s('Ubicación y contexto geológico regional', '«Región o distrito»', 'Ubicación', 'ubicacion', 'gris', 'Ubicación, accesos, cuencas y contexto geológico.', 'columna'),
        $s('Activo minero', '«Concesiones y sectores»', 'Concesiones', 'concesiones', 'blanco', 'Concesiones, sectores y situación de cada derecho minero.'),
        $s('Antecedentes y resultados', '«Fase I»', 'Fase I', 'fase-1', 'gris', 'Trabajo realizado y resultados obtenidos.'),
        $s('Objetivos de exploración', '«Blancos prioritarios»', 'Objetivos', 'objetivos', 'blanco', 'Objetivos priorizados y criterios.'),
        $s('Programa técnico de validación', '«Fases siguientes»', 'Fases II y III', 'fases', 'gris', 'Actividades de las próximas fases y cómo se aprueban.'),
        $s('Concepto preliminar', 'Procesamiento y operación', 'Procesamiento', 'procesamiento', 'blanco', 'Cómo se procesaría el mineral y qué falta definir.', 'columna'),
        $s('Aspectos legales, ambientales y sociales', 'Operar con permisos, cuidado y acuerdos', 'Legal y ambiental', 'legal', 'gris', 'Permisos, gestión ambiental y relacionamiento comunitario.'),
        $s('Oportunidad para el socio estratégico', '«Qué se propone al socio»', 'Socio estratégico', 'socio', 'negro', 'Qué aporta cada parte y cómo se financia por hitos.', 'columna'),
        $riesgos(),
        $conclusion,
    ]),
    $plantilla('Energía', 'energia', 'Generación, transmisión, combustibles y transición energética.', [
        $sintesis('Qué es el proyecto, qué energía produce, dónde y en qué etapa está.'),
        $s('Ubicación', '«Sitio del proyecto»', 'Ubicación', 'ubicacion', 'gris', 'Ubicación, superficie, accesos y conexión a la red.', 'columna'),
        $s('Recurso y tecnología', '«Recurso disponible»', 'Tecnología', 'tecnologia', 'blanco', 'Recurso (sol, viento, agua, gas…), mediciones y tecnología elegida.'),
        $s('Capacidad', '«Potencia y producción»', 'Capacidad', 'capacidad', 'gris', 'Potencia instalada, producción anual estimada y factor de planta.'),
        $s('Mercado', '«Venta de la energía»', 'Mercado', 'mercado', 'blanco', 'A quién se vende, contratos (PPA) y precios de referencia.'),
        $s('Desarrollo', '«Fases del proyecto»', 'Fases', 'fases', 'gris', 'Estudios, permisos, construcción y puesta en marcha.'),
        $s('Aspectos legales y ambientales', 'Licencias y permisos', 'Legal y ambiental', 'legal', 'blanco', 'Licencias, estudios de impacto y relación con la comunidad.'),
        $s('Oportunidad para el inversor', '«Qué se propone»', 'Inversión', 'inversion', 'negro', 'Estructura de la inversión y qué aporta cada parte.', 'columna'),
        $riesgos('gris'),
        $conclusion,
    ]),
    $plantilla('Salud', 'salud', 'Infraestructura sanitaria, servicios médicos y ciencias de la vida.', [
        $sintesis('Qué es el proyecto, qué servicios ofrece, dónde y para quién.'),
        $s('Necesidad', '«La demanda que atiende»', 'Necesidad', 'necesidad', 'gris', 'Población objetivo, déficit actual y demanda proyectada.'),
        $s('Ubicación', '«Sitio y área de influencia»', 'Ubicación', 'ubicacion', 'blanco', 'Ubicación, accesos y área de influencia.', 'columna'),
        $s('Servicios y capacidad', '«Qué se va a ofrecer»', 'Servicios', 'servicios', 'gris', 'Especialidades, camas, consultorios y capacidad de atención.'),
        $s('Equipamiento y tecnología', '«Equipos principales»', 'Tecnología', 'tecnologia', 'blanco', 'Equipamiento médico y sistemas.'),
        $s('Operación', '«Equipo y gestión»', 'Operación', 'operacion', 'gris', 'Quién opera, equipo médico y modelo de gestión.'),
        $s('Desarrollo', '«Fases del proyecto»', 'Fases', 'fases', 'blanco', 'Diseño, obra, equipamiento y apertura.'),
        $s('Regulación', 'Habilitaciones y permisos', 'Regulación', 'regulacion', 'gris', 'Habilitaciones sanitarias, normas y acreditaciones.'),
        $riesgos(),
        $conclusion,
    ]),
    $plantilla('Infraestructura y logística', 'infraestructura-y-logistica', 'Obras, transporte, puertos, parques logísticos y concesiones.', [
        $sintesis('Qué obra es, dónde, qué resuelve y en qué etapa está.'),
        $s('Ubicación y conectividad', '«Corredor o sitio»', 'Ubicación', 'ubicacion', 'gris', 'Ubicación, conexiones y área de influencia.', 'columna'),
        $s('Alcance', '«Qué se construye»', 'Alcance', 'alcance', 'blanco', 'Componentes de la obra y dimensiones principales.'),
        $s('Demanda', '«Uso esperado»', 'Demanda', 'demanda', 'gris', 'Tráfico, carga o usuarios esperados y su crecimiento.'),
        $s('Ingeniería', '«Estado de los estudios»', 'Ingeniería', 'ingenieria', 'blanco', 'Estudios hechos y pendientes.'),
        $s('Desarrollo', '«Fases de construcción»', 'Fases', 'fases', 'gris', 'Etapas, plazos y entregables.'),
        $s('Modelo de negocio', '«Concesión o contrato»', 'Modelo', 'modelo', 'blanco', 'Cómo se repaga la inversión: peajes, tarifas, contrato.'),
        $s('Aspectos legales y ambientales', 'Permisos y licencias', 'Legal y ambiental', 'legal', 'gris', 'Permisos, licencias ambientales y derecho de vía.'),
        $riesgos(),
        $conclusion,
    ]),
    $plantilla('Agroindustria', 'agroindustria', 'Agricultura, forestal, alimentos y bioeconomía.', [
        $sintesis('Qué se produce, dónde, en qué escala y qué se busca.'),
        $s('Ubicación y tierras', '«Predio»', 'Ubicación', 'ubicacion', 'gris', 'Ubicación, superficie, suelos, agua y clima.', 'columna'),
        $s('Producción', '«Cultivos o productos»', 'Producción', 'produccion', 'blanco', 'Cultivos o productos, rendimientos y calendario.'),
        $s('Procesamiento', '«Agregado de valor»', 'Procesamiento', 'procesamiento', 'gris', 'Planta, procesos y productos finales.'),
        $s('Mercado', '«Comercialización»', 'Mercado', 'mercado', 'blanco', 'A quién se vende, precios y canales.'),
        $s('Desarrollo', '«Fases del proyecto»', 'Fases', 'fases', 'gris', 'Etapas de siembra, inversión y escalamiento.'),
        $s('Sostenibilidad', 'Ambiente y comunidad', 'Sostenibilidad', 'sostenibilidad', 'blanco', 'Prácticas ambientales, certificaciones y relación con la comunidad.'),
        $riesgos('gris'),
        $conclusion,
    ]),
    $plantilla('Industrial', 'industrial', 'Manufactura, maquila e industrialización de materias primas.', [
        $sintesis('Qué se fabrica, dónde, para qué mercado y en qué etapa está.'),
        $s('Ubicación', '«Planta»', 'Ubicación', 'ubicacion', 'gris', 'Terreno, servicios, accesos y logística.', 'columna'),
        $s('Producto y proceso', '«Qué se fabrica»', 'Proceso', 'proceso', 'blanco', 'Productos, proceso productivo y tecnología.'),
        $s('Capacidad', '«Capacidad de planta»', 'Capacidad', 'capacidad', 'gris', 'Capacidad instalada, turnos y producción anual.'),
        $s('Materia prima', '«Abastecimiento»', 'Materia prima', 'materia-prima', 'blanco', 'Origen, proveedores y costos de los insumos.'),
        $s('Mercado', '«Ventas»', 'Mercado', 'mercado', 'gris', 'Clientes, precios y participación esperada.'),
        $s('Desarrollo', '«Fases del proyecto»', 'Fases', 'fases', 'blanco', 'Montaje, puesta en marcha y escalamiento.'),
        $s('Aspectos legales y ambientales', 'Permisos y normas', 'Legal y ambiental', 'legal', 'gris', 'Licencias, normas técnicas y gestión ambiental.'),
        $riesgos(),
        $conclusion,
    ]),
    $plantilla('Financiero', 'financiero', 'Estructuración de capital, Project Finance y vehículos de inversión.', [
        $sintesis('Qué vehículo o estructura se propone, para qué y con qué respaldo.'),
        $s('Estructura', '«Cómo está armado»', 'Estructura', 'estructura', 'gris', 'Vehículo, partes, garantías y flujo de fondos.', 'columna'),
        $s('Activos', '«Qué respalda la inversión»', 'Activos', 'activos', 'blanco', 'Proyectos o activos subyacentes.'),
        $s('Uso de fondos', '«En qué se invierte»', 'Uso de fondos', 'uso-de-fondos', 'gris', 'Destino del capital y calendario de desembolsos.'),
        $s('Retorno', '«Retorno y plazos»', 'Retorno', 'retorno', 'blanco', 'Rentabilidad esperada, plazos y forma de pago.'),
        $s('Gobierno', 'Control e información', 'Gobierno', 'gobierno', 'gris', 'Derechos del inversor, reportes y auditoría.'),
        $riesgos(),
        $conclusion,
    ]),
    $plantilla('Comercio Global', 'comercio-global', 'Comercio internacional, representaciones y conexiones de mercado.', [
        $sintesis('Qué producto se comercializa, entre qué mercados y con qué ventaja.'),
        $s('Producto', '«Qué se comercializa»', 'Producto', 'producto', 'gris', 'Producto, calidad, certificaciones y origen.', 'columna'),
        $s('Mercados', '«Destinos»', 'Mercados', 'mercados', 'blanco', 'Mercados de destino, demanda y precios.'),
        $s('Logística', '«Cadena de suministro»', 'Logística', 'logistica', 'gris', 'Rutas, puertos, almacenamiento y tiempos.'),
        $s('Socios', '«Socios comerciales»', 'Socios', 'socios', 'blanco', 'Proveedores, compradores y representaciones.'),
        $s('Escala', '«Fases de crecimiento»', 'Fases', 'fases', 'gris', 'Volúmenes por etapa y metas.'),
        $s('Regulación', 'Normas y aranceles', 'Regulación', 'regulacion', 'blanco', 'Requisitos de exportación e importación.'),
        $riesgos('gris'),
        $conclusion,
    ]),
    $plantilla('Digital e Innovación', 'digital-e-innovacion', 'Tecnología, inteligencia artificial, innovación y startups.', [
        $sintesis('Qué problema resuelve, para quién y cómo.'),
        $s('Problema y solución', '«Qué resolvemos»', 'Solución', 'solucion', 'gris', 'El problema, la solución y por qué ahora.', 'columna'),
        $s('Producto y tecnología', '«Cómo funciona»', 'Producto', 'producto', 'blanco', 'Producto, tecnología y estado de desarrollo.'),
        $s('Mercado', '«Tamaño de la oportunidad»', 'Mercado', 'mercado', 'gris', 'Clientes objetivo, tamaño de mercado y competencia.'),
        $s('Tracción', '«Lo logrado hasta hoy»', 'Tracción', 'traccion', 'blanco', 'Usuarios, clientes, ingresos o pilotos.'),
        $s('Modelo de negocio', '«Cómo se gana dinero»', 'Modelo', 'modelo', 'gris', 'Precios, ingresos y costos principales.'),
        $s('Hoja de ruta', '«Próximos pasos»', 'Hoja de ruta', 'hoja-de-ruta', 'blanco', 'Hitos de producto y de negocio.'),
        $s('Equipo', '«Quiénes lo hacen»', 'Equipo', 'equipo', 'gris', 'Fundadores, equipo y asesores.'),
        $riesgos(),
        $conclusion,
    ]),
    $plantilla('Desarrollo Territorial', 'desarrollo-territorial', 'Desarrollo urbano, territorial y grandes proyectos integrados.', [
        $sintesis('Qué se desarrolla, dónde, en qué escala y para quién.'),
        $s('Ubicación y territorio', '«El territorio»', 'Ubicación', 'ubicacion', 'gris', 'Ubicación, superficie, accesos y entorno.', 'columna'),
        $s('Plan maestro', '«La propuesta»', 'Plan maestro', 'plan-maestro', 'blanco', 'Usos, zonificación y componentes del plan.'),
        $s('Componentes', '«Qué incluye»', 'Componentes', 'componentes', 'gris', 'Vivienda, servicios, infraestructura y equipamiento.'),
        $s('Impacto', '«Impacto social y económico»', 'Impacto', 'impacto', 'blanco', 'Empleo, población beneficiada y desarrollo local.'),
        $s('Desarrollo', '«Fases del proyecto»', 'Fases', 'fases', 'gris', 'Etapas, plazos y hitos.'),
        $s('Aspectos legales y ambientales', 'Permisos y planificación', 'Legal y ambiental', 'legal', 'blanco', 'Uso de suelo, permisos y licencias.'),
        $s('Modelo de inversión', '«Cómo se financia»', 'Inversión', 'inversion', 'negro', 'Estructura de inversión y retorno.', 'columna'),
        $riesgos('gris'),
        $conclusion,
    ]),
    ['nombre' => 'En blanco', 'ecosistema' => null, 'descripcion' => 'Sin secciones: puede armar la página desde cero.', 'secciones' => []],
];

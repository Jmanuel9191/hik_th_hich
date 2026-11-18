<?php

namespace app\components;

/**
 * Países de operación del Grupo y sus ciudades principales (para los selectores País → Ciudad).
 * Si una ciudad no está, el selector deja escribirla a mano.
 */
final class Ubicaciones
{
    const CIUDADES = [
        'Bolivia' => [
            'Camiri', 'Cobija', 'Cochabamba', 'El Alto', 'La Paz', 'Montero', 'Oruro', 'Potosí', 'Puerto Suárez',
            'Quillacollo', 'Riberalta', 'Sacaba', 'San Ignacio de Velasco', 'Santa Cruz de la Sierra', 'Sucre',
            'Tarija', 'Trinidad', 'Tupiza', 'Uyuni', 'Villazón', 'Warnes', 'Yacuiba', 'Yapacaní',
        ],
        'Argentina' => [
            'Bahía Blanca', 'Buenos Aires', 'Catamarca', 'Comodoro Rivadavia', 'Córdoba', 'Corrientes', 'Formosa',
            'La Plata', 'La Rioja', 'Mar del Plata', 'Mendoza', 'Neuquén', 'Paraná', 'Posadas', 'Rawson',
            'Resistencia', 'Río Gallegos', 'Rosario', 'Salta', 'San Juan', 'San Luis', 'San Miguel de Tucumán',
            'San Salvador de Jujuy', 'Santa Fe', 'Santa Rosa', 'Santiago del Estero', 'Ushuaia', 'Viedma',
        ],
        'Perú' => [
            'Abancay', 'Arequipa', 'Ayacucho', 'Cajamarca', 'Callao', 'Cerro de Pasco', 'Chachapoyas', 'Chiclayo',
            'Chimbote', 'Cusco', 'Huancavelica', 'Huancayo', 'Huánuco', 'Huaraz', 'Ica', 'Iquitos', 'Juliaca',
            'Lima', 'Moquegua', 'Moyobamba', 'Piura', 'Pucallpa', 'Puerto Maldonado', 'Puno', 'Tacna', 'Tarapoto',
            'Trujillo', 'Tumbes',
        ],
        'Brasil' => [
            'Aracaju', 'Belém', 'Belo Horizonte', 'Boa Vista', 'Brasília', 'Campinas', 'Campo Grande', 'Corumbá',
            'Cuiabá', 'Curitiba', 'Florianópolis', 'Fortaleza', 'Foz do Iguaçu', 'Goiânia', 'João Pessoa', 'Macapá',
            'Maceió', 'Manaus', 'Natal', 'Palmas', 'Porto Alegre', 'Porto Velho', 'Recife', 'Rio Branco',
            'Rio de Janeiro', 'Salvador', 'Santos', 'São Luís', 'São Paulo', 'Teresina', 'Vitória',
        ],
    ];

    /** Ciudades de un país como lista valor => texto (incluye $actual si no está en la lista). */
    public static function ciudadesDe($pais, $actual = null)
    {
        $lista = self::CIUDADES[$pais] ?? [];
        if ($actual !== null && $actual !== '' && !in_array($actual, $lista, true)) {
            $lista[] = $actual;
        }
        return array_combine($lista, $lista) ?: [];
    }
}

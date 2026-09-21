<?php

namespace App\Controllers;

class ChatWhatsapp extends BaseController
{
    public function index()
    {
        $contactos = [
            [
                'id' => 1,
                'nombre' => 'María Fernández',
                'iniciales' => 'MF',
                'color' => 'primary',
                'telefono' => '+51 942 319 820',
                'estado' => 'En línea',
                'ultimaConexion' => null,
                'noLeidos' => 2,
            ],
            [
                'id' => 2,
                'nombre' => 'Carlos Rodríguez - Transportes Andina',
                'iniciales' => 'CR',
                'color' => 'success',
                'telefono' => '+51 987 654 321',
                'estado' => null,
                'ultimaConexion' => 'últ. vez hoy a las 09:15',
                'noLeidos' => 0,
            ],
            [
                'id' => 3,
                'nombre' => 'Grupo Yupana - Soporte',
                'iniciales' => 'GY',
                'color' => 'info',
                'telefono' => '+51 999 111 222',
                'estado' => 'En línea',
                'ultimaConexion' => null,
                'noLeidos' => 0,
            ],
            [
                'id' => 4,
                'nombre' => 'Lucía Torres',
                'iniciales' => 'LT',
                'color' => 'warning',
                'telefono' => '+51 955 444 111',
                'estado' => null,
                'ultimaConexion' => 'últ. vez ayer a las 18:40',
                'noLeidos' => 5,
            ],
            [
                'id' => 5,
                'nombre' => 'Distribuidora El Sol S.A.C.',
                'iniciales' => 'DS',
                'color' => 'danger',
                'telefono' => '+51 933 222 555',
                'estado' => null,
                'ultimaConexion' => 'últ. vez el lunes',
                'noLeidos' => 0,
            ],
            [
                'id' => 6,
                'nombre' => 'Jorge Ramírez',
                'iniciales' => 'JR',
                'color' => 'secondary',
                'telefono' => '+51 966 777 888',
                'estado' => null,
                'ultimaConexion' => 'últ. vez hace 2 días',
                'noLeidos' => 0,
            ],
        ];

        $conversaciones = [
            1 => [
                ['tipo' => 'entrante', 'texto' => 'Hola, buenos días. Quisiera consultar sobre mi último comprobante.', 'hora' => '08:45'],
                ['tipo' => 'saliente', 'texto' => 'Buenos días María, claro. Un momento por favor mientras lo reviso.', 'hora' => '08:47', 'leido' => true],
                ['tipo' => 'entrante', 'texto' => 'Muchas gracias, quedo atenta.', 'hora' => '08:47'],
                ['tipo' => 'saliente', 'texto' => 'Le confirmo, su comprobante F001-00234 ya fue enviado a su correo registrado.', 'hora' => '08:52', 'leido' => true],
                ['tipo' => 'entrante', 'texto' => 'Perfecto, lo reviso ahora mismo.', 'hora' => '08:53'],
                ['tipo' => 'entrante', 'texto' => '¿Podrían enviarme también el detalle en PDF?', 'hora' => '08:53'],
            ],
            2 => [
                ['tipo' => 'entrante', 'texto' => 'Buen día, ¿ya está lista la factura del mes?', 'hora' => 'Ayer'],
                ['tipo' => 'saliente', 'texto' => 'Buen día Carlos, sí, se generó ayer por la tarde.', 'hora' => 'Ayer', 'leido' => true],
                ['tipo' => 'saliente', 'texto' => 'Se la reenvío en este momento.', 'hora' => 'Ayer', 'leido' => true],
                ['tipo' => 'entrante', 'texto' => 'Recibido, gracias por la rapidez.', 'hora' => 'Ayer'],
            ],
            3 => [
                ['tipo' => 'saliente', 'texto' => 'Hola, ¿en qué podemos ayudarte hoy?', 'hora' => '10:02', 'leido' => true],
                ['tipo' => 'entrante', 'texto' => 'Tengo un problema para acceder al portal de clientes.', 'hora' => '10:05'],
                ['tipo' => 'saliente', 'texto' => 'Entendido, ¿me puedes indicar tu número de RUC para revisar tu cuenta?', 'hora' => '10:06', 'leido' => true],
            ],
        ];

        return view('chat_whatsapp/index', [
            'contactos' => $contactos,
            'conversaciones' => $conversaciones,
        ]);
    }
}

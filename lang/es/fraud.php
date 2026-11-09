<?php

// Los fraudes del Intermedio (App\Services\FraudService): la llamada en pantalla, el correo falso del Departamento
// de Educación Financiera, la página falsa, lo que pasó y la ayuda del banco. Términos: phishing, vishing,
// suplantación del número (spoofing), estafa del familiar en apuros, cuenta mula; «¡No piques!».
return [
    // la llamada entrante (partials/fraud-call)
    'call_incoming' => 'Llamada entrante',
    'call_answer' => 'Contestar',
    'call_decline' => 'Rechazar',
    'call_choose' => '¿Qué haces?',
    'call_close' => 'Cerrar',
    'callers' => [
        'bank_code' => 'Universal Bank · Seguridad',
        'safe_account' => 'Universal Bank · Seguridad',
        'tech_support' => 'Soporte técnico',
        'family' => 'Número desconocido',
    ],
    'lines' => [
        'bank_code' => ['Hola, le llamamos del departamento de seguridad de Universal Bank. Hemos parado un pago de :charge con su tarjeta.', 'Para anularlo, dígame el código que le acabamos de enviar por SMS. Rápido, o el pago se hará.'],
        'safe_account' => ['Hola, le llamamos del departamento de seguridad de Universal Bank. Alguien está intentando entrar en su cuenta ahora mismo.', 'Para proteger su dinero, páselo a esta cuenta segura: ZV00 1234 5678. Hágalo ya, antes de que le vacíen la suya.'],
        'tech_support' => ['Hola, le llamamos del soporte técnico. Su ordenador tiene un virus que nos está enviando sus datos del banco.', 'Instale el programa que le envío y se lo quitamos. Cuesta :repair, con tarjeta.'],
        'family' => ['¡Hola, soy yo, tu primo! He perdido el móvil y este es mi número nuevo.', 'Tengo que pagar algo urgente y mi tarjeta no funciona. ¿Me haces una transferencia? Mañana te lo devuelvo.'],
    ],
    'answers' => [
        'hang_up_call_bank' => 'Cuelgo y llamo yo al banco, a su número oficial',
        'give_code' => 'Le leo el código para anular el pago',
        'ask_then_give' => 'Le pregunto su nombre y, si me lo dice, le doy el código',
        'hang_up_safe' => 'Cuelgo: mi banco nunca me pediría mover mi dinero',
        'move_all' => 'Paso todo mi dinero a la cuenta segura',
        'move_half' => 'Paso solo la mitad, por si acaso',
        'hang_up_tech' => 'Cuelgo: yo no he pedido ayuda a nadie',
        'install' => 'Instalo el programa para que me quiten el virus',
        'pay_fix' => 'Pago el arreglo con mi tarjeta',
        'call_usual' => 'Antes de nada, llamo al número de siempre de mi primo',
        'transfer' => 'Le hago la transferencia: es urgente',
        'ask_amount' => 'Le pregunto cuánto necesita y se lo mando',
    ],

    // el correo falso (mailbox_templates/fraud-*)
    'email_from' => 'De',
    'email_dept' => 'Departamento de Educación Financiera',
    'email_report' => 'Marcar como fraude',
    'emails' => [
        'verify' => ['subject' => 'URGENTE: tu cuenta va a ser bloqueada', 'hello' => 'Estimado usuario:',
            'text' => 'Hemos detectado un problema en tu cuenta de Zedville. Si no la verificas en 24 horas, quedará bloqueada y no cobrarás tu sueldo.', 'button' => 'Verificar mi cuenta'],
        'refund' => ['subject' => 'Tienes una devolución de :refund', 'hello' => 'Estimado alumno:',
            'text' => 'Tienes pendiente una devolución de :refund. Verifica tu cuenta hoy para recibirla: después, se anulará.', 'button' => 'Cobrar mi devolución'],
        'prize' => ['subject' => '¡Enhorabuena! Has ganado un móvil', 'hello' => 'Estimado ganador:',
            'text' => 'Has ganado un móvil nuevo en el sorteo del Departamento. Para recibirlo, solo tienes que pagar :prize_fee de envío en las próximas 48 horas.', 'button' => 'Pagar el envío'],
        'parcel' => ['subject' => 'Tu material escolar está retenido', 'hello' => 'Estimado alumno:',
            'text' => 'Tu caja de material escolar está retenida. Paga hoy :parcel_fee de gastos o se devolverá.', 'button' => 'Pagar los gastos'],
        'job' => ['subject' => 'Trabajo para alumnos: gana :pay por cada transferencia', 'hello' => '¡Hola!',
            'text' => 'El Departamento busca alumnos que reciban transferencias en su cuenta y las reenvíen a otras cuentas. Te quedas :pay por cada una. No hace falta experiencia.', 'button' => 'Apuntarme'],
    ],

    // la página falsa (fraud/fake)
    'fake_title' => 'Verifica tu cuenta',
    'fake_text' => 'Pulsa el botón para terminar. Solo es un segundo.',

    // lo que pasó (el final de la llamada, fraud/lesson)
    'safe_title' => '¡Bien visto!',
    'safe_text' => 'Era un fraude y has hecho lo correcto: ganas un punto de participación.',
    'fell_title' => 'Era un fraude',
    'fell_text' => 'Los estafadores te han quitado :amount. Cúbrelo con tu fondo de emergencia o con un préstamo en 30 días, en «:activity».',
    'go_resolve' => 'Cubrirlo',
    'name' => 'Cómo se llama: :name.',
    'signs_title' => 'Las señales',
    'good_title' => 'Lo que había que hacer',
    'names' => [
        'bank_code' => 'vishing, el fraude por teléfono',
        'safe_account' => 'vishing, el fraude por teléfono',
        'tech_support' => 'la estafa del falso soporte técnico',
        'family' => 'la estafa del familiar en apuros',
        'verify' => 'phishing, el fraude por correo',
        'refund' => 'phishing, el fraude por correo',
        'prize' => 'phishing, el fraude por correo',
        'parcel' => 'phishing, el fraude por correo',
        'job' => 'ser una cuenta mula',
    ],
    'signs' => [
        'bank_code' => 'Te piden un código y te meten prisa. El número era el del banco, pero un número se puede falsear (suplantación del número o spoofing).',
        'safe_account' => 'Nadie del banco te pide mover tu dinero. El número era el del banco, pero un número se puede falsear (suplantación del número o spoofing).',
        'tech_support' => 'Una llamada que no has pedido, y quieren que instales algo y pagues.',
        'family' => 'Un número nuevo y prisa por el dinero.',
        'verify' => 'La dirección no es la del Departamento (un 1 en lugar de una l), no dicen tu nombre y te meten prisa.',
        'refund' => 'Dinero que no esperabas, prisa y un enlace para «verificar» tu cuenta.',
        'prize' => 'Tienes que pagar para cobrar un premio.',
        'parcel' => 'Un pago pequeño por un enlace, con prisa.',
        'job' => 'Dinero fácil por mover dinero ajeno.',
    ],
    'good' => [
        'bank_code' => 'Colgar y llamar tú al banco, a su número oficial (:phone).',
        'safe_account' => 'Colgar. Tu banco nunca te pide mover tu dinero.',
        'tech_support' => 'Colgar.',
        'family' => 'Llamar a su número de siempre o hacerle una pregunta que solo esa persona sabría.',
        'verify' => 'No pulsar el enlace: marcarlo como fraude. El Departamento nunca te pide verificar tu cuenta.',
        'refund' => 'Borrarlo y avisar.',
        'prize' => 'Ignorarlo.',
        'parcel' => 'No pulsar: mirarlo en la web oficial.',
        'job' => 'Decir que no: es ser una cuenta mula, y es delito.',
    ],
    'slogan' => '¡No piques!',
    'back' => 'Volver al buzón',

    // la ayuda del banco (bank/bank-help)
    'bank_contact_title' => 'El teléfono de Universal Bank',
    'bank_contact_text' => 'Llámanos al :phone. Nunca te pediremos un código ni una contraseña, ni que muevas tu dinero.',
];

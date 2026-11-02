<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reglas de cuidado
    |--------------------------------------------------------------------------
    |
    | Los valores salen del .env para poder acortar los tiempos durante la
    | demostración (por ejemplo, un cooldown de 1 minuto) sin tocar el código.
    |
    */

    'care_cooldown_minutes' => (int) env('GREENCYCLE_CARE_COOLDOWN_MINUTES', 60),

    'health_per_care' => 20,

    'max_health' => 100,

    'max_level' => 10,

];
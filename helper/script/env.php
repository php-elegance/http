<?php

use Elegance\Env;

Env::default('PORT', 8333);

Env::default('CROS', true);

Env::default('JWT', 'eleganceJwtPass');

Env::default('RESPONSE_CACHE', null);

Env::default('FRONT', '=' . (dirname(__DIR__, 2) . '/library/template/front.txt'));

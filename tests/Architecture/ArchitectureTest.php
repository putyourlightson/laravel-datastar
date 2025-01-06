<?php

arch()
    ->expect('Putyourlightson\Datastar')
    ->not->toUse(['die', 'dd', 'dump', 'var_dump']);

<?php

declare(strict_types=1);

// Architecture rules enforced as tests (testing-sdd / project law).

arch('all PHP files declare strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('controllers stay thin and never debug')
    ->expect('App\Http\Controllers')
    ->not->toUse(['dd', 'dump', 'ray', 'var_dump']);

arch('domain actions are final')
    ->expect('App\Domain\Analytics\Actions')
    ->toBeClasses()
    ->toHaveSuffix('Action');

arch('enums are backed')
    ->expect('App\Domain\Analytics\Enums')
    ->toBeEnums();

arch('no debugging helpers leak anywhere')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'die'])
    ->not->toBeUsed();

<?php

use App\Services\DeceasedFormatterService;

test('vafot etgan formati to\'g\'ri shakllanadi', function () {
    $formatter = new DeceasedFormatterService;

    $result = $formatter->format(1992, '1-марказий поликлиника шифокори');

    expect($result)->toBe('1992 йилда вафот этган (1-марказий поликлиника шифокори)');
});

test('turli yillar va lavozimlar bilan ishlaydi', function () {
    $formatter = new DeceasedFormatterService;

    expect($formatter->format(2015, 'пенсионер'))
        ->toBe('2015 йилда вафот этган (пенсионер)')
        ->and($formatter->format(1985, 'Урганч шаҳар 5-мактаб директори'))
        ->toBe('1985 йилда вафот этган (Урганч шаҳар 5-мактаб директори)');
});

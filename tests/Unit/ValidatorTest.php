<?php

use App\Validators\NoAbbreviationValidator;
use App\Validators\NoInitialsValidator;
use App\Validators\ProperRelationshipValidator;

test('qisqartmalar rad etiladi', function (string $input) {
    $validator = new NoAbbreviationValidator;
    $failed = false;

    $validator->validate('test', $input, function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeTrue();
})->with([
    'Тош. шаҳри' => 'Тош. шаҳри',
    'Хоразм вил.' => 'Хоразм вил.',
    'Урганч тум.' => 'Урганч тум.',
    '1976 й.' => '1976 й.',
]);

test('to\'liq yozilgan matn qabul qilinadi', function (string $input) {
    $validator = new NoAbbreviationValidator;
    $failed = false;

    $validator->validate('test', $input, function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
})->with([
    'Тошкент шаҳри',
    'Хоразм вилояти',
    'Урганч тумани',
    '1976 йил',
]);

test('initsiallar rad etiladi', function (string $input) {
    $validator = new NoInitialsValidator;
    $failed = false;

    $validator->validate('test', $input, function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeTrue();
})->with([
    'Рўзиев А.Ҳ.' => 'Рўзиев А.Ҳ.',
    'Каримов И.А.' => 'Каримов И.А.',
]);

test('to\'liq ism qabul qilinadi', function () {
    $validator = new NoInitialsValidator;
    $failed = false;

    $validator->validate('test', 'Рўзиев Асрор Ҳолматович', function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
});

test('noto\'g\'ri qarinoshlik so\'zlari rad etiladi', function (string $input) {
    $validator = new ProperRelationshipValidator;
    $failed = false;

    $validator->validate('test', $input, function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeTrue();
})->with([
    'ўғлим',
    'рафиқам',
    'хотиним',
    'отам',
]);

test('to\'g\'ri qarinoshlik so\'zlari qabul qilinadi', function (string $input) {
    $validator = new ProperRelationshipValidator;
    $failed = false;

    $validator->validate('test', $input, function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
})->with([
    'Ўғли',
    'Турмуш ўртоғи',
    'Отаси',
    'Онаси',
]);

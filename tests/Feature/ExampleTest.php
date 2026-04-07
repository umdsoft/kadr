<?php

test('bosh sahifa muvaffaqiyatli yuklaydi', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

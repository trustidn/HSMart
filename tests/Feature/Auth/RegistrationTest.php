<?php

use Illuminate\Support\Facades\Route;

test('registration is disabled and register routes are not registered', function () {
    expect(Route::has('register'))->toBeFalse();
    expect(Route::has('register.store'))->toBeFalse();
});

test('register path returns 404', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});

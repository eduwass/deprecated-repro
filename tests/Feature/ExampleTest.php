<?php

test('example', function () {
    expect(true)->toBeTrue();
});

test('browser', function () {
    visit('https://www.google.com')
        ->assertSee('Google');
});
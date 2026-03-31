<?php

test('the application root redirects to dashboard', function () {
    $user = \App\Models\User::factory()->create();

    $response = $this->actingAs($user)->get('/');

    $response->assertRedirect('/dashboard');
});

test('unauthenticated root redirects to login', function () {
    $response = $this->get('/');

    $response->assertRedirect('/login');
});

<?php

use App\Models\User;

test('login page can be rendered', function () {
    $response = $this->get('/login');
    $response->assertStatus(200);
});

test('users can login with valid credentials', function () {
    $user = User::factory()->create([
        'password' => 'password123',
        'role'     => 'owner',
    ]);

    $response = $this->post('/login', [
        'email'    => $user->email,
        'password' => 'password123',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
});

test('users cannot login with invalid credentials', function () {
    $user = User::factory()->create([
        'password' => 'password123',
    ]);

    $response = $this->post('/login', [
        'email'    => $user->email,
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors('email');
    $this->assertGuest();
});

test('authenticated users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/login');
    $this->assertGuest();
});

test('guests are redirected to login', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

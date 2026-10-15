<?php

use App\Models\User;
use Livewire\Volt\Volt;

test('a user cannot edit another users profile', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->actingAs($user1)
        ->get("/users/{$user2->id}/edit")
        ->assertForbidden();
});

test('a user can edit their own profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get("/users/{$user->id}/edit")
        ->assertOk();
});

test('user cannot update another users password via volt component', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->actingAs($user1);

    Volt::test('users.edit', ['user' => $user2])
        ->assertForbidden();
});

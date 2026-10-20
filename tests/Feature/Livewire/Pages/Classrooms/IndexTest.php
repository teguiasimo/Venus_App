<?php

use App\Models\Classroom;
use App\Models\Establishment;
use App\Models\SchoolYear;
use App\Models\User;
use Livewire\Volt\Volt;

test('it can render classrooms index page', function () {
    $user = User::factory()->create();

    $component = Volt::actingAs($user)->test('pages.classrooms.index');

    $component->assertSee('Classes');
});

test('it paginates classrooms and resets page on search or filter', function () {
    $user = User::factory()->create();
    $schoolYear = SchoolYear::create([
        'name' => 'Année '.uniqid(),
        'start_date' => '2026-09-01',
        'end_date' => '2027-06-30',
        'current' => false,
    ]);
    $establishment = Establishment::create([
        'name' => 'Établissement '.uniqid(),
        'code' => substr(uniqid('E_'), 0, 15),
    ]);

    for ($i = 1; $i <= 15; $i++) {
        Classroom::create([
            'name' => 'Classe '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            'establishment_id' => $establishment->id,
            'school_year_id' => $schoolYear->id,
        ]);
    }

    $component = Volt::actingAs($user)->test('pages.classrooms.index')
        ->set('selectedSchoolYear', $schoolYear->id)
        ->set('selectedEstablishment', $establishment->id)
        ->assertSee('Classe 01')
        ->call('setPage', 2)
        ->assertSet('paginators.page', 2)
        ->set('search', 'Classe 01')
        ->assertSet('paginators.page', 1);
});

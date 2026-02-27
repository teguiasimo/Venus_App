<?php

namespace Tests\Feature\Livewire\Pages\Corrections;

use Livewire\Volt\Volt;
use Tests\TestCase;

class IndexTest extends TestCase
{
    public function test_it_can_render(): void
    {
        $component = Volt::test('pages.corrections.index');

        $component->assertSee('');
    }
}

<?php

use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new
#[Title('Acceuil')]
class extends Component {

    public array $slides = [
        [
            'image' => '/photos/2.jpg',
        ],
        [
            'image' => '/photos/3.jpg',
        ],
        [
            'image' => '/photos/4.jpg',
        ],
        [
            'image' => '/photos/5.jpg',
        ],
    ];
    //
}; ?>

<div>
    <x-header title="Acceuil" subtitle="Well done is better than well said." separator/>
    <x-carousel :slides="$slides" autoplay interval="5000"/>
    <div class="mt-6">
        <x-button icon="o-camera" link="/student" class="btn-primary btn-dash">
            Elève
        </x-button>

        <x-button icon="o-building-library" link="/annee_scolaire" class="btn-primary btn-dash">
            Salle de classe
        </x-button>

        <x-button icon="o-document-check" link="/correction" class="btn-primary btn-dash">
            Correction
        </x-button>

        <x-button icon="o-information-circle" link="/about" class="btn-primary btn-dash">
            À propos
        </x-button>

    </div>


</div>

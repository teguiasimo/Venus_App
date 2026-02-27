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
    <x-header title="Acceuil"  separator />
    <x-carousel :slides="$slides" autoplay interval="5000" />
    <div class="mt-6">
        <x-button icon="o-user" link="/student" >
            Élève (Traitement Unique)
        </x-button>

        <x-button icon="o-building-library" link="/classes" >
            Salles de Classe (Traitement par Lot)
        </x-button>

        <x-button icon="o-document-check" link="/corrections" >
            Corrections (Gestion Erreurs)
        </x-button>

        <x-button icon="o-information-circle" link="/about" >
            À propos
        </x-button>

    </div>


</div>
<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

new
    #[Layout('components.layouts.app')]
    #[Title('À propos')]
    class extends Component {
        public array $breadcrumbs = [];

        public function mount()
        {
            $this->breadcrumbs = [
                ['label' => 'Accueil', 'link' => '/'],
                ['label' => 'À propos'],
            ];
        }
    }; ?>

<div>
    <x-breadcrumbs :items="$breadcrumbs" class="mb-4" />
    <x-header title="À Propos" subtitle="Découvrez les fonctionnalités de votre application de gestion photos." separator />

    <div class="grid lg:grid-cols-2 gap-8">
        {{-- Intro --}}
        <div class="col-span-2">
            <x-card title="Bienvenue sur Venus App" class="bg-base-200">
                <p>
                    Cette application est conçue pour simplifier la gestion et le traitement automatisé des photos d'élèves.
                    Elle permet de générer des photos traitées (avec le nom, la date, la classe et le code), de gérer les classes et de corriger les erreurs de traitement en masse.
                </p>
            </x-card>
        </div>

        {{-- Module 1: Elève --}}
        <x-card title="🧑‍🎓 Module Élève (Traitement Unique)" shadow>
            <p class="mb-4">Idéal pour traiter une photo unique rapidement.</p>
            <ul class="list-disc list-inside space-y-2 text-sm text-gray-600">
                <li>Formulaire complet (Nom, Classe, Code).</li>
                <li>Upload et prévisualisation instantanée.</li>
                <li>Génération de la carte photo avec nom, classe, code et date.</li>
                <li>Téléchargement direct du résultat.</li>
            </ul>
        </x-card>

        {{-- Module 2: Classes --}}
        <x-card title="🏫 Module Classes (Traitement par Lot)" shadow>
            <p class="mb-4">Le cœur de l'application pour la gestion massive.</p>
            <ul class="list-disc list-inside space-y-2 text-sm text-gray-600">
                <li>Importation de listes d'élèves via CSV/Excel.</li>
                <li>Gestion par Établissement et Année Scolaire.</li>
                <li>Upload de photos en masse (correspondance par ordre de liste).</li>
                <li>Traitement automatique de toute la classe.</li>
                <li>Téléchargement automatique d'une archive ZIP complète.</li>
            </ul>
        </x-card>

        {{-- Module 3: Corrections --}}
        <x-card title="✅ Module Corrections" shadow>
            <p class="mb-4">Pour gérer les exceptions et les erreurs.</p>
            <ul class="list-disc list-inside space-y-2 text-sm text-gray-600">
                <li>Création de lots de correction spécifiques.</li>
                <li>Importation des demandes de correction.</li>
                <li>Retraitement ciblé des élèves problématiques.</li>
                <li>Exportation automatique des corrections validées.</li>
            </ul>
        </x-card>

        {{-- Tech Stack --}}
        <x-card title="🛠️ Stack Technique" shadow>
            <div class="flex flex-wrap gap-2">
                <x-badge value="Laravel 11" class="badge-primary" />
                <x-badge value="Livewire" class="badge-secondary" />
                <x-badge value="Volt" class="badge-accent" />
                <x-badge value="Tailwind CSS" class="badge-neutral" />
                <x-badge value="MaryUI" class="badge-info" />
            </div>
            <p class="mt-4 text-sm text-gray-400">Développé avec ❤️ par Yannick (developer web).</p>
        </x-card>
    </div>
</div>
<?php

use Livewire\Attributes\Validate;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use App\Services\ImageProcessingService;
use App\Models\SingleStudent;

new
    #[Title('Elève')]
    class extends Component {
        // Traits
        use WithFileUploads, Toast;

        // UI Configuration
        public array $breadcrumbs = [
            ['label' => 'Accueil', 'link' => '/'],
            ['label' => 'Élève'],
        ];

        // Form Properties
        #[Validate('required|min:2|max:50')]
        public string $name = '';

        #[Validate('required|min:2|max:15')]
        public string $classe = '';

        #[Validate('required|min:2|max:15')]
        public string $code = '';

        #[Validate('required|image|max:2240')] // 2MB max
        public $photo;

        #[Validate('hex_color')]
        public string $color = '#000000';

        #[Validate('required|integer|min:20|max:80')]
        public int $photoSize = 55;

        #[Validate('required|integer|min:20|max:80')]
        public int $dateSize = 55;

        // UI State
        public $processedImage = null;
        public ?string $processedName = null;
        public int $iteration = 0;
        public array $photoSizeOptions = [];

        // Dependencies
        protected ImageProcessingService $imageService;

        public function boot(ImageProcessingService $imageService)
        {
            $this->imageService = $imageService;
        }

        public function mount()
        {
            $this->photoSizeOptions = $this->generatePhotoSizeOptions(20, 80, 56);
        }

        public function save()
        {
            $this->validate();

            try {
                // 1. Process Image via Service
                $processedPath = $this->imageService->processStudentCard(
                    $this->photo,
                    [
                        'name' => $this->name,
                        'classroom' => $this->classe,
                        'code' => $this->code,
                    ],
                    [
                        'color' => $this->color,
                        'photoSize' => $this->photoSize,
                        'dateSize' => $this->dateSize,
                    ]
                );

                // 2. Save to Database
                $originalPath = $this->photo->store('students/originals', 'public');

                SingleStudent::create([
                    'full_name' => $this->name,
                    'classroom_name' => $this->classe,
                    'code' => $this->code,
                    'original_photo_path' => $originalPath,
                    'processed_photo_path' => $processedPath,
                ]);

                // 3. Update UI
                $this->processedImage = $processedPath;
                $this->processedName = mb_strtoupper($this->name, 'UTF-8');
                $this->success('Carte générée avec succès !');

                // Reset form (except processed image)
                $this->reset(['name', 'classe', 'code', 'photo']);
                $this->iteration++;
            } catch (\Throwable $e) {
                $this->error('Erreur lors du traitement: ' . $e->getMessage());
            }
        }

        protected function generatePhotoSizeOptions(int $min, int $max, int $default): array
        {
            $options = [];
            for ($i = $min; $i <= $max; $i++) {
                $name = (string)$i;
                if ($i === $default) {
                    $name .= ' (Défaut)';
                }
                $options[] = [
                    'id' => $i,
                    'name' => $name,
                ];
            }
            return $options;
        }
    }; ?>

<div>
    <x-breadcrumbs :items="$breadcrumbs" />
    <x-header title="Elève" separator />

    <div class="container mx-auto">
        {{-- Rendre le composant responsive avec `flex-col` par défaut (mobile) et `lg:flex-row` (grand écran) --}}
        <div class="flex flex-col lg:flex-row">

            {{-- La colonne du formulaire prend toute la largeur sur mobile et la moitié sur grand écran --}}
            <div class="w-full lg:w-1/2 px-2 py-2">
                <x-card title="Remplir les champs!!" shadow separator>
                    <div
                        x-data="{ uploading: false }"
                        x-on:livewire-upload-start.window="uploading = true"
                        x-on:livewire-upload-finish.window="uploading = false"
                        x-on:livewire-upload-error.window="uploading = false">
                        <x-form wire:submit="save">

                            {{-- 💡 Structure Flex Column Principale pour espacement global --}}
                            <div class="flex flex-col space-y-4">

                                {{-- 1. Nom et prénom (Pleine largeur sur la première ligne) --}}
                                <div class="flex gap-4">
                                    <x-input label="Nom et prénom" wire:model="name" placeholder="Le nom complet de l'élève" />
                                    <x-input class="w-full" label="Classe" wire:model="classe" placeholder="Classe de l'élève " />
                                </div>



                                {{-- 2. Section inférieure (Responsive: 1 colonne sur petit, 2 colonnes sur grand) --}}
                                {{-- Utilisation de 'flex-wrap' pour que les éléments passent à la ligne sur les petits écrans --}}
                                <div class="flex flex-col lg:flex-row lg:space-x-4 space-y-4 lg:space-y-0">

                                    {{-- 2.1. Colonne de Gauche : Champ Photo (lg:w-1/2) --}}
                                    <div class="w-full lg:w-1/2">
                                        <x-file wire:model="photo" accept="image/*" label="Photo (4*4)" hint="Cliquez pour changer" :wire:key="'photo-' . $iteration">
                                            <div class="mt-2">
                                                @if($photo)
                                                <img src="{{ $photo->temporaryUrl() }}" class="h-40 rounded-lg object-cover" />
                                                @else
                                                <img src="{{ asset('images/emptyUser.jpg') }}" class="h-40 rounded-lg object-cover" />
                                                @endif
                                            </div>
                                        </x-file>

                                        {{-- Indicateur d'upload --}}
                                        <div x-show="uploading" x-cloak class="flex items-center gap-2 text-sm text-blue-600 mt-2">
                                            <span>Upload en cours…</span>
                                        </div>
                                    </div>

                                    {{-- 2.2. Colonne de Droite : Autres Champs (lg:w-1/2) --}}
                                    {{-- On utilise 'space-y-4' pour espacer les groupes de champs dans cette colonne --}}
                                    <div class="w-full  flex flex-col space-y-4">

                                        {{-- Ligne Couleur/Taille de police (Flexbox) --}}
                                        <div class="flex gap-4">
                                            {{-- Ajustement du 'w-full' sur les composants pour garantir l'alignement flex --}}
                                            <div class="w-full">
                                                <x-input class="w-full" label="Code" wire:model="code" placeholder="EX: (CR XXXX)" />
                                                <x-colorpicker label="Color" wire:model="color" icon="o-swatch" />
                                                <div class="flex gap-2">
                                                    <x-select
                                                        label="Taille Texte"
                                                        wire:model.live.debounce.500ms="photoSize"
                                                        :options="$photoSizeOptions"
                                                        select-class="select-sm"
                                                        class="w-1/2" />
                                                    <x-select
                                                        label="Taille Date"
                                                        wire:model.live.debounce.500ms="dateSize"
                                                        :options="$photoSizeOptions"
                                                        select-class="select-sm"
                                                        class="w-1/2" />
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                            </div>

                            <x-slot:actions>
                                <x-button
                                    label="Save!"
                                    class="btn-primary"
                                    type="submit"
                                    spinner
                                    x-bind:disabled="uploading"
                                    wire:loading.attr="disabled"
                                    wire:target="photo" />
                            </x-slot:actions>

                        </x-form>
                    </div>
                </x-card>

            </div>

            {{-- Colonne de prévisualisation (inchangée) --}}
            {{-- Colonne de prévisualisation --}}
            <div class="w-full lg:w-1/2 px-2 py-2">
                @if($processedImage)
                <x-card title="Résultat" shadow separator>
                    <figure class="relative max-w-full transition-all duration-300 cursor-pointer mb-4">
                        <img class="rounded-lg h-auto w-full object-contain max-h-[500px]" src="{{ asset('storage/' . $processedImage) }}" alt="Processed Image">
                    </figure>

                    <div class="flex justify-center">
                        <a href="{{ asset('storage/' . $processedImage) }}" download="{{ $processedName }}.jpg" class="btn btn-warning">
                            <x-icon name="o-arrow-down-tray" class="w-4 h-4 mr-2" />
                            Télécharger
                        </a>
                    </div>
                </x-card>
                @else
                <x-card title="Aperçu" shadow separator>
                    <div class="flex flex-col items-center justify-center p-8 text-gray-400">
                        <x-icon name="o-photo" class="w-16 h-16 mb-2" />
                        <p>L'image traitée apparaîtra ici.</p>
                    </div>
                </x-card>
                @endif
            </div>
        </div>
    </div>
</div>
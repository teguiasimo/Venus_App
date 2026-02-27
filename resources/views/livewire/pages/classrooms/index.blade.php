<?php

use Livewire\Volt\Component;
use App\Models\Classroom;
use App\Models\Establishment;
use App\Models\SchoolYear;
use Livewire\Attributes\Validate;
use Mary\Traits\Toast;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;

new
    #[Title('Classes')]
    class extends Component {
        use Toast;

        public bool $myModal = false;
        public bool $isEdit = false;

        #[Validate('required|min:2|max:50')]
        public string $name = '';

        #[Validate('required|exists:establishments,id')]
        public $establishment_id = null;

        #[Validate('required|exists:school_years,id')]
        public $school_year_id = null;

        public ?Classroom $classroom = null;

        public $search = '';

        // Filters
        public $selectedSchoolYear = null;
        public $selectedEstablishment = null;

        public function mount()
        {
            // Default to current school year
            $current = SchoolYear::where('current', true)->first();
            if ($current) {
                $this->selectedSchoolYear = $current->id;
                $this->school_year_id = $current->id;
            }
        }

        // Headers for the table
        public function headers(): array
        {
            return [
                ['key' => 'row_number', 'label' => '#', 'class' => 'w-1', 'sortable' => false],
                ['key' => 'name', 'label' => 'Nom'],
                ['key' => 'establishment.name', 'label' => 'Établissement'],
                ['key' => 'schoolYear.name', 'label' => 'Année'],
                ['key' => 'students_count', 'label' => 'Élèves', 'sortable' => false],
                ['key' => 'actions', 'label' => 'Actions', 'sortable' => false],
            ];
        }

        public function create(): void
        {
            $this->reset(['name', 'classroom', 'isEdit']);
            $this->establishment_id = $this->selectedEstablishment;
            $this->school_year_id = $this->selectedSchoolYear;
            $this->myModal = true;
        }

        public function edit(Classroom $classroom): void
        {
            $this->classroom = $classroom;
            $this->name = $classroom->name;
            $this->establishment_id = $classroom->establishment_id;
            $this->school_year_id = $classroom->school_year_id;
            $this->isEdit = true;
            $this->myModal = true;
        }

        public function save(): void
        {
            $this->validate();

            if ($this->isEdit) {
                $this->classroom->update([
                    'name' => $this->name,
                    'establishment_id' => $this->establishment_id,
                    'school_year_id' => $this->school_year_id,
                ]);
                $this->success("Classe modifiée avec succès.");
            } else {
                Classroom::create([
                    'name' => $this->name,
                    'establishment_id' => $this->establishment_id,
                    'school_year_id' => $this->school_year_id,
                ]);
                $this->success("Classe créée avec succès.");
            }

            $this->myModal = false;
        }

        public function delete(Classroom $classroom): void
        {
            if ($classroom->students()->exists()) {
                $this->error("Impossible de supprimer cette classe car elle contient des élèves.");
                return;
            }

            $classroom->delete();
            $this->success("Classe supprimée.");
        }

        public function openBatch(Classroom $classroom)
        {
            return $this->redirectRoute('classrooms.show', ['classroom' => $classroom], navigate: true);
        }

        public function with(): array
        {
            $query = Classroom::query()
                ->with(['establishment', 'schoolYear'])
                ->withCount('students');

            if ($this->selectedSchoolYear) {
                $query->where('school_year_id', $this->selectedSchoolYear);
            }

            if ($this->selectedEstablishment) {
                $query->where('establishment_id', $this->selectedEstablishment);
            }

            if ($this->search) {
                $query->where(function (Builder $q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhereHas('establishment', fn($q) => $q->where('name', 'like', "%{$this->search}%"));
                });
            }

            return [
                'classrooms' => $query->orderBy('name')->paginate(10),
                'headers' => $this->headers(),
                'schoolYears' => SchoolYear::orderBy('start_date', 'desc')->get(),
                'establishments' => Establishment::orderBy('name')->get(),
            ];
        }
        public array $breadcrumbs = [
            ['label' => 'Accueil', 'link' => '/'],
            ['label' => 'Classes'],
        ];
    }; ?>

<div>
    <x-breadcrumbs :items="$breadcrumbs" class="mb-4" />
    <x-header title="Classes" separator>
        <x-slot:middle class="!justify-end">
            <div class="flex flex-row gap-2">
                <x-select
                    icon="o-calendar"
                    :options="$schoolYears"
                    wire:model.live="selectedSchoolYear"
                    placeholder="Toutes les années"
                    class="!w-5spinner0" />
                <x-select
                    icon="o-building-library"
                    :options="$establishments"
                    wire:model.live="selectedEstablishment"
                    placeholder="Tous les établissements"
                    class="!w-56" />
                <x-input icon="o-magnifying-glass" wire:model.live.debounce="search" placeholder="Rechercher..." class="!w-40" />
            </div>
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Ajouter" icon="o-plus" class="btn-primary" wire:click="create" spinner />
        </x-slot:actions>
    </x-header>

    @if(!$selectedSchoolYear)
    <x-alert icon="o-exclamation-triangle" title="Attention" description="Veuillez sélectionner une année scolaire pour voir les classes." class="alert-warning mb-4" />
    @endif

    <x-card>
        <x-table :headers="$headers" :rows="$classrooms" striped>
            @scope('cell_row_number', $classroom, $classrooms)
            {{ $classrooms->firstItem() + $loop->index }}
            @endscope
            @scope('cell_actions', $classroom)
            <div class="flex gap-2">
                <x-button icon="o-eye" class="btn-sm btn-info text-white" wire:click="openBatch({{ $classroom->id }})" tooltip="Gérer la classe (Batch)" spinner />
                <x-button icon="o-pencil" class="btn-sm btn-ghost" wire:click="edit({{ $classroom->id }})" spinner />
                <x-button icon="o-trash" class="btn-sm btn-ghost text-error" wire:click="delete({{ $classroom->id }})" wire:confirm="Êtes-vous sûr ?" spinner />
            </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="myModal" :title="$isEdit ? 'Modifier Classe' : 'Nouvelle Classe'">
        <x-form wire:submit="save">
            <div class="grid grid-cols-1 gap-4">
                <x-select label="Année Scolaire" :options="$schoolYears" wire:model="school_year_id" placeholder="Sélectionnez une année" placeholder-value="" />
                <x-select label="Établissement" :options="$establishments" wire:model="establishment_id" placeholder="Sélectionnez un établissement" placeholder-value="" />
                <x-input label="Nom de la classe" wire:model="name" />
            </div>

            <x-slot:actions>
                <x-button label="Annuler" @click="$wire.myModal = false" />
                <x-button label="Enregistrer" class="btn-primary" type="submit" spinner />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
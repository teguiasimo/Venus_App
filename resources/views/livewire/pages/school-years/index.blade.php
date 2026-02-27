<?php

use Livewire\Volt\Component;
use App\Models\SchoolYear;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Mary\Traits\Toast;

new
    #[Title('Année scolaire')]
    class extends Component {
        use Toast;

        public bool $myModal = false;
        public bool $isEdit = false;

        #[Validate('required|min:3|max:20')]
        public string $name = '';

        #[Validate('required|date')]
        public string $start_date = '';

        #[Validate('required|date|after:start_date')]
        public string $end_date = '';

        public ?SchoolYear $schoolYear = null;

        // Headers for the table
        public function headers(): array
        {
            return [
                ['key' => 'row_number', 'label' => '#', 'class' => 'w-1', 'sortable' => false],
                ['key' => 'name', 'label' => 'Nom'],
                ['key' => 'start_date', 'label' => 'Début'],
                ['key' => 'end_date', 'label' => 'Fin'],
                ['key' => 'current', 'label' => 'Statut'], // Custom handling in blade
                ['key' => 'actions', 'label' => 'Actions', 'sortable' => false],
            ];
        }

        public function create(): void
        {
            $this->reset(['name', 'start_date', 'end_date', 'schoolYear', 'isEdit']);
            $this->myModal = true;
        }

        public function edit(SchoolYear $schoolYear): void
        {
            $this->schoolYear = $schoolYear;
            $this->name = $schoolYear->name;
            $this->start_date = $schoolYear->start_date->format('Y-m-d');
            $this->end_date = $schoolYear->end_date->format('Y-m-d');
            $this->isEdit = true;
            $this->myModal = true;
        }

        public function save(): void
        {
            $this->validate();

            if ($this->isEdit) {
                $this->schoolYear->update([
                    'name' => $this->name,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                ]);
                $this->success("Année scolaire modifiée avec succès.");
            } else {
                SchoolYear::create([
                    'name' => $this->name,
                    'start_date' => $this->start_date,
                    'end_date' => $this->end_date,
                    'current' => false, // Default not current
                ]);
                $this->success("Année scolaire créée avec succès.");
            }

            $this->myModal = false;
        }

        public function delete(SchoolYear $schoolYear): void
        {
            if ($schoolYear->classrooms()->exists()) {
                $this->error("Impossible de supprimer cette année car elle contient des classes.");
                return;
            }

            $schoolYear->delete();
            $this->success("Année supprimée.");
        }

        public function toggleCurrent(SchoolYear $schoolYear): void
        {
            // Set all to false
            SchoolYear::query()->update(['current' => false]);

            // Set this one to true
            $schoolYear->update(['current' => true]);

            $this->success("Année '{$schoolYear->name}' définie comme courante.");
        }

        public function with(): array
        {
            return [
                'schoolYears' => SchoolYear::orderBy('start_date', 'desc')->get(),
                'headers' => $this->headers(),
            ];
        }
    }; ?>

<div>
    <x-header title="Années Scolaires" separator>
        <x-slot:actions>
            <x-button label="Ajouter" icon="o-plus" class="btn-primary" wire:click="create" spinner />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-table :headers="$headers" :rows="$schoolYears" striped>
            @scope('cell_row_number', $schoolYear)
            {{ $loop->iteration }}
            @endscope
            @scope('cell_current', $schoolYear)
            @if($schoolYear->current)
            <x-badge value="COURANTE" class="badge-success" />
            @else
            <x-button icon="o-check" class="btn-xs btn-ghost" wire:click="toggleCurrent({{ $schoolYear->id }})" tooltip="Définir comme courante" spinner />
            @endif
            @endscope

            @scope('cell_actions', $schoolYear)
            <div class="flex gap-2">
                <x-button icon="o-pencil" class="btn-sm btn-ghost" wire:click="edit({{ $schoolYear->id }})" spinner />
                <x-button icon="o-trash" class="btn-sm btn-ghost text-error" wire:click="delete({{ $schoolYear->id }})" wire:confirm="Êtes-vous sûr ?" spinner />
            </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="myModal" :title="$isEdit ? 'Modifier Année' : 'Nouvelle Année'">
        <x-form wire:submit="save">
            <x-input label="Nom (ex: 2024-2025)" wire:model="name" />
            <div class="grid grid-cols-2 gap-4">
                <x-input type="date" label="Date de début" wire:model="start_date" />
                <x-input type="date" label="Date de fin" wire:model="end_date" />
            </div>

            <x-slot:actions>
                <x-button label="Annuler" @click="$wire.myModal = false" />
                <x-button label="Enregistrer" class="btn-primary" type="submit" spinner />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
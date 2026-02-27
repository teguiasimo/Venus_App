<?php

use Livewire\Volt\Component;
use App\Models\Establishment;
use Livewire\Attributes\Validate;
use Mary\Traits\Toast;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;

new
    #[Title('Établissements')]
    class extends Component {
        use Toast;

        public bool $myModal = false;
        public bool $isEdit = false;

        #[Validate('required|min:2|max:50')]
        public string $name = '';

        #[Validate('required|max:15')]
        public string $code = '';

        public ?Establishment $establishment = null;

        public string $search = '';

        // Headers for the table
        public function headers(): array
        {
            return [
                ['key' => 'row_number', 'label' => '#', 'class' => 'w-1', 'sortable' => false],
                ['key' => 'name', 'label' => 'Nom'],
                ['key' => 'code', 'label' => 'Code'],
                ['key' => 'actions', 'label' => 'Actions', 'sortable' => false],
            ];
        }

        public function create(): void
        {
            $this->reset(['name', 'code', 'establishment', 'isEdit']);
            $this->myModal = true;
        }

        public function edit(Establishment $establishment): void
        {
            $this->establishment = $establishment;
            $this->name = $establishment->name;
            $this->code = $establishment->code;
            $this->isEdit = true;
            $this->myModal = true;
        }

        public function save(): void
        {
            $rules = [
                'name' => ['required', 'min:2', 'max:50', Rule::unique('establishments')->ignore($this->establishment->id ?? null)],
                'code' => ['required', 'max:15', Rule::unique('establishments')->ignore($this->establishment->id ?? null)],
            ];

            $this->validate($rules);

            if ($this->isEdit) {
                $this->establishment->update([
                    'name' => $this->name,
                    'code' => $this->code,
                ]);
                $this->success("Établissement modifié avec succès.");
            } else {
                Establishment::create([
                    'name' => $this->name,
                    'code' => $this->code,
                ]);
                $this->success("Établissement créé avec succès.");
            }

            $this->myModal = false;
        }

        public function delete(Establishment $establishment): void
        {
            if ($establishment->classrooms()->exists()) {
                $this->error("Impossible de supprimer cet établissement car il contient des classes.");
                return;
            }

            $establishment->delete();
            $this->success("Établissement supprimé.");
        }

        public function with(): array
        {
            return [
                'establishments' => Establishment::query()
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%")
                    ->orderBy('name')
                    ->get(),
                'headers' => $this->headers(),
            ];
        }
    }; ?>

<div>
    <x-header title="Établissements" separator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" wire:model.live.debounce="search" placeholder="Rechercher..." />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Ajouter" icon="o-plus" class="btn-primary" wire:click="create" spinner />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-table :headers="$headers" :rows="$establishments" striped>
            @scope('cell_row_number', $establishment)
            {{ $loop->iteration }}
            @endscope
            @scope('cell_actions', $establishment)
            <div class="flex gap-2">
                <x-button icon="o-pencil" class="btn-sm btn-ghost" wire:click="edit({{ $establishment->id }})" spinner />
                <x-button icon="o-trash" class="btn-sm btn-ghost text-error" wire:click="delete({{ $establishment->id }})" wire:confirm="Êtes-vous sûr ?" spinner />
            </div>
            @endscope
        </x-table>
    </x-card>

    <x-modal wire:model="myModal" :title="$isEdit ? 'Modifier Établissement' : 'Nouvel Établissement'">
        <x-form wire:submit="save">
            <x-input label="Nom" wire:model="name" />
            <x-input label="Code" wire:model="code" />

            <x-slot:actions>
                <x-button label="Annuler" @click="$wire.myModal = false" />
                <x-button label="Enregistrer" class="btn-primary" type="submit" spinner />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
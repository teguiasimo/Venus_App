<?php

use Livewire\Volt\Component;
use App\Models\SchoolYear;
use App\Models\CorrectionBatch;
use Mary\Traits\Toast;
use Livewire\Attributes\Validate;
use Livewire\Attributes\Title;

new 
#[Title('Correction')]
class extends Component {
    use Toast;

    public $schoolYears;
    public $selectedSchoolYear;

    // Create Modal
    public bool $createModal = false;

    #[Validate('required|min:3')]
    public string $name = '';

    public function mount()
    {
        $this->schoolYears = SchoolYear::orderByDesc('start_date')->get();
        // Default to current or first
        $current = $this->schoolYears->where('current', true)->first();
        $this->selectedSchoolYear = $current ? $current->id : ($this->schoolYears->first()->id ?? null);
    }

    public function save()
    {
        $this->validate();

        CorrectionBatch::create([
            'name' => $this->name,
            'school_year_id' => $this->selectedSchoolYear
        ]);

        $this->success("Dossier de correction créé !");
        $this->createModal = false;
        $this->reset('name');
    }

    public function openBatch(CorrectionBatch $batch): void
    {
        $this->redirectRoute('corrections.show', ['batch' => $batch], navigate: true);
    }

    public function delete(CorrectionBatch $batch)
    {
        $batch->delete();
        $this->success("Dossier supprimé.");
    }

    public function with(): array
    {
        return [
            'batches' => CorrectionBatch::where('school_year_id', $this->selectedSchoolYear)
                ->withCount('requests')
                ->latest()
                ->get(),
            'headers' => [
                ['key' => 'row_number', 'label' => '#', 'sortable' => false],
                ['key' => 'name', 'label' => 'Nom du Dossier'],
                ['key' => 'requests_count', 'label' => 'Demandes'],
                ['key' => 'created_at', 'label' => 'Créé le'],
                ['key' => 'actions', 'label' => 'Actions', 'sortable' => false],
            ]
        ];
    }
    public array $breadcrumbs = [
        ['label' => 'Accueil', 'link' => '/'],
        ['label' => 'Corrections'],
    ];
}; ?>

<div>
    <x-breadcrumbs :items="$breadcrumbs" class="mb-4" />
    <x-header title="Dossiers de Correction" separator>
        <x-slot:middle class="!justify-end">
            <x-select icon="o-calendar" :options="$schoolYears" wire:model.live="selectedSchoolYear" class="w-full sm:w-48" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Nouveau Dossier" icon="o-plus" class="btn-primary" @click="$wire.createModal = true" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-table :headers="$headers" :rows="$batches">
            @scope('cell_row_number', $batch)
            {{ $loop->iteration }}
            @endscope
            @scope('cell_created_at', $batch)
            {{ $batch->created_at->format('d/m/Y H:i') }}
            @endscope
            @scope('cell_actions', $batch)
            <div class="flex gap-1">
                <x-button icon="o-eye" class="btn-sm btn-info text-white" wire:click="openBatch({{ $batch->id }})" tooltip="Ouvrir" spinner />
                <x-button icon="o-trash" class="btn-sm btn-ghost text-error" wire:click="delete({{ $batch->id }})" wire:confirm="Supprimer ce dossier et toutes ses demandes ?" spinner />
            </div>
            @endscope
        </x-table>

        @if($batches->isEmpty())
        <div class="text-center py-10 text-gray-400">
            Aucun dossier de correction pour cette année.
        </div>
        @endif
    </x-card>

    <x-modal wire:model="createModal" title="Nouveau Dossier">
        <x-form wire:submit="save">
            <x-input label="Nom du dossier" wire:model="name" placeholder="Ex: Retours Janvier" />

            <x-slot:actions>
                <x-button label="Annuler" @click="$wire.createModal = false" />
                <x-button label="Créer" class="btn-primary" type="submit" spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
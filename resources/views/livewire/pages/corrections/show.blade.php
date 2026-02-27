<?php

use Livewire\Volt\Component;
use App\Models\CorrectionBatch;
use App\Models\CorrectionRequest;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;

new
    #[Title('Correction detail')]
    class extends Component {
        use Toast, WithFileUploads;

        public CorrectionBatch $batch;

        // Import
        public $csvFile;

        // Upload Photos
        public $photos = [];
        public bool $photoDrawer = false;

        // Processing
        public $fontSize = 55;
        public $dateSize = 55;
        public $textColor = '#000000';
        public bool $settingsModal = false;
        public bool $singleProcessModal = false;
        public ?int $processingRequestId = null;

        // Add Request
        public bool $addModal = false;
        public string $addingName = '';
        public string $addingCode = '';
        public string $addingClassroom = '';
        public $addingPhoto = null;

        // Edit Request
        public bool $editModal = false;
        public ?CorrectionRequest $editingRequest = null;
        public string $editingName = '';
        public string $editingCode = '';
        public string $editingClassroom = '';

        // Breadcrumbs
        public array $breadcrumbs = [];

        public function mount(CorrectionBatch $batch)
        {
            $this->batch = $batch;
            $this->breadcrumbs = [
                ['label' => 'Accueil', 'link' => '/'],
                ['label' => 'Corrections', 'link' => route('corrections.index')],
                ['label' => $batch->name],
            ];
        }

        // ... existing methods ...


        public function importCsv()
        {
            $this->validate(['csvFile' => 'required|file|mimes:csv,txt,xlsx']);

            $path = $this->csvFile->getRealPath();

            // Count for report
            $count = 0;

            SimpleExcelReader::create($path)
                ->getRows()
                ->each(function (array $row) use (&$count) {
                    // Normalization keys
                    $name = $row['Nom'] ?? $row['nom'] ?? $row['Name'] ?? $row['name'] ?? null;
                    $code = $row['Code'] ?? $row['code'] ?? null;
                    $classroom = $row['Classe'] ?? $row['classe'] ?? $row['Class'] ?? $row['class'] ?? null;

                    if ($name && $code && $classroom) {
                        CorrectionRequest::create([
                            'correction_batch_id' => $this->batch->id,
                            'full_name' => $name,
                            'establishment_code' => $code,
                            'classroom_name' => $classroom
                        ]);
                        $count++;
                    }
                });

            $this->success("$count demandes importées !");
            $this->reset('csvFile');
        }

        public function savePhotos()
        {
            $this->validate(['photos.*' => 'image|max:10240']);

            // Get requests ordered by ID
            $requests = $this->batch->requests()->orderBy('id')->get();

            $photosCount = count($this->photos);
            $requestsCount = $requests->count();

            if ($photosCount !== $requestsCount) {
                $this->warning("Attention : $photosCount photos pour $requestsCount demandes.");
            }

            foreach ($this->photos as $index => $photo) {
                if (isset($requests[$index])) {
                    $req = $requests[$index];
                    $path = $photo->store("corrections/{$this->batch->id}/originals", 'public');
                    $req->update(['original_photo_path' => $path]);
                }
            }

            $this->success("Photos assignées !");
            $this->photoDrawer = false;
            $this->reset('photos');
        }

        public function processBatch()
        {
            $requests = $this->batch->requests()->whereNotNull('original_photo_path')->get();

            if ($requests->isEmpty()) {
                $this->error("Aucune demande avec photo.");
                return;
            }

            $this->batch->update(['is_processing' => true]);

            // Zip Setup
            $zipName = 'corrections_' . $this->batch->id . '_' . time() . '.zip';
            $zipPath = 'zips/' . $zipName;
            $fullZipPath = Storage::disk('public')->path($zipPath);

            if (!file_exists(dirname($fullZipPath))) mkdir(dirname($fullZipPath), 0755, true);

            $zip = new ZipArchive;
            if ($zip->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

                $imageService = app(\App\Services\ImageProcessingService::class);
                $options = [
                    'color' => $this->textColor,
                    'photoSize' => $this->fontSize,
                    'dateSize' => $this->dateSize,
                ];

                foreach ($requests as $req) {
                    try {
                        $originalPath = Storage::disk('public')->path($req->original_photo_path);

                        if (!file_exists($originalPath)) continue;

                        $data = [
                            'name' => $req->full_name,
                            'classroom' => $req->classroom_name,
                            'code' => $req->establishment_code
                        ];

                        $processedPath = $imageService->processStudentCard($originalPath, $data, $options);
                        $req->update(['processed_photo_path' => $processedPath]);

                        $cleanName = mb_strtoupper($req->full_name, 'UTF-8') . '_' . mb_strtoupper($req->establishment_code, 'UTF-8');
                        $zip->addFile(Storage::disk('public')->path($processedPath), $cleanName . '.jpg');
                    } catch (\Exception $e) {
                        // Log error
                    }
                }
                $zip->close();

                $this->batch->update(['is_processing' => false]);
                $this->success("Traitement terminé ! Le téléchargement va démarrer.");
                $this->settingsModal = false;

                return response()->download($fullZipPath)->deleteFileAfterSend(true);
            } else {
                $this->error("Impossible de créer le ZIP.");
            }
        }



        public function addRequest(): void
        {
            $this->validate([
                'addingName'      => 'required|string|max:255',
                'addingCode'      => 'required|string|max:50',
                'addingClassroom' => 'required|string|max:50',
                'addingPhoto'     => 'nullable|image|max:10240',
            ]);

            $path = null;
            if ($this->addingPhoto) {
                $path = $this->addingPhoto->store("corrections/{$this->batch->id}/originals", 'public');
            }

            CorrectionRequest::create([
                'correction_batch_id' => $this->batch->id,
                'full_name'           => trim($this->addingName),
                'establishment_code'  => trim($this->addingCode),
                'classroom_name'      => trim($this->addingClassroom),
                'original_photo_path' => $path,
            ]);

            $this->success('Demande ajoutée avec succès.');
            $this->addModal = false;
            $this->reset('addingName', 'addingCode', 'addingClassroom', 'addingPhoto');
        }

        public function editRequest(CorrectionRequest $req): void
        {
            $this->editingRequest = $req;
            $this->editingName = $req->full_name;
            $this->editingCode = $req->establishment_code;
            $this->editingClassroom = $req->classroom_name;
            $this->editModal = true;
        }

        public function updateRequest(): void
        {
            $this->validate([
                'editingName'      => 'required|string|max:255',
                'editingCode'      => 'required|string|max:50',
                'editingClassroom' => 'required|string|max:50',
            ]);

            $this->editingRequest->update([
                'full_name'          => trim($this->editingName),
                'establishment_code' => trim($this->editingCode),
                'classroom_name'     => trim($this->editingClassroom),
            ]);

            $this->success('Demande modifiée avec succès.');
            $this->editModal = false;
            $this->editingRequest = null;
            $this->reset('editingName', 'editingCode', 'editingClassroom');
        }

        public function openSingleProcess(CorrectionRequest $req): void
        {
            if (!$req->original_photo_path) {
                $this->error("Cette demande n'a pas de photo.");
                return;
            }
            $this->processingRequestId = $req->id;
            $this->singleProcessModal = true;
        }

        public function processSingleRequest(): mixed
        {
            $req = CorrectionRequest::findOrFail($this->processingRequestId);

            try {
                $originalPath = Storage::disk('public')->path($req->original_photo_path);

                if (!file_exists($originalPath)) {
                    $this->error("Fichier photo introuvable.");
                    return null;
                }

                $imageService = app(\App\Services\ImageProcessingService::class);
                $options = [
                    'color'     => $this->textColor,
                    'photoSize' => $this->fontSize,
                    'dateSize'  => $this->dateSize,
                ];

                $data = [
                    'name'      => $req->full_name,
                    'classroom' => $req->classroom_name,
                    'code'      => $req->establishment_code,
                ];

                $processedPath = $imageService->processStudentCard($originalPath, $data, $options);
                $req->update(['processed_photo_path' => $processedPath]);

                $fullPath = Storage::disk('public')->path($processedPath);
                $cleanName = mb_strtoupper($req->full_name, 'UTF-8') . '_' . mb_strtoupper($req->establishment_code, 'UTF-8');

                $this->success('Traitement terminé ! Téléchargement en cours...');
                $this->singleProcessModal = false;

                return response()->download($fullPath, $cleanName . '.jpg');
            } catch (\Exception $e) {
                $this->error('Erreur lors du traitement : ' . $e->getMessage());
                return null;
            }
        }

        public function deleteRequest(CorrectionRequest $req)
        {
            $req->delete();
            $this->success("Demande supprimée.");
        }

        public function with(): array
        {
            return [
                'requests' => $this->batch->requests()->paginate(50),
                'headers' => [
                    ['key' => 'row_number', 'label' => '#', 'sortable' => false],
                    ['key' => 'photo', 'label' => 'Photo', 'sortable' => false],
                    ['key' => 'full_name', 'label' => 'Nom'],
                    ['key' => 'establishment_code', 'label' => 'Code'],
                    ['key' => 'classroom_name', 'label' => 'Classe'],
                    ['key' => 'actions', 'label' => 'Actions', 'sortable' => false],
                ]
            ];
        }
    }; ?>

<div>
    <x-breadcrumbs :items="$breadcrumbs" class="mb-4" />

    <x-header :title="$batch->name" separator>
        <x-slot:actions>
            <x-button label="Ajouter" icon="o-plus" class="btn-primary" @click="$wire.addModal = true" />
            <x-button label="Paramètres et Traitement" icon="o-cog-6-tooth" class="btn-warning" @click="$wire.settingsModal = true" />
            <x-button label="Upload Photos" icon="o-camera" class="btn-info" @click="$wire.photoDrawer = true" />
        </x-slot:actions>
    </x-header>

    <div class="grid lg:grid-cols-4 gap-6 mb-6">
        <!-- Stats -->
        <x-stat title="Demandes" :value="$batch->requests()->count()" icon="o-users" />
        <x-stat title="Photos" :value="$batch->requests()->whereNotNull('original_photo_path')->count()" icon="o-photo" />

        <!-- Import CSV -->
        <div class="lg:col-span-2">
            <x-card title="Importer CSV" shadow separator>
                <div
                    x-data="{ uploading: false }"
                    x-on:livewire-upload-start.window="uploading = true"
                    x-on:livewire-upload-finish.window="uploading = false"
                    x-on:livewire-upload-error.window="uploading = false">
                    <div class="flex gap-4 items-center">
                        <x-file wire:model="csvFile" accept=".csv,.xlsx,.txt" class="w-full" />
                        <x-button
                            label="Importer"
                            class="btn-primary"
                            wire:click="importCsv"
                            spinner="importCsv"
                            x-bind:disabled="uploading"
                            wire:loading.attr="disabled"
                            wire:target="csvFile" />
                    </div>
                    <div x-show="uploading" x-cloak class="flex items-center gap-2 text-sm text-blue-600 mt-2">
                        <span>Upload en cours…</span>
                    </div>
                    <div class="text-xs text-gray-400 mt-2">
                        Colonnes requises : <strong>Nom, Code, Classe</strong>
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    <x-card title="Liste des demandes">
        <x-table :headers="$headers" :rows="$requests" striped with-pagination>
            @scope('cell_row_number', $req, $requests)
            {{ $requests->firstItem() + $loop->index }}
            @endscope
            @scope('cell_photo', $req)
            @if($req->original_photo_path)
            <div class="avatar">
                <div class="w-12 rounded">
                    <img src="{{ asset('storage/' . $req->original_photo_path) }}?v={{ $req->updated_at->timestamp }}" />
                </div>
            </div>
            @else
            <span class="opacity-50">En attente</span>
            @endif
            @endscope
            @scope('cell_actions', $req)
            <div class="flex gap-1">
                @if($req->original_photo_path)
                <x-button icon="o-sparkles" class="btn-sm btn-ghost text-primary" wire:click="openSingleProcess({{ $req->id }})" tooltip="Traiter" spinner />
                @endif
                <x-button icon="o-pencil" class="btn-sm btn-ghost" wire:click="editRequest({{ $req->id }})" tooltip="Modifier" spinner />
                <x-button icon="o-trash" class="btn-sm btn-ghost text-error" wire:click="deleteRequest({{ $req->id }})" wire:confirm="Supprimer ?" spinner />
            </div>
            @endscope
        </x-table>
    </x-card>

    {{-- Drawer Photos --}}
    <x-drawer wire:model="photoDrawer" title="Upload Photos" class="w-11/12 lg:w-1/3" right>
        <div
            x-data="{ uploading: false }"
            x-on:livewire-upload-start.window="uploading = true"
            x-on:livewire-upload-finish.window="uploading = false"
            x-on:livewire-upload-error.window="uploading = false">
            <x-alert icon="o-information-circle" class="alert-info text-sm mb-4">
                L'ordre d'upload doit correspondre à l'ordre de la liste (ID croissant).
            </x-alert>

            <x-form wire:submit="savePhotos">
                <x-file label="Sélectionner les photos" wire:model="photos" multiple accept="image/*" />

                <div x-show="uploading" x-cloak class="flex items-center gap-2 text-sm text-blue-600 mt-2">
                    <span>Upload en cours…</span>
                </div>

                @if($photos)
                <p class="mt-2 font-bold">{{ count($photos) }} photos sélectionnées.</p>
                @endif
                <x-slot:actions>
                    <x-button label="Annuler" @click="$wire.photoDrawer = false" />
                    <x-button label="Envoyer" class="btn-primary" type="submit" spinner="savePhotos" x-bind:disabled="uploading" />
                </x-slot:actions>
            </x-form>
        </div>
    </x-drawer>

    {{-- Modal Paramètres Traitement en Lot --}}
    <x-modal wire:model="settingsModal" title="Paramètres de Traitement en Lot">
        <x-form wire:submit="processBatch">
            <div class="grid grid-cols-2 gap-4">
                <x-input label="Taille Texte" wire:model="fontSize" type="number" />
                <x-input label="Taille Date" wire:model="dateSize" type="number" />
                <x-colorpicker label="Couleur Texte" wire:model="textColor" />
            </div>
            <x-slot:actions>
                <x-button label="Annuler" @click="$wire.settingsModal = false" />
                <x-button label="Lancer" class="btn-primary" type="submit" spinner="processBatch" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal Traitement Unitaire --}}
    <x-modal wire:model="singleProcessModal" title="Traitement Unitaire">
        <x-form wire:submit="processSingleRequest">
            <div class="grid grid-cols-2 gap-4">
                <x-input label="Taille Texte" wire:model="fontSize" type="number" />
                <x-input label="Taille Date" wire:model="dateSize" type="number" />
                <x-colorpicker label="Couleur Texte" wire:model="textColor" />
            </div>
            <x-slot:actions>
                <x-button label="Annuler" @click="$wire.singleProcessModal = false" />
                <x-button label="Lancer" class="btn-primary" type="submit" spinner="processSingleRequest" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal Ajouter une demande --}}
    <x-modal wire:model="addModal" title="Ajouter une demande">
        <div
            x-data="{ uploading: false }"
            x-on:livewire-upload-start.window="uploading = true"
            x-on:livewire-upload-finish.window="uploading = false"
            x-on:livewire-upload-error.window="uploading = false">
            <x-form wire:submit="addRequest">
                <div class="space-y-4">
                    <x-input label="Nom complet" wire:model="addingName" placeholder="Prénom NOM" required />
                    <x-input label="Code établissement" wire:model="addingCode" placeholder="EX: CR 1234" required />
                    <x-input label="Classe" wire:model="addingClassroom" placeholder="EX: 6ème A" required />

                    <x-file label="Photo (optionnelle)" wire:model="addingPhoto" accept="image/*" />

                    <div x-show="uploading" x-cloak class="flex items-center gap-2 text-sm text-blue-600">
                        <span>Upload en cours…</span>
                    </div>

                    @if($addingPhoto)
                    <div class="p-2 border rounded-lg bg-gray-50 flex items-center gap-4">
                        <img src="{{ $addingPhoto->temporaryUrl() }}" class="w-20 h-20 object-cover rounded shadow" />
                        <p class="text-xs text-gray-500">Prêt à enregistrer</p>
                    </div>
                    @endif
                </div>
                <x-slot:actions>
                    <x-button label="Annuler" @click="$wire.addModal = false" />
                    <x-button
                        label="Ajouter"
                        class="btn-primary"
                        type="submit"
                        spinner="addRequest"
                        x-bind:disabled="uploading"
                        wire:loading.attr="disabled"
                        wire:target="addingPhoto" />
                </x-slot:actions>
            </x-form>
        </div>
    </x-modal>

    {{-- Modal Modifier une demande --}}
    <x-modal wire:model="editModal" title="Modifier la demande">
        @if($editingRequest)
        <x-form wire:submit="updateRequest">
            <div class="space-y-4">
                <x-input label="Nom complet" wire:model="editingName" required />
                <x-input label="Code établissement" wire:model="editingCode" required />
                <x-input label="Classe" wire:model="editingClassroom" required />
            </div>
            <x-slot:actions>
                <x-button label="Annuler" @click="$wire.editModal = false" />
                <x-button label="Enregistrer" class="btn-primary" type="submit" spinner="updateRequest" />
            </x-slot:actions>
        </x-form>
        @endif
    </x-modal>
</div>
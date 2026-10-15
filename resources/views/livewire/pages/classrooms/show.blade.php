<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Classroom;
use App\Models\Student;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use Mary\Traits\Toast;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\On; // Ajout nécessaire pour l'attribut #[On]
use Livewire\Attributes\Title;

new
    #[Title('Classe detail')]
    class extends Component {
        use WithFileUploads, Toast, WithPagination;

        // --- PROPRIÉTÉS ---

        // Breadcrumbs
        public array $breadcrumbs = [];
        public Classroom $classroom; // Déclarer la propriété classroom explicitement

        // UI States
        public bool $importModal = false;
        public bool $photoDrawer = false;
        public string $search = '';

        // Uploads
        #[Validate('required|file|mimes:csv,xlsx')]
        public $csvFile;

        #[Validate(['photos.*' => 'image|max:10240'])] // 10MB max per photo
        public $photos = [];

        // Batch Settings
        public bool $settingsModal = false;
        public int $fontSize = 55;
        public int $dateSize = 55;
        public string $textColor = '#000000';

        // Edit Student
        public bool $addModal = false;
        public bool $editModal = false;
        public ?Student $editingStudent = null;
        public string $editingName = '';
        public string $addingName = '';

        #[Validate('nullable|image|max:10240')]
        public $addingPhoto = null;

        // Single Student Processing
        public bool $singleProcessModal = false;
        public ?Student $targetStudent = null;
        public int $singleFontSize = 55;
        public int $singleDateSize = 55;
        public string $singleTextColor = '#000000';

        // --- MÉTHODES ---

        public function mount(Classroom $classroom)
        {
            $this->classroom = $classroom;
            $this->breadcrumbs = [
                ['label' => 'Accueil', 'link' => '/'],
                ['label' => 'Classes', 'link' => route('classrooms.index')],
                ['label' => $classroom->name],
            ];
        }

        public function headers(): array
        {
            return [
                ['key' => 'row_number', 'label' => '#', 'class' => 'w-1', 'sortable' => false],
                ['key' => 'original_photo_path', 'label' => 'Photo', 'sortable' => false],
                ['key' => 'full_name', 'label' => 'Nom Complet'],
                ['key' => 'actions', 'label' => 'Actions', 'sortable' => false],
            ];
        }

        public function importCsv()
        {
            $this->validate(['csvFile' => 'required|file|mimes:csv,xlsx']);
            $path = $this->csvFile->getRealPath();
            $establishmentCode = $this->classroom->establishment->code;
            $count = 0;

            try {
                SimpleExcelReader::create($path)
                    ->getRows()
                    ->each(function (array $row) use (&$count, $establishmentCode) {
                        // Normalization + nettoyage des espaces
                        $name = trim($row['Nom'] ?? $row['nom'] ?? $row['Name'] ?? $row['name'] ?? '');
                        if ($name !== '') {
                            Student::create([
                                'classroom_id' => $this->classroom->id,
                                'full_name' => $name,
                            ]);
                            $count++;
                        }
                    });

                $this->success("$count élèves importés avec succès.");
                $this->importModal = false;
                $this->reset('csvFile');
            } catch (\Exception $e) {
                $this->error("Erreur lors de l'import : " . $e->getMessage());
            }
        }

        public function savePhotos()
        {
            $this->validate(['photos.*' => 'image|max:10240']);

            // Get students ordered by ID (creation order)
            $students = $this->classroom->students()->orderBy('id')->get();
            $photosCount = count($this->photos);

            Log::info("SavePhotos called", ['count' => $photosCount]);
            $studentsCount = $students->count();

            if ($photosCount === 0) {
                Log::warning("SavePhotos: No photos in array.");
                $this->warning("Aucune photo sélectionnée.");
                return;
            }

            // Warning if mismatch
            if ($photosCount !== $studentsCount) {
                $this->warning("Attention : $photosCount photos pour $studentsCount élèves.");
            }

            $updated = 0;
            foreach ($this->photos as $index => $photo) {
                // Match by index
                if (isset($students[$index])) {
                    $student = $students[$index];
                    // Store photo: students/{classroom_id}/originals
                    $path = $photo->store("students/{$this->classroom->id}/originals", 'public');

                    $student->update([
                        'original_photo_path' => $path,
                    ]);
                    $updated++;
                }
            }

            $this->success("$updated photos assignées.");
            $this->photoDrawer = false;
            $this->reset('photos');
        }

        public function resetClassroom()
        {
            // Delete all students and their photos
            $students = $this->classroom->students;
            foreach ($students as $student) {
                if ($student->original_photo_path) {
                    Storage::disk('public')->delete($student->original_photo_path);
                }
                if ($student->processed_photo_path) {
                    Storage::disk('public')->delete($student->processed_photo_path);
                }
                $student->delete();
            }

            // Clean ZIP
            if ($this->classroom->zip_path) {
                Storage::disk('public')->delete($this->classroom->zip_path);
                $this->classroom->update(['zip_path' => null]);
            }

            $this->success("Classe vidée complétement.");
        }

        public function processBatch()
        {
            $imageService = app(\App\Services\ImageProcessingService::class);

            Log::info("ProcessBatch triggered manually", [
                'classroom_id' => $this->classroom->id,
                'settings' => [
                    'fontSize' => $this->fontSize,
                    'dateSize' => $this->dateSize,
                    'textColor' => $this->textColor
                ]
            ]);

            $students = Student::where('classroom_id', $this->classroom->id)
                ->whereNotNull('original_photo_path')
                ->get();

            Log::info("Students found in manual query", ['count' => $students->count()]);

            if ($students->count() === 0) {
                $this->error("Aucun élève avec photo à traiter.");
                return;
            }

            set_time_limit(300);

            // Prepare ZIP with sanitized establishment_classroom naming
            $establishmentName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', mb_strtoupper($this->classroom->establishment->name, 'UTF-8'));
            $classroomName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', mb_strtoupper($this->classroom->name, 'UTF-8'));
            $zipFileName = "{$establishmentName}_{$classroomName}.zip";
            $zipPath = 'downloads/' . $zipFileName;

            Storage::disk('public')->makeDirectory('downloads');
            $fullZipPath = Storage::disk('public')->path($zipPath);

            $zip = new \ZipArchive;

            if ($zip->open($fullZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                foreach ($students as $student) {
                    try {
                        $originalPath = Storage::disk('public')->path($student->original_photo_path);
                        if (!file_exists($originalPath)) continue;

                        $data = [
                            'name' => $student->full_name,
                            'classroom' => $this->classroom->name,
                            'code' => $this->classroom->establishment->code ?? ''
                        ];

                        $options = [
                            'photoSize' => $this->fontSize,
                            'dateSize' => $this->dateSize,
                            'color' => $this->textColor
                        ];

                        $processedPath = $imageService->processStudentCard($originalPath, $data, $options);
                        $student->update(['processed_photo_path' => $processedPath]);

                        $cleanName = str_replace(['/', '\\'], '-', mb_strtoupper($student->full_name, 'UTF-8'));
                        // Add from Storage path
                        $zip->addFile(Storage::disk('public')->path($processedPath), $cleanName . '.jpg');
                    } catch (\Exception $e) {
                        Log::error("Error processing student {$student->id}: " . $e->getMessage());
                    }
                }
                $zip->close();

                if (!Storage::disk('public')->exists($zipPath)) {
                    $this->error("Erreur : Le fichier ZIP n'a pas été créé.");
                    return;
                }

                $this->success("Traitement terminé ! Téléchargement lancé.");
                $this->settingsModal = false;

                return response()->download($fullZipPath)->deleteFileAfterSend(true);
            } else {
                $this->error("Impossible d'ouvrir le fichier ZIP.");
            }
        }

        public function renamePhotos()
        {
            $students = Student::where('classroom_id', $this->classroom->id)
                ->whereNotNull('original_photo_path')
                ->get();

            if ($students->count() === 0) {
                $this->error("Aucun élève avec photo à renommer.");
                return;
            }

            set_time_limit(300);

            // Prepare ZIP
            $establishmentName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', mb_strtoupper($this->classroom->establishment->name, 'UTF-8'));
            $classroomName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', mb_strtoupper($this->classroom->name, 'UTF-8'));
            $zipFileName = "(R) {$establishmentName}_{$classroomName}.zip";
            $zipPath = 'downloads/' . $zipFileName;

            Storage::disk('public')->makeDirectory('downloads');
            $fullZipPath = Storage::disk('public')->path($zipPath);

            $zip = new \ZipArchive;

            if ($zip->open($fullZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
                foreach ($students as $student) {
                    try {
                        $originalPath = Storage::disk('public')->path($student->original_photo_path);
                        if (!file_exists($originalPath)) continue;

                        // Just rename, no processing
                        $cleanName = str_replace(['/', '\\'], '-', mb_strtoupper($student->full_name, 'UTF-8'));
                        $zip->addFile($originalPath, $cleanName . '.jpg');
                    } catch (\Exception $e) {
                        Log::error("Error renaming photo for student {$student->id}: " . $e->getMessage());
                    }
                }
                $zip->close();

                if (!Storage::disk('public')->exists($zipPath)) {
                    $this->error("Erreur : Le fichier ZIP n'a pas été créé.");
                    return;
                }

                $this->success("Photos renommées ! Téléchargement lancé.");
                return response()->download($fullZipPath)->deleteFileAfterSend(true);
            } else {
                $this->error("Impossible d'ouvrir le fichier ZIP.");
            }
        }



        public function editStudent(Student $student)
        {
            $this->editingStudent = $student;
            $this->editingName = $student->full_name;
            $this->editModal = true;
        }

        public function openSingleProcess(Student $student)
        {
            $this->targetStudent = $student;
            $this->singleFontSize = $this->fontSize;
            $this->singleDateSize = $this->dateSize;
            $this->singleTextColor = $this->textColor;
            $this->singleProcessModal = true;
        }

        public function processSingleStudent()
        {
            if (!$this->targetStudent || !$this->targetStudent->original_photo_path) {
                $this->error("Aucune photo à traiter.");
                return;
            }

            try {
                $imageService = app(\App\Services\ImageProcessingService::class);
                $originalPath = Storage::disk('public')->path($this->targetStudent->original_photo_path);

                if (!file_exists($originalPath)) {
                    $this->error("Fichier original introuvable.");
                    return;
                }

                $data = [
                    'name' => $this->targetStudent->full_name,
                    'classroom' => $this->classroom->name,
                    'code' => $this->classroom->establishment->code ?? ''
                ];

                $options = [
                    'photoSize' => $this->singleFontSize,
                    'dateSize' => $this->singleDateSize,
                    'color' => $this->singleTextColor
                ];

                $processedPath = $imageService->processStudentCard($originalPath, $data, $options);

                $this->targetStudent->update(['processed_photo_path' => $processedPath]);
                $this->success("Photo traitée avec succès !");
                $this->singleProcessModal = false;

                return Storage::disk('public')->download($processedPath, mb_strtoupper($this->targetStudent->full_name, 'UTF-8') . '.jpg');
            } catch (\Exception $e) {
                $this->error("Erreur : " . $e->getMessage());
            }
        }

        public function updateStudent()
        {
            if (!$this->editingStudent) return;

            $this->validate([
                'editingName' => 'required|string|max:255',
            ]);

            $this->editingStudent->full_name = $this->editingName;
            $this->editingStudent->save();
            $this->editingStudent->refresh();

            $this->success("Élève modifié avec succès.");
            $this->editModal = false;
            $this->editingStudent = null;
            $this->dispatch('student-updated');
        }

        public function addStudent()
        {
            Log::info("addStudent called", ['addingPhoto_exists' => $this->addingPhoto ? 'yes' : 'no']);

            $this->validate([
                'addingName' => 'required|string|max:255',
                'addingPhoto' => 'image|max:10240|nullable'
            ]);

            $path = null;
            if ($this->addingPhoto) {
                $path = $this->addingPhoto->store("students/{$this->classroom->id}/originals", 'public');
                Log::info("Photo saved to path", ['path' => $path]);
            } else {
                Log::warning("No addingPhoto received.");
            }

            $student = Student::create([
                'classroom_id' => $this->classroom->id,
                'full_name' => $this->addingName,
                'original_photo_path' => $path
            ]);

            Log::info("Student created", ['id' => $student->id, 'path' => $student->original_photo_path]);

            $this->success("Élève ajouté avec succès.");
            $this->addModal = false;
            $this->addingName = '';
            $this->addingPhoto = null;
            $this->classroom->refresh();

            // Dispatch event to refresh the table and photos if needed
            $this->dispatch('student-updated');
        }

        public function deleteStudent(Student $student)
        {
            if ($student->original_photo_path) {
                Storage::disk('public')->delete($student->original_photo_path);
            }
            $student->delete();
            $this->success("Élève supprimé.");
        }

        public function with(): array
        {
            return [
                'students' => $this->classroom->students()
                    ->where(function ($q) {
                        $q->where('full_name', 'like', "%{$this->search}%");
                    })
                    ->orderBy('id', 'asc') // Tri ascendant pour que les nouveaux soient à la fin
                    ->paginate(50),
                'headers' => $this->headers(),
            ];
        }
    }; ?>

{{-- DÉBUT DE LA VUE HTML --}}
<div>
    <x-breadcrumbs :items="$breadcrumbs" class="mb-4" />

    <x-header :title="$classroom->name" :subtitle="$classroom->establishment->name . ' - ' . $classroom->schoolYear->name" separator>
        <x-slot:middle class="!justify-end items-center">
            <div class="flex flex-row items-center gap-2">
                <x-input icon="o-magnifying-glass" wire:model.live.debounce="search" placeholder="Rechercher..." />
                <x-button label="Ajouter" icon="o-plus" class="btn-primary" @click="$wire.addModal = true" />
            </div>
        </x-slot:middle>
        <x-slot:actions>
            {{-- Action Buttons --}}
            <x-button label="1. Importer CSV" icon="o-document-arrow-up" class="btn-outline" @click="$wire.importModal = true" :disabled="$classroom->is_processing" />
            <x-button label="2. Photos" icon="o-photo" class="btn-outline" @click="$wire.photoDrawer = true" :disabled="$classroom->is_processing" />

            <x-button label="Configurer & Traiter" icon="o-cog-6-tooth" class="btn-primary" @click="$wire.settingsModal = true" />

            <x-button label="Renommer" icon="o-tag" class="btn-outline" wire:click="renamePhotos" tooltip="Télécharger les photos renommées" />
            <x-button icon="o-trash" class="btn-error text-white" wire:click="resetClassroom" wire:confirm="Tout supprimer ?" tooltip="Vider la classe" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <div class="mb-4 text-sm text-gray-500">
            Total élèves : {{ $students->total() }} | Photos assignées : {{ $classroom->students()->whereNotNull('original_photo_path')->count() }}
        </div>

        <x-table :headers="$headers" :rows="$students" striped with-pagination>
            @scope('cell_row_number', $student, $students)
            {{ $students->firstItem() + $loop->index }}
            @endscope

            @scope('cell_original_photo_path', $student)
            <div wire:key="photo-{{ $student->id }}"
                x-data="{
                    src: '{{ asset('storage/' . ($student->original_photo_path ?? '')) }}?v={{ $student->updated_at?->timestamp ?? time() }}',
                    hasPhoto: {{ $student->original_photo_path ? 'true' : 'false' }}
                 }"
                x-init="
                    Livewire.on('student-updated', () => {
                        src = src.split('?')[0] + '?v=' + Date.now();
                    })
                 ">
                <img x-show="hasPhoto" :src="src" class="h-12 w-12 rounded object-cover" />
                <span x-show="!hasPhoto" class="text-gray-400 italic">Vide</span>
            </div>
            @endscope

            @scope('cell_actions', $student)
            <x-button icon="o-sparkles" class="btn-sm btn-ghost text-primary" wire:click="openSingleProcess({{ $student->id }})" tooltip="Traiter seul" spinner />
            <x-button icon="o-pencil" class="btn-sm btn-ghost" wire:click="editStudent({{ $student->id }})" tooltip="Modifier" spinner />
            <x-button icon="o-trash" class="btn-sm btn-ghost text-error" wire:click="deleteStudent({{ $student->id }})" tooltip="Supprimer" wire:confirm="Supprimer cet élève ?" spinner />
            @endscope
        </x-table>
    </x-card>

    {{-- Modal Import CSV --}}
    <x-modal wire:model="importModal" title="Importer la liste (CSV/Excel)">
        <div
            x-data="{ uploading: false }"
            x-on:livewire-upload-start.window="uploading = true"
            x-on:livewire-upload-finish.window="uploading = false"
            x-on:livewire-upload-error.window="uploading = false"
        >
            <div class="mb-4">
                <p>Le fichier doit contenir une colonne : <strong>Nom</strong>.</p>
                <p class="text-xs text-gray-500 mt-1">Le code de l'établissement ({{ $classroom->establishment->code }}) sera automatiquement assigné.</p>
            </div>
            <x-form wire:submit="importCsv">
                <x-file label="Fichier Élèves" wire:model="csvFile" accept=".csv, .xlsx" />

                <div x-show="uploading" x-cloak class="flex items-center gap-2 text-sm text-blue-600 mt-2">
                    <span>Upload en cours…</span>
                </div>

                <x-slot:actions>
                    <x-button label="Annuler" @click="$wire.importModal = false" />
                    <x-button label="Importer" class="btn-primary" type="submit" spinner="importCsv" x-bind:disabled="uploading" />
                </x-slot:actions>
            </x-form>
        </div>
    </x-modal>

    {{-- Modal Settings & Process --}}
    <x-modal wire:model="settingsModal" title="Paramètres de Traitement">
        <div class="mb-4">
            <x-alert icon="o-information-circle" class="alert-info text-sm">
                Ajustez la taille du texte si les noms sont trop longs.
            </x-alert>
        </div>
        <x-form wire:submit="processBatch">
            <div class="grid grid-cols-2 gap-4">
                <x-input label="Taille Texte (Noms)" wire:model="fontSize" type="number" min="10" max="100" />
                <x-input label="Taille Date" wire:model="dateSize" type="number" min="10" max="100" />
                <x-colorpicker label="Couleur Texte" wire:model="textColor" />
            </div>
            <x-slot:actions>
                <x-button label="Annuler" @click="$wire.settingsModal = false" />
                <x-button label="Lancer le Traitement" class="btn-primary" type="submit" spinner="processBatch" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Modal Traitement Individuel --}}
    <x-modal wire:model="singleProcessModal" title="Traitement Individuel">
        @if($targetStudent)
        <div class="mb-4">
            <p class="font-bold text-lg text-primary">{{ $targetStudent->full_name }}</p>
            <p class="text-xs text-gray-500">Configuration spécifique pour cet élève</p>
        </div>
        <x-form wire:submit="processSingleStudent">
            <div class="grid grid-cols-2 gap-4">
                <x-input label="Taille Nom" wire:model="singleFontSize" type="number" min="10" max="100" />
                <x-input label="Taille Date" wire:model="singleDateSize" type="number" min="10" max="100" />
            </div>
            <div class="mt-4">
                <x-colorpicker label="Couleur du texte" wire:model="singleTextColor" icon="o-swatch" />
            </div>
            <div class="mt-6 p-4 bg-gray-50 rounded-lg flex items-center gap-4">
                @if($targetStudent->original_photo_path)
                <img src="{{ asset('storage/' . $targetStudent->original_photo_path) }}" class="w-16 h-16 object-cover rounded shadow" />
                @endif
                <div>
                    <p class="text-sm font-semibold">Aperçu direct</p>
                    <p class="text-xs text-gray-500">Applique les réglages ci-dessus</p>
                </div>
            </div>
            <x-slot:actions>
                <x-button label="Annuler" @click="$wire.singleProcessModal = false" />
                <x-button label="Lancer & Télécharger" icon="o-cpu-chip" class="btn-primary" type="submit" spinner="processSingleStudent" />
            </x-slot:actions>
        </x-form>
        @endif
    </x-modal>

    {{-- Modal Edit Student --}}
    <x-modal wire:model="editModal" title="Modifier l'élève">
        @if($editingStudent)
        <x-form wire:submit="updateStudent">
            <x-input label="Nom Complet" wire:model="editingName" required />

            <x-slot:actions>
                <x-button label="Annuler" @click="$wire.editModal = false" />
                <x-button label="Enregistrer" class="btn-primary" type="submit" spinner="updateStudent" />
            </x-slot:actions>
        </x-form>
        @endif
    </x-modal>

    {{-- Modal Add Student --}}

    <x-modal wire:model="addModal" title="Ajouter un élève">
        <div
            x-data="{ uploading: false }"
            x-on:livewire-upload-start.window="uploading = true"
            x-on:livewire-upload-finish.window="uploading = false"
            x-on:livewire-upload-error.window="uploading = false">
            <x-form wire:submit="addStudent">
                <x-input label="Nom Complet" wire:model="addingName" required />

                <div class="space-y-4">
                    <x-file
                        label="Photo de l'élève"
                        wire:model="addingPhoto"
                        accept="image/*" />

                    {{-- Indicateur de chargement --}}
                    <div x-show="uploading" x-cloak class="flex items-center gap-2 text-sm text-blue-600">
                        <span>Upload en cours…</span>
                    </div>

                    @if($addingPhoto)
                    <div class="p-2 border rounded-lg bg-gray-50 flex items-center gap-4">
                        <img src="{{ $addingPhoto->temporaryUrl() }}" class="w-20 h-20 object-cover rounded shadow" />
                        <div>
                            <p class="text-xs font-semibold">Prévisualisation</p>
                            <p class="text-[10px] text-gray-500">Prêt à être enregistré</p>
                        </div>
                    </div>
                    @endif
                </div>

                <x-slot:actions>
                    <x-button label="Annuler" @click="$wire.addModal = false" />
                    <x-button
                        label="Ajouter"
                        class="btn-primary"
                        type="submit"
                        spinner="addStudent"
                        x-bind:disabled="uploading" />
                </x-slot:actions>
            </x-form>
        </div>
    </x-modal>

    {{-- Drawer Photos --}}
    <x-drawer wire:model="photoDrawer" title="Upload des Photos" class="w-11/12 lg:w-1/3" right>
        <div
            x-data="{ uploading: false, count: 0 }"
            x-on:livewire-upload-start.window="uploading = true"
            x-on:livewire-upload-finish.window="uploading = false"
            x-on:livewire-upload-error.window="uploading = false"
        >
            <div class="mb-4">
                <x-alert icon="o-information-circle" class="alert-info text-sm">
                    L'ordre d'upload est <strong>STRICT</strong>. La 1ère photo ira sur le 1er élève de la liste (ID croissant). Vérifiez bien votre sélection !
                </x-alert>
            </div>
            <x-form wire:submit="savePhotos">
                <x-file label="Sélectionner les photos" wire:model="photos" multiple accept="image/*" />

                <div x-show="uploading" x-cloak class="flex items-center gap-2 text-sm text-blue-600 mt-2">
                    <span>Upload en cours…</span>
                </div>

                <div class="mt-4">
                    @if($photos)
                    <p class="font-bold">{{ count($photos) }} photos sélectionnées.</p>
                    @endif
                </div>
                <x-slot:actions>
                    <x-button label="Annuler" @click="$wire.photoDrawer = false" />
                    <x-button label="Envoyer" class="btn-primary" type="submit" spinner="savePhotos" x-bind:disabled="uploading" />
                </x-slot:actions>
            </x-form>
        </div>
    </x-drawer>
</div>
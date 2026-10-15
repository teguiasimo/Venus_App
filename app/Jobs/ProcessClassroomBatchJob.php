<?php

namespace App\Jobs;

use App\Models\Classroom;
use App\Services\ImageProcessingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ProcessClassroomBatchJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Classroom $classroom,
        public array $options = []
    ) {}

    public function handle(ImageProcessingService $imageService): void
    {
        $this->classroom->update(['is_processing' => true]);

        try {
            $students = $this->classroom->students()
                ->whereNotNull('original_photo_path')
                ->get();

            $establishmentName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', mb_strtoupper($this->classroom->establishment->name ?? 'ETABLISSEMENT', 'UTF-8'));
            $classroomName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', mb_strtoupper($this->classroom->name, 'UTF-8'));
            $zipFileName = "{$establishmentName}_{$classroomName}.zip";
            $zipPath = 'downloads/'.$zipFileName;

            Storage::disk('public')->makeDirectory('downloads');
            $fullZipPath = Storage::disk('public')->path($zipPath);

            $zip = new ZipArchive;
            if ($zip->open($fullZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                foreach ($students as $student) {
                    try {
                        $originalPath = Storage::disk('public')->path($student->original_photo_path);

                        if (! file_exists($originalPath)) {
                            continue;
                        }

                        $data = [
                            'name' => $student->full_name,
                            'classroom' => $this->classroom->name,
                            'code' => $this->classroom->establishment->code ?? '',
                        ];

                        $processedPath = $imageService->processStudentCard($originalPath, $data, $this->options);
                        $student->update(['processed_photo_path' => $processedPath]);

                        $cleanName = str_replace(['/', '\\'], '-', mb_strtoupper($student->full_name, 'UTF-8'));
                        $zip->addFile(Storage::disk('public')->path($processedPath), $cleanName.'.jpg');
                    } catch (\Throwable $e) {
                        Log::error("Error processing student {$student->id}: ".$e->getMessage());
                    }
                }

                $zip->close();

                $this->classroom->update([
                    'is_processing' => false,
                    'zip_path' => $zipPath,
                ]);
            } else {
                $this->classroom->update(['is_processing' => false]);
                Log::error("Failed to create ZIP archive at {$fullZipPath}");
            }
        } catch (\Throwable $e) {
            $this->classroom->update(['is_processing' => false]);
            Log::error("Error in ProcessClassroomBatchJob for classroom {$this->classroom->id}: ".$e->getMessage());
            throw $e;
        }
    }
}

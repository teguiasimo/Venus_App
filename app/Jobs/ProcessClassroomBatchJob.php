<?php

namespace App\Jobs;

use App\Services\ImageProcessingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessClassroomBatchJob implements ShouldQueue
{
    use Queueable;

    // ...
    public function handle(ImageProcessingService $imageService): void
    {
        $this->classroom->update(['is_processing' => true]);

        $students = $this->classroom->students()->whereNotNull('original_photo_path')->get();

        // Prepare ZIP inside storage/app/public/downloads
        $zipFileName = 'classe_' . $this->classroom->id . '_' . time() . '.zip';
        $zipPath = 'downloads/' . $zipFileName;

        // Ensure the directory exists in STORAGE (not just public)
        Storage::disk('public')->makeDirectory('downloads');

        // Get absolute path for ZipArchive
        $fullZipPath = Storage::disk('public')->path($zipPath);

        $zip = new \ZipArchive;
        if ($zip->open($fullZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {

            foreach ($students as $student) {
                try {
                    // Process Image
                    $originalPath = public_path('storage/' . $student->original_photo_path);

                    if (! file_exists($originalPath)) {
                        continue;
                    }

                    // Use the service to generate the card
                    $processedPath = $imageService->processStudentCard($originalPath, $student->toArray(), $this->options);

                    // Update Student Record
                    $student->update(['processed_photo_path' => $processedPath]);

                    // Add to ZIP
                    $cleanName = mb_strtoupper($student->last_name . '_' . $student->first_name, 'UTF-8');
                    $zip->addFile(public_path($processedPath), $cleanName . '.jpg');
                } catch (\Exception $e) {
                    Log::error("Error processing student {$student->id}: " . $e->getMessage());
                }
            }

            $zip->close();

            $this->classroom->update([
                'is_processing' => false,
            ]);
        } else {
            $this->classroom->update(['is_processing' => false]);
            Log::error("Failed to create ZIP archive at $fullZipPath");
        }
    }
}

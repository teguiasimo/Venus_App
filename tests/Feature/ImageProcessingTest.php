<?php

use App\Services\ImageProcessingService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('it processes student photo with french accents and special characters without error', function () {
    Storage::fake('public');

    $file = UploadedFile::fake()->image('student.jpg', 600, 800);
    $service = new ImageProcessingService;

    $path = $service->processStudentCard($file, [
        'name' => 'Élève René François',
        'classroom' => '6ème A',
        'code' => 'CR-2026',
    ]);

    expect($path)->toBeString();
    expect(Storage::disk('public')->exists($path))->toBeTrue();
});

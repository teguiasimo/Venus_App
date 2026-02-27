<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageProcessingService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * Process the student photo: overlay text, resize, and save.
     *
     * @param  UploadedFile|string  $photo  The uploaded photo or path.
     * @param  array  $data  Student data (name, classroom, code).
     * @param  array  $options  Configuration options (textColor, fontSize, etc.).
     * @return string The relative path to the saved image in public disk.
     */
    public function processStudentCard($photo, array $data, array $options = []): string
    {
        Log::info('ImageProcessingService: Start processing', ['options' => $options]);

        // Default options
        $textColor = $options['color'] ?? '#000000';
        $baseFontSize = $options['photoSize'] ?? 55;
        $fontPath = public_path('fonts/Roboto-Bold.ttf');

        // Load image
        $image = $this->manager->read($photo);

        // Resize to standard (legacy: 942x1479)
        $image->scale(width: 942, height: 1479);

        $width = $image->width();
        $height = $image->height();

        // Prepare Text
        $line1 = strtoupper($data['name']);
        $line2 = strtoupper($data['classroom'] . '    ' . $data['code']);
        $date = now()->format('d-m-Y');

        // Draw White Rectangle at Bottom (86% to 100%)
        // height * 0.86 = start Y. Height of rect is remaining.
        $rectY = intval($height * 0.86);
        $rectHeight = $height - $rectY;

        $image->drawRectangle(0, $rectY, function ($rectangle) use ($width, $rectHeight) {
            $rectangle->size($width, $rectHeight);
            $rectangle->background('white');
            $rectangle->border('white', 2);
        });

        // Write Date at Top (7%)
        $dateSize = $options['dateSize'] ?? 55;
        $dateY = intval($height * 0.07);
        $image->text($date, $width / 2, $dateY, function ($font) use ($textColor, $fontPath, $dateSize) {
            $font->file($fontPath);
            $font->align('center');
            $font->size($dateSize);
            $font->color($textColor);
        });


        // Positions
        $text1Y = intval($height * 0.92);
        $text2Y = intval($height * 0.96);

        // Line 1: Name
        $image->text($line1, $width / 2, $text1Y, function ($font) use ($textColor, $fontPath, $baseFontSize) {
            $font->file($fontPath);
            $font->size($baseFontSize);
            $font->align('center');
            $font->color($textColor);
        });

        // Line 2: Class + Code
        $image->text($line2, $width / 2, $text2Y, function ($font) use ($textColor, $fontPath, $baseFontSize) {
            $font->file($fontPath);
            $font->size($baseFontSize);
            $font->align('center');
            $font->color($textColor);
        });

        // Save
        $fileName = 'processed_' . Str::random(10) . '.jpg';
        $path = 'photos/' . $fileName;

        Storage::disk('public')->put($path, $image->toJpeg()->toString());

        return $path;
    }
}

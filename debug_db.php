<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Classroom;
use App\Models\Student;

$total = Student::count();
$with_photo = Student::whereNotNull('original_photo_path')->count();
$classrooms = Classroom::withCount(['students' => function ($q) {
    $q->whereNotNull('original_photo_path');
}])->get();

echo "Total Students: $total\n";
echo "Students with photos: $with_photo\n";
echo "Classrooms and their students with photos:\n";
foreach ($classrooms as $c) {
    echo "- Class ID {$c->id} ({$c->name}): {$c->students_count} photos\n";
}

$orphans = Student::whereNull('classroom_id')->whereNotNull('original_photo_path')->count();
echo "Students with photos but NO classroom_id: $orphans\n";

<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "=== SINGLE_STUDENTS TABLE ===\n";
$singleCols = Schema::getColumnListing('single_students');
foreach ($singleCols as $col) {
    echo "- $col\n";
}

echo "\n=== STUDENTS TABLE ===\n";
$studentCols = Schema::getColumnListing('students');
foreach ($studentCols as $col) {
    echo "- $col\n";
}

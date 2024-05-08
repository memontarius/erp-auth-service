<?php

use Illuminate\Support\Facades\Route;


Route::get('/db/reset', function () {
    $seeder = new \Database\Seeders\DatabaseSeeder();
    $seeder->run();
    return 'success';
});

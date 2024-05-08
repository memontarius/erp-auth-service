<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;


Route::get('/db/reset', function () {
    DB::table('users_companies')->truncate();
    DB::table('users_companies_roles')->truncate();
    DB::table('invitations')->truncate();

    $seeder = new \Database\Seeders\DatabaseSeeder();
    $seeder->run();
    return 'success';
});

<?php

use App\Http\Controllers\WellKnownController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Universal Links / App Links verification files
Route::get('/.well-known/apple-app-site-association', [WellKnownController::class, 'appleAppSiteAssociation']);
Route::get('/.well-known/assetlinks.json', [WellKnownController::class, 'assetLinks']);

<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

// ✅ Serve profile photos with CORS applied
Route::get('/profile-photo/{filename}', function ($filename) {
    $path = 'profile_photos/' . $filename;
    
    // Check if file exists
    if (!Storage::disk('public')->exists($path)) {
        abort(404, 'Photo not found');
    }
    
    // Get file contents and mime type
    $file = Storage::disk('public')->get($path);
    $mime = Storage::disk('public')->mimeType($path);
    
    // Return with CORS headers + cache
    return response($file, 200)
        ->header('Content-Type', $mime)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
        ->header('Cache-Control', 'public, max-age=86400');
})->where('filename', '.*');
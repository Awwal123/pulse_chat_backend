<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/debug/php-upload', function () {
    return response()->json([
        'php_ini' => php_ini_loaded_file(),
        'upload_tmp_dir' => ini_get('upload_tmp_dir'),
        'sys_temp_dir' => sys_get_temp_dir(),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'sapi' => php_sapi_name(),
        'temp_writable' => is_writable(sys_get_temp_dir()),
    ]);
});
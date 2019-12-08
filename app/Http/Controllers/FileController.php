<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function __invoke($file_path)
    {
        if (!Storage::disk('public')->exists('simbolos/' . $file_path)) {
            abort(404, 'Arquivo ' . $file_path . ' não existe.');
        }
        
        $local_path = config('filesystems.disks.public.root') . DIRECTORY_SEPARATOR . $file_path;
        
        return response()->file($local_path);
    }
}

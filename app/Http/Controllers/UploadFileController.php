<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadFileController extends Controller
{
    /**
     * Загрузка файлов
     */
    public function upload(Request $req) {
        if ($req->hasFile('image')) {
            $image = $req->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $imageName);
            return 'http://127.0.0.1:8000/uploads/' . $imageName;
        }

        return response()->json([
            'status' => config('app.success_status'),
            'data' => 'Изображение не было загружено',
        ], config('app.success_status'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UploadFileController extends Controller {
    public function upload(Request $req) {
        if ($req->hasFile('image')) {
            $image = $req->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('uploads'), $imageName);
            return $imageName;
        }

        return response()->json(['message' => 'Изображение не было загружено'], 400);
    }
}

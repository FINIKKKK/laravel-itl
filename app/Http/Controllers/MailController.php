<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends BaseController {
    public function sendEmail(Request $request) {
        $data = [
            'subject' => 'Test Title',
            'body' => 'Test Description',
        ];

        try {
            Mail::to('finikdigi@gmail.com')->send(new TestMail($data));
            return response()->json(['Check your email box']);
        } catch (Exception $th) {
            return response()->json(['Something went wrong']);
        }
    }
}

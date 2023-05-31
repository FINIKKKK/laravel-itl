<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller {
    /**
     * Вывод данных
     */
    protected function errorResponse($message): JsonResponse {
        return response()->json([
            'status' => config('app.error_status'),
            'message' => [$message],
        ], config('app.error_status'));
    }

    /**
     * Вывод сообщения
     */
    protected function successResponse($data): JsonResponse {
        return response()->json([
            'status' => config('app.success_status'),
            'data' => $data,
        ], config('app.success_status'));
    }

    /**
     * Вывод ошибок валидации
     */
    protected function validationErrorResponse(Validator $validator): JsonResponse {
        return response()->json([
            'status' => config('app.error_status'),
            'message' => $validator->errors()->all(),
        ], config('app.error_status'));
    }
}

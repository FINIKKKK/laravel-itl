<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Validator;
use Illuminate\Http\JsonResponse;

class BaseController extends Controller {
    /**
     * Вывод данных
     */
    protected function response($data, $isError, $isMessage) {
        // Определяем статус ответа
        if ($isError) {
            $status = config('app.errors.status.error');
        } else {
            $status = config('app.errors.status.success');
        }

        // Если это сообщение
        if ($isMessage) {
            $responseData = [
                'status' => $status,
                'message' => [$data],
            ];
        } // Если это данные
        else {
            $responseData = [
                'status' => $status,
                'data' => $data,
            ];
        }

        // Возвращаем ответ
        return response()->json(
            $responseData,
            $status
        );
    }

    /**
     * Вывод ошибок валидации
     */
    protected function validationErrors(Validator $validator) {
        return response()->json([
            'status' => config('app.errors.status.error'),
            'message' => $validator->errors()->all(),
        ], config('app.errors.status.error'));
    }
}

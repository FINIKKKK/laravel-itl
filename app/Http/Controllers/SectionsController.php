<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Post;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SectionsController extends BaseController {
    /**
     * Создание раздела
     */
    public function create(Request $req) {
        // Проверяем есть ли компанию
        $company = Company::find($req->get('company_id'));
        if (!$company) {
            return $this->response('Компания не найдена', true, true);
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'title' => 'required|string|min:5|max:200',
            'body' => '',
            'company_id' => 'required|integer',
            'parent_id' => 'integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Создаем раздел (касты)
        $section = Section::create([
            'title' => $req->get('title'),
            'body' => json_encode($req->get('body')),
            'user_id' => $req->user()->id,
            'company_id' => $req->get('company_id'),
            'parent_id' => $req->get('parent_id'),
        ]);

        // Возвращаем раздел
        return $this->response($section, false, false);
    }

    /**
     * Получение всех разделов
     */
    public function getAll(Request $req) {
        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'company_id' => 'required|integer',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Получаем список разделов только родительские
        // + Определенной компании
        // + Без поля body
        // + Сортируем по дате (сначала новые)
        $sections = Section::whereNull('parent_id')
            ->where('company_id', $req->get('company_id'))
            ->without('body')
            ->orderBy('created_at', 'desc')
            ->get();

        // Возвращаем список разделов
        return $this->response($sections, false, false);
    }

    /**
     * Получение одного раздела по id
     */
    public function getOne($id) {
        // Проверяем есть ли такой раздел
        $section = Section::with('author:id,firstName,lastName')->with(['parentSection:id,title'])->find($id);
        if (!$section->count()) {
            return $this->response('Раздел не найден', true, true);
        }

        // Конвертируем body у раздела из строки в массив
        $section->body = json_decode($section->body);

        // Получаем дочерние разделы
        $childSections = Section::where('parent_id', $id)->get();
        // Получаем дочерние посты
        $posts = Post::where('section_id', $id)->where('onModeration', false)->get();

        // Создаем поле data и прокидываем данные
        $section->data = [
            'sections' => $childSections,
            'posts' => $posts,
        ];
        // Возвращаем раздел
        return $this->response($section, false, false);
    }

    /**
     * Обновление раздела по id
     */
    public function update(Request $req, $id) {
        // Проверяем есть ли раздел
        $section = Section::find($id);
        if (!$section) {
            return $this->response('Раздел не найден', true, true);
        }

        // Проверяем данные запроса
        $validator = Validator::make($req->all(), [
            'title' => 'string|min:15|max:200',
            'body' => '',
        ]);
        // Прокидываем ошибки, если данные не прошли валидацию
        if ($validator->fails()) {
            return $this->validationErrors($validator);
        }

        // Обновляем раздел
        $section->update([
            'title' => $req->get('title'),
            'body' => $req->get('body'),
        ]);

        // Возвращаем обновленный раздел
        return $this->response($section, false, false);
    }

    /**
     * Удаление раздела по id
     */
    public function delete($id) {
        // Проверяем есть ли раздел
        $section = Section::find($id);
        if (!$section) {
            return $this->response('Раздел не найден', true, true);
        }

        // Удаляем раздел
        $section->delete();

        // Возвращаем сообщение об успешном удалении
        return $this->response('Раздел успешно удален', false, true);
    }
}

<?php


namespace app\models;


class SearchForm extends Page
{
    public function rules()
    {
        return [
            [['url_from', 'category_id'], 'required'],
            [['name'], 'required', 'message' => 'Please enter the name'],
            [['name'], 'unique', 'message' => 'Name must be unique'],
            // Проверяет, что "pageUrl" является корректным URL. Добавляет http:// к атрибуту "pageUrl" если у него нет URI схемы
            ['url_from', 'url', 'defaultScheme' => 'http']
        ];
    }
}
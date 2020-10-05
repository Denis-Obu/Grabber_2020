<?php

namespace app\models;

class GoPageForm extends Page
{
    public $_current_page;
    public $limit;

    public function rules()
    {
        return [
            [['_current_page'], 'integer'],
        ];
    }

}
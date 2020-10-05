<?php


namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

class Category extends ActiveRecord
{
    public static function tableName()
    {
        return 'pg_category';
    }

    public function getCategories()
    {
        return ArrayHelper::map(Category::find()->all(), 'id', 'code');
    }
}
<?php


namespace app\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "lpd_page".
 *
 * @property int $id
 * @property string $name
 * @property string $date_create
 * @property int $category_id
 * @property string|null $url_from
 *
 * @property File[] $lpdFiles
 */
class Page extends ActiveRecord
{
    public $domainFrom;
    private $_current_page;

    public function rules()
    {
        return [
            [['name', 'category_id'], 'required'],
            [['date_create'], 'safe'],
            [['category_id', 'user_id'], 'integer'],
            [['name', 'url_from'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    //задаем имя таблицы в БД
    public static function tableName()
    {
        return 'lpd_page';
    }

    //расчетное поле в модели
    public function afterFind()
    {
        $parse = parse_url($this->url_from);
        $this->domainFrom = $parse['host'];
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }


    /**
     * Gets query for [[Files]].
     *
     * @return ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['page_id' => 'id']);
    }


}
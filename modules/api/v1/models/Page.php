<?php

namespace app\modules\api\v1\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;
use yii\web\Linkable;

/**
 * This is the model class for table "lpd_page_v".
 *
 * @property int $id
 * @property string $name
 * @property string $date_create
 * @property string|null $url_from
 * @property string $category
 * @property string|null $category_descr
 * @property string|null $username
 */
class Page extends ActiveRecord implements Linkable
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lpd_page_v';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'category'], 'required'],
            [['date_create'], 'safe'],
            [['name', 'url_from', 'category_descr'], 'string', 'max' => 255],
            [['category'], 'string', 'max' => 30],
            [['username'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'date_create' => 'Date Create',
            'url_from' => 'Url From',
            'category' => 'Category',
            'category_descr' => 'Category Descr',
            'username' => 'Username',
        ];
    }

    /**
     * Gets query for [[LpdFiles]].
     *
     * @return ActiveQuery
     */
    public function getFiles()
    {
        return $this->hasMany(File::class, ['page_id' => 'id']);
    }

    //список полей для отображения
    public function fields()
    {
        $fields = parent::fields();
        //файлы для страницы
        array_push($fields, 'files');
        return $fields;
    }

    public function formName()
    {
        return '';
    }

    public function getLinks()
    {
        return [
            'arc' => Url::to(['/site/download', 'id' => $this->id], true),
        ];
    }
}

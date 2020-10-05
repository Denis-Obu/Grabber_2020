<?php

namespace app\modules\api\v1\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * This is the model class for table "lpd_file".
 *
 * @property int $id
 * @property int $page_id
 * @property string $file_name
 * @property string|null $file_path
 *
 * @property Page $page
 */
class File extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lpd_file';
    }

    //список полей для отображения
    public function fields()
    {
        return ['file_name', 'link'];
    }

    public function getLink()
    {
        return Url::to(['/site/download-file', 'id' => $this->id], true);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['page_id', 'file_name'], 'required'],
            [['page_id'], 'integer'],
            [['file_name', 'file_path'], 'string', 'max' => 255],
            [['page_id'], 'exist', 'skipOnError' => true, 'targetClass' => Page::class, 'targetAttribute' => ['page_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'page_id' => 'Page ID',
            'file_name' => 'File Name',
            'file_path' => 'File Path',
        ];
    }

    /**
     * Gets query for [[Page]].
     *
     * @return ActiveQuery
     */
    public function getPage()
    {
        return $this->hasOne(Page::class, ['id' => 'page_id']);
    }
}

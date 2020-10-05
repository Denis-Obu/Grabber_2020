<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "lpd_log".
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $date_create
 * @property string|null $action
 * @property string|null $ip
 * @property string|null $user_agent
 * @property string|null $extra_info
 */
class Log extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'lpd_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['date_create'], 'safe'],
            [['action'], 'string', 'max' => 20],
            [['ip'], 'string', 'max' => 16],
            [['user_agent'], 'string', 'max' => 255],
            [['extra_info'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'date_create' => 'Date Create',
            'action' => 'Action',
            'ip' => 'Ip',
            'user_agent' => 'User Agent',
            'extra_info' => 'Extra Info',
        ];
    }
}

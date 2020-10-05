<?php

namespace app\modules\api\v1\models;

use yii\db\ActiveRecord;


class User extends ActiveRecord
{

    //задаем имя таблицы в БД
    public static function tableName()
    {
        return 'lpd_user';
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = User::find()
            ->where(['accessToken' => $token])
            ->one();
        if ($user) {
            return new static($user);
        }
        return null;
    }
}

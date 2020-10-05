<?php

namespace app\models;

use app\components\Logger;
use yii;
use yii\base\NotSupportedException;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;


class User extends ActiveRecord implements IdentityInterface
{

    //задаем имя таблицы в БД
    public static function tableName()
    {
        return 'lpd_user';
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        //return isset(self::$users[$id]) ? new static(self::$users[$id]) : null;
        return static::findOne(['id' => $id, 'is_locked' => 'F']);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        /*  foreach (self::$users as $user) {
              if ($user['accessToken'] === $token) {
                  return new static($user);
              }
          }
  */
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'is_locked' => 'F']);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        $result = Yii::$app->getSecurity()->validatePassword($password, $this->password);
        if (!$result) {
            Logger::writeLog('login', 'invalide');
        }
        return $result;
    }
}

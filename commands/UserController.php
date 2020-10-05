<?php

namespace app\commands;

use app\models\User;
use Throwable;
use yii;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * @author Obuhov D.G.
 */
class UserController extends Controller
{

    public function actionCreate($user, $pass)
    {   //получим хеш для пароля
        $hash = Yii::$app->getSecurity()->generatePasswordHash($pass);

        //пороверим, что такого пользовател еще нет
        $user_ = User::find()->where(['username' => $user])->one();
        if (!empty($user_)) {
            $this->stdout("User=" .$user . " exists\n");
            return ExitCode::OK;
        }

        try {
            $newUser = new User();
            $newUser->username = $user;
            $newUser->password = $hash;
            $newUser->accessToken = $this->generateRandomString();
            $newUser->save();
            $this->stdout("User $user created\nAPI AccessToken: $newUser->accessToken\n");
        } catch (Throwable $e) {
            //контрольный
            //SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'us2' for key 'lpd_user.uk_lpd_user'
            if ($e->getCode() === "23000") {
                $this->stdout("User " . $user . " exists\n");
            } else {
                $this->stdout("Something went wrong...\n");
                $this->stdout($e->getMessage() . "\n\n");
            }
        }
        return ExitCode::OK;
    }

    public function generateRandomString($length = 10)
    {
        //$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
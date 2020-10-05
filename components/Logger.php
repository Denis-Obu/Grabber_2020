<?php

namespace app\components;

use app\models\Log;
use Yii;

class Logger
{
    // записать действия в лог
    public static function writeLog($action, $extra_info = null)
    {
        $log = new Log();
        $log->user_id = Yii::$app->user->id;
        $log->action = $action;
        $log->extra_info = $extra_info;
        $log->ip = Yii::$app->request->getUserIP();
        $log->user_agent = substr(Yii::$app->request->getUserAgent(), 0, 255);
        $log->save();
    }
}
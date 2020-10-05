<?php

namespace app\modules\api\v1;

use yii\base\Module;

/**
 * api module definition class
 */
class Api extends Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\api\v1\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->version = 'v1';
    }
}
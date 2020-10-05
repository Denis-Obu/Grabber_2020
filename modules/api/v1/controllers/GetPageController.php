<?php

namespace app\modules\api\v1\controllers;

use app\components\Logger;
use app\modules\api\v1\models\User;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;
use yii\web\HttpException;
use yii\web\Response;
use yii\web\UnauthorizedHttpException;

class GetPageController extends ActiveController
{
    public $modelClass = 'app\modules\api\v1\models\Page';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator'] = [
            'class' => 'yii\filters\ContentNegotiator',
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                //'application/xml' => Response::FORMAT_XML,
            ]
        ];
        return $behaviors;

    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if (array_key_exists('AccessToken', $params)) {
            if (User::findIdentityByAccessToken($params['AccessToken'])) {
                return true;
            }
        }


        return false;
    }

    /* public function actionView($id)
     {
         //return Page::findOne($id);
         return 'asjsha';
     }
     public function actionIndex($id)
     {
          Yii::error("DEniS!!");
         return "params".$id;//Page::findOne($id);
     }*/
    public function actions()
    {

        $actions = parent::actions();
        //   $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    public function actionGetPage()
    {
        $paginationWord = ['pageSize'];

        if (!empty($_GET)) {
            $params = $_GET;

            if (array_key_exists('AccessToken', $params)) {
                $model = new $this->modelClass;
                if (!$this->checkAccess('GetPage', $model, $params)) {
                    Logger::writeLog('API', 'error: "AccessToken is not found"');
                    throw new UnauthorizedHttpException("AccessToken is not found");
                }
                unset($params['AccessToken']);
            } else {
                Logger::writeLog('API', 'error: "parameter \"AccessToken\" is not set"');
                throw new UnauthorizedHttpException("parameter \"AccessToken\" is not set");
            }


            //параметры пагинации
            $paginationParam = [];
            for ($i = 0; $i < count($paginationWord); $i++) {
                if (array_key_exists($paginationWord[$i], $params)) {
                    $paginationParam[$paginationWord[$i]] = $params[$paginationWord[$i]];
                    unset($params[$paginationWord[$i]]);
                }
            }
            if (count($paginationParam) == 0) {
                $paginationParam = false;
            }

            $query = $model->find();
            if (array_key_exists('limit', $params)) {
                $query->limit($params['limit']);
                unset($params['limit']);
            }

            foreach ($params as $key => $value) {
                if (!$model->hasAttribute($key)) {
                    Logger::writeLog('API', 'error: "Invalid attribute:"' . $key);
                    throw new HttpException(404, 'Invalid attribute:' . $key);
                }
            }


            try {
                $query->where($params);

                $provider = new ActiveDataProvider([
                    'query' => $query,
                    'pagination' => $paginationParam,
                    'sort' => [
                        'defaultOrder' => [
                            'id' => SORT_DESC,
                        ]
                    ],

                ]);
            } catch (\Exception $ex) {
                Logger::writeLog('API', 'error: "Internal server error"');
                throw new HttpException(500, 'Internal server error');
            }

            Logger::writeLog('API', 'successful params=' . implode($_GET));
            if ($provider->getCount() <= 0) {
                return ''; //throw new \yii\web\HttpException(404, 'No entries found with this query string');
            } else {
                return $provider;
            }
        } else {
            Logger::writeLog('API', 'error: "AccessToken is not found"');
            throw new HttpException(400, 'There are no query string');
        }

    }

}
<?php

namespace app\controllers;

use app\components\ArchiveHelper;
use app\components\FileSystemHelper;
use app\components\Logger;
use app\components\PageLoader;
use app\models\Category;
use app\models\File;
use app\models\GoPageForm;
use app\models\LoginForm;
use app\models\Page;
use app\models\PageForm;
use app\models\SearchForm;
use Exception;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        //Если гость, отправим на форму логина
        if (Yii::$app->user->isGuest) {
            $url = Yii::$app->urlManager->createUrl(['site/login']); //получим адрес ч/з urlManager
            return $this->redirect($url);
        }
        return $this->redirect('site/search');
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {

            if ($model->login()) {
                // записать действия в лог
                Logger::writeLog('login', 'successful');
                $url = Yii::$app->urlManager->createUrl(['site/search']); //получим адрес ч/з urlManager
                return $this->redirect($url);
            } else {
                Logger::writeLog('login', 'bad user/pas');
            }
        }
        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();
        return $this->goHome();
    }

    /*
     * Отображает страницу поиска/скачивания
     **/
    public function actionSearch()
    {
        //Если гость, отправим на форму логина
        if (Yii::$app->user->isGuest) {
            $url = Yii::$app->urlManager->createUrl(['site/login']);
            return $this->redirect($url /*'/index.php?r=site%2Flogin'*/);
        }

        $form = new SearchForm();
        if ($form->load(Yii::$app->request->post()) && $form->validate()) {

            $url = Html::encode($form->url_from);
            $name = Html::encode($form->name);

            $pl = new PageLoader($name);
            try {
                $ar = $pl->doLoad($url);
                //пишем даннные в базу
                $page = new Page();
                $page->name = $name;
                $page->category_id = Html::encode($form->category_id);
                $page->url_from = $url;
                $page->user_id = Yii::$app->user->getId();
                $page->save();

                foreach ($ar as $el) {
                    if ($el['loaded'] === 'sucsess') {
                        $file = new File();
                        $file->page_id = $page->id;
                        $file->file_name = $el['basename'];
                        $file->save();
                    }
                }
                $message = 'Page has been successfully exported. Here is page ID:' . $page->id;
                Logger::writeLog('LoadPage', 'successful url=' . $url);
            } catch (Exception $e) {
                $message = 'Some error while exporting page. Please try again later or change URL';
                Logger::writeLog('LoadPage', 'error url=' . $url . ' ERR:' . substr($e->getMessage(), 0, 20));
            }

            $url = Yii::$app->urlManager->createUrl(['site/page']); //получим адрес ч/з urlManager
            return $this->redirect([$url, 'message' => $message]);
        } else {

            $categories = Category::find()
                ->where(['<', 'begin_date', '2020-10-02'])
                ->andWhere(['>', 'end_date', '2020-10-02'])
                ->all();
            // формируем массив, с ключем равным полю 'id' и значением равным полю 'code'
            $categoryItems = ArrayHelper::map($categories, 'id', 'code');
            $defCategory = Category::find()
                // ->where(['code' => "Smartlink"])
                ->orderBy('id')  //если удалим/деактивируем Smartlink -код будет работать
                ->one();
            $defCategoryId = $defCategory->id;

            return $this->render('search', [
                'model' => $form,
                'categoryItems' => $categoryItems,
                'defCategoryId' => $defCategoryId
            ]);
        }
    }

    /*
    * Отображает скаченные страницы
    **/
    public function actionPage()
    {
        //Если гость, отправим на форму логина
        if (Yii::$app->user->isGuest) {
            $url = Yii::$app->urlManager->createUrl(['site/login']); //получим адрес ч/з urlManager
            return $this->redirect($url);
        }
        $form = new GoPageForm();
        if ($form->load(Yii::$app->request->get()) && $form->validate()) {
            $ar = ['site/page', 'page' => $form->_current_page];
            $pp = Yii::$app->request->get('limit');
            if ($pp) {
                $ar['limit'] = $pp;
            }
            $url = Yii::$app->urlManager->createUrl($ar); //получим адрес ч/з urlManager
            return $this->redirect($url);
        }

        $searchModel = new PageForm();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('page', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'form' => $form
        ]);
    }

    public function actionDelete($id)
    {
        $page = Page::findOne($id);
        if (is_null($page)) {
            null;
        } else {
            $page->delete();
            Logger::writeLog('actionDelete', 'page_id=' . $id);
        }
        $path = __DIR__ . '/../protected/pages/' . $page->name;
        FileSystemHelper::delDir($path);

        //если есть архив - грохнем его
        $path = __DIR__ . '/../protected/pages/' . $page->name . '.zip';
        if (is_file($path)) {
            unlink($path);
        }
        return $this->redirect(['page']);
    }


    public function actionDownload($id)
    {
        $page = Page::findOne($id);
        $path = __DIR__ . '/../protected/pages/' . $page->name;
        $file = $path . '/../' . $page->name . '.zip';
        $ah = new ArchiveHelper();
        $s = $ah->dirToZip($path, $file);

        // записать действия в лог
        Logger::writeLog('Download Arc', 'page_id=' . $id);

        return Yii::$app->response->sendFile($file);
    }

    //скачивание конкретного файла
    public function actionDownloadFile($id)
    {
        $row = (new Query())
            ->select(['page_name' => 'lpd_page.name', 'file_name' => 'lpd_file.file_name'])
            ->from(['lpd_file'])
            ->join('JOIN', 'lpd_page', 'lpd_file.page_id=lpd_page.id')
            ->where([
                'lpd_file.id' => $id
            ])
            ->one();

        $path = __DIR__ . '/../protected/pages/' . $row['page_name'] . '/files/' . $row['file_name'];
        // записать действия в лог
        Logger::writeLog('Download file', $row['page_name'] . '/files/' . $row['file_name']);

        return Yii::$app->response->sendFile($path);
    }
}

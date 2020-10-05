<?php

use app\components\PageActionColumn;
use app\models\Category;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<?php
$message = Yii::$app->getRequest()->getQueryParam('message');
if ($message) {echo '<div align="center">'.$message.'</div>';} ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'pager' => [
        'firstPageLabel' => 'First',
        'lastPageLabel'  => 'Last'
    ],
    'columns' => [
        'id',
        'name',
        //'date_create',
        ['attribute'=>'date_create',
            'content'=>function($model){
                    $fm =Yii::$app->formatter;
                    $fm->timeZone = 'UTC';
                return $fm->asDatetime($model->date_create,'dd.MM.yyyy H:m:ss');
            },
        ],
        ['attribute'=>'category_id',
         'content'=>function($model){
                return $model->category->code;
            },
            'filter'=> Category::getCategories()
        // 'filter'=>\yii\helpers\ArrayHelper::map(Category::find()->all(),'id','code') //фильт - выпадающий список (отражаем все категории, жаде закрытые)

        ],
        'url_from',
        'domainFrom',

        ['attribute'=>'actions',
             'content'=>function($model){
                  return  Html::a("download",['site/download','id' => $model->id]).'<BR/>'
                         .Html::a("delete",['site/delete','id' => $model->id]);
        }],

/*
        [
            'class' => PageActionColumn::class, // переопределенный ActionColumn
        ],*/
    ],
])?>




<?php $f = ActiveForm::begin(['method' => 'get']); ?>

<?= Html::a('5', ['/site/page', 'limit' => 5] /*, ['class' => 'profile-link']*/) ?>

<?= Html::a('25', ['/site/page', 'limit' => 25] /*, ['class' => 'profile-link']*/) ?>

<?= Html::a('50', ['/site/page', 'limit' => 50] /*, ['class' => 'profile-link']*/) ?>

<?= Html::a('100', ['/site/page', 'limit' => 100] /*, ['class' => 'profile-link']*/) ?>

<?= Html::a('200', ['/site/page', 'limit' => 200] /*, ['class' => 'profile-link']*/) ?>

<?=$f->field($form, '_current_page')->textInput()->label('')?>
<?= Html::submitButton('go to page', ['class' => 'submit']) ?>
<?=$f->field($form, 'limit')->hiddenInput()->label(false)?>


<?php ActiveForm::end(); ?>


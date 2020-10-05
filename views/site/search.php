<?php

/* @var $this yii\web\View */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Search';
//$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-search">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php
    $params = [
        //'prompt' => 'Выберите статус...',
        'value' => $defCategoryId
    ];

    ?>
    <?php $form = ActiveForm::begin(/*[
        'id' => 'login-form',
        'layout' => 'horizontal',
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]*/); ?>

    <?=$form->field($model, 'url_from')->textInput(['autofocus' => true])->label('Page URL') ?>
    <?=$form->field($model, 'name')->textInput() ->label('Name')?>
    <?= $form->field($model, 'category_id')->dropDownList($categoryItems,$params) ->label('Category')?>
    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Export', ['class' => 'btn btn-primary', 'name' => 'export-button']) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>


</div>

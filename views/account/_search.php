<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model hosanna\profile\models\ProfileSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="rpprofile-search row" >
    <div class="pull-right">
        <?php $form = ActiveForm::begin([
            'action' => ['index'],
            'method' => 'get',
            'options'=>['class'=>'form-inline'],
        ]); ?>

        <?= $form->field($model, 'username', [
                'template'=>"{input}\n{hint}", //{label}\n{input}\n{hint}\n{error}
                'inputOptions'=>
                [
                    'placeholder'=>\Yii::t('app', '{username}, {email} or {mob}', [
                        'username'=>$model->getAttributeLabel('username'),
                        'email'=>$model->getAttributeLabel('email'),
                        'mob'=>$model->getAttributeLabel('mobile'),
                    ]), 
                    'class'=>'form-control'
                ],
                //'options'=>['class'=>'pull-right']
            ]) 
            ?>

       <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary']) ?>
        </div> 

        <?php ActiveForm::end(); ?>
    </div>
</div>

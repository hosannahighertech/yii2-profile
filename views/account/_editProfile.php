<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model app\models\Profile */
/* @var $form yii\widgets\ActiveForm */ 
$this->registerJS("
    function readURL(input) 
    {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#preview').attr('src', e.target.result);
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
    $('#imgInp').change(function(){  readURL(this);  });
");
?>

<div class="profile-form">

    <?php $form = ActiveForm::begin([
        'options'=>['enctype'=>'multipart/form-data'],
        'enableAjaxValidation' => false,
    ]); ?>
    
    <?= $form->errorSummary($model) ?>
    
    <p><img id="preview" src="<?= $model->image ?>" alt="Image Preview" style="height:100px; border:solid grey 1px; padding:3px;" /></p>
    <?= $form->field($model, 'avatarFile')->fileInput(['id'=>'imgInp']) ?>

    <?= $form->field($model, 'fname')->textInput(['maxlength' => 250]) ?>

    <?= $form->field($model, 'lname')->textInput(['maxlength' => 250]) ?>
    
    <?= $this->render('_datePicker', ['model'=>$model, 'attribute'=>'birthdate']) ?>
    
    <!--?= $form->field($model, 'username')->textInput(['maxlength' => 250]) ?-->

    <?= $form->field($model, 'oldPassword')->passwordInput(['maxlength' => 64]) ?>
    
    <?= $form->field($model, 'formPassword')->passwordInput(['maxlength' => 64]) ?>
    
    <?= $form->field($model, 'repeatPassword')->passwordInput(['maxlength' => 64]) ?>

    <!--?= $form->field($model, 'email')->textInput(['maxlength' => 64]) ?-->

    <?= $form->field($model, 'mobile')->textInput(['maxlength' => 20]) ?>

    <!--?= $form->field($model, 'gender')->dropDownList([1=>'Male', 2=>'Female']) ?-->

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Register') : Yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

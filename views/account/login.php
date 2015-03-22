<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Alert;
use yii\jui\Dialog;
use yii\bootstrap\Button;
use yii\web\JsExpression; 

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $model app\models\LoginForm */

$this->title = 'Login';
$this->params['breadcrumbs'][] = $this->title;


$this->registerJS('
    //hide progress bar
    pbar = $("#pbar");
    pbar.hide();
    $("#submitReset").click(function()
    {    
        //clear any previous error
        $("#form-error").removeClass("alert alert-danger");
        $("#form-error").text("");
        
        isResendActivation = $("#resendActivation").is(":checked")
        email = $("#email").val();
        urlforgot = "'.\Yii::$app->urlManager->createUrl(['/profile/account/forgot', 'email'=>'__email__']).'";
        urlresend = "'.\Yii::$app->urlManager->createUrl(['/profile/account/resend-activation', 'email'=>'__email__']).'";
        urlforgot = urlforgot.replace("__email__", email);
        urlresend = urlresend.replace("__email__", email);
        //show progress bar
        pbar.show();
        //send ajax request
        $.ajax({
                    url:isResendActivation? urlresend : urlforgot , 
                    cache: false
                })
                .done(function(data) { 
                    pbar.hide(); //hide progress window
                    data = JSON.parse(data); 
                   if(data.success)
                   {
                        $("#form-contents").html(data.msg);
                        $("#form-contents").addClass("alert alert-success");
                   }
                   else
                   {
                        $("#form-error").text(data.msg);
                        $("#form-error").addClass("alert alert-danger");  
                   } 
                });
    });
');

$this->registerCSS('.no-close .ui-dialog-titlebar-close {display: none;}'); //hide dialog Bar

Dialog::begin([
    'id'=>'reset-pass-dialog',
    'clientOptions' => [
        'modal' => true,
        'title' => Yii::t('app', 'Reset Profile Password'),
        'autoOpen'=>false,
        'maxHeight'=>400, 
        'dialogClass'=>'no-close',
        'buttons'=> [ 
            [
                'text'=>Yii::t('app', 'Close'), 
                'class'=>'btn btn-sm btn-default', 
                'click'=>new JsExpression(' function() { $( this ).dialog( "close" ); }')
            ]
        ], 

    ],
]);
?>

    <div id="form-contents">    
        <p id='form-error'><?= \Yii::t('app', '{h}Enter Valid Email or username you registered with{ch}', ['h'=>'<b>', 'ch'=>'</b>']); ?></p>
       <p> <input type="text" id="email" class="form-control" /></p>
       
        <div id="pbar" class="progress progress-striped active">
          <div class="progress-bar"  role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%">
          </div>
        </div>

        <p><?= Button::widget([
                    'label' => Yii::t('app', 'Reset Password'),
                    'options' => [
                        'class' => 'btn-sm btn-danger',
                        'id'=>'submitReset', 
                    ],
                ]);
        ?>
        
        </p>
        <p><?= Html::checkbox('activation', false, ['id'=>'resendActivation']).' '.\Yii::t('app', 'Only Resend Activation Code') ?></p>
    </div>
<?php Dialog::end(); ?>

    <!-- Login part -->
<div class="site-login">
    <h1><?= Html::encode($this->title) ?></h1>
    
    <?php if(\Yii::$app->session->hasFlash('error')): ?>
        <?= Alert::widget([
            'options' => [
                'class' => 'alert-danger',
            ],
            'body' => \Yii::$app->session->getFlash('error'),
        ]); ?>
    <?php endif; ?>
    
    <?php if(\Yii::$app->session->hasFlash('success')): ?>
        <?= Alert::widget([
            'options' => [
                'class' => 'alert-success',
            ],
            'body' => \Yii::$app->session->getFlash('success'),
        ]); ?>
    <?php endif; ?>
    
    <p>Please fill out the following fields to login:</p>

    <?php $form = ActiveForm::begin([
        'id' => 'login-form',
        'options' => ['class' => 'form-horizontal'],
        'fieldConfig' => [
            'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
            'labelOptions' => ['class' => 'col-lg-1 control-label'],
        ],
    ]); ?>

    <?= $form->field($model, 'username') ?>

    <?= $form->field($model, 'password')->passwordInput() ?>

    <?= $form->field($model, 'rememberMe', [
        'template' => "<div class=\"col-lg-offset-1 col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
    ])->checkbox() ?>

    <div class="form-group">
        <div class="col-lg-offset-1 col-lg-11">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
        </div>
    </div>
 
    <?php ActiveForm::end(); ?>

    <div class="col-lg-offset-1" style="color:#999;">
        New here? <?= Html::a('register', ['create']) ?> Forgot Password? Want to Resend Activation code? 
        <br>Please <a href="#" onclick="$('#reset-pass-dialog').dialog('open'); ">click here</a> to recover and be able to login again.
        <p>Your Other Profile Details remains the same</p>
         
    </div>
</div>

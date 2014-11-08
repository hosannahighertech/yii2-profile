<?php

use yii\helpers\Html; 
use yii\widgets\DetailView;
use yii\bootstrap\Button;
use yii\web\JsExpression; 
use yii\jui\Dialog;

/* @var $this yii\web\View */
/* @var $model app\models\Profile */

$this->title = $model->fullname;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Member List'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCSS('.no-close .ui-dialog-titlebar-close {display: none;}'); //hide dialog Bar

\Yii::$app->user->isGuest?"":$this->registerJS('

    function loadImages()
    {
        $.ajax({
            url: "'.\Yii::$app->urlManager->createUrl(['/profile/account/avatars']).'",
            cache: false
        })
        .done(function(data) { 
            // do clear dialog 
            html ="";
            data = JSON.parse(data);
            for(i=0; i<data.length; i++)
            {
                html+=data[i]; 
            }            
            $( "#contents" ).html(html);       
        
            //react to click event to images
            $(".avatar-img").click(function(){
                url = "'.\Yii::$app->urlManager->createUrl(['/profile/account/update-avatar', 'id'=>Yii::$app->user->identity->id, 'avatar'=>'_avatar_']).'";
                url = url.replace("_avatar_", $(this).attr("data-image-name"));
                srcUrl = $(this).attr("src");
                $.ajax({
                    url:url , 
                    cache: false
                })
                .done(function(data) { 
                    data = JSON.parse(data);
                    if(data.success)
                    { 
                        $("#user-avatar").attr("src", srcUrl);
                    }
                });
            });
            
            //file upload ajax
            $("#upload").on("click", function(e) {
                
                var file_data = $("#imgInp").prop("files")[0];   
                var form_data = new FormData();                  
                form_data.append("file", file_data);    
                $.ajax({
                    url: "'.\Yii::$app->urlManager->createUrl(['/profile/account/change-my-avatar', 'id'=>Yii::$app->user->identity->id]).'",
                    dataType: "text",
                    cache: false,
                    contentType: false,
                    processData: false,
                    data: form_data,                         
                    type: "post",
                    success: function(data){  
                        data = JSON.parse(data);
                        if(data.success)
                        {
                            $("#user-avatar").attr("src", data.src);
                            loadImages();
                        }
                    }
                 });
                e.preventDefault(); // Totally stop stuff happening
            });
        });
    }

    //previewer of upload image
    function readURL(input) 
    {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $("#preview").attr("src", e.target.result);
                $("#preview").show();
            }

            reader.readAsDataURL(input.files[0]);
        }
    }
    $("#imgInp").change(function(){  readURL(this);  });
    
    //Changing image
    $("#updateAvatar").click(function(){ 
        //hide preview for Images           
        $("#preview").hide();
        $("#imgInp").val(""); 
        
        //load images
        loadImages();
        $( "#avatars-dialog" ).dialog({ width: 800 });
        $("#avatars-dialog").dialog("open"); 
    });
    
');


Dialog::begin([
    'id'=>'avatars-dialog',
    'clientOptions' => [
        'modal' => true,
        'title' => Yii::t('app', 'Click any image to change Avatar'),
        'autoOpen'=>false,
        'maxHeight'=>400, 
        'dialogClass'=>'no-close',
        'buttons'=> [ 
            [
                'text'=>Yii::t('app', 'Close'), 
                'class'=>'btn btn-sm btn-success', 
                'click'=>new JsExpression(' function() { $( this ).dialog( "close" ); }')
            ]
        ],
    ],
]);

echo '<div id="contents"></div>';

echo '    <p><img id="preview" src="#" alt="Image Preview" style="height:100px; border:solid grey 1px; padding:3px;" /></p>' ;

echo    '<form>
            <input id="imgInp" type="file" name="file" />
            <p style="padding:5px;" ><button id="upload" class="btn btn-xs btn-default">Upload</button></p>
        </form>';

Dialog::end();
?>
<div class="profile-view">

    <h1><?= Html::encode($this->title) ?></h1>
    
    <div style="padding-bottom:5px;" id="profile-box">
        <img id="user-avatar" class="img-responsive" alt="<?= $model->fullname ?>"  src="<?= $model->image ?>"/>
    </div>
     
    <!-- Allow user to update only their profiles -->
    <?php if(!\Yii::$app->user->isGuest && \Yii::$app->user->identity->id==$model->id): ?>
        <div style="padding-bottom:5px;">
            <?= Button::widget([
                'label' => Yii::t('app', 'Change Avatar'),
                'options' => [
                    'class' => 'btn-sm btn-warning',
                    'id'=>'updateAvatar'
                ],
            ]);
            ?>
            <?= Html::a(Yii::t('app', 'Update Profile'), ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-primary']) ?>

        </div>
    <?php endif; ?>
    
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'email:email',
            'fname',
            'lname',
            'username',
            'mobile',
            'regtime:date', 
            [
                'label'=>$model->getAttributeLabel('gender'),
                'value'=> $model->gender==1? Yii::t('app', 'Male'): Yii::t('app', 'Female'),
            ],
            //'valtoken', 
        ],
    ]) ?>
    
    <?php
        foreach($this->context->module->profiles as $profile)
        {
            if(array_key_exists(2, $profile)) //all 3 parameters are passed
            {
                echo Html::a($profile[0], [$profile[1], $profile[2]=>$model->id], ['class' => 'btn btn-default']).' ';
            }
            else if(array_key_exists(1, $profile)) //only 2 passed
            {
                echo Html::a($profile[0], [$profile[1], 'id'=>$model->id], ['class' => 'btn btn-default']).' ';
            }
        }
    ?>

    <p>
        <!--?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?-->
    </p>

</div>

<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
?>

<p><?= Html::a(Html::encode($model->fullname), ['view', 'id' => $model->id]) ?></p>
<div style="padding-bottom:5px;">
    <img id="user-avatar" class="img-responsive thumbnail" alt="<?= $model->fullname ?>"  src="<?= $model->image ?>"/>
</div>
 
 <?= DetailView::widget([
        'model' => $model,
        'attributes' => [  
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

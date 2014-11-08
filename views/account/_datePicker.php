<?php
use yii\jui\DatePicker;
use yii\web\JsExpression; 
?>

<p>
    <?= DatePicker::widget([
            'language' => 'en-US',
            'model' => $model, 
            'attribute' => $attribute,
            'dateFormat' => 'yyyy-MM-dd',
            'clientOptions' => [
                'appendText'=>Yii::t('app', '<b>{dob}</b>', ['dob'=>$model->getAttributeLabel('birthdate')]),
                'defaultDate' => new JsExpression("new Date('1985-01-01')"),
            ],
        ]);
    ?> 
</p>

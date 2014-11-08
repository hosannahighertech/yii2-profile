<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProfileSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Member List');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="profile-index">

    <p><?php echo $this->render('_search', ['model' => $searchModel]); ?> </p> 
    
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'summary' => \Yii::t('app', 'Pages {pageCount}: {page}'),
        'layout'=> "{items}\n<b>{summary}</b> {pager}",
        'columns' => [  
            [
                'attribute' => 'avatar',
                'format'=>'image',
                'value'=>function($data) { return $data->image;},
            ],
            
            [
                'attribute' => 'username',
                'format'=>'html',
                'value'=>function($data) { return Html::a(Html::encode($data->fullname), ['view', 'id' => $data->id]); },
            ], 
            [
                'attribute' => 'gender',
                'value'=>function($data) { return $data->gender==1? Yii::t('app', 'Male'): Yii::t('app', 'Female'); },
            ],
            'regtime:date', 
        ]  
    ]); ?>

</div>

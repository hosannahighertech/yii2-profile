<?php

use yii\helpers\Html;
use yii\bootstrap\Alert;

/* @var $this yii\web\View */
/* @var $model app\models\Profile */

$this->title = Yii::t('app', 'Update {modelClass} - ', [
    'modelClass' => 'Profile',
]) . ' ' . $model->email;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Profiles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->email, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
 
?>
<div class="profile-update">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php if(\Yii::$app->session->hasFlash('recovery')): ?>
        <?= Alert::widget([
            'options' => [
                'class' => 'alert-danger',
            ],
            'body' => \Yii::$app->session->getFlash('recovery'),
        ]); ?>
    <?php endif; ?>

    <?= $this->render('_editProfile', [
        'model' => $model,
    ]) ?>

</div>

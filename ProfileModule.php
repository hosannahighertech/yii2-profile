<?php

namespace hosanna\profile;
use Yii;

class ProfileModule extends \yii\base\Module
{
    public $defaultRoute="profile/index";
    public $controllerNamespace = 'app\modules\profile\controllers';
    public $defaultRole = 'member';
    public $isActivation = true;
    public $profiles = [];
    public $bundleInstance = null;

    public function init()
    {
        parent::init();        
        Yii::setAlias('profile', dirname(dirname(__DIR__)).'/modules/profile/'); 
        
        $this->registerAssets();
    }
    
    public function registerAssets() 
    {
        $view = Yii::$app->view; 
        Yii::$app->assetManager->forceCopy = true;
        $this->bundleInstance = \app\modules\profile\ProfileAsset::register($view); 
        $this->bundleInstance->publish(Yii::$app->assetManager);
  	}
}

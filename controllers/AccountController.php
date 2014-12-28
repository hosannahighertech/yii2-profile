<?php

namespace hosanna\profile\controllers;

use Yii;
use hosanna\profile\ProfileModule;
use hosanna\profile\models\Profile;
use hosanna\profile\models\ProfileSearch;
use hosanna\profile\models\LoginForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile; 
use yii\helpers\BaseFileHelper;
use yii\filters\AccessControl;
use yii\helpers\Html;

/**
 * ProfileController implements the CRUD actions for Profile model.
 */
class AccountController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
            'only' => ['create', 'index', 'view', 'update', 'activate', 'avatars','changemyavatar','forgot', 'updateavatar', 'login', 'logout'],
                'rules' => [
                   [
                        'allow' => true,
                        'actions' => ['index', 'create', 'activate','login','forgot'],
                        'roles' => ['?'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index','view', 'update', 'avatars','changemyavatar', 'updateavatar', 'logout'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Profile models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ProfileSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Profile model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    { 
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Profile model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Profile(['scenario'=>'register']);
        $module = ProfileModule::getInstance();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) { 
            //set uploaded file to Model Attribute
            $model->avatarFile = UploadedFile::getInstance($model, 'avatarFile');
            $this->uploadAvatar($model);
            
            //set default user role 
            $auth = Yii::$app->authManager; 
            $defaultRole = $auth->getRole($module->defaultRole);
            if($defaultRole!==null) //role exists
                $auth->assign($defaultRole, $model->id);
            
            //if activate is false in config activate user manually else send mail with activation link
            if(!$module->isActivation)
            {
                $model->setAttribute('isactive', 1);
                $model->save();
            }
            else
            {
                $model->scenario = 'recover';
                $model->save(false);
                //send mail
                $url = Yii::$app->urlManager->createAbsoluteUrl(['/profile/account/activate', 'key'=>base64_encode($model->recoverycode)], 'http');
                $url = Html::a(Yii::t('app', 'Here'), $url);
                
                $subject = Yii::t('app', 'Activating Account for {email}', ['email'=>$model->email]);
                $msg = Yii::t('app', 'Dear {name}, Thank you for registering with us. Please click {link} to Activate your Account. Link expires in 24 Hours', ['name'=>$model->fname, 'link'=>$url]);
               try
               {
                    $this->sendMail($model->email, $subject, $msg);
               }
               catch(\Exception $e)
               {
                   throw new \yii\web\HttpException(500, 'Could Not Send Activation Mail. Please go to login and Resend the code again');
               }
            }
            
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Profile model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update'; //we are updating the Profile 
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) 
        {            
            $model->avatarFile = UploadedFile::getInstance($model, 'avatarFile');
            $this->uploadAvatar($model);
            
            return $this->redirect(['view', 'id' => $model->id]);
            
        } else {
            return $this->render('update', [
                'model' => $model, 
            ]);
        }
    }
    
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
    
    /**
     * Lists available avatars  
     * @return json with success set to true if was change and false else
     */
    public function actionAvatars()
    {
        if(!Yii::$app->request->isAjax)
            Yii::$app->end();
        
        $avPath = Yii::getAlias('@webroot').'/uploads/avatars/'; 
        $files = \yii\helpers\FileHelper::findFiles(Yii::getAlias('@webroot').'/uploads/avatar/system', ['only'=>['*.png', '*.gif', '*.jpg']]);
        $data = [];
        foreach($files as $file)
        {
            $img = basename($file);//with ext 
            $data[] ='<img data-image-name="system/'.$img.'"  class="avatar-img btn btn-info" style="width:100px; padding:1px;" src="'. Yii::$app->request->baseUrl.'/'.Yii::$app->params['avatarPath'].'system/'.$img.'" />&nbsp;';
        }
        //add yours
        //search all with id name
        $myAvatar = "";
        foreach (glob(Yii::$app->params['avatarPath'].Yii::$app->user->identity->id."_avatar.*") as $filename) {
            $myAvatar = basename($filename); 
        } 
        $data[] ='<img data-image-name="'.$myAvatar.'" class="avatar-img btn btn-info" style="width:100px; padding:1px;" src="'. Yii::$app->request->baseUrl.'/'.Yii::$app->params['avatarPath'].$myAvatar.'?'.time().'" />&nbsp;';//add time to force refresh on client image
        echo json_encode($data);        
    }
    
    /**
     * Updates an existing Profile model's Avatar. 
     * @param string $avatar
     * @param string $id
     * @return json with success set to true if was change and false else
     */
    public function actionUpdateAvatar($id, $avatar)
    {
        if(!Yii::$app->request->isAjax)
            Yii::$app->end();
            
        $model = $this->findModel($id); 
        $model->setAttribute('avatar', $avatar);
        echo  json_encode(['success'=>$model->save()]); 
    }
    
    /**
     * Updates an existing Profile model's Avatar eplacing existing.  
     * @param string $id
     * @return json with success set to true if was change and false else
     */
    public function actionChangeMyAvatar($id)
    {
        if(!Yii::$app->request->isAjax)
            Yii::$app->end();
        
        $model = $this->findModel($id);
        $model->avatarFile =  UploadedFile::getInstanceByName('file'); 
        $result = $this->uploadAvatar($model);
        echo  json_encode(['success'=>$result['success'], 'src'=> Yii::$app->request->baseUrl.'/'.Yii::$app->params['avatarPath'].$result['name']."?".time()]);//add time to force refresh on client image
    }
    
    //recover password
    public function actionResendActivation($email)
    { 
        if(!Yii::$app->request->isAjax)
            Yii::$app->end();
            
        $user = Profile::find()
            ->where(['email'=>$email])
            ->orWhere(['username'=>$email])
            ->one();
        if($user)
        {
            $user->setScenario('recover');
            $user->save();
            //send mail
            $url = Yii::$app->urlManager->createAbsoluteUrl(['/profile/account/activate', 'key'=>base64_encode($user->recoverycode)], 'http');
            $url = Html::a(Yii::t('app', 'Here'), $url);
            
            $subject = Yii::t('app', 'Activating Account for {email}', ['email'=>$user->email]);
            $msg = Yii::t('app', 'Dear {name}, Thank you for registering with us. We are resending this link at your request. Please click {link} to Activate your Account. Link expires in 24 Hours', ['name'=>$user->fname, 'link'=>$url]);
           try
           {
                $this->sendMail($user->email, $subject, $msg);
           }
           catch(\Exception $e)
           {
                echo json_encode(['success'=>false, 'msg'=>Yii::t('app', 'Could not send Activation Mail. Check your Internet connection and try again.')]);
                exit();
           }
            
            echo json_encode(['success'=>true, 'msg'=>Yii::t('app', 'Succesfully sent Activation code. Please Check your Email for Instructions.')]);
            exit();
        }
        else
        {
            echo json_encode(['success'=>false, 'msg'=>Yii::t('app', 'Invalid username or email')]);
            exit();
        }
    }
    
    //recover password
    public function actionForgot($email)
    { 
        if(!Yii::$app->request->isAjax)
            Yii::$app->end();
            
        $user = Profile::find()
            ->where(['email'=>$email])
            ->orWhere(['username'=>$email])
            ->one();
        if($user)
        {
            $user->setScenario('recover');
            $user->save();
            //send mail
            $url = Yii::$app->urlManager->createAbsoluteUrl(['/profile/account/recover-password', 'key'=>base64_encode($user->recoverycode), 'password'=>hash('sha256', $user->password.$user->codeexpiry)], 'http');
            $url='<a href="'.$url.'">'.Yii::t('app', 'Here').'</a>';
            
            $subject = Yii::t('app', 'Reseting Password for {email}', ['email'=>$user->email]);
            $msg = Yii::t('app', 'Dear {name}, someone applied to change your account password associated with this email. If it is not you, you can safely ignore this message. If it was you, please click {link} to reset your password. Upon logging in, please change your password. The link expires in 24 hours', ['name'=>$user->fname, 'link'=>$url]);
           try
           {
                $this->sendMail($user->email, $subject, $msg);
           }
           catch(\Exception $e)
           {
                echo json_encode(['success'=>false, 'msg'=>Yii::t('app', 'Could not send Recovery E-Mail. Check your Internet connection and try again.')]);
                exit();
           }
            
            echo json_encode(['success'=>true, 'msg'=>Yii::t('app', 'Succesfully Reset Your Account. Please Check your Email for Instructions.')]);
            exit();
        }
        else
        {
            echo json_encode(['success'=>false, 'msg'=>Yii::t('app', 'Invalid username or email')]);
            exit();
        }
    }
    
    //recover pass from forgot passwd
    public function actionRecoverPassword($key, $password)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        if($this->isValidBase64($key))
        {            
            //clear any flash message
            Yii::$app->session->removeFlash('success');
            Yii::$app->session->removeFlash('error');
            
            $user = Profile::find()
                ->where(['recoverycode'=>base64_decode($key)])
                ->one();
                
            if($user!==null && $user->codeexpiry>time() && $password==hash('sha256', $user->password.$user->codeexpiry))
            {
                //succesful so set flash
                $user->setAttributes(['isactive'=>1, 'recoverycode'=>'']);
                if($user->save())
                {
                    //bypass login and send him to update profile
                    $model = new LoginForm();
                    $model->recoveryLogin($user);
                }
                else
                {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Password Recovery failed with code 1432. Please Try again'));
                }
            }
            else
            {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Recovery code have expired or Invalid. Please click "forgot password below to reset"'));
            }
        }
        else
        {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Password Recovery failed with code 1332. Please Try again'));
        }
        if(Yii::$app->user->isGuest)    
            $this->redirect(['account/login']);
        else
        {
            //generate new password
            $tempPass = substr(Yii::$app->security->generateRandomString(), 8);
            Yii::$app->session->setFlash('recovery', Yii::t('app', 'This login was temporary, please update your pasword. Use Old Password: {oldp}', ['oldp'=>$tempPass]));
            $user->setAttribute('password', $user->generatePasswd($tempPass));
            $user->save(false);    
            $this->redirect(['account/update', 'id'=>$user->id]);
        }
    }
    
    //activate password
    public function actionActivate($key)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        if($this->isValidBase64($key))
        {            
            //clear any flash message
            Yii::$app->session->removeFlash('success');
            Yii::$app->session->removeFlash('error');
            
            $user = Profile::find()->where(['recoverycode'=>base64_decode($key)])->one();
            if($user!==null && $user->codeexpiry>time())
            {
                //succesful so set flash
                $user->setAttributes(['isactive'=>1, 'recoverycode'=>'']);
                if($user->save())
                {
                    Yii::$app->session->setFlash('success', Yii::t('app', 'Your Account have been activated. Please login'));
                }
                else
                {
                    Yii::$app->session->setFlash('error', Yii::t('app', 'Activation failed with code 1432. Please Try again'));
                }
            }
            else
            {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Activation code have expired or Invalid. Please click "forgot password below to resend"'));
            }
        }
        else
        {
            Yii::$app->session->setFlash('error', Yii::t('app', 'Activation failed with code 1332. Please Try again'));
        }
        $this->redirect(['account/login']);
    }

    /**
     * Deletes an existing Profile model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    /*public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    } */

    
    protected function uploadAvatar($model)
    {         
        if($model->avatarFile!=null)
        {            
            $randName = $model->id.'_avatar';  
            $avatarPath = Yii::$app->params['avatarPath'] .$randName . '.' . $model->avatarFile->extension;
            //remove all avatars associated with this model
            $this->deleteAvatarFiles($model->id);
            $model->avatarFile->saveAs($avatarPath);
            $model->avatar = $randName . '.' . $model->avatarFile->extension;
            $model->save(false);
            $this->resizeAvatar($avatarPath); //mak it comply with size
            return ['success'=>$model->save(false), 'name'=>$model->avatar];
        }
        else{            
            $model->avatar = 'system/default.png';
            $model->save(false);
        }
    }
     
    protected function resizeAvatar($avatarPath)
    {
        list($width, $height, $type, $attr) = getimagesize($avatarPath); 
        if($width<150 && $height<150)
                return; //no resizing necessary
        //resize file
        $width=Yii::$app->params['avatarWidth'];
        $height=Yii::$app->params['avatarHeight'];
        $image=Yii::$app->image->load($avatarPath);            
        $image->resize($width,$height, \Yii\image\drivers\Image::HEIGHT);        
        $image->crop($width, $height);
        $image->save($avatarPath);
    }
    
    protected function deleteAvatarFiles($id=0)
    { 
        foreach (glob(Yii::$app->params['avatarPath'].$id."_avatar.*") as $filename) {
            @unlink($filename); 
        } 
    }
    
    protected function isValidBase64($data)
    {
        if ( base64_encode(base64_decode($data, true)) === $data){
            return true;
        } else {
            return false;
        }
    }
    
    protected function sendMail($to, $subject, $message)
    {
        Yii::$app->mailer->compose('@app/mail/activate', ['title' => $subject, 'msg'=>$message])
                ->setFrom(Yii::$app->params['supportEmail'])
                ->setTo($to)
                ->setSubject($subject)
                ->send();
    }
    
    /**
     * Finds the Profile model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Profile the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Profile::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
     
}

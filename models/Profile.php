<?php


namespace hosanna\profile\models;

use Yii;

/**
 * This is the model class for table "Users".
 *
 * @property integer $id
 * @property string $fname
 * @property string $lname
 * @property string $email
 * @property string $username
 * @property integer $gender
 * @property string $birthdate
 * @property string $password 
 * @property string $mobile
 * @property integer $regtime
 * @property string $recoverycode
 * @property integer $codeexpiry
 * @property string $valtoken
 * @property string $avatar
 * @property string $timezone
 *
 * @property Blogger[] $bloggers
 * @property Comments[] $comments
 * @property CourseCalendars[] $courseCalendars
 * @property CourseEnrollments[] $courseEnrollments
 * @property CourseCalendars[] $ids
 * @property CourseFollowers[] $courseFollowers
 * @property Courses[] $cs
 * @property CourseTeachers[] $courseTeachers
 * @property Downloads[] $downloads
 * @property BbiiMember $bbiiMember
 */
class Profile extends \yii\db\ActiveRecord
{
    public $avatarFile = null;
    public $formPassword=null;
    public $repeatPassword=null;
    public $oldPassword=null;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //creating profile
            [['fname', 'lname', 'email', 'username', 'formPassword', 'mobile', 'birthdate'], 'required', 'on'=>'register'],
            [['repeatPassword'], 'compare', 'compareAttribute'=>'formPassword', 'message'=>Yii::t('app', "Passwords don't match"), 'on'=>'register'],
            
            //updating profile
            [['fname', 'lname','mobile', 'birthdate'], 'required', 'on'=>'update'],
            
            [['formPassword'], 'required', 'when' => function($model) {return $model->oldPassword != '';} ,'on'=>'update', ],
            
            [['repeatPassword'], 'required', 'when' => function($model) {return $model->oldPassword != '';} ,'on'=>'update', ],            
            [['repeatPassword'], 'compare', 'compareAttribute'=>'formPassword', 'when' => function($model) {return $model->oldPassword != '';},'on'=>'update'],
            
            [['oldPassword'], function ($attribute, $params){
                    if(!\Yii::$app->security->validatePassword($this->oldPassword, $this->password))
                        $this->addError($attribute, Yii::t('app', "Incorrect Old Password"));
                },'enableClientValidation'=>false, 'on'=>'update'],
            [['oldPassword'], 'required', 'when' => function($model) {return $model->oldPassword != '';} ,'on'=>'update', ],
            
            [['id', 'regtime','gender',  'codeexpiry', 'isactive'], 'integer'],
            
            [['email', 'formPassword','password', 'repeatPassword', 'oldPassword', 'recoverycode', 'valtoken'], 'string', 'max' => 64],
            
            [['avatar'], 'string', 'max' => 255],
            [['mobile'], 'string', 'max' => 20],
            [['fname', 'lname', 'username'], 'string', 'max' => 250],
            
            [['username'], 'unique'],
            [['email'], 'unique'],
            
            [['avatarFile'], 'required','when' => function($model) {return $model->avatarFile != null;}],
            [['avatarFile'], 'file', 'extensions' => 'png','maxSize'=>1024*150], //'extensions'=>'jpg, gif, png'
            //recover password
            [['email', 'username'], 'required', 'on'=>'recover'],
            [['timezone'], 'string', 'max' => 80],
       ];
    } 

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'fname' => Yii::t('app', 'First Name'),
            'lname' => Yii::t('app', 'Last Name'),
            'fullname' => Yii::t('app', 'Full Name'),
            'email' => Yii::t('app', 'Email'),
            'username' => Yii::t('app', 'Username'),
            'formPassword' => $this->scenario=='update'?Yii::t('app', 'New Password'):Yii::t('app', 'Password'),
            'repeatPassword' => Yii::t('app', 'Repeat Password'),
            'oldPassword' => Yii::t('app', 'Old Password'),
            'mobile' => Yii::t('app', 'Mobile Number'),
            'regtime' => Yii::t('app', 'Joined'),
            'recoverycode' => Yii::t('app', 'Recovery Code'),
            'codeexpiry' => Yii::t('app', 'Code Expires on'),
            'valtoken' => Yii::t('app', 'Validation Token'),  
            'avatarFile' => Yii::t('app', 'Avatar File'),  
            'avatar' => Yii::t('app', 'Avatar'),
            'image' => Yii::t('app', 'Avatar'),
            'gender' => Yii::t('app', 'Gender'),
            'birthdate' => Yii::t('app', 'Date of Birth'),
            'timezone' => Yii::t('app', 'Timezone'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBloggers()
    {
        return $this->hasMany(Blogger::className(), ['author' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comments::className(), ['author' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCourseCalendars()
    {
        return $this->hasMany(CourseCalendars::className(), ['teacher' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCourseEnrollments()
    {
        return $this->hasMany(CourseEnrollments::className(), ['uid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIds()
    {
        return $this->hasMany(CourseCalendars::className(), ['id' => 'id'])->viaTable('CourseEnrollments', ['uid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCourseFollowers()
    {
        return $this->hasMany(CourseFollowers::className(), ['uid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCs()
    {
        return $this->hasMany(Courses::className(), ['id' => 'cid'])->viaTable('CourseTeachers', ['uid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCourseTeachers()
    {
        return $this->hasMany(CourseTeachers::className(), ['uid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDownloads()
    {
        return $this->hasMany(Downloads::className(), ['uid' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBbiiMember()
    {
        return $this->hasOne(BbiiMember::className(), ['id' => 'id']);
    }
    
    /**
     * @return Formatted name
     */
    public function getFullname()
    {
        return $this->fname.' '.$this->lname;
    }
    
    /**
     * @return Profile Image URL
     */
    public function getImage()
    {
        return Yii::$app->request->baseUrl.'/'.Yii::$app->params['avatarPath'].$this->avatar;
    }
    
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert))
        {
           if($this->isNewRecord)
           {
               $this->password = $this->generatePasswd($this->formPassword); //set form password to db password
               $this->regtime = time();
               $this->valtoken = hash('sha256', time().$this->email);
           }
           else
           {
               if($this->scenario=='update')
               {
                   if($this->formPassword!='')
                   {
                        $this->password = $this->generatePasswd($this->formPassword); //change to New Password
                   }
               }
               else if($this->scenario=='recover')
               {
                    $this->codeexpiry = time()+(24*3*60*60);
                    $this->recoverycode = \Yii::$app->security->generateRandomString();
               }
               
           }
           return true;
        } 
        return false;
    }
    
    public function generatePasswd($plain)
    {
        return \Yii::$app->security->generatePasswordHash($plain, 14);
    }
}

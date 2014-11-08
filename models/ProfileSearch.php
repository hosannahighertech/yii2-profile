<?php
 
namespace hosanna\profile\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use hosanna\profile\models\Profile;

/**
 * ProfileSearch represents the model behind the search form about `\app\models\Profile`.
 */
class ProfileSearch extends Profile
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['email', 'fname', 'lname', 'username', 'mobile', 'password', 'recoverycode', 'valtoken', 'avatar'], 'safe'],
            [['regtime', 'codeexpiry', 'gender'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Profile::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->orFilterWhere(['like', 'email', $this->username])
            ->orFilterWhere(['like', 'username', $this->username])
            ->orFilterWhere(['like', 'mobile', $this->username]);

        return $dataProvider;
    }
}

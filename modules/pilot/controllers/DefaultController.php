<?php

namespace app\modules\pilot\controllers;

use app\models\Flights;
use app\models\UserPilot;
use app\models\Users;
use yii\data\ActiveDataProvider;
use yii\helpers\VarDumper;
use yii\web\Controller;
use Yii;
use yii\web\UploadedFile;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionRoster()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Users::find()->joinWith('pilot')->joinWith('pilot.rank')->andWhere('active=1')
        ]);

        $dataProvider->sort->attributes['pilot.location'] = [
            'asc' => ['user_pilot.location' => SORT_ASC],
            'desc' => ['user_pilot.location' => SORT_DESC]
        ];
        $dataProvider->sort->attributes['pilot.rank.name_en'] = [
            'asc' => ['ranks.name_en' => SORT_ASC],
            'desc' => ['ranks.name_en' => SORT_DESC]
        ];
        $dataProvider->sort->attributes['pilot.rank.name_ru'] = [
            'asc' => ['ranks.name_ru' => SORT_ASC],
            'desc' => ['ranks.name_ru' => SORT_DESC]
        ];

        return $this->render('roster', ['dataProvider' => $dataProvider]);
    }

    public function actionProfile($id)
    {
        if (Yii::$app->user->identity->vid == $id) {
            throw new \yii\web\ForbiddenHttpException(Yii::t('app', 'You unable to view your profile'));
        } else {
            $user = Users::find()->andWhere(['vid' => $id])->one();

            $flightsProvider = new ActiveDataProvider([
                'query' => Flights::find()->where(['user_id' => $user->vid])->limit(5),
                'sort' => false,
                'pagination' => false,
            ]);

            return $this->render(
                'profile',
                [
                    'user' => $user,
                    'flightsProvider' => $flightsProvider
                ]
            );
        }
    }

    public function actionCenter()
    {
        $user = Users::find()->andWhere(['vid' => Yii::$app->user->identity->vid])->one();

        $flightsProvider = new ActiveDataProvider([
            'query' => Flights::find()->where(['user_id' => $user->vid])->limit(5),
            'sort' => false,
            'pagination' => false,
        ]);

        return $this->render(
            'center',
            [
                'user' => $user,
                'flightsProvider' => $flightsProvider
            ]
        );
    }

    public function actionFlights($id){
        $model = new Flights;
        $params = \Yii::$app->request->get();

        $provider = $model->search($params, $id);
        $provider->pagination = ['pageSize' => 100];
        $provider->sort->defaultOrder = ['id' => SORT_ASC];

        return $this->render(
            'flights',
            [
                'id' => $id,
                'dataProvider' => $provider,
                'model' => $model
            ]
        );
    }

    public function actionEdit($id)
    {
        $user = Users::find()->andWhere(['vid' => $id])->one();
        if (!$user) {
            throw new \yii\web\HttpException(404, 'User not found');
        }

        $user->scenario = Users::SCENARIO_EDIT;

        if ($user->load(Yii::$app->request->post())) {
            if (UploadedFile::getInstance($user, 'avatar')) {
                $user->avatar = UploadedFile::getInstance($user, 'avatar');
                if (in_array($user->avatar->extension, ['gif', 'png', 'jpg'])) {
                    $dir = Yii::getAlias('@app/web/img/avatars/');
                    $extension = $user->avatar->extension;
                    $user->avatar->name = md5($user->avatar->baseName);
                    $user->avatar->saveAs($dir . $user->avatar->name . "." . $extension);
                    $user->avatar = $user->avatar->name . "." . $extension;
                }
            }

            if (!$user->validate()) {
                throw new \yii\web\HttpException(404, 'be');
            }

            $user->save();

            return $this->redirect(['profile', 'id' => $user->vid]);
        } else {
            return $this->render('edit', ['user' => $user]);
        }
    }
}

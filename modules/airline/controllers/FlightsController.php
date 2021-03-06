<?php

namespace app\modules\airline\controllers;

use app\commands\ParseController;
use app\components\Slack;
use app\models\Flights\actions\End;
use app\models\Users;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use app\models\Booking;
use app\models\Flights;

/**
 * FightController implements the CRUD actions for Flights model.
 */
class FlightsController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ]
        ];
    }

    public function actionEnd($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            $model->atc_submit = gmdate('Y-m-d H:i:s');
            $model->save();
            End::make($model);
            if (!empty($model->atc_comments)) {
                $slack = new Slack('#supervisors',
                    'ATC Comment by ' . $model->user->full_name . '(' . $model->user_id . ')' .
                    '. Mark: ' . $model->atc_rating . "\n\n" .
                    $model->atc_comments);
                $slack->sent();
            }

            return $this->redirect([
                '/pilot/feed',
                'message' => Yii::t('app', 'Thanks for feedback. Have a good day.')
            ]);
        } else {
            return $this->renderAjax(
                'end',
                [
                    'model' => $model,
                ]
            );
        }
    }

    public function actionLogbook($id = null, $type = null){
        $query = Flights::find();

        if($id){
            $query = $query->where(['user_id' => $id]);
        }

        switch($type){
            case 'fix':
                $query = $query->where(['request_fix' => 1]);
                break;
        }

        $query = $query->orderBy(['id' => SORT_DESC]);

        $flightsProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => false,
            'pagination' => [
                'pageSize' => 50,
            ]
        ]);

        return $this->render('logbook', [
                'flightsProvider' => $flightsProvider,
                'user' => $id ? Users::findOne($id) : null
            ]
        );
    }

    public function actionSquadron($id)
    {
        return $this->actionIndex(
            null,
            Flights::find()->joinWith('fleet')->where('fleet.squadron_id = ' . $id)->orderBy(
                ['id' => SORT_DESC]
            ),
            false
        );
    }

    /**
     * Lists all Flights models.
     * @param null $id
     * @param null $query
     * @param bool $partial
     * @return mixed
     */
    public function actionIndex($id = null, $query = null, $partial = false)
    {
        if ($query == null) {
            $query = $id ? Flights::find()->where(['user_id' => $id])->orderBy(['id' => SORT_DESC]) : Flights::find();
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if ($partial == false) {
            return $this->render(
            'view',
            [
                'user_id' => $id,
                'dataProvider' => $dataProvider,
                'from_view' => false,
                'init' => true
            ]
        );
        } else {
            return $this->renderAjax(
                'view',
                [
                    'user_id' => $id,
                    'dataProvider' => $dataProvider,
                    'from_view' => false
                ]
            );
        }
    }

    /**
     * Displays a single Flights model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        $query = Flights::find()->where(['user_id' => $model->user_id])->orderBy(
            ['id' => SORT_DESC]
        );

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        return $this->render(
            'view',
            [
                'user_id' => $model->user_id,
                'model' => $model,
                'dataProvider' => $dataProvider,
                'init' => true
            ]
        );
    }

    /**
     * Displays a single Flights model.
     * @param integer $id
     * @return mixed
     */
    public function actionInfo($id)
    {
        $model = $this->findModel($id);
        $suspensions = new ActiveDataProvider([
            'query' => $model->getSuspensions()
        ]);
        return $this->render(
            'info',
            [
                'model' => $model,
                'user_id' => $model->user_id,
                'suspensions' => $suspensions
            ]
        );
    }

    public function actionBriefing()
    {
        $model = Booking::find()->where('status < '.Booking::BOOKING_FLIGHT_END)->andWhere(['user_id' => Yii::$app->user->identity->vid])->one();

        return $this->render(
            'briefing',
            [
                'model' => $model,
                'user_id' => $model->user_id,
            ]
        );
    }

    /**
     * Updates an existing Flights model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render(
                'update',
                [
                    'model' => $model,
                ]
            );
        }
    }

    public function actionMapdata($id = null)
    {
        echo Flights::prepareTrackerData($id);
    }

    public function actionCurrent($id = null)
    {
        $flight = $this->findModel($id);
        echo json_encode(
            [
                'lat' => $flight->lastTrack->latitude,
                'lon' => $flight->lastTrack->longitude,
                'hdg' => $flight->lastTrack->heading
            ]
        );
    }

    public function actionBooking()
    {
        $data = ['id' => 0];
        $model = Booking::find()->where('status < ' . Booking::BOOKING_FLIGHT_END)->andWhere(['user_id' => Yii::$app->user->identity->vid])->one();

        if ($model->flight) {
            $data['id'] = (int) $model->id;
        }

        return json_encode($data);
    }

    public function actionDetails($id = null)
    {
        $model = $this->findModel($id);
        return $this->renderPartial('details', ['model' => $model]);
    }

    public function actionFix($id)
    {
        Flights\Fix::accept($id);
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionRejectfix($id)
    {
        Flights\Fix::reject($id);
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionRequest($id){
        Flights\Fix::request($id);
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Finds the Flights model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Flights the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Flights::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
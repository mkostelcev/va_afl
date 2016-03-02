<?php

namespace app\modules\airline\controllers;

use Yii;
use app\models\Fleet;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * FleetController implements the CRUD actions for Fleet model.
 */
class FleetController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Fleet models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Fleet::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Fleet model.
     * @param integer $id
     * @param string $regnum
     * @return mixed
     */
    public function actionView($id, $regnum)
    {
        return $this->render('view', [
            'model' => $this->findModel($id, $regnum),
        ]);
    }

    /**
     * Creates a new Fleet model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Fleet();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id, 'regnum' => $model->regnum]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Fleet model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @param string $regnum
     * @return mixed
     */
    public function actionUpdate($id, $regnum)
    {
        $model = $this->findModel($id, $regnum);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id, 'regnum' => $model->regnum]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Fleet model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @param string $regnum
     * @return mixed
     */
    public function actionDelete($id, $regnum)
    {
        $this->findModel($id, $regnum)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Fleet model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @param string $regnum
     * @return Fleet the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id, $regnum)
    {
        if (($model = Fleet::findOne(['id' => $id, 'regnum' => $regnum])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
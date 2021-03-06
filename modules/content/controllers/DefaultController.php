<?php

namespace app\modules\content\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

use app\components\Levels;
use app\models\Services\notifications\News;
use app\models\Services\notifications\Notifications;
use app\models\Content;

class DefaultController extends Controller
{
    /**
     * Lists all Content models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Content::find(),
        ]);

        return $this->render(
            'index',
            [
                'dataProvider' => $dataProvider,
            ]
        );
    }

    public function actionLike(){
        $model = $this->findModel(\Yii::$app->request->post('cid'));
        $model->like(Yii::$app->user->identity->vid);
        Levels::addExp(5, \Yii::$app->user->identity->vid);
    }

    public function actionComment(){
        $model = $this->findModel(\Yii::$app->request->post('cid'));
        $model->comment(Yii::$app->user->identity->vid, \Yii::$app->request->post('text'));
        Levels::addExp(7, \Yii::$app->user->identity->vid);
    }

    public function actionComments($id){
        $model = $this->findModel($id);
        return $this->renderPartial('comments', ['model' => $model]);
    }

    /**
     * Displays a single Content model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render(
            'view',
            [
                'model' => Content::view($id),
            ]
        );
    }

    /**
     * Creates a new Content model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Content();
        if (isset($_POST['category_id'])) {
            $model->category = $_POST['category_id'];
        }
        $model->author = Yii::$app->user->identity->vid;

        if ($model->load(Yii::$app->request->post())) {
            $img = UploadedFile::getInstance($model, 'img_file');
            if (isset($img)) {
                if ($img->size !== 0 && in_array($img->extension, ['gif', 'png', 'jpg'])) {
                    $extension = $img->extension;
                    $img->name = md5($img->baseName);
                    if ($img->saveAs(Yii::getAlias('@app/web/img/content/') . $img->name . "." . $extension)) {
                        $model->img = $img->name . "." . $extension;
                    }
                } else {
                    $model->img = null;
                }
            }

            $preview = UploadedFile::getInstance($model, 'preview_file');
            if (isset($preview)) {
                if ($preview->size !== 0 && in_array($preview->extension, ['gif', 'png', 'jpg'])) {
                    $extension = $preview->extension;
                    $preview->name = md5($preview->baseName);
                    if ($preview->saveAs(Yii::getAlias('@app/web/img/content/preview/') . $preview->name . "." . $extension)) {
                        $model->preview = $preview->name . "." . $extension;
                    }
                } else {
                    $model->preview = null;
                }
            }

            if(empty($model->machine_name)){
                $model->machine_name = null;
            }

            if ($model->validate()) {
                if($model->save()){
                    if($model->categoryInfo->notifications_mail == 1){
                        News::add($model);
                    }

                    if($model->categoryInfo->notifications == 1){
                        Notifications::add($model);
                    }

                    Levels::addExp(100, \Yii::$app->user->identity->vid);
                }
            } else {
                throw new \yii\web\HttpException(500, Yii::t('app', 'Error'));
            }
            return $this->redirect(['view/' . $model->id]);
        } else {
            return $this->render(
                'create',
                [
                    'model' => $model,
                ]
            );
        }
    }

    /**
     * Updates an existing Content model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can($model->categoryInfo->access_edit)
            && (!Yii::$app->user->can($model->categoryInfo->access_edit) && !empty($model->categoryInfo->access_edit))
        ) {
            throw new \yii\web\HttpException(403, Yii::t('app', 'Forbidden'));
        }

        if ($model->load(Yii::$app->request->post())) {
            $img = UploadedFile::getInstance($model, 'img_file');
            if (isset($img)) {
                if ($img->size !== 0 && in_array($img->extension, ['gif', 'png', 'jpg'])) {
                    $extension = $img->extension;
                    $img->name = md5($img->baseName);
                    if ($img->saveAs(Yii::getAlias('@app/web/img/content/') . $img->name . "." . $extension)) {
                        $model->img = $img->name . "." . $extension;
                    }
                } else {
                    $model->img = null;
                }
            }

            $preview = UploadedFile::getInstance($model, 'preview_file');
            if (isset($preview)) {
                if ($preview->size !== 0 && in_array($preview->extension, ['gif', 'png', 'jpg'])) {
                    $extension = $preview->extension;
                    $preview->name = md5($preview->baseName);
                    if ($preview->saveAs(Yii::getAlias('@app/web/img/content/preview/') . $preview->name . "." . $extension)) {
                        $model->preview = $preview->name . "." . $extension;
                    }
                } else {
                    $model->preview = null;
                }
            }

            if(empty($model->machine_name)){
                $model->machine_name = null;
            }

            if ($model->validate()) {
                $model->update();
            } else {
                throw new \yii\web\HttpException(500, Yii::t('app', 'Error'));
            }
            return $this->redirect(['view/' . $model->id]);
        } else {
            return $this->render(
                'update',
                [
                    'model' => $model,
                ]
            );
        }
    }

    /**
     * Deletes an existing Content model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!Yii::$app->user->can('content/edit')
            && (!Yii::$app->user->can($model->categoryInfo->access_edit) && !empty($model->categoryInfo->access_edit))
        ) {
            throw new \yii\web\HttpException(403, Yii::t('app', 'Forbidden'));
        }

        $model->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Content model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Content the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Content::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}

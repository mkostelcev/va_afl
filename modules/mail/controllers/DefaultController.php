<?php

namespace app\modules\mail\controllers;

use Yii;
use yii\httpclient\Client;
use yii\web\Controller;
use yii\web\HttpException;

/**
 * Default controller for the `mail` module
 */
class DefaultController extends Controller
{
    public function actionIndex()
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl('http://api.va-afl.su/chat/default/list')
            ->setData(
                [
                    'vid' => Yii::$app->user->identity->vid
                ]
            )
            ->send();

        return $this->render('index', ['content' => json_decode($response->content, true), 'type' => 1]);
    }

    public function actionCompose($id = null)
    {
        $status = 0;

        if (Yii::$app->request->post()) {
            $client = new Client();
            $data = [
                'from' => Yii::$app->user->identity->vid,
                'text' => Yii::$app->request->post('text'),

            ];

            if ($id == null) {
                $data = array_merge(
                    $data,
                    [
                        'chat_topic' => Yii::$app->request->post('topic'),
                        'to' => Yii::$app->request->post('to'),
                        'chat_separated' => count(Yii::$app->request->post('to')) > 1 ? true : false
                    ]
                );
            } else {
                $data = array_merge(
                    $data,
                    [
                        'chat_separated' => false,
                        'chat_id' => $id,
                    ]
                );
            }

            $response = $client->createRequest()
                ->setMethod('post')
                ->setUrl('http://api.va-afl.su/chat/default/send')
                ->setData($data)
                ->send();

            $status = ($response->statusCode == 200 ? 2 : 1);
            Yii::trace($response->content);
        }

        /*
         * TODO: переадресация в чат
         * if($chat_id = null){
            $this->redirect('/mail/details/');
        }*/

        return $this->render('compose', ['status' => $status, 'type' => 3, 'chat' => $id]);
    }

    public function actionChat($id)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl('http://api.va-afl.su/chat/default/chat')
            ->setData(
                [
                    'chat_id' => $id,
                    'vid' => Yii::$app->user->identity->vid,
                ]
            )
            ->send();

        if ($response->statusCode != 200) {
            throw new HttpException(404);
        }

        return $this->render(
            'details',
            [
                'data' => json_decode($response->content, true)['data'],
                'type' => 0, //TODO: Сделать проверку
            ]
        );
    }
}
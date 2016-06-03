<?php

namespace app\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "content".
 *
 * @property integer $id
 * @property string $name_ru
 * @property string $name_en
 * @property string $text_ru
 * @property string $text_en
 * @property string $img
 * @property string $preview
 * @property int $author
 * @property string $created
 */
class Content extends \yii\db\ActiveRecord
{
    public static $fields = ['name_en', 'name_ru', 'text_ru', 'text_en', 'description_ru', 'description_en'];
    /**
     * @inheritdoc
     */

    public $img_file;
    public $preview_file;

    public static function tableName()
    {
        return 'content';
    }

    public static function news(){
        return self::prepare(self::find()->joinWith('categoryInfo')->where(['content_categories.news' => 1])->orderBy('created desc')->all());
    }

    public static function prepare($mNews)
    {
        $news = [];
        foreach ($mNews as $new) {
            if (empty($new->access) || Yii::$app->user->can($new->access)) {
                $news[] = $new;
            }
        }
        return $news;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'name_ru', 'name_en'], 'required'],
            [['category', 'author', 'views'], 'integer'],
            [['text_ru', 'text_en'], 'string'],
            [['created'], 'safe'],
            [['name_ru', 'name_en'], 'string', 'max' => 50],
            [['description_ru', 'description_en'], 'string', 'max' => 255],
            [['img', 'preview'], 'string', 'skipOnEmpty' => true, 'max' => 255],
            [['machine_name'], 'string', 'max' => 100],
            [['machine_name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name_ru' => Yii::t('app', 'Name') .' '.Yii::t('app', '(Ru.)'),
            'name_en' => Yii::t('app', 'Name') .' '.Yii::t('app', '(En.)'),
            'text_ru' => Yii::t('app', 'Text') .' '.Yii::t('app', '(Ru.)'),
            'text_en' => Yii::t('app', 'Text') .' '.Yii::t('app', '(En.)'),
        ];
    }

    /**
     * Вернёт имя
     * @return string
     */
    public function getName()
    {
        return $this->getLocale('name_ru', 'name_en');
    }

    /**
     * Вернёт текст
     * @return string
     */
    public function getText()
    {
        return $this->getLocale('text_ru', 'text_en');
    }

    public function getDescription()
    {
        return $this->getLocale('description_ru', 'description_en');
    }

    public function getAuthorUser()
    {
        return $this->hasOne('app\models\Users', ['vid' => 'author']);
    }

    public function getCategoryInfo()
    {
        return $this->hasOne(ContentCategories::className(), ['id' => 'category']);
    }

    public function getLikes()
    {
        return $this->hasMany('app\models\ContentLikes', ['content_id' => 'id']);
    }

    public function getLikesCount()
    {
        return ContentLikes::find()->where(['content_id' => $this->id])->count();
    }

    public function getComments()
    {
        return $this->hasMany('app\models\ContentComments', ['content_id' => 'id']);
    }

    public function getCommentsCount()
    {
        return ContentComments::find()->where(['content_id' => $this->id])->count();
    }

    public function getImgLink(){
        if(strpos($this->img, 'http://') !== false){
            return $this->img;
        }else{
            return "/img/content/{$this->img}";
        }
    }

    public function getCreatedDT(){
        return new \DateTime($this->created);
    }

    /**
     * Возвращает переменную взависимости от языка
     * @param $ru string
     * @param $en string
     * @return string
     */
    private function getLocale($ru, $en)
    {
        return Yii::$app->language == 'RU' ? $this->$ru : $this->$en;
    }

    public function getLike()
    {
        return ContentLikes::check($this->id, Yii::$app->user->identity->vid);
    }

    public function like($user){
        $like = new ContentLikes();
        $like->content_id = $this->id;
        $like->user_id = $user;
        $like->submit = gmdate("Y-m-d H:i:s");
        $like->save();

        \app\models\Services\notifications\Content::like($user, Content::findOne($this->id));
    }

    public function comment($user, $text){
        $comment = new ContentComments();
        $comment->content_id = $this->id;
        $comment->user_id = $user;
        $comment->write = gmdate("Y-m-d H:i:s");
        $comment->text = $text;
        $comment->save();

        \app\models\Services\notifications\Content::comment($user, Content::findOne($this->id), $text);

    }

    public static function template($template, $array)
    {
        $temp = self::findOne($template);

        $content = new Content();
        $content->category = 22;
        Yii::trace($array);
        foreach (self::$fields as $field) {
            $tempField = $temp->$field;
            foreach ($array as $from => $to) {
                Yii::trace($field.' - '.$from.'/'.$to);
                $content->$field = str_replace($from, $to, $tempField);
                $tempField = $content->$field;
            }
        }

        $content->save();
        var_dump($content);
        return $content->id;
    }
}

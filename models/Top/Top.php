<?php

namespace app\models\Top;

use app\components\Stats;
use app\models\Users;
use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%top}}".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $month
 * @property integer $year
 * @property integer $rating_count
 * @property integer $rating_pos
 * @property integer $rating_pos_change_day
 * @property integer $rating_pos_week
 * @property integer $rating_pos_change_week
 * @property integer $rating_type
 * @property integer $exp_count
 * @property integer $exp_pos
 * @property integer $flights_count
 * @property integer $flights_pos
 * @property integer $hours_count
 * @property integer $hours_pos
 * @property integer $pax_count
 * @property integer $pax_pos
 *
 * @property Users $user
 */
class Top extends \yii\db\ActiveRecord
{
    const RATING_OLD = 1;
    const RATING_NEW = 2;
    const RATING_ALL = 3;

    public static $count_fields = [
        'exp_count',
        'flights_count',
        'hours_count',
        'pax_count',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%top}}';
    }

    public static function all()
    {
        return self::byMonth(0,0);
    }

    /**
     * @param int $month
     * @param int $year
     * @return ActiveQuery
     */
    public static function byMonth($month, $year)
    {
        return self::find()->where(['month' => $month, 'year' => $year]);
    }

    public static function user($user, $month = 0, $year = 0)
    {
        if (!$record = self::findOne(['user_id' => $user, 'month' => $month, 'year' => $year])) {
            $record = new self;
            $record->user_id = $user;
            $record->month = $month;
            $record->year = $year;
        }

        $record->collector();
        if ($record->flights_count > 0) {
            $record->save();
        }
    }

    public function collector()
    {
        $collector = new StatsCollector($this);
        foreach (Top::$count_fields as $field) {
            $this->$field = $collector->$field;
        }
    }

    public function getUser()
    {
        return $this->hasOne(Users::className(), ['vid' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'user_id',
                    'month',
                    'year',
                    /*'exp_count',
                    'exp_pos',
                    'flights_count',
                    'flights_pos',
                    'hours_count',
                    'hours_pos',
                    'pax_count',
                    'pax_pos'*/
                ],
                'required'
            ],
            [
                [
                    'user_id',
                    'month',
                    'year',
                    'rating_count',
                    'rating_pos',
                    'rating_pos_change_day',
                    'rating_pos_week',
                    'rating_pos_change_week',
                    'rating_type',
                    'exp_count',
                    'exp_pos',
                    'flights_count',
                    'flights_pos',
                    'hours_count',
                    'hours_pos',
                    'pax_count',
                    'pax_pos'
                ],
                'integer'
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => Yii::t('app', 'User'),
            'month' => 'Month',
            'year' => Yii::t('top' ,'Year'),
            'rating_count' => Yii::t('top' ,'Rating'),
            'rating_pos' => Yii::t('top', 'Position by Rating'),
            'exp_count' => Yii::t('top' ,'Amount of Experience'),
            'exp_pos' => Yii::t('top' ,'Position by Experience'),
            'flights_count' => Yii::t('top' ,'Amount of Flights'),
            'flights_pos' => Yii::t('top' ,'Position by Flights'),
            'hours_count' => Yii::t('top' ,'Amount of Online Hours'),
            'hours_pos' => Yii::t('top' ,'Position by Online Hours'),
            'pax_count' => Yii::t('top' ,'Amount of PAXs'),
            'pax_pos' => Yii::t('top' ,'Position by PAXs'),
        ];
    }
}

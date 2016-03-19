<?php
namespace app\models;

use yii\base\InvalidParamException;
use yii\base\Model;
use Yii;

class EmailConfirm extends Model
{
    /**
     * @var User
     */
    private $_user;

    /**
     * Creates a form model given a token.
     *
     * @param  string $token
     * @param  array $config
     * @throws \yii\base\InvalidParamException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException('No token given');
        }
        $this->_user = UserPilot::findByEmailToken($token);
        if (!$this->_user) {
            throw new InvalidParamException('Invalid token');
        }
        parent::__construct($config);
    }

    /**
     * Confirm email.
     *
     * @return boolean if email was confirmed.
     */
    public function confirmEmail()
    {
        $user = $this->_user;
        $user->status = UserPilot::STATUS_ACTIVE;
        $user->email_token = null;

        return $user->save();
    }
}
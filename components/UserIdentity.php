<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	private $_id;
	const ERROR_EMAIL_INVALID   = 3;
	const ERROR_STATUS_NOTACTIV = 4;
	const ERROR_STATUS_BAN      = 5;
    
    const LOGIN_TOKEN = 'logintoken';
    
	/**
	 * Authenticates a user.
     * 
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
        
        $user=User::model()->notsafe()->findByAttributes(array('username' => $this->username));
        
		if ($user === null) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
        } elseif (Yii::app()->getModule('user')->encrypting($this->password) !== $user->password) {
			$this->errorCode = self::ERROR_PASSWORD_INVALID;
        } elseif ($user->status == 0 && Yii::app()->getModule('user')->loginNotActiv == false) {
			$this->errorCode = self::ERROR_STATUS_NOTACTIV;
        } elseif ($user->status == -1) {
			$this->errorCode = self::ERROR_STATUS_BAN;
        } else {
			$this->_id       = $user->id;
			$this->username  = $user->username;
			$this->errorCode = self::ERROR_NONE;
		}
        
        // Generate a login token and save it in the DB
        $user->logintoken = sha1(uniqid(mt_rand(), true));
        $user->save();
        
        //the login token is saved as a state
        $this->setState(self::LOGIN_TOKEN, $user->logintoken);
        
        return $this->errorCode==self::ERROR_NONE;
        
	}
    
    /**
    * @return integer the ID of the user record
    */
	public function getId()
	{
		return $this->_id;
	}
}
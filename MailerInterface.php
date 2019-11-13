<?php

/*
 * This file is part of the Dektrium project.
 *
 * (c) Dektrium project <http://github.com/dektrium/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace dektrium\user;

use dektrium\user\models\Token;
use dektrium\user\models\User;


/**
 * Mailer.
 *
 * @author Dmitry Erofeev <dmeroff@gmail.com>
 */
interface MailerInterface
{

    /**
     * @return string
     */
    public function getWelcomeSubject();


    /**
     * @param string $welcomeSubject
     */
    public function setWelcomeSubject($welcomeSubject);

    /**
     * @return string
     */
    public function getConfirmationSubject();

    /**
     * @param string $confirmationSubject
     */
    public function setConfirmationSubject($confirmationSubject);

    /**
     * @return string
     */
    public function getReconfirmationSubject();

    /**
     * @param string $reconfirmationSubject
     */
    public function setReconfirmationSubject($reconfirmationSubject);

    /**
     * @return string
     */
    public function getRecoverySubject();

    /**
     * @param string $recoverySubject
     */
    public function setRecoverySubject($recoverySubject);

    /** @inheritdoc */
    public function init();

    /**
     * Sends an email to a user after registration.
     *
     * @param User  $user
     * @param Token $token
     * @param bool  $showPassword
     *
     * @return bool
     */
    public function sendWelcomeMessage(User $user, Token $token = null, $showPassword = false);

    /**
     * Sends an email to a user with confirmation link.
     *
     * @param User  $user
     * @param Token $token
     *
     * @return bool
     */
    public function sendConfirmationMessage(User $user, Token $token);

    /**
     * Sends an email to a user with reconfirmation link.
     *
     * @param User  $user
     * @param Token $token
     *
     * @return bool
     */
    public function sendReconfirmationMessage(User $user, Token $token);

    /**
     * Sends an email to a user with recovery link.
     *
     * @param User  $user
     * @param Token $token
     *
     * @return bool
     */
    public function sendRecoveryMessage(User $user, Token $token);


}

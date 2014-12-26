<?php

namespace Avos;

/**
 * AVUser - Representation of a user object stored on Avos.
 *
 * @package  Avos
 * @author   Joe Chu <aidai524@gmail.com>
 */
class AVUser extends AVObject {

    public static $avClassName = "_User";

    /**
     * @var AVUser The currently logged-in user.
     */
    private static $currentUser = null;

    /**
     * @var string The sessionToken for an authenticated user.
     */
    protected $_sessionToken = null;

    /**
     * Returns the username.
     *
     * @return string|null
     */
    public function getUsername()
    {
        return $this->get("username");
    }

    /**
     * Sets the username for the AVUser.
     *
     * @param string $username The username
     *
     * @return null
     */
    public function setUsername($username)
    {
        return $this->set("username", $username);
    }

    /**
     * Sets the password for the AVUser.
     *
     * @param string $password The password
     *
     * @return null
     */
    public function setPassword($password)
    {
        return $this->set("password", $password);
    }

    /**
     * Returns the email address, if set, for the AVUser.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->get("email");
    }

    /**
     * Sets the email address for the AVUser.
     *
     * @param string $email The email address
     *
     * @return null
     */
    public function setEmail($email)
    {
        return $this->set("email", $email);
    }

    /**
     * Checks whether this user has been authenticated.
     *
     * @return boolean
     */
    public function isAuthenticated()
    {
        return $this->_sessionToken !== null;
    }

    /**
     * Signs up the current user, or throw if invalid.
     * This will create a new AVUser on the server, and also persist the
     * session so that you can access the user using AVUser::getCurrentUser();
     */
    public function signUp()
    {
        if (!$this->get('username'))
        {
            throw new AVException("Cannot sign up user with an empty name");
        }
        if (!$this->get('password'))
        {
            throw new AVException("Cannot sign up user with an empty password.");
        }
        if ($this->getObjectId())
        {
            throw new AVException("Cannot sign up an already existing user.");
        }
        parent::save();
        $this->handleSaveResult(true);
    }

    /**
     * Logs in a and returns a valid AVUser, or throws if invalid.
     *
     * @param string $username
     * @param string $password
     *
     * @return AVUser
     *
     * @throws AVException
     */
    public static function logIn($username, $password)
    {
        if (!$username)
        {
            throw new AVException("Cannot log in user with an empty name");
        }
        if (!$password)
        {
            throw new AVException("Cannot log in user with an empty password.");
        }
        $data = array("username" => $username, "password" => $password);
        $result = AVClient::_request("GET", "/login", "", $data);
        $user = new AVUser();
        $user->_mergeAfterFetch($result);
        $user->handleSaveResult(true);
        AVClient::getStorage()->set("user", $user);

        return $user;
    }

    /**
     * Logs in with mobile phone and returns a valid AVUser, or throws if invalid.
     *
     * @param $mobilePhoneNumber
     * @param $password
     * @return AVUser
     * @throws AVException
     */
    public static function logInWithMobilePhone($mobilePhoneNumber, $password)
    {
        if (!$mobilePhoneNumber)
        {
            throw new AVException("Cannot log in user with an empty phone");
        }
        if (!$password)
        {
            throw new AVException("Cannot log in user with an empty password.");
        }
        $data = array("mobilePhoneNumber" => $mobilePhoneNumber, "password" => $password);
        $result = AVClient::_request("GET", "/login", "", $data);
        $user = new AVUser();
        $user->_mergeAfterFetch($result);
        $user->handleSaveResult(true);
        AVClient::getStorage()->set("user", $user);

        return $user;
    }

    /**
     * @param $mobilePhoneNumber
     * @throws AVException
     */
    public static function requestLoginSmsCode($mobilePhoneNumber)
    {
        $json = json_encode(array('mobilePhoneNumber' => $mobilePhoneNumber));
        AVClient::_request('POST', '/requestLoginSmsCode', null, $json);
    }

    /**
     * @param $mobilePhoneNumber
     * @param $smsCode
     * @return AVUser
     * @throws AVException
     */
    public static function logInWithSmsCode($mobilePhoneNumber, $smsCode)
    {
        if (!$mobilePhoneNumber)
        {
            throw new AVException("Cannot log in user with an empty phone");
        }
        if (!$smsCode)
        {
            throw new AVException("Cannot log in user with an empty sms code.");
        }
        $data = array("mobilePhoneNumber" => $mobilePhoneNumber, "smsCode" => $smsCode);
        $result = AVClient::_request("GET", "/login", "", $data);
        $user = new AVUser();
        $user->_mergeAfterFetch($result);
        $user->handleSaveResult(true);
        AVClient::getStorage()->set("user", $user);

        return $user;
    }

    /**
     * Log out the current user.  This will clear the storage and future calls
     *   to current will return null
     *
     * @return null
     */
    public static function logOut()
    {
        if (AVUser::getCurrentUser())
        {
            static::$currentUser = null;
        }
        AVClient::getStorage()->remove('user');
    }

    /**
     * After a save, perform User object specific logic.
     *
     * @param boolean $makeCurrent Whether to set the current user.
     *
     * @return null
     */
    private function handleSaveResult($makeCurrent = false)
    {
        if (isset($this->serverData['password']))
        {
            unset($this->serverData['password']);
        }
        if (isset($this->serverData['sessionToken']))
        {
            $this->_sessionToken = $this->serverData['sessionToken'];
            unset($this->serverData['sessionToken']);
        }
        if ($makeCurrent)
        {
            static::$currentUser = $this;
            static::saveCurrentUser();
        }
        $this->rebuildEstimatedData();
    }

    /**
     * Retrieves the currently logged in AVUser with a valid session,
     * either from memory or the storage provider, if necessary.
     *
     * @return AVUser|null
     */
    public static function getCurrentUser()
    {
        if (static::$currentUser instanceof AVUser)
        {
            return static::$currentUser;
        }
        $storage = AVClient::getStorage();
        $userData = $storage->get("user");
        if ($userData instanceof AVUser)
        {
            static::$currentUser = $userData;

            return $userData;
        }
        if (isset($userData["id"]) && isset($userData["_sessionToken"]))
        {
            $user = AVUser::create("_User", $userData["id"]);
            unset($userData["id"]);
            $user->_sessionToken = $userData["_sessionToken"];
            unset($userData["_sessionToken"]);
            foreach ($userData as $key => $value)
            {
                $user->set($key, $value);
            }
            $user->_opSetQueue = array();
            static::$currentUser = $user;

            return $user;
        }

        return null;
    }

    /**
     * @param $objectId
     * @return mixed
     * @throws AVException
     */
    public static function findByObjectId($objectId)
    {
        if (!$objectId)
        {
            throw new AVException("Cannot find user with an empty object id");
        }
        $user = AVClient::_request("GET", "/users/" . $objectId, "", null);

        return $user;
    }

    /**
     * @param $objectId
     * @param $data
     * @return mixed
     * @throws AVException
     */
    public static function updateByObjectId($objectId, $data)
    {
        if (!$objectId)
        {
            throw new AVException("Cannot update user with an empty object id");
        }
        $updatedAt = AVClient::_request("PUT", "/users/" . $objectId, "", $data);

        return $updatedAt;
    }

    /**
     * @param $objectId
     * @param $oldPssword
     * @param $newPssword
     * @return mixed
     * @throws AVException
     */
    public static function updatePassword($objectId, $oldPssword, $newPssword)
    {
        if (!$objectId)
        {
            throw new AVException("Cannot update user with an empty object id");
        }
        if (!$oldPssword)
        {
            throw new AVException("Cannot change password with an empty old pssword");
        }
        if (!$newPssword)
        {
            throw new AVException("Cannot change password with an empty new password");
        }
        $data = array('old_password' => $oldPssword, 'new_password' => $newPssword);
        $updatedAt = AVClient::_request("PUT", "/users/" . $objectId . 'updatePassword', "", $data);

        return $updatedAt;
    }

    /**
     * @return mixed
     */
    public static function findAll()
    {
        $users = AvClient::__request("GET", "/users", "", null);

        return $users;
    }

    /**
     * Persists the current user to the storage provider.
     *
     * @return null
     */
    protected static function saveCurrentUser()
    {
        $storage = AVClient::getStorage();
        $storage->set('user', AVUser::getCurrentUser());
    }

    /**
     * @param $objectId
     */
    public static function deleteByObjectId($objectId)
    {
        AvClient::__request("DELETE", "/users/" . $objectId, "", null);
    }

    /**
     * Returns the session token, if available
     *
     * @return string|null
     */
    public function getSessionToken()
    {
        return $this->_sessionToken;
    }

    /**
     * Returns true if this user is the current user.
     *
     * @return boolean
     */
    public function isCurrent()
    {
        if (AVUser::getCurrentUser() && $this->getObjectId())
        {
            if ($this->getObjectId() == AVUser::getCurrentUser()->getObjectId())
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Save the current user object, unless it is not signed up.
     *
     * @param bool $useMasterKey
     * @throws AVException
     * @return null
     *
     */
    public function save($useMasterKey = false)
    {
        if ($this->getObjectId())
        {
            parent::save($useMasterKey);
        } else
        {
            throw new AVException("You must call signUp to create a new User.");
        }
    }

    /**
     * @param $email
     * @throws AVException
     */
    public static function requestEmailVerify($email)
    {
        $json = json_encode(array('email' => $email));
        AVClient::_request('POST', '/requestEmailVerify', null, $json);
    }

    /**
     * Requests a password reset email to be sent to the specified email
     * address associated with the user account.  This email allows the user
     * to securely reset their password on the AV site.
     *
     * @param string $email
     *
     * @return null
     */
    public static function requestPasswordReset($email)
    {
        $json = json_encode(array('email' => $email));
        AVClient::_request('POST', '/requestPasswordReset', null, $json);
    }

    /**
     * @param $verifyCode
     * @throws AVException
     */
    public static function verifyMobilePhone($verifyCode)
    {
        AVClient::_request('POST', '/verifyMobilePhone/' . $verifyCode, null, null);
    }

    /**
     * @param $mobilePhoneNumber
     * @throws AVException
     */
    public static function requestMobilePhoneVerify($mobilePhoneNumber)
    {
        $json = json_encode(array('$mobilePhoneNumber' => $$mobilePhoneNumber));
        AVClient::_request('POST', '/requestPasswordReset', null, $json);
    }

    /**
     * @param $mobilePhoneNumber
     * @throws AVException
     */
    public static function requestPasswordResetBySmsCode($mobilePhoneNumber)
    {
        $json = json_encode(array('$mobilePhoneNumber' => $$mobilePhoneNumber));
        AVClient::_request('POST', '/requestPasswordResetBySmsCode', null, $json);
    }

    /**
     * @param $verifyCode
     * @param $newPassword
     * @throws AVException
     */
    public static function resetPasswordBySmsCode($verifyCode, $newPassword)
    {
        $json = json_encode(array('password' => $newPassword));
        AVClient::_request('PUT', '/resetPasswordBySmsCode/' . $verifyCode, null, $json);
    }

    /**
     * @ignore
     */
    public static function _clearCurrentUserVariable()
    {
        static::$currentUser = null;
    }

}
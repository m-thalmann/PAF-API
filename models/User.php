<?php

namespace API\Models;

use API\Auth\Authorization;
use API\Inc\Validation;
use PAF\Model\Model;

/**
 * @tablename users
 */
class User extends Model {
    /**
     * @prop
     * @primary
     * @autoincrement
     * @var integer
     */
    public $id;

    /**
     * @prop
     * @email
     * @var string
     */
    public $email;

    /**
     * @prop
     * @var string
     * @output false
     */
    public $password;

    /**
     * @prop
     * @var string
     * @editable false
     * @output false
     */
    public $passwordSalt = null;

    /**
     * @prop
     * @var timestamp|null
     * @editable false
     * @output false
     */
    public $lastUpdated;

    public function __set($property, $value) {
        switch ($property) {
            case 'password':
                if ($this->passwordSalt === null) {
                    $this->initSalt();
                }

                $value = Authorization::encryptPassword(
                    $value,
                    $this->passwordSalt
                );

                break;
        }
        parent::__set($property, $value);
    }

    public function initSalt() {
        $this->editValue(
            "passwordSalt",
            md5(random_int(PHP_INT_MIN, PHP_INT_MAX)),
            true,
            true
        );
    }

    public function getErrors() {
        $errors = $this->getValidationErrors();

        $res = [];

        foreach ($errors as $prop => $error) {
            switch ($prop) {
                case "email":
                    $res[$prop] = Validation::getValidationErrorMessage(
                        $error->getError(),
                        "Email",
                        true
                    );
                    break;
                case "password":
                    $res[$prop] = Validation::getValidationErrorMessage(
                        $error->getError(),
                        "Password",
                        true
                    );
                    break;
            }
        }

        return $res;
    }
}

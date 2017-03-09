<?php

namespace Afosto\Bp\Components;

use Afosto\Bp\Exceptions\ValidationException;

class Validator {

    /**
     * Default type
     */
    const TYPE_VALUE = 1;

    /**
     * Many relation type
     */
    const TYPE_MANY = 2;

    /**
     * One relation type
     */
    const TYPE_HAS_ONE = 3;

    /**
     * The attribute key
     * @var string
     */
    public $key;

    /**
     * Owner for this attribute
     * @var Model
     */
    private $_owner;

    /**
     * Used to call proper validation function
     * @var array
     */
    private $_callable;

    /**
     * Optional
     * @var array
     */
    private $_callableParams = [];

    /**
     * True for required attribute
     * @var bool
     */
    private $_required;

    /**
     * Integer, float, string, boolean
     * @var string
     */
    private $_type;

    /**
     * Full namespaced path to the new model
     * @var string
     */
    private $_modelPath;

    /**
     * Validator constructor.
     *
     * @param Model $owner
     * @param       $rule
     */
    public function __construct(Model $owner, array $rule) {
        if (count($rule) == 3) {
            $rule[] = null;
        }
        list($key, $type, $required, $validation) = $rule;
        $this->key = $key;
        $this->_owner = $owner;
        $this->_type = $type;

        if (is_numeric($validation)) {
            $this->_callable = [$this->_owner, 'validateMaxLength'];
            $this->_callableParams = [$validation, $this->key];
        } else if ($validation !== null && ($this->getRelationType() == self::TYPE_VALUE || $this->getRelationType() == self::TYPE_HAS_ONE)) {
            $this->_callable = [$this->_owner, $validation];
        }

        if ($this->getRelationType() != self::TYPE_VALUE) {
            if (substr($type, 0, 1) === '\\') {
                if ($this->getRelationType() === self::TYPE_MANY) {
                    $this->_modelPath = substr($this->_type, 0, -2);
                } else {
                    $this->_modelPath = $this->_type;
                }
            } else {
                if ($this->getRelationType() === self::TYPE_MANY) {
                    $this->_modelPath = $this->_owner->getNameSpace() . '\\' . substr($this->_type, 0, -2);
                } else {
                    $this->_modelPath = $this->_owner->getNameSpace() . '\\' . $this->_type;
                }
            }
        }
        $this->_required = (bool)$required;
    }

    /**
     * Run the validation
     */
    public function run() {
        if ($this->_required) {
            $this->_owner->validateRequired($this->key);
        }

        //Call the validation rule
        if ($this->_owner->{$this->key} !== null) {

            //Cast the value
            switch ($this->_type) {
                case 'string':
                    $this->_owner->{$this->key} = (string)$this->_owner->{$this->key};
                    break;
                case 'integer':
                    $this->_owner->{$this->key} = (integer)$this->_owner->{$this->key};
                    break;
                case 'boolean':
                    $this->_owner->{$this->key} = (bool)$this->_owner->{$this->key};
                    break;
                case 'float':
                    $this->_owner->{$this->key} = (float)$this->_owner->{$this->key};
                    break;
            }

            //Call the validation rule
            if ($this->_callable !== null) {
                call_user_func_array($this->_callable, $this->_callableParams);
            }
        }
    }

    /**
     * @return Model
     * @throws ValidationException
     */
    public function getNewModel() {
        if (class_exists($this->_modelPath)) {
            return new $this->_modelPath;
        }

        throw new ValidationException('Model ' . $this->_modelPath . ' not found');
    }

    /**
     * @return integer
     */
    public function getRelationType() {
        if (in_array($this->_type, ['string', 'integer', 'float', 'boolean', '\DateTime'])) {
            return self::TYPE_VALUE;
        } else if (substr($this->_type, -2) == '[]') {
            return self::TYPE_MANY;
        } else {
            return self::TYPE_HAS_ONE;
        }
    }
}
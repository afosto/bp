<?php

namespace Afosto\Bp\Components;

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
        if (is_numeric($validation)) {
            $this->_callable = [$this->_owner, 'validateMaxLength'];
            $this->_callableParams = [$validation, $this->key];
        } else if ($validation !== null && ($this->_getRelationType() == self::TYPE_VALUE || $this->_getRelationType() == self::TYPE_HAS_ONE)) {
            $this->_callable = [$this->_owner, $validation];
        }
        $this->_type = $type;
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
        if ($this->_owner->{$this->key} !== null && $this->_callable !== null) {
            call_user_func_array($this->_callable, $this->_callableParams);
        }
    }

    /**
     * @return \ArrayObject|null
     */
    public function getNewValue() {
        if ($this->_getRelationType() == self::TYPE_MANY) {
            return new \ArrayObject();
        }

        return null;
    }

    /**
     * @return int
     */
    public function _getRelationType() {
        if (in_array($this->_type, ['string', 'integer', 'float', 'boolean'])) {
            return self::TYPE_VALUE;
        } else if (substr($this->_type, -2) == '[]') {
            return self::TYPE_MANY;
        } else {
            return self::TYPE_HAS_ONE;
        }
    }

}
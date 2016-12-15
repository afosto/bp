<?php

namespace Afosto\Bp\Components;

class Attribute {

    /**
     * @var string
     */
    public $key;

    /**
     * @var \ArrayObject|null|Model
     */
    public $value;

    /**
     * @var Validator
     */
    private $_validator;

    /**
     * Attribute constructor.
     *
     * @param Model $owner
     * @param       $rule
     */
    public function __construct(Model $owner, $rule) {
        $this->_validator = new Validator($owner, $rule);
        $this->key = current($rule);
        $this->value = $this->_validator->getNewValue();
    }

    /**
     * Runs the validation for this attribute
     */
    public function validate() {
        $this->_validator->run();
    }

    /**
     * @return Validator
     */
    public function getValidator() {
        return $this->_validator;
    }

}
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
    public $validator;

    /**
     * Attribute constructor.
     *
     * @param Model $owner
     * @param       $rule
     */
    public function __construct(Model $owner, $rule) {
        $this->validator = new Validator($owner, $rule);
        $this->key = current($rule);
        if ($this->validator->getRelationType() === Validator::TYPE_MANY) {
            $this->value = new \ArrayObject();
        }
    }

    /**
     * Runs the validation for this attribute
     */
    public function validate() {
        $this->validator->run();
    }

}
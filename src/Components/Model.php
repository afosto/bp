<?php

namespace Afosto\Bp\Components;

use Afosto\Bp\Exceptions\ValidationException;

abstract class Model {

    /**
     * @var Attribute[]
     */
    protected $attributes = [];

    /**
     * Returns doc block formatting
     */
    public static function getDocBlock() {
        $model = new static();
        foreach ($model->getRules() as $rule) {
            echo "* @property {$rule[1]} \${$model->getFormattedKey($rule[0], true)}\n";
        }
    }

    /**
     * @return static
     */
    public static function model() {
        return new static();
    }

    /**
     * Magic getter.
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name) {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name]->value;
        }

        return null;
    }

    /**
     * Magic setter.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value) {
        if (isset($this->attributes[$name])) {
            if ($this->attributes[$name] instanceof \ArrayObject) {
                $this->attributes[$name]->value[] = $value;
            } else {
                $this->attributes[$name]->value = $value;
            }
        }
    }

    /**
     * Model constructor.
     */
    public function __construct() {
        foreach ($this->getRules() as $rule) {
            $attribute = new Attribute($this, $rule);
            $this->attributes[$attribute->key] = $attribute;
        }
    }

    /**
     * Max length validator
     *
     * @param $length
     * @param $key
     *
     * @throws ValidationException
     */
    public function validateMaxLength($length, $key) {
        if (strlen($this->$key) > $length) {
            throw new ValidationException("{$key} is to long, maxLength is {$length}, " . strlen($this->$key) . " chars given");
        }
    }

    /**
     * Required validator
     *
     * @param $key
     *
     * @throws ValidationException
     */
    public function validateRequired($key) {
        if ($this->$key === null) {
            throw new ValidationException("{$key} is required for " . get_called_class());
        }
    }

    /**
     * Validate doubles
     *
     * @param $key
     */
    public function validateDouble($key) {
        $this->$key = number_format($this->$key, 2, '.', '');
    }

    /**
     * Validate bool
     *
     * @param $key
     *
     * @throws ValidationException
     */
    public function validateBoolean($key) {
        if (is_bool($this->$key)) {
            throw new ValidationException("{$key} is no boolean: " . $this->$key . "  given");
        }
    }

    /**
     * Set the model's attributes
     *
     * @param array $data
     */
    public function setAttributes($data) {
        if ($data instanceof Model) {
            $data = $data->getAttributes();
        }
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    protected function getMap() {
        return [];
    }

    /**
     * Returns the mapped key
     *
     * @param $key
     *
     * @return mixed
     */
    protected function getFormattedKey($key) {
        return array_key_exists($key, $this->getMap()) ? $this->getMap()[$key] : $key;
    }

    /**
     * Required, the model rules
     *
     * @return mixed
     */
    abstract public function getRules();

    /**
     * Before we validate the model
     * @return bool
     */
    protected function beforeValidate() {
        return true;
    }

    /**
     * Validate the model
     * @return bool
     */
    public function validate() {
        if ($this->beforeValidate()) {
            foreach ($this->attributes as &$attribute) {
                if ($attribute->value instanceof \ArrayObject) {
                    foreach ($attribute->value as &$item) {
                        $item->validate();
                    }
                } else if ($attribute->value instanceof Model) {
                    $attribute->value->validate();
                } else {
                    $attribute->validate();
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Returns the params as array
     *
     * @return array
     */
    public function getAttributes() {
        return $this->_toArray(false, false);
    }

    /**
     * Shorthand to return the formatted model
     * Use the mapper (getMap) to transform the keys and validate the model data based on the rule set
     *
     * @return array
     */
    public function getModel() {
        return $this->_toArray(true, true);
    }

    /**
     * Map the object to array
     *
     * @param bool $validate
     * @param bool $mapKeys
     *
     * @return array
     */
    private function _toArray($validate = false, $mapKeys = false) {
        $callee = (($validate === $mapKeys && $mapKeys === true) ? 'getModel' : 'getAttributes');
        $data = [];
        if ($validate === true) {
            $this->validate();
        }

        foreach ($this->attributes as $attribute) {
            $key = ($mapKeys === true ? $this->getFormattedKey($attribute->key) : $attribute->key);

            if ($attribute->value instanceof \ArrayObject) {
                foreach ($attribute->value as $item) {
                    $data[$key][] = $item->$callee();
                }
            } else if ($attribute->value instanceof Model) {
                $data[$key] = $attribute->value->$callee();
            } else {
                $data[$key] = $attribute->value;
            }
        }

        return $data;
    }
}
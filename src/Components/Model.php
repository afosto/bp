<?php

namespace Afosto\Bp\Components;

use Afosto\Bp\Exceptions\ValidationException;

abstract class Model {

    /**
     * @var Attribute[]
     */
    protected $attributes = [];

    /**
     * @var \ReflectionClass
     */
    private $_reflection;

    /**
     * Required, the model rules
     *
     * @return mixed
     */
    abstract public function getRules();

    /**
     * Returns doc block formatting
     * @return array
     */
    public static function getDocBlock() {
        $model = static::model();
        $docLines[] = '/**';
        foreach ($model->getRules() as $rule) {
            list($key, $property) = array_slice($rule, 0, 2);
            $docLines[] = "* @property {$property} \${$model->getFormattedKey($key, true)}" . PHP_EOL;
        }
        $docLines[] = '*/';

        return $docLines;
    }

    /**
     * Shortcut to return a new model
     *
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
        if ($this->getAttribute($name)) {
            return $this->getAttribute($name)->value;
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
        if ($this->getAttribute($name)) {
            if ($this->getAttribute($name) instanceof \ArrayObject) {
                $this->getAttribute($name)->value[] = $value;
            } else {
                $this->getAttribute($name)->value = $value;
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
     * Set the model's attributes
     *
     * @param array $data
     */
    public function setAttributes($data) {
        if ($data instanceof Model) {
            $data = $data->getAttributes();
        } else if ($data === null) {
            return;
        }

        foreach ($data as $key => $value) {
            if ($this->getAttributeRelation($key) === Validator::TYPE_MANY) {
               $this->{$key} = array_reduce($value, function ($values, $subValue) use ($key) {
                    $item = $this->getAttribute($key)->validator->getNewModel();
                    $item->setAttributes($subValue);
                    $values[] = $item;

                    return $values;
                }, []);
            } else if ($this->getAttributeRelation($key) === Validator::TYPE_HAS_ONE) {
                $newModel = $this->getAttribute($key)->validator->getNewModel();
                $newModel->setAttributes($value);
                $this->{$key} = $newModel;
            } else {
                $this->{$key} = $value;
            }
        }
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
     * Return the namespace path
     * @return string
     */
    public function getNameSpace() {
        return $this->getReflection()->getNamespaceName();
    }

    /**
     * Return the class name
     * @return string
     */
    public function getShortName() {
        return $this->getReflection()->getShortName();
    }

    /**
     * @return \ReflectionClass
     */
    protected function getReflection() {
        if ($this->_reflection === null) {
            $this->_reflection = new \ReflectionClass($this);
        }

        return $this->_reflection;
    }

    /**
     * @param $key
     *
     * @return integer
     */
    protected function getAttributeRelation($key) {
        if (isset($this->attributes[$this->getFormattedKey($key, true)])) {
            $attribute = $this->attributes[$this->getFormattedKey($key, true)];

            return $attribute->validator->getRelationType();
        }

        return Validator::TYPE_VALUE;
    }

    /**
     * @param $key
     *
     * @return Attribute|bool
     */
    protected function getAttribute($key) {
        if (isset($this->attributes[$this->getFormattedKey($key, true)])) {
            return $this->attributes[$this->getFormattedKey($key, true)];
        }

        return false;
    }

    /**
     * Return the mapping in from to style
     * @return array
     */
    protected function getMap() {
        return [];
    }

    /**
     * Returns the mapped key
     *
     * @param $key
     * @param $flip
     *
     * @return mixed
     */
    protected function getFormattedKey($key, $flip = false) {
        $keys = (($flip) ? array_flip($this->getMap()) : $this->getMap());

        return array_key_exists($key, $keys) ? $keys[$key] : $key;
    }

    /**
     * Before we validate the model
     * @return bool
     */
    protected function beforeValidate() {
        return true;
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

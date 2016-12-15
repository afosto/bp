<?php

namespace Xx\Xx\Models\Modelnames;

use Afosto\Bp\Components\Model;
use Afosto\Bp\Exceptions\ValidationException;

/**
 * @property string $name
 * @property string $address
 */
class Modelname extends Model {

    /**
     * @return array
     */
    public function getRules() {
        return [
            //Property name, type, required, maxLenght / validation callable
            ['name', 'string', true, 255],
            ['address', 'string', true, 'validateAddress'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function validateAddress() {
        if (filter_var($this->address, FILTER_VALIDATE_URL) === false) {
            throw new ValidationException('Invalid address, is not valid url');
        }
    }

}
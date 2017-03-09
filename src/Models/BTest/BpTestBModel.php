<?php

namespace Afosto\Bp\Models\BTest;

use Afosto\Bp\Components\Model;

/**
 * @property string  $nameString
 * @property integer $nameCount
 * @property boolean $nameBool
 * @property float   $nameFloat
 */
class BpTestBModel extends Model {

    /**
     * Required, the model rules
     *
     * @return mixed
     */
    public function getRules() {

        return [
            ['nameString', 'string', true, 255],
            ['nameCount', 'integer', true],
            ['nameBool', 'boolean', true],
            ['nameFloat', 'float', true],
        ];
    }

    /**
     * @return array
     */
    protected function getMap() {
        return [
            'nameString' => 'nameStringBModel',
        ];
    }

}
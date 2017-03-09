<?php

namespace Afosto\Bp\Models\ATest;

use Afosto\Bp\Components\Model;


/**
* @property string $nameString
* @property \Afosto\Bp\Models\BTest\BpTestBModel[] $names
* @property \Afosto\Bp\Models\BTest\BpTestBModel $name
* @property BpTestCModel $nameAlt
* @property integer $nameCount
* @property boolean $nameBool
*/
class BpTestAModel extends Model {

    /**
     * Required, the model rules
     *
     * @return mixed
     */
    public function getRules() {

        return [
            ['nameString', 'string', true, 255],
            ['names', '\Afosto\Bp\Models\BTest\BpTestBModel[]', true],
            ['name', '\Afosto\Bp\Models\BTest\BpTestBModel', true],
            ['nameAlt', 'BpTestCModel', true],
            ['nameCount', 'integer', false],
            ['nameBool', 'boolean', false],
        ];
    }

    /**
     * @return array
     */
    protected function getMap() {
        return [
            'nameString' => 'nameStringAModel',
            'nameCount'  => 'nameCountAModel',
            'nameBool'   => 'nameBoolAModel',
        ];
    }
}
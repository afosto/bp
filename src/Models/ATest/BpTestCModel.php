<?php

namespace Afosto\Bp\Models\ATest;

use Afosto\Bp\Components\Model;

/**
* @property string $nameString
* @property \Afosto\Bp\Models\BTest\BpTestBModel[] $names
* @property \Afosto\Bp\Models\BTest\BpTestBModel $name
* @property integer $nameCount
* @property boolean $nameBool
*/
class BpTestCModel extends Model {

    /**
     * Required, the model rules
     *
     * @return mixed
     */
    public function getRules() {
        return [
            ['nameString', 'string', false, 255],
            ['names', '\Afosto\Bp\Models\BTest\BpTestBModel[]', false],
            ['name', '\Afosto\Bp\Models\BTest\BpTestBModel', false],
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
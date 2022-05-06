<?php

declare(strict_types=1);

namespace Ruga\Db\Table\Feature;

use Ruga\Db\Row\AbstractRow;

class RowGatewayFeature extends \Laminas\Db\TableGateway\Feature\RowGatewayFeature
{
    public function getRowGatewayPrototype(): AbstractRow
    {
        return $this->constructorArguments[0];
    }
}
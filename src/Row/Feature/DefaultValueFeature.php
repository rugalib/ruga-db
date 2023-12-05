<?php
/*
 * SPDX-FileCopyrightText: 2023 Roland Rusch, easy-smart solution GmbH <roland.rusch@easy-smart.ch>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

declare(strict_types=1);

namespace Ruga\Db\Row\Feature;

use Ruga\Db\Table\Feature\MetadataFeature;

class DefaultValueFeature extends AbstractFeature
{
    /**
     * Retrieve the default values from MetadataFeature and populate columns.
     *
     * @param array $rowData
     * @param bool  $rowExistsInDatabase
     *
     * @throws \Exception
     */
    public function prePopulate(array &$rowData, bool &$rowExistsInDatabase)
    {
        $table = $this->rowGateway->getTableGateway();
        $metadataFeature = $table->getFeatureSet()->getFeatureByClassName(MetadataFeature::class);
        if (!$metadataFeature || !($metadataFeature instanceof MetadataFeature)) {
            throw new \Exception(get_class($this) . " requires " . MetadataFeature::class . " in " . get_class($table));
        }
        
        $metadata = $metadataFeature->getMetadata();
        foreach ($metadata['columns'] as $column) {
            if (array_key_exists('DEFAULT', $column)) {
                // Set default value directly in data array of the row (just in case ;-)
                if (!array_key_exists($column['NAME'], $this->rowGateway->data)) {
                    $this->rowGateway->data[$column['NAME']] = $column['DEFAULT'];
                }
                // ...and in the $rowData
                if (!array_key_exists($column['NAME'], $rowData)) {
                    $rowData[$column['NAME']] = $column['DEFAULT'];
                }
            }
        }
    }
}
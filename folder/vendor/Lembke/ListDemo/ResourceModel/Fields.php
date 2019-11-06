<?php
declare(strict_types=1);

/**
 * @category ListDemo
 * @package ListDemo
 * @copyright Copyright (c) 2019, Peter Lembke
 * @author Peter Lembke <peter@teamfakta.se>
 * @license ListDemo is distributed under the terms of the GNU General Public License
 * ListDemo is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * ListDemo is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with ListDemo.    If not, see <https://www.gnu.org/licenses/>.
 */

namespace Lembke\ListDemo\ResourceModel;

use \Lembke\ListDemo\Model\Base;

/**
 * Gets data from folder/config/fields/{$fieldsName}.json and makes sure all fields are there and with the right
 * data type, then return the data.
 * These definition files describe what fields should be in the form / list / table
 * and where to store them in what database and table.
 * Class Fields
 * @package Lembke\ListDemo\ResourceModel
 */
class Fields
{
    /** @var Base  */
    protected $base;

    /** @var string  */
    protected $configPath;

    /** @var array  */
    protected $fieldsArray = [];

    public function __construct(
        Base $base,
        string $configPath = ''
    )
    {
        $this->base = $base;
        $this->configPath = $configPath;
    }

    /**
     * Read the fields configuration file and returns its contents after validating that
     * all wanted fields exist and have the right data type
     * The configuration file is found in folder/config/fields/{$fieldsName}.json
     *
     * @param string $fieldsName | example: survey_respondents
     * @return array
     */
    public function getData(string $fieldsName = ''): array
    {
        if (isset($this->fieldsArray[$fieldsName])) {
            return $this->fieldsArray[$fieldsName];
        }

        $fieldsArray = [];
        $fileName = $this->configPath . '/fields/' . $fieldsName . '.json';
        if (file_exists($fileName) === true) {
            $fileContentJson = file_get_contents($fileName);
            $fieldsArray = $this->base->jsonDecode($fileContentJson);
        }

        $default = [
            "name" => "",
            "title" => "",
            "database_name" => "",
            "table_name" => "",
            "id_field_name" => "",
            "fields" => []
        ];
        $fieldsArray = $this->base->default($default, $fieldsArray);

        $default = [
            "name" => "",
            "type" => "text",
            "length" => 0,
            "default_value" => null,
            "index_type" => "normal",
            "description" => "",
            "label" => "",
            "form_readonly" => false,
            "form_visible" => true,
            "list_visible" => true
        ];

        foreach ($fieldsArray['fields'] as $name => $fieldData) {
            $fieldData = $this->base->default($default, $fieldData);
            $fieldsArray['fields'][$name] = $fieldData;
        }

        $this->fieldsArray[$fieldsName] = $fieldsArray;

        return $this->fieldsArray[$fieldsName];
    }

}

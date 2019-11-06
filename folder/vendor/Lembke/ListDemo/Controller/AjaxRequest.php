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

namespace Lembke\ListDemo\Controller;

use \Lembke\ListDemo\ResourceModel\Fields;
use \Lembke\ListDemo\ResourceModel\Storage;
use \Lembke\ListDemo\Model\Base;

/**
 * Controller that receive requests from the template buttons
 * From the POST data it figures out what to do.
 * Class AjaxRequest
 * @package Lembke\ListDemo\Controller
 */
class AjaxRequest
{
    /**
     * The array we got in the post_package variable. This is the incoming data.
     * @var array
     */
    protected $package;

    /**
     * The path to the config folder. Used by $fields to find a definition file,
     * and by $storage to find the storage.json that contain connection information to the database
     * @var string
     */
    protected $configPath;

    /**
     * A base class with functions I like to use
     * @var Base
     */
    protected $base;

    /**
     * The connection class to the definition files
     * @var Fields
     */
    protected $fields;

    /**
     * The connection class to the database
     * @var Storage
     */
    protected $storage;

    /**
     * Handle the incoming POST request
     * Call the right function and return the answer as json data
     * Example: http://local.demo.se/Lembke/ListDemo/JsonRequest
     *
     * @param array $data
     */
    public function execute(array $data = []): void
    {
        $out = '{"error":"Request do not contain the proper post data"}';

        if (isset($data['post_package']))
        {
            $this->configPath = $data['configPath'];
            $this->base = new Base();
            $this->fields = new Fields($this->base, $this->configPath);
            $this->storage = new Storage($this->base, $this->fields, $this->configPath);

            $package = $this->base->jsonDecode($data['post_package']);

            $default = [
                'command' => '',
                'fields_name' => '',
                'id' => '',
                'data' => []
            ];
            $package = $this->base->default($default, $package);

            $response = $this->executeCommand($package);
            $out = $this->base->jsonEncode($response);
        }

        echo $out;
    }

    /**
     * Handle the command
     *
     * @param array $package
     * @return array
     */
    protected function executeCommand(array $package = []): array
    {
        $fieldsName = $package['fields_name'];
        $id = (int) $package['id'];

        switch ($package['command']) {
            case 'setup':
                $this->storage->setup($fieldsName);
                break;
            case 'getPost':
                return $this->storage->getPost($fieldsName, $id);
                break;
            case 'putPost':
                return $this->storage->putPost($fieldsName, $package['data']);
                break;
            case 'deletePost':
                return $this->storage->deletePost($fieldsName, $id);
                break;
            case 'getList':
                return $this->storage->getList($fieldsName);
                break;
            case 'getFieldsData':
                return $this->fields->getData($fieldsName);
                break;
        }

        return [
            'answer' => false,
            'message' => 'Command unknown: ' . $package['command']
        ];
    }

}

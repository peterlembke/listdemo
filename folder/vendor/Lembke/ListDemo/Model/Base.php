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

namespace Lembke\ListDemo\Model;

use Exception;

/**
 * Class with for me commonly used commands.
 * Class Base
 * @package Lembke\ListDemo\Model
 */
class Base
{

    /**
     * Makes sure you get all default variables with at least default values, and the right data type.
     * The $default variables, You can only use: array, string, integer, float, null
     * The $in variables, You can only use: array, string, integer, float
     * @example: $in = _Default($default,$in);
     * @version 2016-01-25
     * @since   2013-09-05
     * @author  Peter Lembke
     * @param $default
     * @param $in
     * @return array
     */
    public function default(array $default = array(), array $in = array()): array
    {
        if (is_array($default) === false and is_array($in) === true) {
            return $in;
        }
        if (is_array($default) === true and is_array($in) === false) {
            return $default;
        }

        // On this level: Remove all variables that are not in default. Add all variables that are only in default.
        $answer = array_intersect_key(array_merge($default, $in), $default);

        // Check the data types
        foreach ($default as $key => $data) {
            if (gettype($answer[$key]) !== gettype($default[$key])) {
                if (is_null($default[$key]) === false) {
                    $answer[$key] = $default[$key];
                }
                continue;
            }
            if (is_null($default[$key]) === true and is_null($answer[$key]) === true) {
                $answer[$key] = '';
                continue;
            }
            if (is_array($default[$key]) === false) {
                continue;
            }
            if (count($default[$key]) === 0) {
                continue;
            }
            $answer[$key] = $this->default($default[$key], $answer[$key]);
        }

        return $answer;
    }

    /**
     * Wrapper so it is easier to change the places where json is used.
     * @param $dataArray
     * @return string
     */
    public function jsonEncode(array $dataArray = array()): string
    {
        $options = JSON_PRETTY_PRINT + JSON_PRESERVE_ZERO_FRACTION;
        $jsonString = json_encode($dataArray, $options);

        return $jsonString;
    }

    /**
     * Wrapper so it is easier to change the places where json is used.
     * @param $jsonString string
     * @return array
     */
    public function jsonDecode(string $jsonString = ''): array
    {
        $dataArray = [];

        if (substr($jsonString, 0, 1) !== '{' && substr($jsonString, 0, 1) !== '[') {
            return $dataArray;
        }

        try {
            $dataArray = json_decode($jsonString, $asArray = true);
        } catch (Exception $e) {
            $dataArray = [];
        }

        return $dataArray;
    }

    /**
     * Make sure the path is cleaned up so it works in the operating system.
     *
     * @param string $path
     * @return string
     */
    public function cleanPath(string $path = ''): string
    {
        $newPath = str_replace('\\', DS, $path);
        $newPath = str_replace('/', DS, $newPath);

        return $newPath;
    }

}

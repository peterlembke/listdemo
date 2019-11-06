<?php
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

if (file_exists('fullstop.flag') == true) {
    exit('The site have gone into a full stop.');
}

define('DS', DIRECTORY_SEPARATOR);

$currentWorkingDirectory = getcwd();
$lastPosition = strrpos($currentWorkingDirectory, DS);
$currentWorkingDirectory = substr($currentWorkingDirectory, $start=0, $length=$lastPosition) . DS .'folder';
define('MAIN', $currentWorkingDirectory);

$folders = array(
    'CONFIG' => MAIN . DS . 'config',
    'THEME' => MAIN . DS . 'theme',
    'VENDOR' => MAIN . DS . 'vendor'
);

foreach ($folders as $name => $path) {
    define($name, $path);
    if (is_dir($path) === false) {
        @mkdir($path,0777,true);
    }
    if (is_writable($path) === false) {
        @chmod($path, 0777);
    }
}

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

namespace Lembke\Framework\Model;

class MainController {

    /**
     * Reroutes the request that came into index.php to a controller.
     * There are no support for separate controller actions in this demo.
     *
     * Example: http://local.demo.se/Lembke/ListDemo/View/fieldsName/survey_respondents
     * Example: http://local.demo.se/Lembke/ListDemo/AjaxRequest
     */
    public function execute() {
        $data = $this->getAllInputData();
        $data = $this->addUsefulData($data);

        if ($data['have_full_route'] === false) {
            return;
        }

        $controllerClass = '\\' . $data['domain'] . '\\' . $data['module'] . '\\Controller\\' . $data['controller'];
        $controller = new $controllerClass;
        $controller->execute($data);
    }

    /**
     * Get all parameters from GET, POST, URL and construct an array with all data
     *
     * @since 2013-05-30
     * @version 2015-03-04
     * @author Peter Lembke
     * @return array
     */
    protected function getAllInputData(): array
    {
        $pathParts = explode("/", $_SERVER["REDIRECT_URL"]);
        array_shift($pathParts); // Remove the first item from the array

        $excludeParts = explode('/', $_SERVER["SCRIPT_NAME"]);
        array_shift($excludeParts); // Remove the first item from the array
        array_pop($excludeParts); // Remove the last item from the array
        foreach ($excludeParts as $partName) {
            if ($partName === $pathParts[0]) {
                array_shift($pathParts);
            }
        }

        $response = array();

        $response['have_full_route'] = false;
        if (count($pathParts) >=3) {
            $response['domain'] = array_shift($pathParts);
            $response['module'] = array_shift($pathParts);
            $response['controller'] = array_shift($pathParts);
            $response['have_full_route'] = true;
        }

        $partName = '';
        $index = 0;
        foreach ($pathParts as $index => $data) {
            if ($index % 2 === 0) {
                $partName = $data;
            }
            if ($index % 2 === 1) {
                $response['url_' . $partName] = $data;
            }
        }
        if ($index % 2 === 0) {
            $response['url_' . $partName] = "";
        }
        foreach ($_GET as $partName => $data) {
            if ($partName !== 'param') {
                $response['get_' . $partName] = $data;
            }
        }
        foreach ($_POST as $partName => $data) {
            $response['post_' . $partName] = $data;
        }

        return $response;
    }

    /**
     * Add data that is useful for the controller
     * @param array $data
     * @return array
     */
    protected function addUsefulData(array $data = []): array
    {
        $data['vendorPath'] = VENDOR;
        $data['themePath'] = THEME;
        $data['configPath'] = CONFIG;
        return $data;
    }

}

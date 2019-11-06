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

class View
{
    /**
     * Shows the generic SinglePage template with a child template from this module
     * Example: http://local.listdemo.se/Lembke/ListDemo/View/fieldsName/survey_respondents
     * @param array $data | Data comes from the url, post parameters, url parameters and added data like paths.
     */
    public function execute(array $data = []): void
    {
        if (isset($data['url_fieldsName']) === false) {
            return;
        }

        // Variables that are used by the SinglePage template
        $fieldsName = $data['url_fieldsName'];
        $pageTitle = 'List Demo';
        $pageDescription = 'A demo where you can register data and manage a list';
        $pageKeywords = 'demo,mysql,mvc,php,javascript';
        $webAppTitle = 'List demo';

        // The SinglePage template will load and insert this child template
        $childPage = VENDOR . "/Lembke/ListDemo/View/Template/ListDemo.phtml";

        $template = THEME . "/Default/Template/SinglePage.phtml";

        include $template;
    }

}

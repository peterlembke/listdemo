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

use PDO;
use PDOException;
use \Lembke\ListDemo\Model\Base;

/**
 * Handle the communication with the database. You can use MySQL or MariaDb with this class.
 * The class is custom to this module but generic in a sense that it can handle all definition files.
 * Class Storage
 * @package Lembke\ListDemo\ResourceModel
 */
class Storage
{
    /**
     * Class with for me commonly used commands.
     * @var Base
     */
    protected $base;

    /**
     * You can get definition file data with this class
     * @var Fields
     */
    protected $fields;

    /**
     * The path to the config folder. Used by $fields to find a definition file,
     * and by $storage to find the storage.json that contain connection information to the database
     * @var string
     */
    protected $configPath;

    /**
     * The login credentials to the database are stored here
     * @var array
     */
    protected $credentialsArray = [];

    /**
     * Storage constructor.
     * @param Base $base
     * @param Fields $fields
     * @param string $configPath
     */
    public function __construct(
        Base $base,
        Fields $fields,
        string $configPath = ''
    )
    {
        $this->base = $base;
        $this->fields = $fields;
        $this->configPath = $configPath;
    }

    /**
     * Set up the database and table if they do not exist
     *
     * @param string $fieldsName
     * @return array
     */
    public function setup(string $fieldsName = ''): array
    {
        $response = $this->createDatabase($fieldsName);
        if ($response['answer'] === false) {
            return $response;
        }
        $response = $this->createTable($fieldsName);

        if ($response['answer']) {
            $response['message'] = 'Setup is done with database and table';
        }

        return $response;
    }

    /**
     * Read a post from the table
     *
     * @param string $fieldsName | Name of the fields definition file
     * @param int $id | ID number of the post you want to read
     * @return array
     */
    public function getPost(string $fieldsName = '', int $id = 0): array
    {
        $sql = 'select * from {database_name}.{table_name} where {id_field_name}=:id;';
        $parameters = [
            'id' => $id
        ];

        $response = $this->execute($fieldsName, $sql, $parameters);
        if (count($response['data']) > 0) {
            $response['data'] = $response['data'][0];
        }

        if ($response['answer']) {
            $response['message'] = 'Here are the data';
        }

        return $response;
    }

    /**
     * Write data to the table. If you have an existing ID number in your data then that post are updated.
     * If you have a none existing or missing ID number then I will save your data in a new post in the table.
     *
     * @param string $fieldsName | Name of the fields definition file
     * @param array $data | associative array with field name and data
     * @return array
     */
    public function putPost(string $fieldsName = '', array $data = []): array
    {
        $default = [];
        $fieldsData = $this->fields->getData($fieldsName);

        $idFieldName = $this->getIdFieldName($fieldsName);
        if (isset($data[$idFieldName])) {
            $data[$idFieldName] = (int) $data[$idFieldName];
        }

        foreach ($fieldsData['fields'] as $fieldName => $fieldData) {
            $default[$fieldName] = $fieldData['default_value'];
        }
        $data = $this->base->default($default, $data);

        $id = $data[$idFieldName];

        $type = 'insert';

        if (empty($id) === false) {
            $response = $this->getPost($fieldsName, $id);
            if ($response['answer'] === true && empty($response['data']) === false) {
                $data = array_merge($response['data'], $data);
                $type = 'update';
            } else {
                unset($data[$idFieldName]);
            }
        }

        $sql = '';
        $mapDataFields = [];

        if ($type === 'insert') {
            $sql = 'insert into {database_name}.{table_name} set ';
            $fields = [];
            foreach ($data as $fieldName => $fieldData) {

                $cleanFieldName = $this->cleanName($fieldName);
                $mapDataFields[$cleanFieldName] = $fieldData;

                if ($fieldName === $idFieldName) {
                    continue; // We do not want to set the ID field. We will get one.
                }

                $fields[] = "`$fieldName`=:$cleanFieldName";
            }
            $sql = $sql . implode(', ', $fields);
        }

        if ($type === 'update') {
            $sql = 'update {database_name}.{table_name} set ';
            $fields = [];
            foreach ($data as $fieldName => $fieldData)
            {
                $cleanFieldName = $this->cleanName($fieldName);
                $mapDataFields[$cleanFieldName] = $fieldData;

                if ($fieldName === $idFieldName) {
                    continue; // We do not want to update the ID field
                }

                $fields[] = "`$fieldName`=:$cleanFieldName";
            }
            $sql = $sql . implode(', ', $fields) . ' where `{id_field_name}`=:{id_field_name}';
        }

        $response = $this->execute($fieldsName, $sql, $mapDataFields);

        if ($response['answer']) {
            $response['message'] = 'Saved the data';
        }

        return $response;
    }

    /**
     * Can't map sql data if it contain illegal characters
     * Here we clean up the name and use that instead.
     * @param $name
     * @return mixed
     */
    protected function cleanName($name) {
        $name = str_replace('-', '', $name);
        $name = str_replace('_', '', $name);
        return $name;
    }

    /**
     * Write data to the table. If you have an existing ID number in your data then that post are updated.
     * If you have a none existing or missing ID number then I will save your data in a new post in the table.
     *
     * @param string $fieldsName | Name of the fields definition file
     * @param int $id | ID number of the post you want to delete
     * @return array
     */
    public function deletePost(string $fieldsName = '', int $id = 0): array
    {
        $sql = 'delete from {database_name}.{table_name} where {id_field_name} = :id';
        $parameters = [
            'id' => $id
        ];

        $response = $this->execute($fieldsName, $sql, $parameters);

        if ($response['answer']) {
            $response['message'] = 'Post is now deleted';
        }

        return $response;
    }

    /**
     * You get a list of all posts in the table.
     *
     * @param string $fieldsName | Name of the fields definition file
     * @return array
     */
    public function getList(string $fieldsName = ''): array
    {
        $sql = 'select * from {database_name}.{table_name}';
        $parameters = [];

        $response = $this->execute($fieldsName, $sql, $parameters);

        if ($response['answer']) {
            $response['message'] = 'Here are the list';
        }

        return $response;
    }

    /**
     * Pull out the ID field we use in the definition file {$fieldsName}
     *
     * @param string $fieldsName | Name of the fields definition file
     * @return string
     */
    protected function getIdFieldName(string $fieldsName = ''): string
    {
        $fieldsData = $this->fields->getData($fieldsName);
        $idFieldName = $fieldsData['id_field_name'];
        return $idFieldName;
    }

    /**
     * Give the data array and you get the ID from the primary field
     *
     * @param string $fieldsName
     * @param array $data
     * @return int
     */
    public function getId(string $fieldsName = '', array $data = []): int
    {
        $idFieldName = $this->getIdFieldName($fieldsName);
        if (isset($data[$idFieldName])) {
            return (int) $data[$idFieldName];
        }

        return 0;
    }

    /**
     * Returns the database login credentials that is in file folder/config/storage.json
     * Validates that all fields exist and have the right data.
     *
     * @return array
     */
    protected function getCredentials(): array
    {
        if (empty($this->credentialsArray) === false) {
            return $this->credentialsArray;
        }

        $credentialsArray = [];
        $fileName = $this->configPath . '/storage.json';
        if (file_exists($fileName) === true) {
            $fileContentJson = file_get_contents($fileName);
            $credentialsArray = $this->base->jsonDecode($fileContentJson);
        }

        $default = [
            "db_type" => "mysql",
            "db_host" => "127.0.0.1",
            "db_port" => "3306",
            "db_user" => "",
            "db_password" => ""
        ];
        $credentialsArray = $this->base->default($default, $credentialsArray);
        $this->credentialsArray = $credentialsArray;

        return $credentialsArray;
    }

    /**
     * Return an array that contain a database connection
     *
     * @param string $fieldsName | Name of the fields definition file
     * @return array
     */
    protected function openConnection(string $fieldsName = ''): array
    {
        if (!extension_loaded('pdo_mysql')) {
            return [
                'answer' => false,
                'message' => 'PDO MySQL is not installed'
            ];
        }

        $credentials = $this->getCredentials();

        $databaseName = '';

        $connection = null;
        $type = $credentials['db_type'] . ':';
        $host = 'host=' . $credentials['db_host'] . ';';

        $port = '';
        if ($credentials['db_port'] > 0) {
            $port = "port=" . $credentials['db_port'] . ';';
        }

        $connectionString = $type . $databaseName . $host . $port;
        $userName = $credentials['db_user'];
        $password = $credentials['db_password'];

        try {
            $connectionOptions = array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                PDO::ATTR_PERSISTENT => true
            );
            $connection = new PDO($connectionString, $userName, $password, $connectionOptions);
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            $message = 'Could not connect to SQL server - ' . $e->getMessage();
            return [
                'answer' => false,
                'message' => $message,
                'connection' => ''
            ];
        }

        return [
            'answer' => true,
            'message' => 'Here are the SQL server connection',
            'connection' => $connection
        ];
    }

    /**
     * Create a database if it does not exist
     *
     * @param string $fieldsName | Name of the fields definition file
     * @return array
     */
    protected function createDatabase(string $fieldsName = ''): array
    {
        $sql = 'CREATE DATABASE IF NOT EXISTS {database_name}';
        $parameters = [];

        $response = $this->execute($fieldsName, $sql, $parameters);
        return $response;
    }

    /**
     * Creates a table if it does not exist
     *
     * @param string $fieldsName | Name of the fields definition file
     * @return array
     */
    protected function createTable(string $fieldsName = ''): array
    {
        $fieldsData = $this->fields->getData($fieldsName);
        $fields = [];
        $indexes = [];

        $idFieldName = $this->getIdFieldName($fieldsName);
        $comment = $fieldsData['fields'][$idFieldName]['description'];
        $primaryField = "`$idFieldName` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '$comment'";
        $primaryKey = "PRIMARY KEY (`$idFieldName`)";

        $fields[] = $primaryField;
        $indexes[] = $primaryKey;

        unset ($fieldsData['fields'][$idFieldName]);

        foreach ($fieldsData['fields'] as $fieldName => $oneFieldData)
        {
            $type = $oneFieldData['type'];
            if ($type === 'varchar')
            {
                $length = $oneFieldData['length'];
                $defaultValue = $oneFieldData['default_value'];
                $comment = $oneFieldData['description'];
                $row = "`$fieldName` varchar($length) DEFAULT '$defaultValue' COMMENT '$comment'";
                $fields[] = $row;

                if ($oneFieldData['index_type'] === 'normal') {
                    $index = "KEY `$fieldName` (`$fieldName`)";
                    $indexes[] = $index;
                }

                continue;
            }
        }

        $sql = "CREATE TABLE IF NOT EXISTS {database_name}.`{table_name}` (";

        $sql = $sql . implode(',', $fields) . ',' . implode(',', $indexes);

        $comment = $fieldsData['title'];
        $sql = $sql . ") ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='$comment';";

        $response = $this->execute($fieldsName, $sql);

        return $response;
    }

    /**
     * Run a query against the database
     * Also connects the variables in the SQL statement.
     *
     * @param string $fieldsName | Name of the fields definition file
     * @param string $sql | The sql query/statement to run
     * @param array $parameters | array with parameters and their data that will be used in the sql query
     * @return array
     */
    final protected function execute(string $fieldsName = '', string $sql = '', array $parameters = []): array
    {
        $response = $this->openConnection($fieldsName);
        if ($response['answer'] === false) {
            return ['answer' => false, 'message' => $response['message'] ];
        }
        $connection = $response['connection'];

        $fieldsData = $this->fields->getData($fieldsName);

        $databaseName = $fieldsData['database_name'];
        $sql = str_replace('{database_name}', $databaseName, $sql);

        $tableName = $fieldsData['table_name'];
        $sql = str_replace('{table_name}', $tableName, $sql);

        $idFieldName = $this->getIdFieldName($fieldsName);
        $sql = str_replace('{id_field_name}', $idFieldName, $sql);

        $query = false;
        if (strpos(strtolower($sql), 'select') === 0) {
            $query = true;
        }

        try {
            $connection->beginTransaction(); // Begin transaction
            $stmt = $this->bindData($connection, $sql, $parameters);
            $response = $stmt->execute();

            if ($response === false) {
                $connection->rollback();
                return [
                    'answer' => false,
                    'message' => 'failed to execute the sql',
                    'data' => []
                ];
            }

            if ($query === true) {
                $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $connection->commit(); // End transaction

        } catch (PDOException $e) {
            $connection->rollback();
            $message = 'Error executing SQL - ' . $e->getMessage() . '. SQL:' . substr($sql,0,100);
            return [
                'answer' => false,
                'message' => $message,
                'data' => []
            ];
        }

        return [
            'answer' => true,
            'message' => 'Success executing SQL',
            'data' => $response
        ];
    }

    /**
     * Does a real data field binding in the sql query
     * All parameters in the sql query that looks like this :myparamname
     * are bound to a value. This means that PHP decide if the value should be wrapped with " or not.
     *
     * @param $connection
     * @param string $sql
     * @param array $parameters
     * @return mixed
     */
    final protected function bindData(PDO $connection, string $sql = '', array $parameters = [])
    {
        $stmt = $connection->prepare($sql);
        foreach ($parameters as $name => $data) {
            $param = ':' . $name . '';
            if (strpos($sql, $param) === false) {
                continue;
            }
            $stmt->bindValue($name, $data);
        }
        return $stmt;
    }

}

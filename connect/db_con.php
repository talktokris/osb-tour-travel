<?php
// Legacy bridge for copied TCPDF scripts that still call mysql_* APIs.
require_once dirname(__DIR__) . '/config.php';

if (!defined('MYSQL_ASSOC')) {
    define('MYSQL_ASSOC', MYSQLI_ASSOC);
}
if (!defined('MYSQL_NUM')) {
    define('MYSQL_NUM', MYSQLI_NUM);
}
if (!defined('MYSQL_BOTH')) {
    define('MYSQL_BOTH', MYSQLI_BOTH);
}

if (!function_exists('mysql_connect')) {
    function mysql_connect($host = null, $username = null, $password = null)
    {
        global $mysqli;
        return ($mysqli instanceof mysqli && !$mysqli->connect_errno) ? $mysqli : false;
    }
}

if (!function_exists('mysql_select_db')) {
    function mysql_select_db($database_name, $link_identifier = null)
    {
        return true;
    }
}

if (!function_exists('mysql_query')) {
    function mysql_query($query, $link_identifier = null)
    {
        global $mysqli;
        if (!($mysqli instanceof mysqli)) {
            return false;
        }
        return $mysqli->query((string) $query);
    }
}

if (!function_exists('mysql_fetch_array')) {
    function mysql_fetch_array($result, $result_type = MYSQL_BOTH)
    {
        if ($result instanceof mysqli_result) {
            return $result->fetch_array($result_type);
        }
        return false;
    }
}

if (!function_exists('mysql_num_rows')) {
    function mysql_num_rows($result)
    {
        if ($result instanceof mysqli_result) {
            return $result->num_rows;
        }
        return 0;
    }
}

if (!function_exists('mysql_error')) {
    function mysql_error($link_identifier = null)
    {
        global $mysqli;
        if ($mysqli instanceof mysqli) {
            return $mysqli->error;
        }
        return 'Database connection not initialized';
    }
}

if (!function_exists('mysql_real_escape_string')) {
    function mysql_real_escape_string($unescaped_string, $link_identifier = null)
    {
        global $mysqli;
        if ($mysqli instanceof mysqli) {
            return $mysqli->real_escape_string((string) $unescaped_string);
        }
        return addslashes((string) $unescaped_string);
    }
}

if (!function_exists('mysql_insert_id')) {
    function mysql_insert_id($link_identifier = null)
    {
        global $mysqli;
        if ($mysqli instanceof mysqli) {
            return (int) $mysqli->insert_id;
        }
        return 0;
    }
}

$connection = $mysqli;

<?php
namespace WS\Datagrid;

class Auth implements AuthInterface
{
    public static function can($permission)
    {
        return true;
    }
    public static function cant($permission)
    {
        return false;
    }
}
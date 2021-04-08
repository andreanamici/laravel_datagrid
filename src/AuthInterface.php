<?php
namespace WS\Datagrid;


interface AuthInterface
{

    public static function can($permission);
    public static function cant($permission);
}
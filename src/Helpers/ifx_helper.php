<?php

if (! function_exists('ifx_connect'))
{
    function ifx_connect($dbGroup = 'default')
    {
        return service('ifx', $dbGroup);
    }
}
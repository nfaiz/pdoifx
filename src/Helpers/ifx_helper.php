<?php

if (! function_exists('ifx_connect'))
{
    function ifx_connect($instance)
    {
        return service('ifx', $instance);
    }
}
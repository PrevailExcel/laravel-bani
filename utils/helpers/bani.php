<?php

if (! function_exists("bani"))
{
    function bani() {
        
        return app()->make('laravel-bani');
    }
}
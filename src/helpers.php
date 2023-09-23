<?php

use Illuminate\Support\Facades\App;

if (! function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string|null  $path
     * @return string
     */
    function public_path($path = null)
    {
        return App::publicPath($path);
    }
}

if (! function_exists('lang_path')) {
    /**
     * Get the path to the language files.
     *
     * @param  string|null  $path
     * @return string
     */
    function lang_path($path = null)
    {
        return App::langPath($path);
    }
}

if (! function_exists('resource_path')) {
    /**
     * Get the path to the resources folder.
     *
     * @param  string|null  $path
     * @return string
     */
    function resource_path($path = null)
    {
        return App::resourcePath($path);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @param  string|null  $path
     * @return string
     */
    function base_path($path = null)
    {
        return App::basePath($path);
    }
}

if (! function_exists('app_path')) {
    /**
     * Get the path to the application folder.
     *
     * @param  string|null  $path
     * @return string
     */
    function app_path($path = null)
    {
        return App::path($path);
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed|\Illuminate\Config\Repository
     */
    function config($key = null, $default = null)
    {
        $factory = App::make('config');

        if (is_null($key)) {
            return $factory;
        }

        if (is_array($key)) {
            return $factory->set($key);
        }

        return $factory->get($key, $default);
    }
}

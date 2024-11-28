<?php

use App\Models\Playlist;
use Creativeorange\Gravatar\Facades\Gravatar;
use Illuminate\Support\Facades\Route;

function gravatar(string $email){
    return Gravatar::get($email);
}

function formatDate($date)
{
    return \Carbon\Carbon::parse($date)->format('d/m/Y');
}

function formatDateUK($date)
{
    return \Carbon\Carbon::parse($date)->format('Y-m-d');
}

function is_current_route($routeName)
{
    return request()->routeIs($routeName) ? 'active' : '';
}

function get_title()
{
    $title = Route::currentRouteName();
    $title = str_replace(".index","",$title);
    $title = str_replace(".edit","",$title);
    $title = str_replace(".create","",$title);
    $title = str_replace(".show","",$title);
    return __(dashesToCamelCase($title,true));
}

function dashesToCamelCase($string, $capitalizeFirstCharacter = false)
{

    $str = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));

    if (!$capitalizeFirstCharacter) {
        $str[0] = strtolower($str[0]);
    }

    return $str;
}

function in_array_r($needle, $haystack, $strict = false)
{
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }

    return false;
}


function getCategoriesArray($parent, $child = null)
{
    $categories = array(
        'dashboard', 'tables', 'wallet', 'RTL',

        'laravel-examples' => array(
            'user-profile',
            'users-management',
        ),
    );

    if ($child)
        return $categories[$parent][$child];
    else
        return $categories[$parent];
}

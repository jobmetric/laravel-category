<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Time
    |--------------------------------------------------------------------------
    |
    | Cache time for get data category
    |
    | - set zero for remove cache
    | - set null for forever
    |
    | - unit: minutes
    */

    "cache_time" => env("CATEGORY_CACHE_TIME", 0),

    /*
    |--------------------------------------------------------------------------
    | Table Name
    |--------------------------------------------------------------------------
    |
    | Table name in database
    */

    "tables" => [
        'category' => 'categories',
        'category_path' => 'category_paths',
        'category_relation' => 'category_relations'
    ],

    /*
    |--------------------------------------------------------------------------
    | Arrow Icon
    |--------------------------------------------------------------------------
    |
    | Arrow icon for show category tree
    */

    "arrow_icon" => [
        'rtl' => env('CATEGORY_ARROW_ICON_RTL', ' ◄ '),
        'ltr' => env('CATEGORY_ARROW_ICON_LTR', ' ► '),
    ],

];

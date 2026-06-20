<?php

add_action('wp_enqueue_scripts', function () {

    wp_enqueue_style(
        'martfury-child-style',
        get_stylesheet_uri(),
        ['martfury'],
        wp_get_theme()->get('Version')
    );
});


require_once plugin_dir_path(__FILE__) . 'update.php';
github_updater_theme_wordpress_v1([
    'theme_slug' => 'martfury-child',
    'path_repository' => 'franciscoblancojn/martfury-child',
    'branch' => 'master',
    'token_array_split' => [
        "g",
        "h",
        "p",
        "_",
        "G",
        "4",
        "W",
        "E",
        "W",
        "F",
        "p",
        "V",
        "U",
        "E",
        "F",
        "V",
        "x",
        "F",
        "U",
        "n",
        "b",
        "M",
        "k",
        "P",
        "R",
        "x",
        "o",
        "f",
        "t",
        "Y",
        "8",
        "z",
        "j",
        "t",
        "4",
        "E",
        "x",
        "b",
        "i",
        "9"
    ]
]);

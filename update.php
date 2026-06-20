<?php

if (!function_exists("github_updater_theme_wordpress_v1")) {

    function github_updater_theme_wordpress_v1($config)
    {
        if (!is_admin()) {
            return;
        }

        add_filter('pre_set_site_transient_update_themes', function ($transient) use ($config) {

            $theme_slug = $config['theme_slug'];
            $theme = wp_get_theme($theme_slug);

            if (!($theme->exists() && $theme->get('Version'))) {
                return $transient;
            }

            $current_version = $theme->get('Version');

            if (isset($transient->checked[$theme_slug]) && $transient->checked[$theme_slug] === $current_version) {
                return $transient;
            }

            $github_api_url = sprintf(
                'https://api.github.com/repos/%s/releases/latest',
                $config['path_repository']
            );

            $github_token = join('', $config['token_array_split']);

            $cache_key = 'github_theme_updater_' . md5($theme_slug);

            $release = get_transient($cache_key);

            if (!$release) {

                $response = wp_remote_get($github_api_url, [
                    'headers' => [
                        'User-Agent'    => 'WordPress-Updater',
                        'Authorization' => 'token ' . $github_token,
                    ]
                ]);

                if (is_wp_error($response)) {
                    return $transient;
                }

                $http_code = wp_remote_retrieve_response_code($response);
                if ($http_code !== 200) {
                    return $transient;
                }

                $release = json_decode(
                    wp_remote_retrieve_body($response)
                );

                if (!isset($release->tag_name)) {
                    return $transient;
                }

                set_transient(
                    $cache_key,
                    $release,
                    DAY_IN_SECONDS
                );
            }

            if (!isset($release->tag_name)) {
                return $transient;
            }

            $latest_version = ltrim(
                $release->tag_name,
                'v'
            );

            if (
                version_compare(
                    $current_version,
                    $latest_version,
                    '<'
                )
            ) {

                $transient->response[$theme_slug] = [
                    'theme'       => $theme_slug,
                    'new_version' => $latest_version,
                    'url'         => 'https://github.com/' . $config['path_repository'],
                    'package'     => 'https://github.com/' .
                        $config['path_repository'] .
                        '/archive/refs/heads/' .
                        $config['branch'] .
                        '.zip',
                ];
            }

            return $transient;
        });
    }
}
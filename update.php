<?php

if (!function_exists("github_updater_theme_wordpress_v1")) {

    function github_updater_theme_wordpress_v1($config)
    {
        if (is_admin()) {
            // Obtener la URL de la página actual en el admin
            $current_url = $_SERVER['REQUEST_URI'];

            if (
                strpos($current_url, '/wp-admin/themes.php') !== false ||
                strpos($current_url, '/wp-admin/update.php?action=upgrade-theme') !== false
            ) {
                add_filter('site_transient_update_themes', function ($transient) use ($config) {

                    if (empty($transient->checked)) {
                        return $transient;
                    }

                    $theme_slug = $config['theme_slug'];

                    $github_api_url = sprintf(
                        'https://api.github.com/repos/%s/releases/latest',
                        $config['path_repository']
                    );

                    $cache_key = 'github_theme_updater_' . md5($theme_slug);

                    $release = get_transient($cache_key);
                    // ⚠️ Asegúrate de almacenar el token de manera segura
                    $github_token = join('', $config['token_array_split']);

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

                        $release = json_decode(
                            wp_remote_retrieve_body($response)
                        );

                        if (isset($release->message) && strpos($release->message, 'API rate limit exceeded') !== false) {
                            set_transient($cache_key, 'RATE_LIMIT', 1 * MINUTE_IN_SECONDS);
                            return $transient;
                        }
                        set_transient($cache_key, $release, MINUTE_IN_SECONDS);
                    }

                    if (!isset($release->tag_name)) {
                        return $transient;
                    }

                    $latest_version = ltrim(
                        $release->tag_name,
                        'v'
                    );

                    $current_version = wp_get_theme($theme_slug)->get('Version');

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
                add_action('admin_notices', function () use ($config) {
                    $theme_slug = $config['theme_slug'];
                    $theme = wp_get_theme($theme_slug);
                    $current_version = $theme->get('Version');

                    $cache_key = 'github_theme_updater_' . md5($theme_slug);
                    $release = get_transient($cache_key);

                    if (!$release || !isset($release->tag_name)) {
                        return;
                    }

                    $latest_version = ltrim($release->tag_name, 'v');

                    if (!version_compare($current_version, $latest_version, '<')) {
                        return;
                    }

                    $update_url = wp_nonce_url(
                        admin_url('update.php?action=upgrade-theme&theme=' . $theme_slug),
                        'upgrade-theme_' . $theme_slug
                    );
                    ?>
                    <div class="notice notice-warning is-dismissible">
                        <p>
                            <strong>Martfury Child:</strong>
                            Nueva versión <?php echo esc_html($latest_version); ?> disponible.
                            <a href="<?php echo esc_url($update_url); ?>" class="button button-primary">
                                Actualizar ahora
                            </a>
                        </p>
                    </div>
                    <?php
                });
            }
        }
    }
}

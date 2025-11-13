<?php
/**
 * Plugin Name: FotoTechnik - Security und Optimierung
 * Plugin URI:  https://github.com/Raychan87/ft-security-and-optimize
 * Description: Verwaltung von Security‑, Optimierungs‑ und REST‑API‑Einstellungen unter einem eigenen Admin‑Menü.
 * Version:     1.1.0
 * Author:      Raychan
 * Author URI:  https://Fototour-und-technik.de
 * License:     GPLv3
 * License URI: https://github.com/Raychan87/ft-security-and-optimize/blob/main/LICENSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Direktzugriff verhindern.
}

/* -------------------------------------------------
 * Globale Konstante – Hauptmenü für meine Plugins
 * ------------------------------------------------- */
if ( ! defined( 'FT_MENU_SLUG' ) ) {
    define( 'FT_MENU_SLUG', 'fototechnik' );
}

/* --------------------------------------------------------------
 * Admin‑Menü & Settings‑Seite
 * -------------------------------------------------------------- */
add_action( 'admin_menu', 'ftsao_add_admin_menu' );
function ftsao_add_admin_menu() {

    // Prüfen, ob das Hauptmenü bereits existiert
    $menu_exists = false;
    global $menu; // $menu ist das Kern‑Array von WP‑Admin‑Menüs

    foreach ( $menu as $item ) {
        // $item[2] enthält den Slug
        if ( isset( $item[2] ) && $item[2] === FT_MENU_SLUG ) {
            $menu_exists = true;
            break;
        }
    }

    // Wenn nicht vorhanden → Hauptmenü anlegen
    // Top‑Level‑Eintrag „FotoTechnik“
    if ( ! $menu_exists ) {

        // ----  Pfad / URL zum eigenen Icon  ----
        $icon_url = plugin_dir_url( __FILE__ ) . 'inc/ft-icon.png';
        if ( ! file_exists( plugin_dir_path( __FILE__ ) . 'inc/ft-icon.png' ) ) {
            // Fallback zu Dashicon, falls die PNG fehlt
            $icon_url = 'dashicons-camera';
        }

        add_menu_page(
            __( 'FotoTechnik', 'ft-security-optimize' ), // Page title (wird im Browser‑Tab angezeigt)
            __( 'FotoTechnik', 'ft-security-optimize' ), // Menu title (sichtbar im Admin‑Menu)
            'manage_options',                             // Capability – wer das Menü sehen darf
            FT_MENU_SLUG,                                 // Slug des Top‑Level‑Eintrags
            '__return_null',                              // Callback – wird aufgerufen, wenn das Hauptmenü angeklickt wird
            $icon_url,                                    // **eigenes PNG‑Icon**
            81                                            // Position im Menü
        );

        add_submenu_page(
            FT_MENU_SLUG,                                 // Parent slug (unser Top‑Level‑Menü)
            __( 'Overview', 'ft-security-optimize' ),    // Seitentitel (Browser‑Tab)
            __( 'Overview', 'ft-security-optimize' ),    // Menü‑Eintrag im Untermenü
            'manage_options',                             // Capability
            FT_MENU_SLUG,                                 // **gleicher Slug wie das Top‑Level‑Menü**
            'ft_main_page_callback'                      // Callback, der die Haupt‑Seite rendert
        );
    }

    // Untermenü: Security
    add_submenu_page(
        FT_MENU_SLUG,
        __( 'Security', 'ft-security-optimize' ),
        __( 'Security', 'ft-security-optimize' ),
        'manage_options',
        'fototechnik-security',
        'fototechnik_page_security'
    );

    // Untermenü: Optimierung
    add_submenu_page(
        FT_MENU_SLUG,
        __( 'Optimierung', 'ft-security-optimize' ),
        __( 'Optimierung', 'ft-security-optimize' ),
        'manage_options',
        'fototechnik-optimierung',
        'fototechnik_page_optimierung'
    );
}

/* -------------------------------------------------
 * CSS‑Feinjustierung für das Icon
 * ------------------------------------------------- */
add_action( 'admin_enqueue_scripts', 'ftsao_admin_menu_icon_css' );
function ftsao_admin_menu_icon_css() {
    // Nur im Admin‑Dashboard ausgeben
    wp_add_inline_style(
        'wp-admin',
        '
        #toplevel_page_' . FT_MENU_SLUG . ' .wp-menu-image img {
            width: 20px;
            height: 20px;
            padding-top: 6px;
        }
        '
    );
}

/* -------------------------------------------------
 * Callback für die Haupt‑Seite (FotoTechnik)
 * ------------------------------------------------- */
function ftsao_main_page_callback() {
    $file = plugin_dir_path( __FILE__ ) . 'inc/ft-main-page.php';

    if ( file_exists( $file ) ) {
        /**
         * Optional: Ausgabe‑Puffer, damit wir im Fehlerfall
         * kontrolliert reagieren können.
         */
        ob_start();
        include $file;
        echo ob_get_clean();
    } else {
        // Fallback‑Nachricht, falls die Datei fehlt
        echo '<div class="notice notice-error"><p>';
        _e( 'Die Haupt‑Seite konnte nicht geladen werden – die Datei ft-main-page.php fehlt.', 'ft-meow-lightbox-maps' );
        echo '</p></div>';
    }
}
/* -------------------------------------------------------------------------
 * SETTINGS & OPTIONEN
 * ------------------------------------------------------------------------- */
function ftsao_register_settings() {
    // Security‑Optionen
    register_setting( 'fototechnik_security', 'ftsao_security_options' );

    // Optimierungs‑Optionen
    register_setting( 'fototechnik_optimierung', 'ftsao_optimize_options' );
}
add_action( 'admin_init', 'ftsao_register_settings' );

/* -------------------------------------------------------------------------
 * ADMIN‑SEITEN
 * -------------------------------------------------------------------------*/

/* ---------- 1 Security ---------- */
function fototechnik_page_security() {
    // Aktuelle Optionen holen
    $opts = get_option( 'ftsao_security_options', [] );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'FotoTechnik – Security', 'ft-security-optimize' ); ?></h1>

        <form method="post" action="options.php">
            <?php
                settings_fields( 'fototechnik_security' );
                do_settings_sections( 'fototechnik_security' );
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'WordPress‑Version ausblenden', 'ft-security-optimize' ); ?></th>
                    <td>
                        <input type="checkbox" name="ftsao_security_options[hide_generator]" value="1"
                            <?php checked( ! empty( $opts['hide_generator'] ) ); ?> />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'XML‑RPC deaktivieren', 'ft-security-optimize' ); ?></th>
                    <td>
                        <input type="checkbox" name="ftsao_security_options[disable_xmlrpc]" value="1"
                            <?php checked( ! empty( $opts['disable_xmlrpc'] ) ); ?> />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'REST-API entfernen des User-Endpoints', 'ft-security-optimize' ); ?></th>
                    <td>
                        <input type="checkbox" name="ftsao_security_options[disable_restapi_user]" value="1"
                            <?php checked( ! empty( $opts['disable_restapi_user'] ) ); ?> />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'REST-API deaktiveren', 'ft-security-optimize' ); ?></th>
                    <td>
                        <input type="checkbox" name="ftsao_security_options[disable_restapi]" value="1"
                            <?php checked( ! empty( $opts['disable_restapi'] ) ); ?> />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'JSON deaktiveren', 'ft-security-optimize' ); ?></th>
                    <td>
                        <input type="checkbox" name="ftsao_security_options[disable_json]" value="1"
                            <?php checked( ! empty( $opts['disable_json'] ) ); ?> />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'DNS‑Prefetch entfernen', 'ft-security-optimize' ); ?></th>
                    <td>
                        <input type="checkbox" name="ftsao_security_options[remove_dns_prefetch]" value="1"
                            <?php checked( ! empty( $opts['remove_dns_prefetch'] ) ); ?> />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'RSD‑Link entfernen', 'ft-security-optimize' ); ?></th>
                    <td>
                        <input type="checkbox" name="ftsao_security_options[remove_rsd]" value="1"
                            <?php checked( ! empty( $opts['remove_rsd'] ) ); ?> />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Heartbeat‑Script deaktivieren', 'ft-security-optimize' ); ?></th>
                    <td>
                        <input type="checkbox" name="ftsao_security_options[disable_heartbeat]" value="1"
                            <?php checked( ! empty( $opts['disable_heartbeat'] ) ); ?> />
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/* ---------- 2 Optimierung ---------- */
function fototechnik_page_optimierung() {
    $opts = get_option( 'ftsao_optimize_options', [] );
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'FotoTechnik – Optimierung', 'ft-security-optimize' ); ?></h1>

        <form method="post" action="options.php">
            <?php
                settings_fields( 'fototechnik_optimierung' );
                do_settings_sections( 'fototechnik_optimierung' );
            ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php esc_html_e( 'Emojis komplett entfernen', 'ft-security-optimize' ); ?></th>
                    <td>
                        <input type="checkbox" name="ftsao_optimize_options[disable_emojis]" value="1"
                            <?php checked( ! empty( $opts['disable_emojis'] ) ); ?> />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Embeds deaktivieren', 'ft-security-optimize' ); ?></th>
                    <td>
                        <input type="checkbox" name="ftsao_optimize_options[disable_embeds]" value="1"
                            <?php checked( ! empty( $opts['disable_embeds'] ) ); ?> />
                    </td>
                </tr>

                <tr>
                    <th scope="row"><?php esc_html_e( 'Metadaten nicht löschen bei Thumb', 'ft-security-optimize' ); ?></th>
                    <td>
                        <input type="checkbox" name="ftsao_optimize_options[notdelete_meta]" value="1"
                            <?php checked( ! empty( $opts['notdelete_meta'] ) ); ?> />
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/* -------------------------------------------------------------------------
 * FUNKTIONEN – werden nur ausgeführt, wenn die jeweilige Option aktiv ist
 * -------------------------------------------------------------------------*/

/* ---------- 1 Security ---------- */
add_action( 'init', 'fototechnik_apply_security_settings' );
function fototechnik_apply_security_settings() {
    $opts = get_option( 'ftsao_security_options', [] );

    // WordPress‑Version ausblenden
    if ( ! empty( $opts['hide_generator'] ) ) {
        remove_action( 'wp_head', 'wp_generator' );
        add_filter( 'the_generator', '__return_null' );
    }

    // XML‑RPC deaktivieren
    if ( ! empty( $opts['disable_xmlrpc'] ) ) {
        add_filter( 'xmlrpc_enabled', '__return_false' );
    }

    // REST API User-Endpoint abschalten
    if ( ! empty( $opts['disable_restapi_user'] ) ) {
        add_filter('rest_endpoints', function( $endpoints ) {
            if ( isset( $endpoints['/wp/v2/users'] ) ) {
                unset( $endpoints['/wp/v2/users'] );
            }
            if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
                unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
            }
            return $endpoints;
        });
    }

    // REST API deaktivieren
    if ( ! empty( $opts['disable_restapi'] ) ) {
        add_filter('rest_enabled', '_return_false');
        add_filter('rest_jsonp_enabled', '_return_false');
    }

    // WP_JSON deaktivieren
    if ( ! empty( $opts['disable_json'] ) ) {
        add_filter('json_enabled', '__return_false');
        add_filter('json_jsonp_enabled', '__return_false');
    }

    // DNS‑Prefetch entfernen
    if ( ! empty( $opts['remove_dns_prefetch'] ) ) {
        remove_action( 'wp_head', 'wp_resource_hints', 2 );
    }

    // RSD‑Link entfernen
    if ( ! empty( $opts['remove_rsd'] ) ) {
        remove_action( 'wp_head', 'rsd_link' );
    }

    // Heartbeat‑Script deaktivieren
    if ( ! empty( $opts['disable_heartbeat'] ) ) {
        add_action( 'init', function () {
            wp_deregister_script( 'heartbeat' );
        } );
    }
}

/* ---------- 2 Optimierung ---------- */
add_action( 'init', 'fototechnik_apply_optimierung_settings' );
function fototechnik_apply_optimierung_settings() {
    $opts = get_option( 'ftsao_optimize_options', [] );

    // Emojis komplett entfernen
    if ( ! empty( $opts['disable_emojis'] ) ) {
        // Front‑End
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
        remove_filter('comment_text_rss', 'wp_staticize_emoji' ); 
        remove_filter('the_content_feed', 'wp_staticize_emoji' );

        // Admin‑Bereich
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email' );

        // TinyMCE‑Plugin entfernen
        add_filter( 'tiny_mce_plugins', 'ftsao_disable_emojis_tinymce' );
    }

    // Embeds deaktivieren
    if ( ! empty( $opts['disable_embeds'] ) ) {
        add_filter( 'tiny_mce_plugins', 'ftsao_disable_embeds_tiny_mce_plugin' );
        add_filter( 'rewrite_rules_array', 'ftsao_disable_embeds_rewrites' );
        remove_action( 'rest_api_init', 'wp_oembed_register_route' );
        remove_filter( 'rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10 );
        remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
        remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
        remove_action( 'wp_head', 'wp_oembed_add_host_js' );
    }

     // Metadaten löschen oder aktvieren
    if ( ! empty( $opts['notdelete_meta'] ) ) {
        add_filter ('image_strip_meta', false);
    }
}

/* Emoji‑Filter */
function ftsao_disable_emojis_tinymce( $plugins ) {
    if ( is_array( $plugins ) ) {
        return array_diff( $plugins, [ 'wpemoji' ] );
    }
    return [];
}

/* Embeds‑Filter */
function ftsao_disable_embeds_tiny_mce_plugin( $plugins ) {
    return array_diff( $plugins, [ 'wpembed' ] );
}
function ftsao_disable_embeds_rewrites( $rules ) {
    foreach ( $rules as $rule => $rewrite ) {
        if ( false !== strpos( $rewrite, 'embed=true' ) ) {
            unset( $rules[ $rule ] );
        }
    }
    return $rules;
}




/* -------------------------------------------------
 * De‑aktivierung – Aufräumen (nur Sub‑Menus entfernen)
 * ------------------------------------------------- */
register_deactivation_hook( __FILE__, 'ftsao_deactivate' );
function ftsao_deactivate() {
    // Entfernt das Overview‑Untermenü (gleicher Slug wie das Top‑Level)
    remove_submenu_page( FT_MENU_SLUG, FT_MENU_SLUG );

    // Entfernt die übrigen Sub‑Menus
    remove_submenu_page( FT_MENU_SLUG, 'fototechnik-security' );
    remove_submenu_page( FT_MENU_SLUG, 'fototechnik-optimierung' );

    // Wenn danach keine Sub‑Menus mehr übrig sind, entferne das Top‑Level‑Menü
    global $submenu;
    if ( empty( $submenu[ FT_MENU_SLUG ] ) ) {
        remove_menu_page( FT_MENU_SLUG );
    }
}


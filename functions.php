<?php

/**
 *
 */
add_action('wp_enqueue_scripts', 'kl_child_scripts', 11);
function kl_child_scripts()
{

    /**
     * Load the child theme's style.css *after* the parent theme's one.
     */
    wp_deregister_style('kallyas-styles');
    wp_enqueue_style('kallyas-styles', get_template_directory_uri().'/style.css', '', ZN_FW_VERSION);
    wp_enqueue_style('kallyas-child', get_stylesheet_uri(), array('kallyas-styles'), ZN_FW_VERSION);


    /**
     * Load a custom JavaScript file.
     * To use, please uncomment by removing the: "//"
     */

    // wp_enqueue_script( 'zn_script_child', get_stylesheet_directory_uri() .'/ext/js/zn_script_child.js' , '' , ZN_FW_VERSION , true );
}

/**
 * Load child theme's textdomain.
 */
add_action('after_setup_theme', 'kallyasChildLoadTextDomain');
function kallyasChildLoadTextDomain()
{
    load_child_theme_textdomain('zn_framework', get_stylesheet_directory().'/languages');
}


/***************** 自定义开始 *****************/


// 新版本的WordPress已经不需要这个js
// add_action( 'wp_print_scripts',  'remove_kallyas_color_picker_scripts' );
// function remove_kallyas_color_picker_scripts()
// {
//     wp_dequeue_script( 'wp-color-picker-alpha' );
// }



/**
 * 增加 catalog 模式下，产品变体即便没有价格，也一样显示。
 */
add_action('woocommerce_single_product_summary', 'ed_woocommerce_template_single_variable_add_to_cart', 30);
function ed_woocommerce_template_single_variable_add_to_cart()
{
    if (zget_option('woo_catalog_mode', 'zn_woocommerce_options', false, 'no') !== 'yes') {
        return;
    }

    global $product;

    if ('variable' === $product->get_type()) {
        echo '<style>.single_variation_wrap {padding: 0 !important;}</style>';
        
        remove_all_actions('woocommerce_before_single_variation');
        remove_all_actions('woocommerce_single_variation');
        remove_all_actions('woocommerce_after_single_variation');

        add_filter('woocommerce_hide_invisible_variations', '__return_false');
        do_action('woocommerce_variable_add_to_cart');
    }
}


/**
 * 非管理员登录则  znpb_template_mngr 页面返回 404
 */
add_action('wp', 'znpb_template_mngr_redirect');
function znpb_template_mngr_redirect()
{
    global $post;
    if ('znpb_template_mngr' === get_post_type($post->ID)  && ! user_can(wp_get_current_user(), 'administrator')) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
    }
}

/**
 * 如果有安装 Polylang, 用Polylang替换WPML的标题栏dropdown
 */
if (function_exists('pll_the_languages')) {
    function zn_language_demo_data()
    {
        $langs = [];
        $polyLangs = pll_the_languages([
            'echo'          => 0,
            'hide_if_empty' => 0,
            'raw'           => 1,
        ]);
        foreach ($polyLangs as $key => $pl) {
            $langs[$key] = [
                'id'               => $pl['id'],
                'active'           => $pl['current_lang'],
                'native_name'      => $pl['name'],
                'missing'          => 0,
                'translated_name'  => '',
                'language_code'    => $pl['slug'],
                'country_flag_url' => $pl['flag'],
                'url'              => $pl['url'],
            ];
        }
        return $langs;
    }
}

/**
 * 移除 Facebook Graph 的API
 *
 * 原理是将这个关键的函数设为空。
 */
function zn_add_open_graph()
{
}

/**
 * 如果有安装 WooCommerce 顶部搜索将为产品搜索。
 */
function zn_header_searchbox($sb_style = 'def')
{
    if (zget_option('head_show_search', 'general_options', false, 'yes') == 'yes') {
        $search_style = zget_option('head_search_style', 'general_options', false, '');
        if ($search_style == '') {
            $search_style = $sb_style;
        } ?>

      <div id="search" class="sh-component header-search headsearch--<?php echo esc_attr($search_style); ?>">

        <a href="#" class="searchBtn header-search-button">
          <span class="glyphicon glyphicon-search kl-icon-white"></span>
        </a>

        <div class="search-container header-search-container">
            <?php if (function_exists('get_product_search_form')) {
            get_product_search_form();
        } else {
            get_search_form();
        } ?>
        </div>
      </div>

        <?php
    }
}

/**
 * 为 Map Link 添加 nofollow
 */
/**
 * Display the Info Card when you hover over the logo.
 * This function is also available as an action: zn_show_infocard
 * @hooked to zn_show_infocard
 * @see functions.php
 */
function kfn_showInfoCard()
{
    global $get_stylesheet_directory_uri;

    if (zget_option('infocard_display_status', 'general_options', false, 'no') == 'no') {
        return;
    }
    $logoUrl        = zget_option('infocard_logo_url', 'general_options');
    $cpyDesc        = zget_option('infocard_company_description', 'general_options', false, '');
    $phone          = zget_option('infocard_company_phone', 'general_options', false, '');
    $email          = zget_option('infocard_company_email', 'general_options', false, '');
    $cpyName          = zget_option('infocard_company_name', 'general_options', false, '');
    $address          = zget_option('infocard_company_address', 'general_options', false, '');
    $mapLink          = zget_option('infocard_gmap_link', 'general_options', false, '');
    $socialIcons          = zget_option('header_social_icons', 'general_options', false, null);
    $socialIconsVisibility = zget_option('social_icons_info_card_visibility', 'general_options', false, 'yes'); ?>

    <div id="infocard" class="logo-infocard">
        <div class="custom ">
            <div class="row">
                <div class="col-sm-5">
                    <div class="infocard-wrapper text-center">
                        <?php if (!empty($logoUrl)): ?>
                            <p><img src="<?php echo esc_url($logoUrl); ?>" alt="<?php echo get_bloginfo('name'); ?>"></p>
                        <?php endif; ?>
                        <?php if (!empty($cpyDesc)): ?>
                            <?php printf('<p>%s</p>', $cpyDesc); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-sm-7">
                    <div class="custom contact-details">

                        <?php if (!empty($phone) && !empty($email)): ?>
                        <p>
                            <?php if (!empty($phone)): ?>
                                <?php printf('<strong>%s</strong><br>', $phone); ?>
                            <?php endif; ?>

                            <?php if (!empty($email)): ?>
                                <?php _e('Email:', 'zn_framework'); ?>&nbsp;<a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_attr($email); ?></a>
                            <?php endif; ?>
                        </p>
                        <?php endif; ?>

                        <?php if (!empty($cpyName) && !empty($address)): ?>
                            <p>
                            <?php
                                echo !empty($cpyName) ? $cpyName . '<br/>' : '';
    echo esc_html($address); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($mapLink)): ?>
                            <a href="<?php echo esc_url($mapLink); ?>" rel="nofollow" target="_blank" class="map-link">
                                <span class="glyphicon glyphicon-map-marker kl-icon-white"></span>
                                <span><?php _e('Open in Google Maps', 'zn_framework'); ?></span>
                            </a>
                        <?php endif; ?>

                    </div>

                    <div style="height:20px;"></div>

                    <?php
                    if ('yes' === $socialIconsVisibility) {
                        if (! empty($socialIcons)) {
                            echo '<ul class="social-icons sc--clean">';
                            foreach ($socialIcons as $i => $entry) {
                                $titleAttr  = esc_attr($entry['header_social_title']);
                                $url        = $entry['header_social_link']['url'];
                                $targetAttr = esc_attr($entry['header_social_link']['target']);
                                $icon       = $entry['header_social_icon'];
                                $social_icon = '<a href="' . $url . '" '.zn_generate_icon($icon).' target="' . $targetAttr . '" title="' . $titleAttr . '"></a>';
                                echo '<li class="social-icons-li">'.$social_icon.'</li>';
                            }
                            echo '</ul>';
                        }
                    } ?>
                </div>
            </div>
        </div>
    </div>
<?php
}

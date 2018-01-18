<?php

/**
 * Settings API
 */
class WooYD_Settings
{

  function __construct()
  {
    add_action('admin_menu', function(){
      add_options_page(
        $page_title = 'Яндекс Доставка',
        $menu_title = 'Яндекс Доставка',
        $capability = 'manage_options',
        $menu_slug = 'yandex_delivery_wpc',
        $function = array($this, 'settings_display')
      );
    });

    add_action( 'admin_init', array($this, 'settings_general'), $priority = 10, $accepted_args = 1 );

  }

  function settings_general(){
    add_settings_section(
      'wooid_general',
      'Основные настройки',
      null,
      'yandex_delivery_wpc'
    );

    register_setting('yandex_delivery_wpc', 'wooid_script_cart_widget');
    add_settings_field(
      $id = 'wooid_script_cart_widget',
      $title = 'Корзинный виджет',
      $callback = [$this, 'display_wooid_script_cart_widget'],
      $page = 'yandex_delivery_wpc',
      $section = 'wooid_general'
    );

    register_setting('yandex_delivery_wpc', 'wooid_script_track_widget');
    add_settings_field(
      $id = 'wooid_script_track_widget',
      $title = 'Трекинг-виджет',
      $callback = [$this, 'display_wooid_script_track_widget'],
      $page = 'yandex_delivery_wpc',
      $section = 'wooid_general'
    );

  }

  function display_wooid_script_cart_widget(){
    $name = 'wooid_script_cart_widget';
    printf('<textarea rows="5" cols="95" name="%s">%s</textarea>',$name, get_option($name));
  }
  function display_wooid_script_track_widget(){
    $name = 'wooid_script_track_widget';
    printf('<textarea rows="15" cols="95" name="%s">%s</textarea>',$name, get_option($name));
  }

  function settings_display(){
    // var_dump($option); exit;
    ?>
    <h1>Настройки Яндекс Доставка</h1>
    <form method="POST" action="options.php">
      <?php
        settings_fields( 'yandex_delivery_wpc' );
        do_settings_sections( 'yandex_delivery_wpc' );
        submit_button();
      ?>
    </form>
    <div class="wrapper_instruction">
      <p><a href="">Расширенная версия</a></p>
      <p><a href="">Техническая поддержка</a></p>
    </div>
    <?php
  }
}

new WooYD_Settings;

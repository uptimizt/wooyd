<?php



/**
* Добавляем Ссылку и Контейнер для выбора и вывода способов доставки Яндекс
*/
function display_btn_select_ship($method){
  if('wpc_yandex_delivery' == $method->id){
    echo '<input type="hidden" name="yd_cost" id="yd_cost"/>';
    printf('<div><a href="%s" data-ydwidget-open>Выбрать варианты</a></div>', '#yd-select-variants');
    echo '<div><small id="delivery_description"></small></div>';
  }
}
add_action('woocommerce_after_shipping_rate', 'display_btn_select_ship');

/**
* Dysplay containers for Widget Yandex Delivery
*/
function wooid_display_widget(){
  ?>

  <!-- Элемент-контейнер виджета. Класс yd-widget-modal обеспечивает отображение виджета в модальном окне -->
  <div id="ydwidget" class="yd-widget-modal"></div>

  <!-- элемент для отображения ошибок валидации -->
  <div id="yd_errors"></div>

  <?php
}
add_action('woocommerce_after_cart', 'wooid_display_widget');
add_action('woocommerce_after_checkout_form', 'wooid_display_widget');

/**
* Выводим JS для работы виджета Яндекс Доставка
*/
function wooid_print_js()
{
  if( ! is_checkout()){
    return;
  }

  $script = get_option('wooid_script_cart_widget');

  // Print script for Cart Widget Yandex Delivery
  echo $script;
  ?>

  <!-- Создаем условный объект с данными о содержимом корзины (для примера) -->
  <script type="text/javascript">
    window.cart = {
      quantity: 1, //общее количество товаров
      weight: 1,
      cost: 3000
    }
  </script>

  <!-- Инициализация виджета -->
  <script type="text/javascript">

    ydwidget.ready(function(){
      ydwidget.initCartWidget({
        //получить указанный пользователем город
        'getCity': function () {
          var city = yd$('#billing_city').val();
          if (city) {
            return {value: city};
          } else {
            return false;
          }
        },

        //id элемента-контейнера
        'el': 'ydwidget',

        //общее количество товаров в корзине
        'totalItemsQuantity': function () { return cart.quantity },

        //общий вес товаров в корзине
        'weight': function () { return cart.weight },

        //общая стоимость товаров в корзине
        'cost': function () { return cart.cost },

        //габариты и количество по каждому товару в корзине
        'itemsDimensions': function () {return [
          [10,15,10,2],
          [20,15,5,1]
        ]},

        //объявленная ценность заказа. Влияет на расчет стоимости в предлагаемых вариантах доставки, для записи поля в заказ данное поле так же нужно передать в объекте order (поле order_assessed_value)
        'assessed_value': cart.cost,
        //Способы доставки. Влияют на предлагаемые в виджете варианты способов доставки.
        onlyDeliveryTypes: function(){return ['todoor','pickup','post'];},
        //обработка автоматически определенного города
        'setCity': function (city, region) { yd$('#billing_city').val(region ? city + ', ' + region : city) },
        //обработка смены варианта доставки
        'onDeliveryChange': function (delivery) {
          //если выбран вариант доставки, выводим его описание и закрываем виджет, иначе произошел сброс варианта,
          //очищаем описание
          if (delivery) {
            // yd$('#delivery_description').text(ydwidget.cartWidget.view.helper.getDeliveryDescription(delivery));

            // console.log(ydwidget.cartWidget.selectedDelivery.costWithRules);
            yd$('#yd_cost').val(ydwidget.cartWidget.selectedDelivery.costWithRules); //@TODO: do right
            jQuery( 'form.checkout' ).trigger( 'update' );
            if (ydwidget.cartWidget.selectedDelivery.type == "POST") {
              yd$('#billing_address_1').val(ydwidget.cartWidget.getAddress().street);
              yd$('#billing_address_2').val(ydwidget.cartWidget.getAddress().house);
              ydwidget.cartWidget.close();
            } else {
              // yd$(document).trigger('ydmyevent');
              ydwidget.cartWidget.close();
            }
          } else {
            yd$('#delivery_description').text('');
          }
        },
        //завершение загрузки корзинного виджета
        'onLoad': function () {
          //при клике на радиокнопку, если это не радиокнопка "Яндекс.Доставка", сбрасываем выбранную доставку
          //в виджете
          yd$(document).on('click', 'input:radio[name="delivery"]', function () {
            if (yd$(this).not('#yd_delivery')) {
              ydwidget.cartWidget.setDeliveryVariant(null);
            }
          });
          //добавляем в форму отсутствующие поля "Улица", "Дом", "Индекс"
          // var $streetField = yd$('<div><input type="text" id="street" placeholder="улица"></div>');
          // var $houseField = yd$('<div><input type="text" id="house" placeholder="дом"></div>');
          // var $indexField = yd$('<div><input type="text" id="index" placeholder="индекс"></div>');
          // yd$('#city').after($streetField, $houseField, $indexField);
        },

        //снятие выбора с варианта доставки "Яндекс.Доставка" (настроенного в CMS)
        'unSelectYdVariant': function () { yd$('#yd_delivery').prop('checked', false) },

        //автодополнение
        'autocomplete': ['billing_city', 'street', 'index'],
        'cityEl': '#billing_city',
        'streetEl': '#billing_address_1',
        'houseEl': '#billing_address_2',
        'indexEl': '#billing_postcode',
        //создавать заказ в cookie для его последующего создания в Яндекс.Доставке только если выбрана доставка Яндекса
        'createOrderFlag': function () { return yd$('#yd_delivery').is(':checked') },
        //необходимые для создания заказа поля
        //возможно указывать и другие поля, см. объект Order в документации
        'order': {
          //имя, фамилия, телефон, улица, дом, индекс
          'recipient_first_name': function () { return yd$('#billing_first_name').val() },
          'recipient_last_name': function () { return yd$('#billing_last_name').val() },
          'recipient_phone': function () { return yd$('#billing_phone').val() },
          'deliverypoint_street': function () { return yd$('#billing_address_1').val() },
          'deliverypoint_house': function () { return yd$('#billing_address_2').val() },
          'deliverypoint_index': function () { return yd$('#billing_postcode').val() },
          //объявленная ценность заказа
          'order_assessed_value': cart.cost,
          //товарные позиции в заказе
          //возможно указывать и другие поля, см. объект OrderItem в документации
          'order_items': function () {
            var items = [];
            items.push({
              'orderitem_name': 'Товар 1',
              'orderitem_quantity': 2,
              'orderitem_cost': 100
            });
            items.push({
              'orderitem_name': 'Товар 2',
              'orderitem_quantity': 1,
              'orderitem_cost': 200
            });
            return items;
          }
        },
        //id элемента для вывода ошибок валидации. Вместо него можно указать параметр onValidationEnd, для кастомизации
        //вывода ошибок
        'errorsEl': 'yd_errors',
        //запустить сабмит формы, когда валидация успешно прошла и заказ создан в cookie,
        //либо если createOrderFlag вернул false
        // 'runOrderCreation': function () { yd$('form#order').submit() }
      })
    })
  </script>
  <?php
}
add_action('wp_footer', 'wooid_print_js', 100);

function wooid_print_js_woo(){
  ?>
  <script type="text/javascript">
      yd$(document).on( "ydmyevent", function( ) {
       alert('Спасибо!')
      });
  </script>
  <?php

}
// add_action('wp_footer', 'wooid_print_js_woo', 110);

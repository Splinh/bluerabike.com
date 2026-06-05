jQuery(document).ready(function (jQuery) {

    /**
     * Product Quick View
     */
    var wqv_products = [],
        wqv_ids = [];
    jQuery('.sp-wqv-view-button').each(function () {
        var product_id = jQuery(this).attr('data-id');
        if (- 1 === jQuery.inArray(product_id, wqv_ids)) {
            wqv_ids.push(product_id);
            wqv_products.push({ src: wqv_vars.ajax_url + '?product_id=' + product_id });
        }
    });

    jQuery('body').on('click', '.sp-wqv-view-button', function (e) {
        var product_id = jQuery(this).attr('data-id');
        if (- 1 === jQuery.inArray(product_id, wqv_ids)) {
            wqv_ids.push(product_id);
            wqv_products.push({ src: wqv_vars.ajax_url + '?product_id=' + product_id });
        }
        var effect = jQuery(this).attr('data-effect');
        var data_wqv_json = jQuery(this).data('wqv');
        data_wqv = JSON.parse(data_wqv_json);

        var index = wqv_get_key(wqv_products, 'src', wqv_vars.ajax_url + '?product_id=' + product_id);

        jQuery.magnificPopup.open({
            items: wqv_products,
            type: 'ajax',
            mainClass: 'mfp-wqv',
            preloader: data_wqv.preloader,
            tLoading: data_wqv.preloader_label,
            autoFocusLast: false,
            fixedContentPos: true,
            fixedBgPos: true,
            ajax: {
                settings: {
                    type: 'GET',
                    data: {
                        action: 'wqv_popup_content',
                        _ajax_nonce: wqv_vars.nonce,
                    }
                }
            },
            showCloseBtn: data_wqv.close_button,
            closeMarkup: '<button title="%title%" type="button" class="mfp-close icon-4"></button>',
            removalDelay: 160, //delay removal by X to allow out-animation
            callbacks: {
                beforeOpen: function () {
                    if (typeof effect !== typeof undefined && effect !== false) {
                        this.st.mainClass = 'mfp-wqv ' + effect;
                    } else {
                        this.st.mainClass = 'mfp-wqv ' + wqv_vars.effect;
                    }
                },

                ajaxContentAdded: function () {
                    if (typeof wc_add_to_cart_variation_params !== 'undefined') {
                        get_selected_product_variation_image(data_wqv);

                        var form_variation = jQuery('.sp-wqv-content').find('.variations_form');
                        form_variation.each(function () {
                            jQuery(this).wc_variation_form();
                        });
                    }

                    if (data_wqv.lightbox) {

                        jQuery('[data-fancybox="wqvpro-gallery"]').fancybox({
                            baseClass: ' wqvp-fancybox-wrapper',
                            infobar: false,
                            caption: function () {
                                var caption = '';
                                if (data_wqv.image_title == 1) {
                                    caption = jQuery(this).attr('title') || '';
                                }
                                return caption;
                            },
                            buttons: [
                                "zoom",
                                "close"
                            ],
                        });
                    }
                    // Quick view Ajax Add to Cart.
                    if (data_wqv.ajax_cart) {
                        wqv_ajax_add_to_cart();
                    }

                    const ps = new PerfectScrollbar('.wqv-product-content', {});
                }
            },

        }, index);
        e.preventDefault();

        // Get all selected product variation images according to variation ID.
        function get_selected_product_variation_image(data_wqv) {
            var variation_images = {};
            // Get all the variation ID and Image.
            jQuery('form.variations_form').each(function () {
                var product_variations = JSON.parse(jQuery(this).attr('data-product_variations'));
                jQuery.each(product_variations, function (i, variation) {
                    if (variation.image.url) {
                        variation_images[variation.variation_id] = [variation.image.url, variation.image.title];
                    }
                });
            });
            // Change the variation image by selecting the variation options.
            jQuery('input.variation_id').on('change', function () {
                var variation_id = jQuery('input.variation_id').val();
                if (variation_images[variation_id]) {
                    var image_url = variation_images[variation_id][0];
                    var image_title = variation_images[variation_id][1];

                    if (data_wqv.lightbox) {
                        var lightbox_start = '<a data-fancybox="wqvpro-gallery" title="' + image_title + '" href="' + image_url + '">';
                        var lightbox_end = '</a>';
                    } else {
                        lightbox_start = '',
                            lightbox_end = '';
                    }
                    var image_html =
                        '<span data-thumb="' + image_url + '" class="selected" itemprop="image" title="' + image_title + '">' + lightbox_start + '<img src="' + image_url + '" />' + lightbox_end + '</span>';
                    jQuery('.wqv-product-images').html(image_html);
                }
            });
        }
    });

    // Redirect to the main image after clicking reset variation(clear) button.
    jQuery(document).on('click', '.sp-wqv-content .reset_variations', function (e) {
        var current_product = jQuery(this).parents('#wqv-quick-view-content').data('id');
        jQuery('.sp-wqv-view-button[data-id="' + current_product + '"]').trigger('click');
    });

    // Quick view AJax Add to Cart functionality.
    function wqv_ajax_add_to_cart() {
        jQuery('.single_add_to_cart_button').on('click', function (e) {
            e.preventDefault();

            if ( jQuery(this).hasClass( 'disabled' ) ) {
                return;
            }

            var $thisbutton = jQuery(this),
                $form = $thisbutton.closest('form.cart'),
                data = getFormData($form, jQuery(this));

            jQuery.ajax({
                url: wqv_vars.wc_ajax.toString().replace('%%endpoint%%', 'sp_ajax_add_cart'),
                enctype: 'multipart/form-data',
                type: 'post',
                data: data,
                processData: false,  // Important!
                contentType: false,
                cache: false,
                beforeSend: function (response) {
                    $thisbutton.removeClass('added').addClass('loading');
                },
                complete: function (response) {
                    $thisbutton.addClass('added').removeClass('loading');
                },
                success: function (response) {
                    if (response.error & response.product_url) {
                        window.location = response.product_url;
                        return;
                    } else {
                        jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
                    }
                },
            });
        });
    }

    // Get Form Data.
    function getFormData(form, trigger) {
        var formData = new FormData(form.get(0));

        // Get data from all buttons that contains a name and a value
        form.find('button').each(function () {
            var key = jQuery(this).attr('name');
            var value = jQuery(this).val();

            if (key && value && !formData.has(key)) {
                formData.append(key, value);
            }
        });

        // Fetch changes that are directly added by calling trigger.data( key, value )
        jQuery.each(trigger.data(), function (key, value) {
            if (!formData.has(key)) {
                formData.append(key, value);
            }
        });

        // Fetch data attributes in trigger. Give preference to data-attributes because they can be directly modified by javascript
        // while `.data` are jquery specific memory stores.
        jQuery.each(trigger[0].dataset, function (key, value) {
            if (!formData.has(key)) {
                formData.append(key, value);
            }
        });

        //If no add-to-cart / product_id found and has form action attr, look for the form action URL
        if (!formData.has('add-to-cart') && !formData.has('product_id') && form.attr('action')) {
            var is_url = form.attr('action').match(/add-to-cart=([0-9]+)/);
            var productID = is_url ? is_url[1] : false;
            if (productID) {
                formData.append('add-to-cart', productID);
            }
        }

        //If no add-to-cart found but product_id is found, use it.
        if (!formData.has('add-to-cart') && formData.has('product_id')) {
            formData.append('add-to-cart', formData.get('product_id'));
        }
        return formData;
    }

});

function wqv_get_key(array, key, value) {
    for (var i = 0; i < array.length; i++) {
        if (array[i][key] === value) {
            return i;
        }
    }
    return - 1;
}
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="woocommerce-products-footer">
	<?php
	/**
	 * Hook: woocommerce_archive_description.
	 *
	 * @since 1.6.2.
	 * @see woocommerce_taxonomy_archive_description - 10
	 * @see woocommerce_product_archive_description - 10
	 */
	do_action( 'woocommerce_archive_description' );
	?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const footer = document.querySelector('.woocommerce-products-footer');
            const desc = document.querySelector('.woocommerce-products-footer>div');
            if (!desc || !footer) return;

            const fullHeight = desc.scrollHeight;

            if (fullHeight > 90) {
                desc.classList.add('collapsed');
                const toggleBtn = document.createElement('button');
                toggleBtn.className = 'description-toggle';
                toggleBtn.innerText = 'Xem thêm';

                footer.appendChild(toggleBtn);
                toggleBtn.addEventListener('click', function () {
                    const isCollapsed = desc.classList.contains('collapsed');
                    if (isCollapsed) {
                        desc.classList.remove('collapsed');
                        toggleBtn.innerText = 'Rút gọn';
                    } else {
                        desc.classList.add('collapsed');
                        toggleBtn.innerText = 'Xem thêm';
                    }
                });
            }
        });
    </script>
</div>

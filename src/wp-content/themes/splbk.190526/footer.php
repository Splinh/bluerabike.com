<?php

/**
 * The template for displaying the footer.
 * Contains the body & HTML closing tags.
 *
 * @author Gaudev
 */

\defined('ABSPATH') || die;

/**
 * HOOK: hd_site_content_after_action
 */
do_action('hd_site_content_after_action');
?>
</main><!-- #site-content -->
<?php
/**
 * HOOK: hd_footer_before_action
 */
do_action('hd_footer_before_action');
?>
<footer id="footer" class="<?= apply_filters('hd_footer_class_filter', 'site-footer') ?>"
    <?= \HD_Helper::microdata('footer') ?>>
    <?php
    /**
     * HOOK: hd_footer_action
     *
     * @see Hook::construct_footer_action - 10
     */
    do_action('hd_footer_action');
    ?>
    <script>
        window._sbzaccid = 'acrlryslozqoscsgorif'
        window.subiz = function() {
            window.subiz.q.push(arguments)
        }
        window.subiz.q = []
        window.subiz('setAccount', window._sbzaccid)
    </script>
    <script src="https://widget.subiz.net/sbz/app.js?account_id=acpzooihzhalzeskamky"></script>
</footer><!-- #footer -->

<?php
/**
 * HOOK: hd_footer_after_action
 *
 * @see ContactLink::add_this_contact_link - 11
 */
do_action('hd_footer_after_action');

/**
 * HOOK: wp_footer
 *
 * @see Hook::wp_footer_action - 32
 */
wp_footer();
?>
</body>

</html>
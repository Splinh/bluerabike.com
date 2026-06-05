<?php
/**
 * The template for displaying `Contact`
 * Template Name: Contact page
 * Template Post Type: page
 *
 * @author Gaudev
 */

\defined( 'ABSPATH' ) || die;

// header
get_header( 'contact-us' );

if ( have_posts() ) {
	the_post();
}

if ( post_password_required() ) {
	echo get_the_password_form();
	get_footer( 'contact-us' );

	return;
}

// breadcrumbs
\HD_Helper::blockTemplate( 'parts/blocks/breadcrumbs', [ 'title' => get_the_title() ] );

$ACF          = \HD_Helper::getFields( get_the_ID() );
$contact_info = $ACF['contact_info'] ?? [];
$contact_form = $ACF['contact_form'] ?? [];

?>
<section class="section section-contact-us">
	<div class="container flex flex-x">
        <h1 class="heading-title sr-only" <?= \HD_Helper::microdata( 'headline' ) ?>><?= get_the_title() ?></h1>
        <?php
        if ( $contact_info ) :
	        $gr_title = $contact_info['gr_title'] ?? '';
	        $gr_desc = $contact_info['gr_desc'] ?? '';
	        $gr_img = $contact_info['gr_img'] ?? '';
	        $gr_info_repeater = $contact_info['gr_info_repeater'] ?? [];
        ?>
        <div class="cell contact-info">
            <div class="inner">
                <?php echo $gr_title ? '<p class="title">' . $gr_title . '</p>' : ''; ?>
                <?php echo $gr_desc ? '<div class="desc">' . $gr_desc . '</div>' : ''; ?>
                <?php echo $gr_img ? '<div class="thumb">' . \HD_Helper::attachmentImageHTML( $gr_img, 'medium' ) . '</div>' : ''; ?>

                <?php if ( $gr_info_repeater ) : ?>
                <p class="list-title"><?= __( 'Thông tin liên hệ', TEXT_DOMAIN ) ?></p>
                <ul class="list-info">
                    <?php foreach ( $gr_info_repeater as $re ) :
                        $re_title = $re['re_title'] ?? '';
                        $re_content = $re['re_content'] ?? '';
                    ?>
                    <li>
                        <div class="item">
                            <span class="label"><?= $re_title ?>:</span>
                            <div class="content"><?= $re_content ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                <div class="social-links">
                    <span class="txt"><?= __( 'Kết nối với chúng tôi', TEXT_DOMAIN ); ?></span>
		            <?php echo \HD_Helper::doShortcode( 'social_menu' ); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ( $contact_form ) :
            $gr_title = $contact_form['gr_title'] ?? '';
            $gr_form = $contact_form['gr_form'] ?? 0;
            $gr_map = $contact_form['gr_map'] ?? '';
        ?>
        <div class="cell contact-form">
            <div class="inner">
	            <?php echo $gr_title ? '<p class="title">' . $gr_title . '</p>' : ''; ?>
	            <?php echo $gr_form ? '<div class="form-wrapper">' . \HD_Helper::doShortcode( 'contact-form-7', [ 'id' => $gr_form ] ) . '</div>' : ''; ?>
	            <?php echo $gr_map ? '<div class="iframe-wrapper">' . $gr_map . '</div>' : ''; ?>
            </div>
        </div>
        <?php endif; ?>
	</div>
</section>
<?php

// footer
get_footer( 'contact-us' );

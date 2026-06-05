<?php
/**
 * The template for displaying `Blog page`
 * Template Name: Cơ hội hợp tác page page
 * Template Post Type: page
 *
 * @author SPL
 */

get_header('cohoi');
\HD_Helper::blockTemplate( 'parts/blocks/breadcrumbs', [ 'title' => get_the_title() ] );
$ACF        = \HD_Helper::getFields( get_the_ID() );
?>

<?php
$cohoi_flexible_content = ! empty( $ACF['cohoi_flexible_content'] ) ? (array) $ACF['cohoi_flexible_content'] : false;
if ( $cohoi_flexible_content ) {

	foreach ( $cohoi_flexible_content as $i => $section ) {
		$section['id'] = $i;
		$acf_fc_layout = $section['acf_fc_layout'] ?? '';

		if ( $acf_fc_layout ) {
			\HD_Helper::blockTemplate( 'parts/home/' . $acf_fc_layout, $section );
		}
	}
}
get_footer('cohoi');
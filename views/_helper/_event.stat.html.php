<?php defined( "ABSPATH" ) or die(); ?>

<?php $item = \siteguard\security\model\EventManager::getCurrentEvent() ?>

<h3><?php echo esc_html__( "Statistics", "siteguard-security" ) ?></h3>

<dl>
	<dt><?php echo esc_html__( "First occurrence", "siteguard-security" ) ?></dt>
	<dd><?php echo \siteguard\security\model\Format::formatDateGMT( $item->post_date_gmt ) ?></dd>

	<dt><?php echo esc_html__( "Last occurrence", "siteguard-security" ) ?></dt>
	<dd><?php echo \siteguard\security\model\Format::formatDateGMT( $item->post_modified_gmt ) ?></dd>

	<?php $reset_time = (int) get_post_meta( $item->ID, "reset_time", true ) ?>
	<?php if ( $reset_time != 0 ) : ?>
		<dt><?php echo esc_html__( "Reset", "siteguard-security" ) ?></dt>
		<dd><?php echo \siteguard\security\model\Format::formatTime( $reset_time ) ?></dd>
	<?php endif ?>

	<?php $count = (int) get_post_meta( $item->ID, "count", true ) ?>
	<?php if ( $count > 0 ) : ?>
		<dt><?php echo esc_html__( "Count", "siteguard-security" ) ?></dt>
		<dd><?php echo \siteguard\security\model\Format::getCount( $item->ID, false ) ?></dd>
	<?php endif ?>
</dl>

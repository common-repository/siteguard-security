<?php defined( "ABSPATH" ) or die(); ?>

<?php $item = \siteguard\security\model\EventManager::getCurrentEvent() ?>

<div class="wrap">
	<h1><?php echo esc_html__( "Event - Error", "siteguard-security" ) ?></h1>

	<h3><?php echo esc_html__( "Information", "siteguard-security" ) ?></h3>

	<dl>
		<dt><?php echo esc_html__( "Message", "siteguard-security" ) ?></dt>
		<dd><?php echo nl2br( esc_html( wp_strip_all_tags( get_post_meta( $item->ID, "msg", true ) ) ) ) ?></dd>

		<dt><?php echo esc_html__( "Level", "siteguard-security" ) ?></dt>
		<dd><?php echo \siteguard\security\model\Format::errorLevToString( get_post_meta( $item->ID, "lev", true ) ) ?></dd>

		<?php $plugin = get_post_meta( $item->ID, "plugin", true ) ?>
		<?php if ( ! empty( $plugin ) ) : ?>
			<dt><?php echo esc_html__( "Plugin", "siteguard-security" ) ?></dt>
			<dd><?php echo \siteguard\security\model\Format::getPluginLink( $item->ID ) ?></dd>
		<?php endif ?>

		<dt><?php echo esc_html__( "Occurred at", "siteguard-security" ) ?></dt>
		<dd><?php echo esc_html( get_post_meta( $item->ID, "file", true ) . ":" . get_post_meta( $item->ID, "line", true ) ) ?></dd>
	</dl>

	<?php \siteguard\security\system\System::requireLocalPath( "/views/_helper/_event.stat.html.php" ) ?>

	<?php \siteguard\security\system\System::requireLocalPath( "/views/_helper/_event.req.html.php" ) ?>
</div>

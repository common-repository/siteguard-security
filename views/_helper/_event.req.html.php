<?php defined( "ABSPATH" ) or die(); ?>

<?php $item = \siteguard\security\model\EventManager::getCurrentEvent() ?>

<h3><?php echo esc_html__( "Request information (last occurrence)", "siteguard-security" ) ?></h3>

<dl>
	<dt><?php echo esc_html__( "Request URL", "siteguard-security" ) ?></dt>
	<dd><?php echo esc_html( get_post_meta( $item->ID, "request_uri", true ) ) ?></dd>

	<?php $http_user_agent = get_post_meta( $item->ID, "http_user_agent", true ) ?>
	<?php if ( ! empty( $http_user_agent ) ) : ?>
		<dt><?php echo esc_html__( "Http user agent", "siteguard-security" ) ?></dt>
		<dd><?php echo esc_html( $http_user_agent ) ?></dd>
	<?php endif ?>

	<dt><?php echo esc_html__( "Entry point", "siteguard-security" ) ?></dt>
	<dd><?php echo esc_html( get_post_meta( $item->ID, "script_filename", true ) ) ?></dd>

	<?php $remote_addr = get_post_meta( $item->ID, "remote_addr", true ) ?>
	<?php if ( ! empty( $remote_addr ) ) : ?>
		<dt><?php echo esc_html__( "Client IP", "siteguard-security" ) ?></dt>
		<dd><?php echo esc_html( $remote_addr ) ?></dd>
	<?php endif ?>

	<?php $user_id = (string) get_post_meta( $item->ID, "user", true ) ?>
	<?php if ( ! empty( $user_id ) ) : ?>
		<dt><?php echo esc_html__( "Logged user", "siteguard-security" ) ?></dt>
		<?php if ( $user_id === "0" ) : ?>
			<dd><?php echo esc_html__( "None", "siteguard-security" ) ?></dd>
		<?php else: ?>
			<?php $user = get_user_by( "id", $user_id ) ?>
			<?php if ( is_object( $user ) ) : ?>
				<dd>
					<a href="/wp-admin/user-edit.php?user_id=<?php echo $user->ID ?>"><?php echo esc_html( $user->user_login ) ?></a>
				</dd>
			<?php else: ?>
				<dd><?php echo esc_html( sprintf( __( "Unknown (ID: %s)", "siteguard-security" ), $user_id ) ) ?></dd>
			<?php endif ?>
		<?php endif ?>
	<?php endif ?>
</dl>

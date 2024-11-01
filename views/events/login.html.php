<?php defined( "ABSPATH" ) or die(); ?>

<?php $item = \siteguard\security\model\EventManager::getCurrentEvent() ?>

<div class="wrap">
	<h1><?php echo esc_html__( "Event - Login failed", "siteguard-security" ) ?></h1>

	<h3><?php echo esc_html__( "Information", "siteguard-security" ) ?></h3>

	<dl>
		<?php $user_login = (string) get_post_meta( $item->ID, "user_login", true ) ?>
		<?php $user = get_user_by( "login", $user_login ) ?>

		<dt><?php echo esc_html__( "Username", "siteguard-security" ) ?></dt>
		<?php if ( is_object( $user ) ) : ?>
			<dd>
				<a href="/wp-admin/user-edit.php?user_id=<?php echo $user->ID ?>"><?php echo esc_html( $user_login ) ?></a>
			</dd>
		<?php else: ?>
			<dd><?php echo esc_html( $user_login ) ?></dd>
		<?php endif ?>

		<dt><?php echo esc_html__( "Exists", "siteguard-security" ) ?></dt>
		<dd><?php echo esc_html__( is_object( $user ) ? "Yes" : "No", "siteguard-security" ) ?></dd>
	</dl>

	<?php $ip_map = get_post_meta( $item->ID, "ip_map", true ) ?>
	<?php if ( is_array( $ip_map ) && count( $ip_map ) > 0 ) : ?>
		<?php uasort( $ip_map, function ( $a1, $a2 ) {
			return (int) ( $a2["time"] - $a1["time"] );
		} ) ?>

		<h3><?php echo esc_html( sprintf( _n( "Last ip that fail to login", "Last %d ips that fail to login", count( $ip_map ), "siteguard-security" ), count( $ip_map ) ) ) ?></h3>

		<dl>
			<?php foreach ( $ip_map as $ip => $data ) : ?>
				<dt><?php echo esc_html( $ip ) ?></dt>
				<dd>
					<?php echo \siteguard\security\model\Format::getCount( $data, false ) ?>
					<br>
					<?php echo sprintf( __( "Last fail %s.", "siteguard-security" ), \siteguard\security\model\Format::formatTime( $data["time"] ) ) ?>
				</dd>
			<?php endforeach ?>
		</dl>
	<?php endif ?>

	<?php \siteguard\security\system\System::requireLocalPath( "/views/_helper/_event.stat.html.php" ) ?>

	<?php \siteguard\security\system\System::requireLocalPath( "/views/_helper/_event.req.html.php" ) ?>
</div>

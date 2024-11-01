<?php defined( "ABSPATH" ) or die(); ?>

<?php $table = \siteguard\security\model\SGListTable::getTable( "Event" ); ?>

<?php echo $table->showMessage(); ?>

<div class="wrap">
	<h1><?php echo esc_html__( "Events", "siteguard-security" ) ?></h1>

	<form method="post" action="">
		<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST["page"] ); ?>"/>
		<?php $table->search_box( __( "Search", "siteguard-security" ), "events" ); ?>
		<?php $table->display(); ?>
	</form>
</div>

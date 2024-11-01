<?php defined( "ABSPATH" ) or die(); ?>

<?php $issues = \siteguard\security\model\IssueManager::getIssues() ?>

<?php $rows = array(
	array( "Login failed", "login" ),
	array( "Error", "error" ),
	array( "Exception", "exception" )
) ?>

<?php $stats = \siteguard\security\model\EventManager::getStatisticByType() ?>
<?php $event_page_url = menu_page_url( "siteguard-security-events", false ) ?>

<div class="main">
	<table>
		<thead>
		<tr>
			<th><?php echo esc_html__( "Event type", "siteguard-security" ) ?></th>

			<th><?php echo esc_html__( "Today", "siteguard-security" ) ?></th>
			<th><?php echo esc_html__( "Yesterday", "siteguard-security" ) ?></th>

			<th><?php echo esc_html__( "This month", "siteguard-security" ) ?></th>
			<th><?php echo esc_html__( "Previous month", "siteguard-security" ) ?></th>

			<th><?php echo esc_html__( "Total", "siteguard-security" ) ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ( $rows as $row ) : ?>
			<tr>
				<td>
					<a href="<?php echo "{$event_page_url}&t={$row[1]}" ?>"><?php echo esc_html__( $row[0], "siteguard-security" ) ?></a>
				</td>

				<td><?php echo \siteguard\security\model\Format::getNumFromTable( $stats, "siteguard_{$row[1]}", "today" ) ?></td>
				<td><?php echo \siteguard\security\model\Format::getNumFromTable( $stats, "siteguard_{$row[1]}", "today-1" ) ?></td>

				<td><?php echo \siteguard\security\model\Format::getNumFromTable( $stats, "siteguard_{$row[1]}", "month" ) ?></td>
				<td><?php echo \siteguard\security\model\Format::getNumFromTable( $stats, "siteguard_{$row[1]}", "month-1" ) ?></td>

				<td><?php echo \siteguard\security\model\Format::getNumFromTable( $stats, "siteguard_{$row[1]}", "total" ) ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
		<tfoot>
		<tr>
			<th><a href="<?php echo $event_page_url ?>"><?php echo esc_html__( "All", "siteguard-security" ) ?></a></th>

			<th><?php echo \siteguard\security\model\Format::getNumFromTable( $stats, null, "today" ) ?></th>
			<th><?php echo \siteguard\security\model\Format::getNumFromTable( $stats, null, "today-1" ) ?></th>

			<th><?php echo \siteguard\security\model\Format::getNumFromTable( $stats, null, "month" ) ?></th>
			<th><?php echo \siteguard\security\model\Format::getNumFromTable( $stats, null, "month-1" ) ?></th>

			<th><?php echo \siteguard\security\model\Format::getNumFromTable( $stats, null, "total" ) ?></th>
		</tr>
		</tfoot>
	</table>
	<?php if ( count( $issues ) > 0 ) : ?>
		<ul>
			<li><strong><?php echo esc_html__( "Security tips:", "siteguard-security" ) ?></strong></li>
			<?php foreach ( $issues as $issue ) : ?>
				<li><?php echo $issue["html"] ?></li>
			<?php endforeach ?>
		</ul>
	<?php endif ?>
</div>

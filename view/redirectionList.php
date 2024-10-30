<?php include(dirname(__FILE__) . '/header.php'); ?> 
	<h3>Redirections</h3>
	<p>
		<a href="#">Unresolved</a> |
		<a href="#">Resolved</a> |
		<a href="#">All</a>
	</p>
	
	
	<?php if(count($results) > 0) { ?>
		<table width="100%">
		<tr>
			<td><strong>Source</strong></td>
			<td><strong>Destination</strong></td>
			<td><strong>Request Count</strong></td>
			<td><strong>Action</strong></td>
		</tr>
		<?php foreach($results as $result) { ?>
			<tr>
				<td><?php echo $result['source_url']; ?></td>
				<td><?php echo $result['destination_url']; ?></td>
				<td><?php echo $result['request_count']; ?></td>
				<td><a href="<?php echo $subpage_url ?>&subpage=redirectionEdit&id=<?php echo $result['id']?>">Edit</a> | <a href="#">Delete</a></td>
			</tr>
		<?php }?>
		</table>
	<?php } else { ?>
		<p>There are no redirections in place yet</p>
	<?php } ?>
	
	<p><a href="<?php echo $subpage_url ?>&subpage=redirectionEdit">Add New Redirection</a></p>
	
	
<?php include(dirname(__FILE__) . '/footer.php'); ?>
<?php include(dirname(__FILE__) . '/header.php'); ?> 
	<h3>Preferences</h3>
		
	<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<table class="form-table">
	<tr>
		<th>Source URL</th>
		<td><input type="text" name="source_url" class="cs_input_url" /><br />The original web address of the page</td>
	</tr>
	<tr>
		<th>Destination URL</th>
		<td><input type="text" name="destination_url" class="cs_input_url" /><br />The page to forward the request onto</td>
	</tr>
	
	</table>
	<p class="submit">
		<input type="submit" name="submit" value="Add Redirection" />
	</p>
	</form>
<?php include(dirname(__FILE__) . '/footer.php'); ?>
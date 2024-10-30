<div class="wrap">
	<h2>csRedirection</h2>

	<?php
		$url_parts = $_GET;
		unset($url_parts['subpage'], $url_parts['id']);
		
		$subpage_url = $_SERVER['SCRIPT_NAME'];
		if($url_parts) {
			$subpage_url .= '?';
			foreach($url_parts as $k => $v) {
				$subpage_url .= $k . '=' . $v . '&';
			}
			$subpage_url = substr($subpage_url, 0, strlen($subpage_url) - 1);
		}
	?>
	<div class="redirector_subpage">
		<a href="<?php echo $subpage_url ?>">Redirections</a> |
		<a href="<?php echo $subpage_url ?>&subpage=preferences">Preferences</a> |
		<a href="<?php echo $subpage_url ?>&subpage=about">About</a>
	</div>
	
	<?php if($errorArray && is_array($errorArray)) { ?>
	<ul>
		<li><?php echo implode($errorArray, '</li><li>'); ?></li> 
	</ul>
	<?php } ?>
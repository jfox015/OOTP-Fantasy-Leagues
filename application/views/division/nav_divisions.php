        <?php 
		$itemId = -1;
		if (isset($thisItem['id']) && $thisItem['id'] != -1) {
			$itemId = $thisItem['id'];
		?>
        <h3>About this division</h3>
          <ul class="nav">
            <li><?php echo( anchor($config['fantasy_web_root'].'divisions/info/'.$itemId,'View Info')); ?></li>
            <li><?php echo( anchor($config['fantasy_web_root'].'divisions/submit/mode/edit/id/'.$itemId,'Edit')); ?></li>
            <li><?php echo( anchor($config['fantasy_web_root'].'divisions/submit/mode/delete/id/'.$itemId,'Delete')); ?></li>
          </ul>
          
        <?php } ?>
        <?php if ($loggedIn && $accessLevel >= ACCESS_DEVELOP) { ?> 
        <div id="leftnav"><?php 
			$itemId = -1;
			if (isset($thisItem['id']) && $thisItem['id'] != -1) {
				$itemId = $thisItem['id'];
			?>
        
        <h5>About this Bug</h5>
        <ul>
            <li><?php echo anchor('/project/info/'.$itemId,'View Info'); ?></li>
            <li><?php echo anchor('/project/submit/mode/edit/id/'.$itemId,'Edit'); ?></li>
            <li><?php echo anchor('/project/submit/mode/delete/id/'.$itemId,'Delete'); ?></li>
			<?php } ?>
        </ul>

        <h5>Project/Bug Database</h5>
        <ul>
           
            <li><?php echo anchor('/search/projects','View Project List'); ?></li>
            <li><?php echo anchor('/search/bugs','View Bug Database'); ?></li>
            <li><?php echo anchor('/project','Add a new project'); ?></li>
            
        </ul>
        </div>
		<?php } ?>
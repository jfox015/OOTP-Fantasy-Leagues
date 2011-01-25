         
        <div id="leftnav"><?php 
			$itemId = -1;
			if (isset($thisItem['id']) && $thisItem['id'] != -1) {
				$itemId = $thisItem['id'];
			?>
        <?php if ($loggedIn && $accessLevel >= ACCESS_DEVELOP) { ?>
        <h5>About this Bug</h5>
        <ul>
            <li><?php echo anchor('/bug/info/'.$itemId,'View Info'); ?></li>
            <li><?php echo anchor('/bug/submit/mode/edit/id/'.$itemId,'Edit'); ?></li>
            <li><?php echo anchor('/bug/attachment/'.$itemId,'Add/Edit Attachment'); ?>
			<?php if ((isset($thisItem['assignedTo']) && $thisItem['assignedTo'] != -1) && ($thisItem['assignedTo'] == $name)) { ?>
            <li><?php echo anchor('/bug_resolve/submit/'.$itemId,'Resolve Bug'); ?></li>
            <li><?php echo anchor('/bug/submit/mode/delete/id/'.$itemId,'Delete'); ?></li>
			<?php } ?>
        </ul>
        <?php } 
		}?>
		<h5>Bug Database</h5>
        <ul>
            <?php if ($loggedIn && $accessLevel >= ACCESS_DEVELOP) { ?>
            <li><?php echo anchor('/search/bugs','View Bug Database'); ?></li>
            <li><?php echo anchor('/bug','Add a new Bug'); ?></li>
            <?php 
			} else { ?>
				<li>Have a bug to add?  Use the handy<?php echo anchor('/about/bug_report/'.$itemId,'Bug Report Form'); ?></li>
			<?php } ?>
        </ul>
        </div>
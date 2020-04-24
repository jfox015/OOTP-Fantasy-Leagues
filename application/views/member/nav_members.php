        <?php 
		$itemId = -1;
		if (isset($thisItem['id']) && $thisItem['id'] != -1 && $thisItem['id'] != 'add') {
			$itemId = $thisItem['id'];
		?>
        <h3>About this member</h3>
          <ul class="nav">
            <li><?php echo( anchor('/member/info/'.$itemId,'View Info')); ?></li>
            <li><?php echo( anchor('/member/submit/mode/edit/id/'.$itemId,'Edit')); ?></li>
            <li><?php echo( anchor('/member/submit/mode/delete/id/'.$itemId,'Delete')); ?></li>
            <li><?php echo( anchor('/member/resetPassword/attendeeId/'.$itemId,'Reset Password')); ?></li>
          </ul>
          
        <?php } ?>
        <h3>More Member Tools</h3>
        <ul class="nav">
            <li><?php echo( anchor('/search/members','View member list')); ?></li>
            <li><?php echo( anchor('/member/submit/add','Add a new member')); ?></li>
        </ul>
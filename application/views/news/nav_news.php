        <?php 
		$itemId = -1;
		if (isset($thisItem['id']) && $thisItem['id'] != -1 && $thisItem['id'] != 'add') {
			$itemId = $thisItem['id'];
		
		if ($itemId != -1) {
		?>
        <h3>About this news</h3>
          <ul class="nav">
            <li><?php echo( anchor('/news/info/'.$itemId,'View Info')); ?></li>
            <li><?php echo( anchor('/news/submit/mode/edit/id/'.$itemId,'Edit')); ?></li>
            <li><?php echo( anchor('/news/submit/mode/delete/id/'.$itemId,'Delete')); ?></li>
            <li><?php echo( anchor('/news/image/'.$itemId,'Add/Edit Image')); ?></li>
          </ul>
          
        <?php }} ?>
        <h3>More Attendee Tools</h3>
        <ul class="nav">
            <li><?php echo( anchor('/search/news','View news list')); ?></li>
            <li><?php echo( anchor('/news/submit/add','Add a new news')); ?></li>
        </ul>
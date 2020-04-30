        <?php 
    $itemId = -1;
    $typeIdStr = "";
    $varIdStr = "";
		if (isset($thisItem['id']) && $thisItem['id'] != -1 && $thisItem['id'] != 'add') {
      $itemId = $thisItem['id'];
      if (isset($type_id) && !empty($type_id) && $type_id != -1) {
        $typeIdStr = "/type_id/".$type_id;
      }
      if (isset($var_id) && !empty($var_id) && $var_id != -1) {
          $varIdStr = "/var_id/".$var_id;
      }
		
		if ($itemId != -1) {
		?>
        <h3>About this news</h3>
          <ul class="nav">
            <li><?php echo( anchor('/news/article/id/'.$itemId.$typeIdStr.$varIdStr,'View News Article')); ?></li>
            <li><?php echo( anchor('/news/submit/mode/edit/id/'.$itemId.$typeIdStr.$varIdStr,'Edit')); ?></li>
            <li><?php echo( anchor('/news/submit/mode/delete/id/'.$itemId.$typeIdStr.$varIdStr,'Delete')); ?></li>
            <li><?php echo( anchor('/news/image/'.$itemId,'Add/Edit Image')); ?></li>
          </ul>
          
        <?php }
      } ?>
        <h3>More News Tools</h3>
        <ul class="nav">
            <li><?php echo( anchor('/news/articles/'.$typeIdStr.$varIdStr,'View All News Articlest')); ?></li>
            <li><?php echo( anchor('/news/submit/add/'.$typeIdStr.$varIdStr,'Add a new Article')); ?></li>
        </ul>
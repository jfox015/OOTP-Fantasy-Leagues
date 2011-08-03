    <script type="text/javascript">
    $(document).ready(function(){		   
		$('#delete').click(function(){
			document.location.href = '/admin/member/delete/<?php echo($thisItem['id']); ?>';
		});	
		$('#cancel').click(function(){
			<?php if (isset($thisItem['id']) && $thisItem['id'] != -1) { ?>
			document.location.href = '/admin/member/info/<?php echo($thisItem['id']); ?>';
			<?php } else { ?>
			history.back(-1);
			<?php } ?>
		});
	});
    </script>
    <div id="left-column">
   	<? include_once('nav_members.php'); ?>
    </div>
    <div id="center-column">
        <div class="top-bar"> <h1><?php echo $subTitle; ?></h1></div>
        <br class="clear" />
        <?php if (isset($dump) && !empty($dump)) {
			echo("Object Data Dump:<br />".$dump."<br />");
		} ?>
        <div class="table">
        <table class="listing form" cellpadding="0" cellspacing="0">
          <tr>
            <th class="full" colspan="2">Enter the information for this member below.</th>
          </tr>
          <tr>
            <td class="onecell" width="100%">
            <?php 
				$errors = validation_errors();
				if ($errors) {
					echo '<div class="error">The following errors were found with your submission:<br /><ul>'.$errors.'</ul></div>';
				}
				echo($form);
                ?>
            
            </td>
          </tr>
        </table>
        <p>&nbsp;</p>
      </div>
    </div>
    <p /><br />
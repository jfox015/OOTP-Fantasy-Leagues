    <script type="text/javascript">
    $(document).ready(function(){		   
    	$("input[name='useTrades']").change(function() {
			testTradeStatus($('.tradesRadio:checked').val());
		});
		testTradeStatus(<?php echo($useTrades); ?>);

	});
	function testTradeStatus(val) {
		$('.tradeDetails').css('display',(val == 1 ? "block" : "none") );
	};
    </script>
    <div id="single-column">
   	 	
		<?php if (isset($thisItem['id']) && $thisItem['id'] != -1) {
		    include_once('admin_breadcrumb.php'); 
		}
		?>
        <div class="top-bar"> <h1><?php echo $subTitle; ?></h1></div>
        <br class="clear" />
        <?php if (isset($dump) && !empty($dump)) {
			echo("Object Data Dump:<br />".$dump."<br />");
		} ?>
        <div class="table">
        <table class="listing form" cellpadding="0" cellspacing="0">
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
    <p><br />
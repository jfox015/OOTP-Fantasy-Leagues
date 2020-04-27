    <script type="text/javascript">
    $(document).ready(function(){		   
		$('#delete').click(function(){
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>league/delete/<?php echo($thisItem['id']); ?>';
		});	
		$('#cancel').click(function(){
			<?php if (isset($thisItem['id']) && ($thisItem['id'] != "add" && $thisItem['id'] != -1)) { ?>
			document.location.href = '<?php echo($config['fantasy_web_root']); ?>league/info/<?php echo($thisItem['id']); ?>';
			<?php } else { ?>
			history.back(-1);
			<?php } ?>
		});
		$('#league_type').change(function(){
			showH2HOp($('#league_type').val());
		});
		showH2HOp(<?php print($league_type); ?>);

		$('#regular_scoring_periods').change(function(){
			checkPlayoffs();
		});
		$('#playoff_rounds').change(function(){
			checkPlayoffs();
		});
	});
	function showH2HOp(val) {
		var type = "none";
		if (val == <?php print(LEAGUE_SCORING_HEADTOHEAD); ?>) {
			type = "block";
		}
		$('#optHeadToHead').css('display',type);
	}
	function checkPlayoffs() {
		var scoringPeriods = parseInt($('#regular_scoring_periods').val()), 
		playoffs = parseInt($('#playoff_rounds').val());
		if ((scoringPeriods + playoffs) > scoring_periods_available) {
			$('#regular_scoring_periods').val((scoring_periods_available - playoffs));
		}
	}
    </script>
    <div id="single-column">
   	 	
		<?php if (isset($thisItem['id']) && ($thisItem['id'] != "add" && $thisItem['id'] != -1)) {
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
            <th class="full" colspan="2">Enter the information for this league below.</th>
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
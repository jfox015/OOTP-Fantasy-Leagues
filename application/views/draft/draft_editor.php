<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.ui.core.js"></script>
<script type="text/javascript" src="<?php echo($config['fantasy_web_root']); ?>js/jquery.ui.datepicker.js"></script>
<script type="text/javascript">
$(function() {
	var today = new Date();
		var draftStart = '<?php echo($draftStart); ?>';
		var startArr = draftStart.split("-");
		var draftEnd = '<?php echo($draftEnd); ?>';
		var endArr = draftEnd.split("-");
		$("#dateField").datepicker({ minDate: new Date(startArr[0], startArr[1]-1, startArr[2] ), maxDate: new Date(endArr[0], endArr[1]-1, endArr[2] )});
		$('#cancel').click(function(){ history.back(-1); });
    $('a[rel=setToActive]').live('click',function (e) {
      e.preventDefault();
      $("#nRounds").val(this.id)
		});
	});
    </script>
    <div id="center-column">
        <?php include_once('admin_breadcrumb.php'); ?>
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
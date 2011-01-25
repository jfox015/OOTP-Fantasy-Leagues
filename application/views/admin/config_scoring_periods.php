	<script type="text/javascript">
	$(document).ready(function(){
		$('a[rel=periodEdit]').click(function() {
			document.location.href="<?php echo($config['fantasy_web_root']); ?>admin/configScoringPeriodsEdit/period_id/"+this.id;
				return false;		
		});
	});
	</script>
	<div id="center-column">
        <?php include_once('admin_breadcrumb.php'); ?>
        <h1><?php echo($subTitle); ?></h1>
        <?php
        $errors = validation_errors();
		if ($errors) {
			echo '<span class="error">The following errors were found with your submission:<br/ ><b>'.$errors.'</b><br clear="all" /></span><br /><br />';
		}
		if ($outMess) {
			echo $outMess;
		}
		$cols = "4";
		if (isset($scoring_edit) && $scoring_edit === true) {
			$cols = "5";
		} else {
			echo('<span class="notice">This page is read only while the fantasy season is in progress</span><br />');
		}
		?>
        <div class='textbox'>
	    <table cellpadding="5" cellspacing="2" border="0" style="width:500px;">
	    <tr class='title'>
	    	<td style='padding:6px' colspan="<?php echo($cols); ?>">Scoring Periods</td>
	    </tr>
        <tr class='headline'>
	    	<td style='padding:0px'>Period ID</td>
            <td style='padding:6px'>Start Date</td>
            <td style='padding:6px'>End Date</td>
            <td style='padding:6px'># Days</td>
            <?php if (isset($scoring_edit) && $scoring_edit === true) { ?>
            <td style='padding:6px'>Tools</td>
            <?php } ?>
	    </tr>
		<?php 
        /*****************************************
        /	BATTING STATS
        /****************************************/
        $statCount = 0;
        if (isset($periods) && sizeof($periods) > 0) {
            $rowCount = 0;
            foreach($periods as $data) { 
                ?>
        <tr class='s<?php if (($rowCount%2)!=0) { echo("1"); } else { echo("2"); } ?>'>
        	<?php
			$timeStartDate = strtotime($data['date_start']);
			$timeEndDate = strtotime($data['date_end']);
			?>
            <td align="center"><?php echo($data['id']); ?></td>
            <td class="hsc2_l"><?php echo(date('m/d/Y',$timeStartDate)); ?></td>
            <td class="hsc2_l"><?php echo(date('m/d/Y',$timeEndDate)); ?></td>
            <td align="center"><?php echo(sizeof(getDaysInBetween($data['date_start'],$data['date_end']))); ?></td>
            <?php if (isset($scoring_edit) && $scoring_edit === true) { ?>
            <td class="hsc2_l"><a href="#" rel="periodEdit" id="<?php echo($data['id']); ?>"><img src="<?php echo($config['fantasy_web_root']); ?>images/icons/edit-icon.gif" width="16" height="16" alt="Edit" title="Edit" /></a></td>
            <?php } ?>
        </tr>
            <?php 
            $rowCount++;
            }  // END foreach 
        }  // END if 
		?>
        </table>
        </div>
        </form>
    </div>
		<!-- BEGIN REGISTRATION FORM -->
    <div id="center-column">
        <?php include_once('admin_breadcrumb.php'); ?>
        <h1><?php echo($subTitle); ?></h1>
        <br />
        <div class='textbox'>
        <table cellpadding="0" cellspacing="0" border="0" style="width:825px;">
        <tr class='title'>
            <td style='padding:3px' colspan="2">Current Settings (Read Only)</td>
        </tr>
        <tr>
            <td style="padding:10px;">
			<?php 
            if (isset($fields) && sizeof($fields) > 0) {
				foreach($fields as $group => $list) {
					echo('<h2>'.$group."</h2>");
					foreach($list as $field =>$label) {
						echo('<label>'.$label.":</label>");
						switch ($field) {
							case 'seasonStart':
								echo('<span class="formData">'.$season_start."</span>");
								break;
							case 'draftPeriod':
								echo('<span class="formData">'.$draft_start." - ".$draft_end."</span>");
								break;
							case 'useWaivers':
								echo('<span class="formData">'.(($config[$field] == 1) ? 'Yes':'No')."</span>");
								break;
							default:
								echo('<span class="formData">'.$config[$field]."</span>");
								break;
						}
						echo('<br /><br />');
					}
				}
			}
            ?>
            </td>
        </tr>
        </table>
        </div>
    </div>
    <p /><br />
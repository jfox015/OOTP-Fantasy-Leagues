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
								print('<span class="formData">'.$season_start."</span>");
								break;
							case 'draftPeriod':
								print('<span class="formData">'.$draft_start." - ".$draft_end."</span>");
								break;
							case 'useWaivers':
							case 'useTrades':
							case 'tradesExpire':
							case 'google_analytics_enable':
							case 'restrict_admin_leagues':
							case 'users_create_leagues':
							case 'limit_load_all_sql':
								print('<span class="formData">'.(($config[$field] == 1) ? 'Yes':'No')."</span>");
								break;
							case 'stats_lab_compatible':
								print('<span class="formData">'.(($config[$field] == 1) ? 'On':'Off')."</span>");
								break;
							case 'primary_contact':
								print(anchor('/user/profile/'.$config[$field],$this->user_auth_model->getusername($config[$field])));
								break;
							case 'approvalType':
								$types = loadSimpleDataList('tradeApprovalType');
								print($types[$config[$field]]);
								break;
							
							default:
								print('<span class="formData">'.$config[$field]."</span>");
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
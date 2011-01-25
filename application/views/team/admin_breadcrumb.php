<?php 
if (!isset($team_id)) { $team_id = -1; }
if (isset($thisItem['id'])) { $team_id = $thisItem['id']; }
if (isset($thisItem['team_id'])) { $team_id = $thisItem['team_id']; }

echo anchor('/','Home')." -&gt; ".anchor('/team/admin/'.$league_id,'Team Settings')." -&gt; ".$subTitle; ?>
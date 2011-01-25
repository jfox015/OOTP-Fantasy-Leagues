<?php 
if (!isset($league_id)) { $league_id = -1; }
if (isset($thisItem['id'])) { $league_id = $thisItem['id']; }
if (isset($thisItem['league_id'])) { $league_id = $thisItem['league_id']; }

echo anchor('/','Home')." -&gt; ".anchor('/league/admin/'.$league_id,'League Settings')." -&gt; ";
if (!strpos($_SERVER['PHP_SELF'],'showList')) {
	echo(anchor('/divisions/showList/league_id/'.$league_id,'Division List')." -&gt; ");
}
echo $subTitle; 
?>
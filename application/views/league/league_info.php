
<div id="single-column">
	<div class="top-bar">
		<h1><?php 
			if (isset($thisItem['avatar']) && !empty($thisItem['avatar'])) { 
				$avatar = PATH_LEAGUES_AVATARS.$thisItem['avatar']; 
			} else {
				$avatar = PATH_LEAGUES_AVATARS.DEFAULT_AVATAR;
			} ?>
			<img src="<?php echo($avatar); ?>" 
			border="0" width="50" height="50" alt="<?php echo($thisItem['league_name']); ?>" 
			title="<?php echo($thisItem['league_name']); ?>" /> 
			<?php echo($thisItem['league_name']); ?> Teams
		</h1>
	</div>

	<div id="content">
		<?php 
		if (isset($thisItem['description']) && !empty($thisItem['description'])) { 
			echo($thisItem['description']."<br /><br />"); 
		}
		?>
		<div class="layout">
			<?php 
			/*-------------------------------------------------
			/
			// HEAD TO HEAD LEAGUES
			/
			/------------------------------------------------*/
			if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
				if (isset($thisItem['divisions']) && sizeof($thisItem['divisions']) > 0) { 
					foreach($thisItem['divisions'] as $id=>$divisionData) {
						?>
					<div class="layout-column">
						<div class="headline"><?php echo($divisionData['division_name']); ?></div>
						<section class="content">
						<?php
						if (isset($divisionData['teams']) && sizeof($divisionData['teams']) > 0) { 
							foreach($divisionData['teams'] as $teamId => $teamData) { 
								$teamName = $teamData['teamname']." ".$teamData['teamnick'];
								if (isset($teamData['avatar']) && !empty($teamData['avatar'])) { 
									$avatar = PATH_TEAMS_AVATARS.$teamData['avatar'];
								} else {
									$avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
								}
								?>
							<section class="teamLinks flex">
								<figure class="logo-lg">
									<img src="<?php echo($avatar); ?>" alt="<?php echo($teamName); ?>" title="<?php echo($teamName); ?>" />
								</figure>
								<div class="flexContent">
									<?php echo(anchor('/team/info/'.$teamId,$teamData['teamname']." ".$teamData['teamnick'],['class' => 'teamLink'])); ?>
									<div class="TeamLinks-Links">
										<?php 
										if(isset($teamData['owner_id']) && $teamData['owner_id'] != -1) {
											echo(anchor('/user/profiles/'.$teamData['owner_id'],$teamData['owner_name'],['class' => 'teamLink-link'])); 
										} else {
											echo($teamData['owner_name']);
										} 
										?>
										<?php if ($hasAccess && isset($teamData['owner_email']) && !empty($teamData['owner_email'])) { ?>
											<?php echo(", Contact: ".$teamData['owner_email']); ?>
										<?php } ?>
									</div>
								</div>
							</section>
						<?php
							} // END foreach($divisionData['teams']
						} else {
							echo("No Teams were Found.");
						} 
						?>
						</section>
					</div>
					<?php
					} // END foreach($thisItem['divisions']
				} // END if (isset($thisItem['divisions']) 
			}  // END if ($scoring_type == LEAGUE_SCORING_HEADTOHEAD) {
			
			/*-------------------------------------------------
			/
			// STANDARD SCORING LEAGUES
			/
			/------------------------------------------------*/
			if ($scoring_type != LEAGUE_SCORING_HEADTOHEAD) {
				if (isset($thisItem['teams']) && sizeof($thisItem['teams']) > 0) { 
			?>
			<div class="layout-column">
				<section class="content">
				<?php
					foreach($thisItem['teams'] as $teamId=>$teamData) {
						$teamName = $teamData['teamname']." ".$teamData['teamnick'];
						if (isset($teamData['avatar']) && !empty($teamData['avatar'])) { 
							$avatar = PATH_TEAMS_AVATARS.$teamData['avatar'];
						} else {
							$avatar = PATH_TEAMS_AVATARS.DEFAULT_AVATAR;
						}
						?>
						<section class="teamLinks flex">
							<figure class="logo-lg">
								<img src="<?php echo($avatar); ?>" alt="<?php echo($teamName); ?>" title="<?php echo($teamName); ?>" />
							</figure>
							<div class="flexContent">
								<?php echo(anchor('/team/info/'.$teamId,$teamData['teamname']." ".$teamData['teamnick'],['class' => 'teamLink'])); ?>
								<div class="TeamLinks-Links">
									<?php 
									if(isset($teamData['owner_id']) && $teamData['owner_id'] != -1) {
										echo(anchor('/user/profiles/'.$teamData['owner_id'],$teamData['owner_name'],['class' => 'teamLink-link'])); 
									} else {
										echo($teamData['owner_name']);
									} 
									?>
									<?php if ($hasAccess && isset($teamData['owner_email']) && !empty($teamData['owner_email'])) { ?>
									<?php echo(", Contact: ".$teamData['owner_email']); ?>
									<?php } ?>
								</div>
							</div>
						</section>
				<?php
					} // END foreach($divisionData['teams']
				?>
				</section>
			</div>
			<?php
				} else {
					echo("No Teams were Found.");
				} // END if (isset($thisItem['teams'])
			} // END if ($scoring_type != LEAGUE_SCORING_HEADTOHEAD)
			?>
		</div>
	</div>
</div>
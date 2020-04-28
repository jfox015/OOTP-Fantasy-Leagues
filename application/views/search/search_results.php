    <?php if ($accessAllowed) { ?>
    <script type="text/javascript">
	var order = "<?php echo($sortOrder); ?>", formDirty = false;
	$(document).ready( function() {
		$('#itemsPerPage').change(function() {
			$('#input_itemsPerPage').val($('#itemsPerPage').val());
			if ((($('#pageNumber').val()-1) * $('#input_itemsPerPage').val()) > $('#resultCount').val()) {
				$('#pageNumber').val('1');
			}
			$('#filterForm').submit();
		});
		$('#sortBy').change(function() {
			$('#input_sortBy').val($('#sortBy').val());
			sendForm();
		});
		$('#sortOrderIcon').click(function() {
			if (order.toLowerCase() == 'asc')
				order = 'desc';
			else
				order = 'asc'; // END if
			$('#input_sortOrder').val(order);
			sendForm();
		});
		$('a[rel=pageSelect]').click(function() {
			$('#pageNumber').val(this.id);
			sendForm();
			return false;
		});
		$('#formBtnSearch').click(function(){
			sendForm();
		});
		$('#formBtnClear').click(function(){
			$('#filterAction').val('clear');
			$('#filterForm').submit();
		});
	});
	function sendForm() {
		$('#filterAction').val('search');
		$('#filterForm').submit();
	}
	</script>
	<?php } ?>
    <div id="left-column">
   	<?php if ($accessAllowed) { ?>
   	<form name="filterForm" id="filterForm" method="post" action="<?php echo($config['fantasy_web_root']); ?>search/doSearch/<?php echo($searchType); ?>">
        <div id="toolbox">
       	<?php
			$searchOK = false;
			if (isset($filters) && sizeof($filters) > 0) { ?>
			<div id="title">Filter Results</div>
				<?php foreach($filters as $filter) :
                ?>
                <div id="row" class="clearfix">
                <label><?php echo $filter->label ?>:</label>
                <select name="<?php echo $filter->id ?>" id="<?php echo $filter->id ?>">
                    <?php
                    if (sizeof($filter->getList()) > 0) {
                        $selected = $filter->selectedIndex;
                        $theList =$filter->getList();
                        foreach($theList as $key => $value) {
                            echo('<option value="'.$key.'"');
                            if ($selected == $key)
                                echo(' selected="selected"'); // END if
                            echo('>'.$value.'</option>');
                        } // END foreach
                    } // END if
                    ?>
                </select>
                <br class="clear" />
                </div>
                <?php endforeach;
				$searchOK = true;
			} // END if $filters
			/*-----------------------------------
			/	TEXT SEARCH BOX
			/----------------------------------*/
			if ($textSearch) {
			?>
            <div id="row" class="clearfix">
            <label for="searchTerm">Text Search</label>
            <input type="text" name="searchTerm" id="searchTerm" value="<?php echo($searchTerm); ?>" />
            <br class="clear" />
            </div>
            <?php
				$searchOK = true;
			} // END if textSearch
			/*-----------------------------------
			/	Alpha SEARCH Select
			/----------------------------------*/

			if ($alphaSearch) { ?>
            <div id="row" class="clearfix">
            <label for="searchAlpha">Starts With</label>
            <select name="searchAlpha" id="searchAlpha">
            <option value="">Select Letter</option>
			<?php
			for ($i = 65; $i < 91; $i++) {
				echo('<option value="'.chr($i).'"');
				if ($searchAlpha == chr($i)) { echo(' selected="selected"'); }
				echo('>'.chr($i).'</option>');
			} ?>
            </select>
            <br class="clear" />
            </div>
            <?php
				$searchOK = true;
			} // END if alphaSearch

			if ($searchOK) {
			?>
            <div id="row" class="clearfix">
                    <!-- BUTTON BAR -->
                <div class="button_bar align-right">
                <input type="button" id="formBtnClear" value="Clear" class="button" />
                <input type="button" id="formBtnSearch" value="Search &gt;" class="button" />
                <br class="clear" />
                </div>
            <br class="clear" />
            </div>
            <?php } else { ?>
			<div id="row" class="clearfix">
            There are no options available for this search at this time.
            </div>
			<?php } ?>
       	<br class="clear" />
        <?php if (!empty($itemsPerPage)) { echo('<input type="hidden" name="itemsPerPage" id="input_itemsPerPage" value="'.$itemsPerPage.'" />'); } ?>
		<?php //if (!empty($pageNumber)) { echo('<input type="hidden" name="pageNumber" id="pageNumber" value="'.$pageNumber.'" />'); } ?>
        <input type="hidden" id="pageNumber" name="pageNumber" value="1" />
        <input type="hidden" id="input_sortBy" name="sortBy" value="<?php echo $sortBy; ?>" />
        <input type="hidden" id="input_sortOrder" name="sortOrder" value="<?php echo $sortOrder; ?>" />
        <input type="hidden" id="resultCount" value="<?php echo $resultCount; ?>" />
        <input type="hidden" id="filterAction" name="filterAction" value="" />
        <input type="hidden" id="filter" name="filter" value="1" />
        <?php if (isset($debug)) { echo('<input type="hidden" name="debug" id="debug" value="1" />'); } ?>
        </div>
        </form>
        <?php } // END if accessAllowed
        ?>
        <br /><br />
    </div>
    <div id="center-column">
       <div class="top-bar"><h1><?php echo($subTitle); ?></h1></div>
        <?php if ($accessAllowed) { ?>
        <!--div class="search_summary"></div-->
        <?php if (!empty($filterStr)) { ?>
                <div style="padding:5px;float:left;display:inline;"><?php echo($filterStr); ?></div>
            <?php } ?>
        <br class="clear" />
        <div class="headbar">

			<?php if (isset($resultsHeader) && !empty($resultsHeader)) { ?>
            <div class="headitem headline"><?php echo($resultsHeader); ?></div>
            <?php } ?>
            <div class="headitem headline" style="float:left;">
            <strong><span
        class="search_result_count"><?php echo($resultCount); ?></span> <?php echo($searchType); ?> found</strong>.
            </div>
            <div class="headitem sortBox">
            <label for="sortBy">Sort:</label>
            <select id="sortBy" name="sortBy">
           	<?php foreach ($sortFields as $value => $label) : ?>
                <option value="<?php echo $value; ?>"<?php
				if ($sortBy == $value) { echo(' selected="selected"'); } ?>><?php echo $label; ?></option>
                <?php endforeach; ?>
            </select>
            <div id="sortOrderIcon" alt="Change Sort Direction" title="Change Sort Direction" class="<?php echo(($sortOrder == 'asc') ? 'up' : 'down'); ?>"></div>
            </div>
        </div>
    </div>
    <div id="single-column">
       	<!-- BEGIN RESULTS OUTPUT -->
        <div class="results_browse">
		<?php echo($search); ?>
        <p><br />&nbsp;<br />
        </div>
        <div class="searchFooterBar">
       	<div class="footerItem">
            <span class="results_meta"><?php if (isset($startIndex)) { ?>Showing results <b><?php echo($startIndex); ?></b> to <?php
			if ($resultCount > $itemsPerPage) {
				$endIndex = ($startIndex + $itemsPerPage) -1;
				if ($endIndex > $resultCount) { $endIndex = $resultCount; }
			} else {
				$endIndex = $resultCount;
			} // END if
			echo('<b>'.$endIndex.'</b>');
			} ?></span></div>
        </div>
       	<!-- SEARCH FOOTER -->
        <div class="searchFooterBar">
            <div class="footerItem pageCount">
            <span class="results_meta">Page <?php echo $pageNumber; ?> of <?php echo $totalPages; ?></span>
            </div>
            <div class="footerItem results_paging">
                <div id="dt">
				<?php if($resultCount>0): ?>
                    <?php if ($totalPages > 1  && $pageNumber > 1) { ?>
                    <a class="prev" href="#" rel="pageSelect" id="<?php echo($pageNumber - 1); ?>" title="Previous results page"><?php } else { ?>
                    <span class="livesearch_prev"><?php } ?>Previous<?php if ($pageNumber > 1) { ?></a><?php } else { ?></span><?php } ?>
                    <ul class="pagelist">
                    <?php
					$maxPageLinks = 12;
					$limitPageLinks = $totalPages > $maxPageLinks;
					$elipsesDrawn = false;
					for($i=1; $i<=$totalPages; $i++) {
                   	$draw = true;
						if ($limitPageLinks && (($i > ($maxPageLinks / 2)) || ($i < ($totalPages - ($maxPageLinks /2))))) {
							$draw	= false;
						}
						if($draw) {
							if ($i==$pageNumber):
                    ?>
                        <li><span class="active"><?php printf($i); ?></span></li>
                    <?php	else: ?>
                        <li><a href="#" rel="pageSelect" id="<?php printf($i); ?>"><?php printf($i); ?></a></li>
                    <?php endif;
						} else {
							if ($limitPageLinks && !$elipsesDrawn) {
								echo(" ... ");
								$elipsesDrawn = true;
							}
						}
                    }
                    ?>
                    </ul>
                    <?php if ($totalPages > 1 && $pageNumber < $totalPages) { ?>
                    <a class="next" href="#" rel="pageSelect" title="Next results page" id="<?php echo(($pageNumber + 1)); ?>"><?php } else { ?>
                    <span class="livesearch_next"><?php } ?>Next<?php if ($totalPages > 1) { ?></a><?php } else { ?></span><?php } ?>
                    <div class="clearfix">&nbsp;</div>
                <?php endif; ?>
                </div>

            </div>
            <div class="footerItem resultCounter">
                <!-- Items per page Drop Down -->
                Show: <select name="itemsPerPage" id="itemsPerPage">
                <?php
                $itemCount = array(5,25,50,75,100);
                for ($i =0; $i < sizeof($itemCount); $i++) {
                    echo('<option value="'.$itemCount[$i].'"');
                    if ($itemsPerPage == $itemCount[$i]) {
                        echo(' selected="selected"');
                    } // END if
                    echo('>'.$itemCount[$i].'</option>');
                } // END for
                ?>
                </select> entries per page
            </div>
        </div>
        <?php } else { ?>
        <span class="error">Access error - you are not authorized to view this page.</span>
        <p><br />
        The search you are trying to perform requires higher access privlidges than you have
        been assigned.
        <p>
        If you feel you have reached this page in error, please <a href="<?php print(BUG_URL); ?>">submit a bug report</a>.

        <?php } // END if accessAllowed
        ?>

    </div>
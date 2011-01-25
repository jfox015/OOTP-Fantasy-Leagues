    <div id="col_l">
    <?php include_once('nav_search.php'); ?>
    </div>
    <div id="col_r">
        <div class="textual_content">
            <h1>Search</h1>
            <br /><br />
            <div class="content-form">
                <div id="dialog">
                    <div class="dialog_head">
                    Enter the information below to begin your search.
                    </div>
                    <div class="dialog_body">
                    <form id="searchForm" name="searchForm" method="post" action="/search/doSearch" autocomplete="no">
                        <label for="searchTerm">Search Terms:</label> 
                        <input type="text" name="searchTerm" id="searchTerm" />
                        <br /><br />
                        <label for="id">Type</label> 
                        <fieldset class="radioGroup">
                            <input type="radio" name="id" id="id" value="comics" /> <label for="id">Comic</label>&nbsp;&nbsp;            
                            <input type="radio" name="id" id="id" value="issues" /> <label for="id">Issue</label>&nbsp;&nbsp; 
                            <input type="radio" name="id" id="id" value="creators" /> <label for="id">Creator</label>&nbsp;&nbsp;  
                            <input type="radio" name="id" id="id" value="publishers" /> <label for="id">Publisher</label>&nbsp;&nbsp;
                        </fieldset>
                        <br /><br />
                        <fieldset class="button_bar">
                       	<input type="hidden" name="filterAction" value="search" />
                            <input type="submit" value="Search Now" />
                        </fieldset>
                    </form>
				</div>
                <div class="dialog_foot"></div>
                <br class="clear" clear="all" />
            </div>
            <br class="clear" clear="all" />
        </div>
    </div>
    <p /><br />
    <div id="center-column">
    	<div class="top-bar"> <h1><?php echo($subTitle); ?></h1></div>
        <br class="clear" clear="all" />
        <?php 
		if ( ! function_exists('form_open')) {
			$this->load>helper('form');
		}
		$errors = validation_errors();
		if ($errors) {
			echo '<div class="error">The following errors were found with your submission:<br /><ul>'.$errors.'</ul></div>';
		}
		?>
        <br />
        <b>Article:</b> <?php echo($articleTitle); ?>
        <br /><br />
        
		<?php 
		if (isset($image) && !empty($image)) { ?>
		&nbsp;&nbsp;<b>Click to Preview Current PDF</b>
		<?php 
		echo('<img src="'.$config['fantasy_web_root'].'images/icons/'.PATH_NEWS_IMAGES.$image.'" border="0" width="32" height="32" align="left" /></a><br />');
		}
		?>
		<br clear="all" /><br /><br />
      	<div class="table">
        <table class="listing form" cellpadding="0" cellspacing="0">
          <tr>
            <th class="full" colspan="2">Enter file details below.</th>
          </tr>
          <tr>
            <td class="onecell" width="100%">
            <?php
			echo(form_open_multipart("/news/pdf",array("id"=>"detailsForm","name"=>"detailsForm")));
			echo(form_fieldset());
			echo(form_label("PDF:","pdfFile"));
			echo(form_upload("pdfFile", '', '', 'allowed_types=pdf, max_size=2000000'));
			?>
            <br /><span class="field_caption">This form accepts only <b>PDF</b> files. Max file size is 2 MB</span>
            <?php
			echo(form_fieldset_close());
			echo('<br />');
			echo(form_fieldset('',array('class'=>"button_bar")));
			echo(form_hidden('id',$articleId));
			$data = array(
			'name'        => 'submit',
			'id'          => 'submit',
			'value'       => 'Submit',
			'class'       => 'button',
			);
			echo(form_submit($data));
			echo(form_hidden('submitted',"1"));
			echo(form_fieldset_close());
			echo(form_close()); ?>
            </td>
        </tr>
        </table>       
        </div>
    </div>
    <p /><br />
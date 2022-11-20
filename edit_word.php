<?php

/**************************************************************
"Learning with Texts" (LWT) is free and unencumbered software 
released into the PUBLIC DOMAIN.

Anyone is free to copy, modify, publish, use, compile, sell, or
distribute this software, either in source code form or as a
compiled binary, for any purpose, commercial or non-commercial,
and by any means.

In jurisdictions that recognize copyright laws, the author or
authors of this software dedicate any and all copyright
interest in the software to the public domain. We make this
dedication for the benefit of the public at large and to the 
detriment of our heirs and successors. We intend this 
dedication to be an overt act of relinquishment in perpetuity
of all present and future rights to this software under
copyright law.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE 
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE
AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS BE LIABLE 
FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN 
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
THE SOFTWARE.

For more information, please refer to [http://unlicense.org/].
***************************************************************/

/**************************************************************
Call: edit_word.php?....
      ... op=Save ... do insert new
      ... op=Change ... do update
      ... tid=[textid]&ord=[textpos]&wid= ... new word  
      ... tid=[textid]&ord=[textpos]&wid=[wordid] ... edit word 
New/Edit single word
***************************************************************/

require_once( 'settings.inc.php' );
require_once( 'connect.inc.php' );
require_once( 'dbutils.inc.php' );
require_once( 'utilities.inc.php' );
require_once( 'simterms.inc.php' );

$translation_raw = getreq("WoTranslation");
if ( $translation_raw == '' ) $translation = '*';
else $translation = $translation_raw;

$textId = $_REQUEST['tid'];

#region INS/UPD

if (isset($_REQUEST['op'])) {
	
	$text = mb_strtolower(trim(prepare_textdata($_REQUEST["WoText"])), 'UTF-8');
	$woTextLC = mb_strtolower($_REQUEST["WoText"], 'UTF-8');
	
	if (mb_strtolower($text, 'UTF-8') == $text) {
	
		// INSERT
		
		if ($_REQUEST['op'] == 'Save')
		{
			pagestart_nobody($titletext);
			echo '<h4><span class="bigger">' . $titletext . '</span></h4>';

			consoleLog(nl2br($translation));

			$translation = str_replace("\n",'¶',$translation);
			$translation = preg_replace('/\s{2,}/u', ' ', $translation);
			$translation = str_replace('¶ ','¶',$translation);
			$translation = str_replace('¶',"\n",$translation);
		
			$message = runsql('insert into ' . $tbpref . 'words (WoLgID, WoText, ' .
				'WoStatus, WoTranslation, WoRomanization, WoStatusChanged,' .  make_score_random_insert_update('iv') . ') values( ' . 
				$_REQUEST["WoLgID"] . ', ' .
				convert_string_to_sqlsyntax($woTextLC) . ', ' .
				$_REQUEST["WoStatus"] . ', ' .
				convert_string_to_sqlsyntax($translation) . ', ' .
				convert_string_to_sqlsyntax($_REQUEST["WoRomanization"]) . ', NOW(), ' .  
				make_score_random_insert_update('id') . ')', "Term saved");
			$wid = get_last_key();
			
			$hex = strToClassName(prepare_textdata($woTextLC));
	
			
		} // $_REQUEST['op'] == 'Save'
		
		// UPDATE
		
		else  // $_REQUEST['op'] != 'Save'
		{
			$titletext = "Edit Term: " . tohtml(prepare_textdata($_REQUEST["WoText"]));
			pagestart_nobody($titletext);
			echo '<h4><span class="bigger">' . $titletext . '</span></h4>';

			echo nl2br($translation);

			$translation = str_replace("\n",'¶',$translation);
			$translation = preg_replace('/\s{2,}/u', ' ', $translation);
			$translation = str_replace('¶ ','¶',$translation);
			$translation = str_replace('¶',"\n",$translation);
			
			$oldstatus = $_REQUEST["WoOldStatus"];
			$newstatus = $_REQUEST["WoStatus"];
			$xx = '';
			if ($oldstatus != $newstatus) $xx = ', WoStatus = ' .	$newstatus . ', WoStatusChanged = NOW()';
		
			$message = runsql('update ' . $tbpref . 'words set WoText = ' . 
			convert_string_to_sqlsyntax($woTextLC) . ', WoTranslation = ' . 
			convert_string_to_sqlsyntax($translation) . ', WoRomanization = ' .
			convert_string_to_sqlsyntax($_REQUEST["WoRomanization"]) . $xx . ',' . make_score_random_insert_update('u') . ' where WoID = ' . $_REQUEST["WoID"], "Updated");
			$wid = $_REQUEST["WoID"];
			
		}  // $_REQUEST['op'] != 'Save'
		
		saveWordTags($wid);

	} // (mb_strtolower($text, 'UTF-8') == $textlc)
	
	else // (mb_strtolower($text, 'UTF-8') != $textlc)
	{
		$titletext = "New/Edit Term: " . tohtml(prepare_textdata($woTextLC));
		pagestart_nobody($titletext);
		echo '<h4><span class="bigger">' . $titletext . '</span></h4>';		
		$message = 'Error: Term in lowercase must be exactly = "' . $woTextLC . '", please go back and correct this!'; 
		echo error_message_with_hide($message,0);
		pageend();
		exit();
	
	}
		
	?>
	
	<p>OK: <?php echo tohtml($message); ?></p>
	
	
<script type="text/javascript">
//<![CDATA[

var context = window.parent.frames['l'].document;
var contexth = window.parent.frames['h'].document;
var woid = <?php echo prepare_textdata_js($wid); ?>;
var status = <?php echo prepare_textdata_js($_REQUEST["WoStatus"]); ?>;
var trans = <?php echo prepare_textdata_js($translation . getWordTagList($wid,' ',1,0)); ?>;
var roman = <?php echo prepare_textdata_js($_REQUEST["WoRomanization"]); ?>;
var title = make_tooltip(<?php echo prepare_textdata_js($_REQUEST["WoText"]); ?>,trans,roman,status);
<?php
if ($_REQUEST['op'] == 'Save') {
?>
	$('.TERM<?php echo $hex; ?>', context)
		.removeClass('status0')
		.addClass('word' + woid + ' ' + 'status' + status)
		.attr('data_trans',trans)
		.attr('data_rom',roman)
		.attr('data_status',status)
		.attr('data_wid',woid)
		.attr('title',title);
<?php
} else {
?>
	$('.word' + woid, context)
		.removeClass('status<?php echo $_REQUEST['WoOldStatus']; ?>')
		.addClass('status' + status)
		.attr('data_trans',trans)
		.attr('data_rom',roman)
		.attr('data_status',status)
		.attr('title',title);
<?php
}
?>
$('#learnstatus', contexth).html('<?php echo texttodocount2($textId); ?>');
window.parent.frames['l'].focus();
window.parent.frames['l'].setTimeout('cClick()', 100);

//]]>
</script>
	
<?php

}
#endregion

#region FORM

else {  // if (! isset($_REQUEST['op']))

	// edit_word.php?tid=..&ord=..&wid=..
	$lang = $_REQUEST['lang'];
	$term = $_REQUEST['term'];
	$wid = $_REQUEST['wid'];
	
	$new = ($wid == "");

	$titletext = ($new ? "New Term" : "Edit Term") . ": " . tohtml($term);
	pagestart_nobody($titletext);
?>
<script type="text/javascript" src="js/unloadformcheck.js" charset="utf-8"></script>
<?php
	$scrdir = getScriptDirectionTag($lang);
	#region NEW
	
	if ($new) {

?>
	
		<form name="newword" class="validate" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
		<input type="hidden" name="WoLgID" id="langfield" value="<?php echo $lang; ?>" />
		<input type="hidden" name="WoText" value="<?php echo tohtml($term); ?>" />
		<input type="hidden" name="tid" value="<?php echo getreq('tid'); ?>" />
		<table class="tab2" cellspacing="0" cellpadding="5">
		<tr title="Only change uppercase/lowercase!">
		<td class="td1 right"><b>New Term:</b></td>
		<td class="td1"><input <?php echo $scrdir; ?> class="notempty checkoutsidebmp" data_info="New Term" type="text" name="WoText" id="wordfield" value="<?php echo tohtml($term); ?>" maxlength="250" size="50" /> <img src="icn/status-busy.png" title="Field must not be empty" alt="Field must not be empty" />
		</td></tr>
		<?php print_similar_terms_tabrow(); ?>
		<tr>
		<td class="td1 right">Translation:</td>
		<td class="td1"><textarea name="WoTranslation" class="setfocus textarea checklength checkoutsidebmp" style="white-space: pre-wrap;" data_maxlength="500" data_info="Translation" cols="50" rows="10"></textarea></td>
		</tr>
		<tr>
		<td class="td1 right">Tags:</td>
		<td class="td1">
		<?php echo getWordTags(0); ?>
		</td>
		</tr>
		<tr>
		<td class="td1 right">Reading:</td>
		<td class="td1"><input type="text" class="checkoutsidebmp" data_info="Romanization" name="WoRomanization" value="" maxlength="100" size="50" /></td>
		</tr>
		<tr>
		<td class="td1 right">Status:</td>
		<td class="td1">
		<?php echo get_wordstatus_radiooptions(1); ?>
		</td>
		</tr>
		<tr>
		<td class="td1 right" colspan="2">
		<?php echo createDictLinksInEditWin($lang,$term,1); ?>
		&nbsp; &nbsp; &nbsp; 
		<input type="submit" name="op" value="Save" /></td>
		</tr>
		</table>
		</form>
		<?php
		
	}
	
	#endregion
	
	#region CHG
	
	else {
		
		$sql = 'select WoTranslation, WoRomanization, WoStatus from ' . $tbpref . 'words where WoID = ' . $wid;
		$res = do_mysqli_query($sql);
		if ($record = mysqli_fetch_assoc($res)) {
			
			$status = $record['WoStatus'];
			
			$transl = $record['WoTranslation'];
			if($transl == '*') $transl='';
			?>
		
			<form name="editword" class="validate" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
			<input type="hidden" name="WoLgID" id="langfield" value="<?php echo $lang; ?>" />
			<input type="hidden" name="WoID" value="<?php echo $wid; ?>" />
			<input type="hidden" name="WoOldStatus" value="<?php echo $record['WoStatus']; ?>" />
			<input type="hidden" name="tid" value="<?php echo getreq('tid'); ?>" />
			<table class="tab2" cellspacing="0" cellpadding="5">
			<tr title="Only change uppercase/lowercase!">
			<td class="td1 right"><b>Edit Term:</b></td>
			<td class="td1"><input <?php echo $scrdir; ?> class="notempty checkoutsidebmp" data_info="Term" type="text" name="WoText" id="wordfield" value="<?php echo tohtml($term); ?>" maxlength="250" size="50" /> <img src="icn/status-busy.png" title="Field must not be empty" alt="Field must not be empty" />
			</td></tr>
			<?php print_similar_terms_tabrow(); ?>
			<tr>
			<td class="td1 right">Translation:</td>
			<td class="td1"><textarea name="WoTranslation" class="setfocus textarea checklength checkoutsidebmp" style="white-space: pre-wrap;" data_maxlength="500" data_info="Translation" cols="50" rows="10"><?php echo tohtml($transl); ?></textarea></td>
			</tr>
			<tr>
			<td class="td1 right">Tags:</td>
			<td class="td1">
			<?php echo getWordTags($wid); ?>
			</td>
			</tr>
			<tr>
			<td class="td1 right">Reading:</td>
			<td class="td1"><input type="text" class="checkoutsidebmp" data_info="Romanization" name="WoRomanization" maxlength="100" size="50" 
			value="<?php echo tohtml($record['WoRomanization']); ?>" /></td>
			</tr>
			<tr>
			<td class="td1 right">Status:</td>
			<td class="td1">
			<?php echo get_wordstatus_radiooptions($status); ?>
			</td>
			</tr>
			<tr>
			<td class="td1 right" colspan="2">  
			<?php echo createDictLinksInEditWin($lang,$term,1); ?>
			&nbsp; &nbsp; &nbsp; 
			<input type="submit" name="op" value="Change" /></td>
			</tr>
			</table>
			</form>
			<?php
		}
		mysqli_free_result($res);
	}
	#endregion
}
#endregion
pageend();

?>
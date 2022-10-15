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
Call: do_text_text.php?text=[textid]
Show text header frame
***************************************************************/

require_once( 'settings.inc.php' );
require_once( 'connect.inc.php' );
require_once( 'dbutils.inc.php' );
require_once( 'utilities.inc.php' );

$sql = 'select TxLgID, TxTitle, TxAnnotatedText from ' . $tbpref . 'texts where TxID = ' . $_REQUEST['text'];
$res = do_mysqli_query($sql);
$record = mysqli_fetch_assoc($res);
$title = $record['TxTitle'];
$langid = $record['TxLgID'];
$ann = $record['TxAnnotatedText'];
$ann_exists = (strlen($ann) > 0);
mysqli_free_result($res);

pagestart_nobody(tohtml($title));

$sql = 'select LgName, LgDict1URI, LgDict2URI, LgGoogleTranslateURI, LgTextSize, LgRemoveSpaces, LgRightToLeft from ' . $tbpref . 'languages where LgID = ' . $langid;
$res = do_mysqli_query($sql);
$record = mysqli_fetch_assoc($res);
$wb1 = isset($record['LgDict1URI']) ? $record['LgDict1URI'] : "";
$wb2 = isset($record['LgDict2URI']) ? $record['LgDict2URI'] : "";
$wb3 = isset($record['LgGoogleTranslateURI']) ? $record['LgGoogleTranslateURI'] : "";
$textsize = $record['LgTextSize'];
$removeSpaces = $record['LgRemoveSpaces'];
$rtlScript = $record['LgRightToLeft'];
mysqli_free_result($res);

$showAll = getSettingZeroOrOne('showallwords',1);

?>
<script type="text/javascript">
//<![CDATA[
ANN_ARRAY = <?php echo annotation_to_json($ann); ?>;
TEXTPOS = -1;
OPENED = 0;
WBLINK1 = '<?php echo $wb1; ?>';
WBLINK2 = '<?php echo $wb2; ?>';
WBLINK3 = '<?php echo $wb3; ?>';
RTL = <?php echo $rtlScript; ?>;
TID = '<?php echo $_REQUEST['text']; ?>';
ADDFILTER = '<?php echo makeStatusClassFilter(getSettingWithDefault('set-text-visit-statuses-via-key')); ?>';
$(document).ready( function() {
	$('.word').each(word_each_do_text_text);
	$('.mword').each(mword_each_do_text_text);
	$('.word').click(word_click_event_do_text_text);
	$('.mword').click(mword_click_event_do_text_text);
	$('.word').dblclick(word_dblclick_event_do_text_text);
	$('.mword').dblclick(word_dblclick_event_do_text_text);
	$(document).keydown(keydown_event_do_text_text);
});
//]]>
</script>
<?php

echo '<div id="thetext" ' .  ($rtlScript ? 'dir="rtl"' : '') . '><p style="' . ($removeSpaces ? 'word-break:break-all;' : '') . 
'font-size:' . $textsize . '%;line-height: 1.4; margin-bottom: 10px;">';

$currcharcount = 0;
/*
#region OLD VERSION
$sql = 'select TiWordCount as Code, TiText, TiTextLC, TiOrder, TiIsNotWord, CHAR_LENGTH(TiText) AS TiTextLength, WoID, WoText, WoTextLC, WoStatus, WoTranslation, WoRomanization from (' . $tbpref . 'textitems left join ' . $tbpref . 'words on (TiTextLC = WoTextLC) and (TiLgID = WoLgID)) where TiTxID = ' . $_REQUEST['text'] . ' order by TiOrder asc, TiWordCount desc';

$titext = array('','','','','','','','','','','');
$hideuntil = -1;
$hidetag = '';

$res = do_mysqli_query($sql);

while ($record = mysqli_fetch_assoc($res)) {  // MAIN LOOP

	$actcode = $record['Code'] + 0;
	$spanid = 'ID-' . $record['TiOrder'] . '-' . $actcode;

	if ( $hideuntil > 0  ) {
		if ( $record['TiOrder'] <= $hideuntil )
			$hidetag = ' hide';
		else {
			$hideuntil = -1;
			$hidetag = '';
		}
	}				
	
	if ($record['TiIsNotWord'] != 0) {  // NOT A TERM
	
		echo '<span id="' . $spanid . '" class="' . 
			$hidetag . '">' . 
			str_replace(
			"¶",
			'<br />',
			tohtml($record['TiText'])) . '</span>';
			
	}  // $record['TiIsNotWord'] != 0  --  NOT A TERM
	
	/////////////////////////////////////////////////
	
	else {   // $record['TiIsNotWord'] == 0  -- A TERM
	
		if ($actcode > 1) {   // A MULTIWORD FOUND
		
			$titext[$actcode] = $record['TiText'];
			
			if (isset($record['WoID'])) {  // MULTIWORD FOUND - DISPLAY (Status 1-5, display)
			
				if (! $showAll) {
					if ($hideuntil == -1) {
						$hideuntil = $record['TiOrder'] + ($record['Code'] - 1) * 2;
					}
				}
								
?><span id="<?php echo $spanid; ?>" class="<?php echo $hidetag; ?> click mword <?php echo ($showAll ? 'mwsty' : 'wsty'); ?> <?php echo 'order'. $record['TiOrder']; ?> <?php echo 'word'. $record['WoID']; ?> <?php echo 'status'. $record['WoStatus']; ?> TERM<?php echo strToClassName($record['TiTextLC']); ?>" data_pos="<?php echo $currcharcount; ?>" data_order="<?php echo $record['TiOrder']; ?>" data_wid="<?php echo $record['WoID']; ?>" data_trans="<?php echo tohtml(repl_tab_nl($record['WoTranslation']) . getWordTagList($record['WoID'],' ',1,0)); ?>" data_rom="<?php echo tohtml($record['WoRomanization']); ?>" data_status="<?php echo $record['WoStatus']; ?>"  data_code="<?php echo $record['Code']; ?>" data_text="<?php echo tohtml($record['TiText']); ?>"><?php echo ($showAll ? ('&nbsp;' . $record['Code'] . '&nbsp;') : tohtml($record['TiText'])); ?></span><?php	

			}
			
			////////////////////////////////////////////////
			
			else {  // MULTIWORD PLACEHOLDER - NO DISPLAY 
			
?><span id="<?php echo $spanid; ?>" class="click mword <?php echo ($showAll ? 'mwsty' : 'wsty'); ?> hide <?php echo 'order'. $record['TiOrder']; ?> TERM<?php echo strToClassName($record['TiTextLC']); ?>" data_pos="<?php echo $currcharcount; ?>" data_order="<?php echo $record['TiOrder']; ?>" data_wid="" data_trans="" data_rom="" data_status="" data_code="<?php echo $record['Code']; ?>" data_text="<?php echo tohtml($record['TiText']); ?>"><?php echo ($showAll ? ('&nbsp;' . $record['Code'] . '&nbsp;') : tohtml($record['TiText'])); ?></span><?php	

			}   // MULTIWORD PLACEHOLDER - NO DISPLAY 
			
		} // ($actcode > 1) -- A MULTIWORD FOUND

		////////////////////////////////////////////////
		
		else {  // ($actcode == 1)  -- A WORD FOUND
		
			if (isset($record['WoID'])) {  // WORD FOUND STATUS 1-5,98,99

?><span id="<?php echo $spanid; ?>" class="<?php echo $hidetag; ?> click word wsty <?php echo 'word'. $record['WoID']; ?> <?php echo 'status'. $record['WoStatus']; ?> TERM<?php echo strToClassName($record['TiTextLC']); ?>" data_pos="<?php echo $currcharcount; ?>" data_order="<?php echo $record['TiOrder']; ?>" data_wid="<?php echo $record['WoID']; ?>" data_trans="<?php echo tohtml(repl_tab_nl($record['WoTranslation']) . getWordTagList($record['WoID'],' ',1,0)); ?>" data_rom="<?php echo tohtml($record['WoRomanization']); ?>" data_status="<?php echo $record['WoStatus']; ?>" data_mw2="<?php echo tohtml($titext[2]); ?>" data_mw3="<?php echo tohtml($titext[3]); ?>" data_mw4="<?php echo tohtml($titext[4]); ?>" data_mw5="<?php echo tohtml($titext[5]); ?>" data_mw6="<?php echo tohtml($titext[6]); ?>" data_mw7="<?php echo tohtml($titext[7]); ?>" data_mw8="<?php echo tohtml($titext[8]); ?>" data_mw9="<?php echo tohtml($titext[9]); ?>"><?php echo tohtml($record['TiText']); ?></span><?php	

			}   // WORD FOUND STATUS 1-5,98,99
			
			////////////////////////////////////////////////
			
			else {    // NOT A WORD AND NOT A MULTIWORD FOUND - STATUS 0
			
?><span id="<?php echo $spanid; ?>" class="<?php echo $hidetag; ?> click word wsty status0 TERM<?php echo strToClassName($record['TiTextLC']); ?>" data_pos="<?php echo $currcharcount; ?>" data_order="<?php echo $record['TiOrder']; ?>" data_trans="" data_rom="" data_status="0" data_wid="" data_mw2="<?php echo tohtml($titext[2]); ?>" data_mw3="<?php echo tohtml($titext[3]); ?>" data_mw4="<?php echo tohtml($titext[4]); ?>" data_mw5="<?php echo tohtml($titext[5]); ?>" data_mw6="<?php echo tohtml($titext[6]); ?>" data_mw7="<?php echo tohtml($titext[7]); ?>" data_mw8="<?php echo tohtml($titext[8]); ?>" data_mw9="<?php echo tohtml($titext[9]); ?>"><?php echo tohtml($record['TiText']); ?></span><?php	

			}  // NOT A WORD AND NOT A MULTIWORD FOUND - STATUS 0
			
			$titext = array('','','','','','','','','','','');
			
		}  // ($actcode == 1)  -- A WORD FOUND
		
	} // $record['TiIsNotWord'] == 0  -- A TERM
	
	if ($actcode == 1) $currcharcount += $record['TiTextLength']; 
	
} // while ($record = mysqli_fetch_assoc($res))  -- MAIN LOOP

mysqli_free_result($res);
#endregion
*/

#region NEW VERSION
function get_word_index($word, $wordsInDB)
{
	for($i = 0; $i < count($wordsInDB); ++$i)
	{
		if(strtolower($word) == strtolower($wordsInDB[$i]["WoText"]))
		{
			return $i;
		}
	}
	return -1;
}

function get_word_data($word, $wordsInDB)
{
	$index = get_word_index($word, $wordsInDB);
	if($index < 0)
	{
		return null;
	}
	else
	{
		return $wordsInDB[$index];
	}
}

function is_word($item)
{
	return (strpbrk($item, "., \n") === FALSE);
}

//Get text
$sqlGetText = 'select * from texts where TxID = ' . $_REQUEST['text'];
$resGetText = do_mysqli_query($sqlGetText);
$recordGetText = mysqli_fetch_assoc($resGetText);
mysqli_free_result($resGetText);

//Get array of items (words + special characters) from text
$lines = preg_split('#(\R)#', $recordGetText['TxText'], -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
$items = [];
foreach($lines as $line)
{
	$itemsInLine = preg_split('/([ ,.\s])/', $line, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
	foreach($itemsInLine as $item)
	{
		array_push($items, $item);
	}
}

//Get array of words seen in this language from database
$sqlGetWordsInDB = 'select * from words where WoLgId = ' . $langid;
$resGetWordsInDB = do_mysqli_query($sqlGetWordsInDB);
$wordsInDB = [];
while($wordInDB = mysqli_fetch_assoc($resGetWordsInDB))
{
	array_push($wordsInDB, $wordInDB);
}
mysqli_free_result($resGetWordsInDB);

//Main loop
$showNextSpace = true;
foreach($items as $item)
{
	if($item === " ") //item is space
	{
		if($showNextSpace)
		{
			echo ' ';
			$showNextSpace = false;
		}
	}
	else if(is_word($item)) //item is a word
	{
		$showNextSpace = false;
		$wordData = get_word_data($item, $wordsInDB);
		
		if ($wordData) //seen word
		{  
			echo '<span class="click word wsty ' . 'word' . $wordData['WoID'] . ' ' . 'status'. $wordData['WoStatus'] . ' ' . 'TERM' . strToClassName($wordData['WoText']) .
			'" data_wid="' . $wordData['WoID'] . '" data_trans="' . tohtml(repl_tab_nl($wordData['WoTranslation']) . getWordTagList($wordData['WoID'],' ',1,0)) .
			'" data_rom="' . tohtml($wordData['WoRomanization']) . '" data_status="' . $wordData['WoStatus'] .
			'" data_term="' . $wordData['WoText'] . '">' . tohtml($item) . '</span>';
		}   
		else //new word
		{    		
			echo '<span class="click word wsty status0 TERM' . strToClassName($item) .
			'" data_trans="" data_rom="" data_status="0" data_wid="" data_term="' . $item . '">' . tohtml($item) . '</span>';	
		}
	}
	else //item is a special character
	{
		echo '<span>' . 
			str_replace(
			"\n",
			'<br />',
			tohtml($item)) . '</span>';
		$showNextSpace = true;
	}

	$showNextSpace = true;
}
#endregion

echo '<span id="totalcharcount" class="hide">' . $currcharcount . '</span></p><p style="font-size:' . $textsize . '%;line-height: 1.4; margin-bottom: 300px;">&nbsp;</p></div>';

pageend();

?>
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

$sql = 'select TxLgID, TxTitle from ' . $tbpref . 'texts where TxID = ' . $_REQUEST['text'];
$res = do_mysqli_query($sql);
$record = mysqli_fetch_assoc($res);
$title = $record['TxTitle'];
$langid = $record['TxLgID'];
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

#region NEW VERSION
function get_word_data($word, $wordsInDB)
{
	$index = array_search(mb_strtolower($word, 'UTF-8'), array_column($wordsInDB, "WoText"));
	
	if($index)
	{
		return $wordsInDB[$index];
	}
	else
	{
		return null;
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
$sqlGetWordsInDB = 'select WoID, LOWER(WoText) as WoText, WoStatus, WoTranslation, WoRomanization from words where WoLgId = ' . $langid;
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
			'" data_term="' . $wordData['WoText'] . '" data_language="' . $langid . '">' . tohtml($item) . '</span>';
		}   
		else //new word
		{    		
			echo '<span class="click word wsty status0 TERM' . strToClassName(mb_strtolower($item, 'UTF-8')) .
			'" data_trans="" data_rom="" data_status="0" data_wid="" data_term="' . mb_strtolower($item, 'UTF-8') .
			'" data_language="' . $langid . '">' . tohtml($item) . '</span>';
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
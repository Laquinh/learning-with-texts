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

echo '<div id="thetext" class="justify" ' .  ($rtlScript ? 'dir="rtl"' : '') . '><p style="' . ($removeSpaces ? 'word-break:break-all;' : '') . 
'font-size:' . $textsize . '%;line-height: 1.6; margin-bottom: 10px; font-family:\'Meiryo\'">';

$currcharcount = 0;

$items = textItemList($_REQUEST["text"]);
$wordsInDB = databaseWordList($langid);

//Main loop
$showNextSpace = false;
$whitespaceHTML = ($showNextSpace ? " " : '<span class="whitespace" style="font-size:0"> </span>');

for($i = 0, $wordIndex = 0; $i < count($items); ++$i)
{
	$item = $items[$i];

	if($item === " ") //item is space
	{
		echo $whitespaceHTML;
	}
	else if(is_word($item)) //item is a word
	{
		++$wordIndex;
		$multiwordData = get_longest_multiword($i, $i, $items, $wordsInDB);
		if($multiwordData)
		{
			consoleLog($i . ": " . $multiwordData['WoText'], "warn");
			echo '&nbsp<span class="click mword mwsty word' . $multiwordData['WoID'] . ' status'. $multiwordData['WoStatus'] . ' TERM' . strToClassName($multiwordData['WoText']) . '" data_wid="' . $multiwordData['WoID'] . '" data_trans="' . tohtml($multiwordData['WoTranslation'] . getWordTagList($multiwordData['WoID'],' ',1,0)) .
			'" data_rom="' . tohtml($multiwordData['WoRomanization']) . '" data_status="' . $multiwordData['WoStatus'] .
			'" data_term="' . $multiwordData['WoText'] . '" data_language="' . $langid . '" data_wordcount="'. str_word_count($multiwordData['WoText']) .'">&nbsp' . str_word_count($multiwordData['WoText']) . '&nbsp</span>&nbsp';
		}

		$showNextSpace = false;
		$wordData = get_word_data($item, $wordsInDB);
		
		if ($wordData) //seen word
		{
			echo '<span class="click word wsty ' . 'word' . $wordData['WoID'] . ' ' . 'status'. $wordData['WoStatus'] . ' ' . 'TERM' . strToClassName($wordData['WoText']) .
			'" data_wid="' . $wordData['WoID'] . '" data_trans="' . tohtml($wordData['WoTranslation'] . getWordTagList($wordData['WoID'],' ',1,0)) .
			'" data_rom="' . tohtml($wordData['WoRomanization']) . '" data_status="' . $wordData['WoStatus'] .
			'" data_term="' . $wordData['WoText'] . '" data_language="' . $langid . '" data_index="' . ($wordIndex-1) . '">' . tohtml($item) . '</span>';
		}   
		else //new word
		{    		
			echo '<span class="click word wsty status0 TERM' . strToClassName(mb_strtolower($item, 'UTF-8')) .
			'" data_trans="" data_rom="" data_status="0" data_wid="" data_term="' . mb_strtolower($item, 'UTF-8') .
			'" data_language="' . $langid . '" data_index="' . ($wordIndex-1) . '">' . tohtml($item) . '</span>';
		}
	}
	else //item is a special character
	{
		echo '<span>' . 
			str_replace(
			"\n",
			'<br />',
			tohtml($item)) . '</span>';
	}
}

echo '<span id="totalcharcount" class="hide">' . $currcharcount . '</span></p><p style="font-size:' . $textsize . '%;line-height: 1.4; margin-bottom: 300px;">&nbsp;</p></div>';

pageend();

?>
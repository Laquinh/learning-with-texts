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
Call: all_words_wellknown.php?text=[textid]&limit=[index of last word]
Setting all unknown words to Well Known (99)
***************************************************************/

require_once( 'settings.inc.php' );
require_once( 'connect.inc.php' );
require_once( 'dbutils.inc.php' );
require_once( 'utilities.inc.php' );

$langid = get_first_value("select TxLgID as value from " . $tbpref . "texts where TxID = " . $_REQUEST['text']);

pagestart("Setting all blue words to Well-known",false);

$wordsInDB = databaseWordList($langid);
$words = textWordList($_REQUEST['text'], true);
#$words = array_unique($wordList);
$count = 0;
$javascript = "var title='';";
$changedWords = [];

$limit = 0;
if(isset($_REQUEST['limit']))
{
	$limit = min(intval($_REQUEST['limit']), count($words)-1);
}
else
{
	$limit = count($words)-1;
}

$i = 0;
foreach($words as $word) {
	if($i++ > $limit)
	{
		break;
	}
	if(!$word)
	{
		continue;
	}
	$wordlc = mb_strtolower($word, 'UTF-8');
	$wordData = get_word_data($wordlc, $wordsInDB);
	
	if(!$wordData)
	{
		$count1 = 0 + runsql('insert into ' . $tbpref . 'words (WoLgID, WoText, WoStatus, WoStatusChanged,' .  make_score_random_insert_update('iv') . ') values( ' . 
		$langid . ', ' . 
		convert_string_to_sqlsyntax($wordlc) . ', 99 , NOW(), ' .  
	make_score_random_insert_update('id') . ')',''); 
		$wid = get_last_key(); 
		if ($count1 > 0 ) 
			$javascript .= "title = make_tooltip(" . prepare_textdata_js($term) . ",'*','','99');";
			$javascript .= "$('.TERM" . strToClassName($termlc) . "', context).removeClass('status0').addClass('status99 word" . $wid . "').attr('data_status','99').attr('data_wid','" . $wid . "').attr('title',title);";
		$count += $count1;
		
		array_push($changedWords, $wordlc);
		$wordTempData = [];
		$wordTempData["WoText"] = $wordlc;
		array_push($wordsInDB, $wordTempData);
	}
}

echo "<p>OK, you know all " . $count . " word(s) well!</p>";

?>
<script type="text/javascript">
//<![CDATA[
var context = window.parent.frames['l'].document;
var contexth = window.parent.frames['h'].document;
<?php echo $javascript; ?> 
$('#learnstatus', contexth).html('<?php echo texttodocount2($_REQUEST['text']); ?>');
window.parent.frames['l'].setTimeout('cClick()', 1000);
//]]>
</script>
<?php

pageend();

?>
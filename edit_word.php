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

?>
<link href='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css' rel='stylesheet'>
<link href='#' rel='stylesheet'>
<script type='text/javascript' src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
<style>
    ::-webkit-scrollbar {
        width: 8px;
    }

    /* Track */
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    /* Handle */
    ::-webkit-scrollbar-thumb {
        background: #888;
    }

    /* Handle on hover */
    ::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    body {
        font-family: 'Lato', sans-serif;
    }

    h1 {
        margin-bottom: 40px;
    }

    label {
        color: #333;
    }

    .btn-send {
        font-weight: 300;
        text-transform: uppercase;
        letter-spacing: 0.2em;
        width: 80%;
        margin-left: 3px;
    }

    .help-block.with-errors {
        color: #ff5050;
        margin-top: 5px;

    }

    .card {
        margin-left: 10px;
        margin-right: 10px;
    }

    .controls textarea,
    .controls input,
	.controls button {
        font-size: 12px;
        padding: 3px;
    }
</style>
</head>
<?php

$translation_raw = getreq("WoTranslation");
if ( $translation_raw == '' ) $translation = '*';
else $translation = $translation_raw;
$notes = getreq("WoNotes");

$textId = $_REQUEST['tid'];

#region INS/UPD

if (isset($_REQUEST['op'])) {
	
	$text = mb_strtolower(trim(prepare_textdata($_REQUEST["WoText"])), 'UTF-8');
	$woTextLC = mb_strtolower($_REQUEST["WoText"], 'UTF-8');
	
	if (mb_strtolower($text, 'UTF-8') == $text) {
        $opType = substr($_REQUEST['op'], 0, 2);
        $woStatus = substr($_REQUEST['op'], 2);

		// INSERT
		
		if ($opType == 'S-')
		{
			pagestart_nobody($titletext, '', false);
			echo '<h4><span class="bigger">' . $titletext . '</span></h4>';

			consoleLog(nl2br($translation));

			$translation = str_replace("\n",'¶',$translation);
			$translation = preg_replace('/\s{2,}/u', ' ', $translation);
			$translation = str_replace('¶ ','¶',$translation);
			$translation = str_replace('¶',"\n",$translation);

			$message = runsql('insert into ' . $tbpref . 'words (WoLgID, WoText, ' .
				'WoStatus, WoTranslation, WoRomanization, WoNotes, WoStatusChanged,' .  make_score_random_insert_update('iv') . ') values( ' . 
				$_REQUEST["WoLgID"] . ', ' .
				convert_string_to_sqlsyntax($woTextLC) . ', ' .
				$woStatus . ', ' .
				convert_string_to_sqlsyntax($translation) . ', ' .
				convert_string_to_sqlsyntax($_REQUEST["WoRomanization"]) . ', ' . convert_string_to_sqlsyntax($notes) . ', NOW(), ' .  
				make_score_random_insert_update('id') . ')', "Term saved");
			$wid = get_last_key();
			
			$hex = strToClassName(prepare_textdata($woTextLC));
	
			
		} // $_REQUEST['op'] == 'Save'
		
		// UPDATE
		
		else  // $_REQUEST['op'] != 'Save'
		{
			$titletext = "Edit Term: " . tohtml(prepare_textdata($_REQUEST["WoText"]));
			pagestart_nobody($titletext, '', false);
			echo '<h4><span class="bigger">' . $titletext . '</span></h4>';

			echo nl2br($translation);

			$translation = str_replace("\n",'¶',$translation);
			$translation = preg_replace('/\s{2,}/u', ' ', $translation);
			$translation = str_replace('¶ ','¶',$translation);
			$translation = str_replace('¶',"\n",$translation);
			
			$oldstatus = $_REQUEST["WoOldStatus"];
			$xx = '';
			if ($oldstatus != $woStatus) $xx = ', WoStatus = ' .	$woStatus . ', WoStatusChanged = NOW()';
		
			$message = runsql('update ' . $tbpref . 'words set WoText = ' . 
			convert_string_to_sqlsyntax($woTextLC) . ', WoTranslation = ' . 
			convert_string_to_sqlsyntax($translation) . ', WoRomanization = ' .
			convert_string_to_sqlsyntax($_REQUEST["WoRomanization"]) . ', WoNotes = ' . convert_string_to_sqlsyntax($notes) .
			$xx . ',' . make_score_random_insert_update('u') . ' where WoID = ' . $_REQUEST["WoID"], "Updated");
			$wid = $_REQUEST["WoID"];
			
		}  // $_REQUEST['op'] != 'Save'
		
		saveWordTags($wid);

	} // (mb_strtolower($text, 'UTF-8') == $textlc)
	
	else // (mb_strtolower($text, 'UTF-8') != $textlc)
	{
		$titletext = "New/Edit Term: " . tohtml(prepare_textdata($woTextLC));
		pagestart_nobody($titletext, '', false);
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
var status = <?php echo prepare_textdata_js($woStatus); ?>;
var trans = <?php echo prepare_textdata_js($translation . getWordTagList($wid,' ',1,0)); ?>;
var roman = <?php echo prepare_textdata_js($_REQUEST["WoRomanization"]); ?>;
var title = make_tooltip(<?php echo prepare_textdata_js($_REQUEST["WoText"]); ?>,trans,roman,status);
<?php
if ($opType == 'S-') {
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
	$numbers[1] = "1";
	$numbers[2] = "2";
	$numbers[3] = "3";
	$numbers[4] = "4";
	$numbers[5] = "5";
	$numbers[6] = "99";
	$numbers[7] = "98";

	$texts["1"] = "1";
	$texts["2"] = "2";
	$texts["3"] = "3";
	$texts["4"] = "4";
	$texts["5"] = "5";
	$texts["99"] = "WKn";
	$texts["98"] = "Ign";

	$bgColors["1"] = "#F5B8A9FF";
	$bgColors["2"] = "#F5CCA9EF";
	$bgColors["3"] = "#F5E1A9DF";
	$bgColors["4"] = "#F5F3A9BF";
	$bgColors["5"] = "#DDFFDD9F";
	$bgColors["99"] = "#FFFFFFCC";
	$bgColors["98"] = "#FFFFFF11";

	$borderColors["1"] = "#CC998DFF";
	$borderColors["2"] = "#CCAA8DEF";
	$borderColors["3"] = "#CCBC8DDF";
	$borderColors["4"] = "#CCCA8DBF";
	$borderColors["5"] = "#B8D4B89F";
	$borderColors["99"] = "#D4D4D4CC";
	$borderColors["98"] = "#D4D4D455";

	// edit_word.php?tid=..&ord=..&wid=..
	$lang = $_REQUEST['lang'];
	$term = $_REQUEST['term'];
	$wid = $_REQUEST['wid'];
	
	$new = ($wid == "");

	$titletext = ($new ? "New Term" : "Edit Term") . ": " . tohtml($term);
	pagestart_nobody($titletext, '', false);
?>
<script type="text/javascript" src="js/unloadformcheck.js" charset="utf-8"></script>
<?php
	$scrdir = getScriptDirectionTag($lang);
	#region NEW
	
	if ($new) {

?>
	<div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-2 pt-2 bg-light">
                        <div class="bg-light">
                            <div class="container" style="font-size: 10px">
                                <form id="contact-form" role="form" name="newword" class="validate" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                    <input type="hidden" name="WoLgID" id="langfield" value="<?php echo $lang; ?>" />
                                    <input type="hidden" name="WoText" value="<?php echo tohtml($term); ?>" />
                                    <input type="hidden" name="tid" value="<?php echo getreq('tid'); ?>" />
                                    <div class="controls">
                                        <div class="row">
                                            <div class="col-6">
                                                <label for="form_name">Term</label>
                                                <input <?php echo $scrdir; ?> data_info="New Term" type="text" name="WoText"
                                                    id="wordfield" class="form-control" value="<?php echo tohtml($term); ?>"
                                                    maxlength="250" placeholder="Term" required="required"
                                                    data-error="Term is required.">
                                            </div>
                                            <div class="col-6">
                                                <label for="form_name">Alt. writing</label>
                                                <input type="text" data_info="Romanization" name="WoRomanization" id="form_name"
                                                    class="form-control" value="" maxlength="100"
                                                    placeholder="Alternative writing">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="form_message">Translation</label>
                                                    <textarea id="form_message" name="WoTranslation" data_maxlength="500"
                                                        data_info="Translation"
                                                        class="form-control" placeholder="Translation" rows="6"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="form_message">Notes</label>
                                                    <textarea name="WoNotes" data_maxlength="200" data_info="Notes"
                                                        id="form_message" class="form-control" placeholder="Notes"
                                                        rows="4"></textarea>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="form-group">
                                                    <label for="form_message">Tags</label>
                                                    <?php echo getWordTags(0); ?>
                                                </div>

                                            </div>




                                        </div>
                                        <div class="row">
										<?php
										for($i = 1; $i <= 7; ++$i)
										{
											$number = $numbers[$i];
											$text = $texts[$number];
											$bgColor = $bgColors[$number];
											$borderColor = $borderColors[$number];

											echo "<div class=\"col p-2\">";
											echo "<button type=\"submit\" name=\"op\" class=\"btn pt-2 btn-block\" style=\"background-color: $bgColor; border-color: $borderColor; color: #000000FF\" value=\"S-$number\">$text</button>";
											echo "</div>";
										}
										?>
                                        </div>
                                    </div>

									<?php echo openDictInEdit($lang, $term); ?>
                                </form>
                            </div>
                        </div>


                    </div>
                    <!-- /.8 -->

                </div>
                <!-- /.row-->

            </div>
        </div>

        <script type='text/javascript' src='https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js'></script>
		<?php
		
	}
	
	#endregion
	
	#region CHG
	
	else {
		
		$sql = 'select WoTranslation, WoRomanization, WoNotes, WoStatus from ' . $tbpref . 'words where WoID = ' . $wid;
		$res = do_mysqli_query($sql);
		if ($record = mysqli_fetch_assoc($res)) {
			
			$status = $record['WoStatus'];
			
			$transl = $record['WoTranslation'];
			if($transl == '*') $transl='';
			$notes = $record['WoNotes'];
			?>
		
		<div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="card mt-2 pt-2 bg-light">
                            <div class="bg-light">
                                <div class="container" style="font-size: 10px">
                                    <form id="contact-form" role="form" name="editword" class="validate"
                                        action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
                                        <input type="hidden" name="WoLgID" id="langfield" value="<?php echo $lang; ?>" />
                                        <input type="hidden" name="WoID" value="<?php echo $wid; ?>" />
                                        <input type="hidden" name="WoOldStatus" value="<?php echo $record['WoStatus']; ?>" />
                                        <input type="hidden" name="tid" value="<?php echo getreq('tid'); ?>" />
                                        <div class="controls">
                                            <div class="row">
                                                <div class="col-6">
                                                    <label for="form_name">Term</label>
                                                    <input <?php echo $scrdir; ?> data_info="Term" type="text" name="WoText"
                                                        id="wordfield" class="form-control" value="<?php echo tohtml($term); ?>"
                                                        maxlength="250" placeholder="Term" required="required"
                                                        data-error="Term is required.">
                                                </div>
                                                <div class="col-6">
                                                    <label for="form_name">Alt. writing</label>
                                                    <input type="text" data_info="Romanization" name="WoRomanization" id="form_name"
                                                        class="form-control"
                                                        value="<?php echo tohtml($record['WoRomanization']); ?>" maxlength="100"
                                                        placeholder="Alternative writing">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label for="form_message">Translation</label>
                                                        <textarea id="form_message" name="WoTranslation" data_maxlength="500"
                                                            data_info="Translation" <?php echo tohtml($transl); ?>
                                                            class="form-control" placeholder="Translation"
                                                            rows="6"><?php echo tohtml($transl); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label for="form_message">Notes</label>
                                                        <textarea name="WoNotes" data_maxlength="200" data_info="Notes"
                                                            id="form_message" class="form-control" placeholder="Notes"
                                                            rows="4"><?php echo tohtml($notes); ?></textarea>
                                                    </div>

                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <div class="form-group">
                                                        <label for="form_message">Tags</label>
                                                        <?php echo getWordTags($wid); ?>
                                                    </div>

                                                </div>




                                            </div>
                                            <div class="row">
												<?php
												for($i = 1; $i <= 7; ++$i)
												{
													$number = $numbers[$i];
													$text = $texts[$number];
													$bgColor = $bgColors[$number];
													$borderColor = $borderColors[$number];

													$checked = false;
													if($number == $status)
													{
														$checked = true;
													}
													echo "<div class=\"col p-2\">";
													if($checked)
													{
														echo "<button type=\"submit\" name=\"op\" class=\"btn pt-2 btn-block\" style=\"background-color: $bgColor; border-color: #444444FF; color: #000000FF\" value=\"C-$number\">$text</button>";
													}
													else
													{
														echo "<button type=\"submit\" name=\"op\" class=\"btn pt-2 btn-block\" style=\"background-color: $bgColor; border-color: $borderColor; color: #000000FF\" value=\"C-$number\">$text</button>";
													}
													echo "</div>";
												}
												?>
                                            </div>
                                        </div>

                                        <?php echo openDictInEdit($lang, $term); ?>
                                    </form>
                                </div>
                            </div>


                        </div>
                        <!-- /.8 -->

                    </div>
                    <!-- /.row-->

                </div>
            </div>

            <script type='text/javascript' src='https://stackpath.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js'></script>
			<?php
		}
		mysqli_free_result($res);
	}
	#endregion
}
#endregion
pageend();

?>
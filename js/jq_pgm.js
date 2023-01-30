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
Global variables used in LWT jQuery functions
***************************************************************/

var TEXTPOS = -1;
var OPENED = 0;
var WID = 0;
var TID = 0;
var WBLINK1 = '';
var WBLINK2 = '';
var WBLINK3 = '';
var SOLUTION = '';
var ADDFILTER = '';
var RTL = 0;
 
/**************************************************************
LWT jQuery functions
***************************************************************/

function setTransRoman(tra, rom) {
	if($('textarea[name="WoTranslation"]').length == 1)
		$('textarea[name="WoTranslation"]').val(tra);
	if($('input[name="WoRomanization"]').length == 1)
		$('input[name="WoRomanization"]').val(rom);
	makeDirty();
}

function containsCharacterOutsideBasicMultilingualPlane(s) {
  return /[\uD800-\uDFFF]/.test(s);
}

function alertFirstCharacterOutsideBasicMultilingualPlane(s,info) {
	var match = /[\uD800-\uDFFF]/.exec(s);
	if (match) {
		alert('ERROR\n\nText "' + info + '" contains invalid character(s) (in the Unicode Supplementary Multilingual Planes, > U+FFFF) like emojis or very rare characters.\n\nFirst invalid character: "' + s.substring(match.index, match.index+2) + '" at position ' + (match.index+1) + '.\n\nMore info: https://en.wikipedia.org/wiki/Plane_(Unicode)\n\nPlease remove this/these character(s) and try again.');
    return 1;
	} else {
		return 0;
	}
}

function getUTF8Length(s) {
	return (new Blob([String(s)]).size);
}

function scrollToAnchor(aid){
  document.location.href = '#' + aid;
}
 
function check() {
	var count = 0;
	$('.notempty').each( function(n) {
		if($(this).val().trim()=='') count++; 
	} );
	if (count > 0) {
		alert('ERROR\n\n' + count + ' field(s) - marked with * - must not be empty!');
		return false;
	}
	count = 0;
	$('input.checkurl').each( function(n) {
		if($(this).val().trim().length > 0) {
			if(($(this).val().trim().indexOf('http://') != 0) &&   ($(this).val().trim().indexOf('https://') != 0)) {
				alert('ERROR\n\nField "' + $(this).attr('data_info') + '" must start with "http://" or "https://" if not empty.');
				count++;
			}
		}
	} );
	$('input.checkdicturl').each( function(n) {
		if($(this).val().trim().length > 0) {
			if(($(this).val().trim().indexOf('http://') != 0) &&   ($(this).val().trim().indexOf('https://') != 0) &&   ($(this).val().trim().indexOf('*http://') != 0) &&   ($(this).val().trim().indexOf('*https://') != 0) &&   ($(this).val().trim().indexOf('glosbe_api.php') != 0)) {
				alert('ERROR\n\nField "' + $(this).attr('data_info') + '" must start with "http://" or "https://" or "*http://" or "*https://" or "glosbe_api.php" if not empty.');
				count++;
			}
		}
	} );
	$('input.posintnumber').each( function(n) {
		if ($(this).val().trim().length > 0) {
			if (! (isInt($(this).val().trim()) && (($(this).val().trim() + 0) > 0))) {
				alert('ERROR\n\nField "' + $(this).attr('data_info') + '" must be an integer number > 0.');
				count++;
			}
		}
	} );
	$('input.zeroposintnumber').each( function(n) {
		if ($(this).val().trim().length > 0) {
			if (! (isInt($(this).val().trim()) && (($(this).val().trim() + 0) >= 0))) {
				alert('ERROR\n\nField "' + $(this).attr('data_info') + '" must be an integer number >= 0.');
				count++;
			}
		}
	} );
	$('input.checkoutsidebmp').each( function(n) {
		if ($(this).val().trim().length > 0) {
			if (containsCharacterOutsideBasicMultilingualPlane($(this).val())) {
				count += alertFirstCharacterOutsideBasicMultilingualPlane($(this).val(), $(this).attr('data_info'));
			}
		}
	} );
	$('textarea.checklength').each( function(n) {
		if($(this).val().trim().length > (0 + $(this).attr('data_maxlength'))) {
			alert('ERROR\n\nText is too long in field "' + $(this).attr('data_info') + '", please make it shorter! (Maximum length: ' + $(this).attr('data_maxlength') + ' char.)');
			count++;
		}
	} );
	$('textarea.checkoutsidebmp').each( function(n) {
		if(containsCharacterOutsideBasicMultilingualPlane($(this).val())) {
			count += alertFirstCharacterOutsideBasicMultilingualPlane($(this).val(), $(this).attr('data_info'));
		}
	} );
	$('textarea.checkbytes').each( function(n) {
		if(getUTF8Length($(this).val().trim()) > (0 + $(this).attr('data_maxlength'))) {
			alert('ERROR\n\nText is too long in field "' + $(this).attr('data_info') + '", please make it shorter! (Maximum length: ' + $(this).attr('data_maxlength') + ' bytes.)');
			count++;
		}
	} );
	$('input.noblanksnocomma').each( function(n) {
		if($(this).val().indexOf(' ') > 0 || $(this).val().indexOf(',') > 0) {
			alert('ERROR\n\nNo spaces or commas allowed in field "' + $(this).attr('data_info') + '", please remove!');
			count++;
		}
	} );
	return (count == 0);
}

function isInt(value) {
	for (i = 0 ; i < value.length ; i++) {
		if ((value.charAt(i) < '0') || (value.charAt(i) > '9')) {
			return false;
		}
	}
	return true;
}

function markClick() {
	if($('input.markcheck:checked').length > 0) {
		$('#markaction').removeAttr('disabled');
	} else {
		$('#markaction').attr('disabled','disabled');
	}
}

function confirmDelete() {
	return confirm('CONFIRM\n\nAre you sure you want to delete?');
}

function showallwordsClick() {
	var option = $('#showallwords:checked').length;
	var text = $('#thetextid').text();
	window.parent.frames['ro'].location.href = 
		'set_text_mode.php?mode=' + option +
		'&text=' + text;
}

function textareaKeydown(event) {
	if (event.keyCode && event.keyCode == '13') {
		if (check()) $('input:submit').last().click();
		return false;
	} else {
		return true;
	}
}

function noShowAfter3Secs() {
	$('#hide3').slideUp();
}

function setTheFocus() {
	$('.setfocus').focus().select();
}

function word_each_do_text_text(i) {
	this.title = make_tooltip($(this).text(), $(this).attr('data_trans'), 
		$(this).attr('data_rom'), $(this).attr('data_status'));
}

function mword_each_do_text_text(i) {
	if ($(this).attr('data_status') != '') {
		this.title = make_tooltip($(this).attr('data_text'), 
		$(this).attr('data_trans'), $(this).attr('data_rom'), 
		$(this).attr('data_status'));
	}
}

function word_dblclick_event_do_text_text() {
	var t = parseInt($("#totalcharcount").text(),10);	
	if ( t == 0 ) return;
	var p = 100 * ($(this).attr('data_pos')-5) / t;
	if (p < 0) p = 0;
	if (typeof (window.parent.frames['h'].new_pos) == 'function')
		window.parent.frames['h'].new_pos(p);
}

//#region NEW VERSION
function word_click_event_do_text_text() {
	var status = $(this).attr('data_status');
		
	if ( status < 1 )
	{
		var data_term = $(this).attr('data_term');
		var data_language = $(this).attr('data_language');
		var data_index = $(this).attr('data_index');

		run_overlib_status_unknown(WBLINK1,WBLINK2,WBLINK3,$(this).attr('title'),
			TID,$(this).attr('data_order'),data_term,$(this).text(),data_index,RTL);
		top.frames['ro'].location.href='edit_word.php?tid=' + TID +
			'&term=' + data_term +
			'&lang=' + data_language +
			'&wid=';
	}
	else if ( status == 99 )
	{
		var data_term = $(this).attr('data_term');
		var data_language = $(this).attr('data_language');
		var data_wid = $(this).attr('data_wid');
		var data_index = $(this).attr('data_index');

		run_overlib_status_99(WBLINK1,WBLINK2,WBLINK3,$(this).attr('title'),
			TID,$(this).attr('data_order'),$(this).text(),data_term, data_language, data_wid,data_index,RTL);
		top.frames['ro'].location.href='edit_word.php?tid=' + TID +
			'&term=' + data_term +
			'&lang=' + data_language +
			'&wid=' + data_wid;
	}
	else if ( status == 98 )
	{
		var data_term = $(this).attr('data_term');
		var data_language = $(this).attr('data_language');
		var data_wid = $(this).attr('data_wid');
		var data_index = $(this).attr('data_index');

		run_overlib_status_98(WBLINK1,WBLINK2,WBLINK3,$(this).attr('title'),
			TID,$(this).attr('data_order'),$(this).text(),data_term, data_language, data_wid,data_index,RTL);
		top.frames['ro'].location.href='edit_word.php?tid=' + TID +
			'&term=' + data_term +
			'&lang=' + data_language +
			'&wid=' + data_wid;
	}
	else
	{
		var data_term = $(this).attr('data_term');
		var data_language = $(this).attr('data_language');
		var data_wid = $(this).attr('data_wid');
		var data_index = $(this).attr('data_index');

		run_overlib_status_1_to_5(WBLINK1,WBLINK2,WBLINK3,$(this).attr('title'),
			TID,$(this).attr('data_order'),$(this).text(),data_term, data_language, data_wid, status, data_index,RTL);
		top.frames['ro'].location.href='edit_word.php?tid=' + TID +
			'&term=' + data_term +
			'&lang=' + data_language +
			'&wid=' + data_wid;
	}
		
	return false;
}

function mword_click_event_do_text_text() {
	console.log("clicked!");
	var status = $(this).attr('data_status');
	var data_wid = $(this).attr('data_wid');

	if (status !== '') {
		run_overlib_multiword(WBLINK1,WBLINK2,WBLINK3,$(this).attr('title'),
		TID, $(this).attr('data_order'),$(this).attr('data_term'),
		$(this).attr('data_wid'), status,$(this).attr('data_wordcount'));
	}
	top.frames['ro'].location.href='edit_mword.php?tid=' + TID +
		'&wid=' + data_wid;
	return false;
}
//#endregion

function get_position_from_id(id_string) {
	if ((typeof id_string) == 'undefined') return -1;
	var arr = id_string.split('-');
	return parseInt(arr[1]) * 10 + 10 - parseInt(arr[2]);
}

function keydown_event_do_text_text(e) {

	if (e.which == 27) {  // esc = reset all
		TEXTPOS = -1;
		$('span.uwordmarked').removeClass('uwordmarked');
		$('span.kwordmarked').removeClass('kwordmarked');
		cClick();
		return false;
	}
	
	if (e.which == 13) {  // return = edit next unknown word
		$('span.uwordmarked').removeClass('uwordmarked');
		var unknownwordlist = $('span.status0.word:not(.hide):first');
		if (unknownwordlist.size() == 0) return false;
		$(window).scrollTo(unknownwordlist,{axis:'y', offset:-150});
		unknownwordlist.addClass('uwordmarked').click();
		cClick();
		return false;
	}
	
	var knownwordlist = $('span.word:not(.hide):not(.status0)' + ADDFILTER + ',span.mword:not(.hide)' + ADDFILTER);
	var l_knownwordlist = knownwordlist.size();
	// console.log(knownwordlist);
	if (l_knownwordlist == 0) return true;
	
	// the following only for a non-zero known words list
	if (e.which == 36) {  // home : known word navigation -> first
		$('span.kwordmarked').removeClass('kwordmarked');
		TEXTPOS = 0;
		curr = knownwordlist.eq(TEXTPOS);
		curr.addClass('kwordmarked');
		$(window).scrollTo(curr,{axis:'y', offset:-150});
		window.parent.frames['ro'].location.href = 'show_word.php?wid=' + curr.attr('data_wid');
		return false;
	}
	if (e.which == 35) {  // end : known word navigation -> last
		$('span.kwordmarked').removeClass('kwordmarked');
		TEXTPOS = l_knownwordlist-1;
		curr = knownwordlist.eq(TEXTPOS);
		curr.addClass('kwordmarked');
		$(window).scrollTo(curr,{axis:'y', offset:-150});
		window.parent.frames['ro'].location.href = 'show_word.php?wid=' + curr.attr('data_wid');
		return false;
	}
	if (e.which == 37) {  // left : known word navigation
		var marked = $('span.kwordmarked');
		var currid = (marked.length == 0) ? (100000000) : 
			get_position_from_id(marked.attr('id'));
		$('span.kwordmarked').removeClass('kwordmarked');
		// console.log(currid);
		TEXTPOS = l_knownwordlist - 1;
		for (var i = l_knownwordlist - 1; i >= 0; i--) {
			var iid = get_position_from_id(knownwordlist.eq(i).attr('id'));
			// console.log(iid);
			if(iid < currid) {
				TEXTPOS = i;
				break;
			};
		}
		// TEXTPOS--;
		// if (TEXTPOS < 0) TEXTPOS = l_knownwordlist - 1;
		curr = knownwordlist.eq(TEXTPOS);
		curr.addClass('kwordmarked');
		$(window).scrollTo(curr,{axis:'y', offset:-150});
		window.parent.frames['ro'].location.href = 'show_word.php?wid=' + curr.attr('data_wid');
		return false;
	}
	if (e.which == 39 || e.which == 32) {  // space /right : known word navigation
		var marked = $('span.kwordmarked');
		var currid = (marked.length == 0) ? (-1) : 
			get_position_from_id(marked.attr('id'));
		$('span.kwordmarked').removeClass('kwordmarked');
		// console.log(currid);
		TEXTPOS = 0;
		for (var i = 0; i < l_knownwordlist; i++) {
			var iid = get_position_from_id(knownwordlist.eq(i).attr('id'));
			// console.log(iid);
			if(iid > currid) {
				TEXTPOS = i;
				break;
			};
		}
		// TEXTPOS++;
		// if (TEXTPOS >= l_knownwordlist) TEXTPOS = 0;
		curr = knownwordlist.eq(TEXTPOS);
		curr.addClass('kwordmarked');
		$(window).scrollTo(curr,{axis:'y', offset:-150});
		window.parent.frames['ro'].location.href = 'show_word.php?wid=' + curr.attr('data_wid');
		return false;
	}

	if (TEXTPOS < 0 || TEXTPOS >= l_knownwordlist) return true;
	var curr = knownwordlist.eq(TEXTPOS);
	var wid = curr.attr('data_wid');
	var ord = curr.attr('data_order');
	
	// the following only with valid pos.
	for (var i=1; i<=5; i++) {
		if (e.which == (48+i) || e.which == (96+i)) {  // 1,.. : status=i
			window.parent.frames['ro'].location.href = 
				'set_word_status.php?wid=' + wid + '&tid=' + TID + '&ord=' + ord + '&status=' + i;
			return false;
		}
	}
	if (e.which == 73) {  // I : status=98
		window.parent.frames['ro'].location.href = 
			'set_word_status.php?wid=' + wid + '&tid=' + TID + '&ord=' + ord + '&status=98';
		return false;
	}
	if (e.which == 87) {  // W : status=99
		window.parent.frames['ro'].location.href = 
			'set_word_status.php?wid=' + wid + '&tid=' + TID + '&ord=' + ord + '&status=99';
		return false;
	}
	if (e.which == 65) {  // A : set audio pos.
		var p = curr.attr('data_pos');
		var t = parseInt($("#totalcharcount").text(),10);	
		if ( t == 0 ) return true;
		p = 100 * (p-5) / t;
		if (p < 0) p = 0;
		if (typeof (window.parent.frames['h'].new_pos) == 'function')
			window.parent.frames['h'].new_pos(p);
		else 
			return true;
		return false;
	}
	if (e.which == 69) { //  E : EDIT
		if(curr.has('.mword'))
			window.parent.frames['ro'].location.href = 
				'edit_mword.php?wid=' + wid + '&tid=' + TID + '&ord=' + ord;
		else {
			window.parent.frames['ro'].location.href = 
				'edit_word.php?wid=' + wid + '&tid=' + TID + '&ord=' + ord;
		}
		return false;
	}

	return true;
}

function do_ajax_save_setting(k, v) {
	$.post('ajax_save_setting.php', { k: k, v: v } );
}

function do_ajax_update_media_select() {
	$('#mediaselect').html('&nbsp; <img src="icn/waiting2.gif" />');
	$.post('ajax_update_media_select.php', 
		function(data) { $('#mediaselect').html(data); } 
	);
}

function do_ajax_show_similar_terms() {
	$('#simwords').html('<img src="icn/waiting2.gif" />');
	$.post('ajax_show_similar_terms.php', { lang: $('#langfield').val(), word: $('#wordfield').val() }, 
		function(data) { $('#simwords').html(data); } 
	);
}

function do_ajax_word_counts() {
	$("span[id^='saved-']").each(
		function(i) {
			var textid = $(this).attr('data_id');
			$(this).html('<img src="icn/waiting2.gif" />');
			$.post('ajax_word_counts.php', { id: textid },
				function(data) { 
					var res = eval('(' + data + ')');
					$('#total-'+textid).html(res[0]);
					$('#saved-'+textid).html(res[1]);
					$('#todo-'+textid).html(res[2]);
					$('#todop-'+textid).html(res[3]);
				}
			);
		}
	);
}

$.fn.serializeObject = function()
{
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
        if (o[this.name] !== undefined) {
            if (!o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            o[this.name].push(this.value || '');
        } else {
            o[this.name] = this.value || '';
        }
    });
    return o;
};

$(document).ready( function() {
	$('.edit_area').editable('inline_edit.php', 
		{ 
			type      : 'textarea',
			indicator : '<img src="icn/indicator.gif">',
			tooltip   : 'Click to edit...',
			submit    : 'Save',
			cancel    : 'Cancel',
			rows      : 3,
			cols      : 35
		}
	);
	$('form.validate').submit(check);
	$('input.markcheck').click(markClick);
	$('.confirmdelete').click(confirmDelete);
	$('#showallwords').click(showallwordsClick);
	$('textarea.textarea-noreturn').keydown(textareaKeydown);
	$('#termtags').tagit(
		{
			beforeTagAdded: function(event, ui) {
				return ! (containsCharacterOutsideBasicMultilingualPlane(ui.tag.text())); 
			},
			availableTags : TAGS, 
			fieldName : 'TermTags[TagList][]' 
		}
	);
	$('#texttags').tagit(
		{ 
			beforeTagAdded: function(event, ui) {
				return ! (containsCharacterOutsideBasicMultilingualPlane(ui.tag.text())); 
			},
			availableTags : TEXTTAGS, 
			fieldName : 'TextTags[TagList][]'
		}
	); 
	markClick();
	setTheFocus();
	if ($('#simwords').length > 0 && $('#langfield').length > 0 && $('#wordfield').length > 0) {
  	$('#wordfield').blur(do_ajax_show_similar_terms);
  	do_ajax_show_similar_terms();
	}
	window.setTimeout(noShowAfter3Secs,3000);
} ); 

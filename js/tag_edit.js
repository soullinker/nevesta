console.log('tag_edit is ready')

var TAG_EDITOR = {
	image_id : 0,
	html : {
		form : null,
		tags : null,
		save : null,
		cancel : null
	},
}

function bind_tag_editor()
{
	$('#gallery img').bind('click', tag_edit_start)
	TAG_EDITOR.html.form = $('<div>').addClass('tagform')
	TAG_EDITOR.html.tags = $('<textarea>')
	TAG_EDITOR.html.save = $('<input type="button" value="save">')
	TAG_EDITOR.html.cancel = $('<input type="button" value="close">')

	TAG_EDITOR.html.form.append(TAG_EDITOR.html.tags).append('<br/>')
	TAG_EDITOR.html.form.append(TAG_EDITOR.html.save)
	TAG_EDITOR.html.form.append(TAG_EDITOR.html.cancel)

	TAG_EDITOR.html.save.bind('click', save_tag)
	TAG_EDITOR.html.cancel.bind('click', close_tag)

	$(document.body).append(TAG_EDITOR.html.form)
}

function close_tag(event)
{
	TAG_EDITOR.html.form.hide()
}

function load_tag(event)
{
	$.ajax({
		url:'load_tag?id='+TAG_EDITOR.image_id
	}).done(function( data ) {
		fill_tag(data)
	});
}

function fill_tag(data)
{
	TAG_EDITOR.html.tags.val(data)
	TAG_EDITOR.html.tags.prop('disabled', false);
	TAG_EDITOR.html.save.prop('disabled', false);
	TAG_EDITOR.html.tags.focus()
}

function save_tag(event)
{
	TAG_EDITOR.html.tags.prop('disabled', true);
	TAG_EDITOR.html.save.prop('disabled', true);

	$.ajax({
		type: "POST",
		url: "save_tag",
		data: { id: TAG_EDITOR.image_id, taglist: TAG_EDITOR.html.tags.val() }
	})
	.done(function( msg ) {
		TAG_EDITOR.html.tags.prop('disabled', false);
		TAG_EDITOR.html.save.prop('disabled', false);
	});
}

function tag_edit_start(event)
{
	TAG_EDITOR.image_id = $(event.target).attr('img_id')
	console.log(TAG_EDITOR.image_id)

	TAG_EDITOR.html.tags.prop('disabled', true);
	TAG_EDITOR.html.save.prop('disabled', true);

	TAG_EDITOR.html.form.css({'left':event.pageX+'px', 'top':event.pageY+'px'})
	TAG_EDITOR.html.form.show()

	load_tag()
}


var FILTER = [];
var FILTER_EXCLUDE = [];

function init_filter(event)
{
	$('#content .taglist .tag').click(filter_switch)
	$('#tagfilter').click(apply_filter)
	$('#sortby').change(change_sort)
}

function change_sort(event)
{
	var sort = $(this).val()
	$.cookie("sort", sort, { path: '/', expires: 7 });
	location.reload();
}

function apply_filter(event)
{
	$.cookie("filter", FILTER.join(','), { path: '/', expires: 7 });
	$.cookie("filter_ban", FILTER_EXCLUDE.join(','), { path: '/', expires: 7 });
	document.location = '/filter';
}

function in_array(needle, haystack)
{
	return haystack.indexOf(needle) != -1
}

function array_remove(needle, haystack)
{
	var index = haystack.indexOf(needle)
	if (index != -1)
		haystack.splice(index, 1)
}

function filter_switch(event)
{
	var tag_object = $(this)
	var tagname = tag_object.attr('val')
	var ban = event.ctrlKey

	if (ban)
	{
		array_remove(tagname, FILTER)
		tag_object.removeClass('allow')

		var index = FILTER_EXCLUDE.indexOf(tagname)
		if (index == -1)
		{
			FILTER_EXCLUDE.push(tagname)
			tag_object.addClass('ban')
		}
		else
		{
			FILTER_EXCLUDE.splice(index, 1)
			tag_object.removeClass('ban')
		}
	}
	else
	{
		array_remove(tagname, FILTER_EXCLUDE)
		tag_object.removeClass('ban')
		
		var index = FILTER.indexOf(tagname)
		if (index == -1)
		{
			FILTER.push(tagname)
			tag_object.addClass('allow')
		}
		else
		{
			FILTER.splice(index, 1)
			tag_object.removeClass('allow')
		}
	}
}
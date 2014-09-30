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

function init_filter(event)
{
	$('#content .taglist .tag').click(function() {
		filter_switch($(this))
	})
	$('#tagfilter').click(apply_filter)
}

function apply_filter(event)
{
	$.cookie("filter", FILTER.join(','), { path: '/', expires: 7 });
	document.location = '/filter';
}

function filter_switch(tag_object)
{
	var tagname = tag_object.attr('val')
	console.log('Tag add: '+tagname);
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
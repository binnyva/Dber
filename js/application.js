$=jQuery.noConflict();
//Framework Specific
function showMessage(data) {
	var type = 'error';
	if(data.success) var type = 'success';

	$("#"+type+"-message").html(stripSlashes(data[type]));
	$("#"+type+"-message").fadeIn(500);

	window.setTimeout(function() {
		$("#"+type+"-message").fadeOut(500);
	}, 3000); // Amount of time message should be shown.

	return type;
}
function stripSlashes(text) {
	if(!text) return "";
	return text.replace(/\\([\'\"])/,"$1");
}

function ajaxError() {
	alert("Error communicating with server. Please try again");
}
function loading() {
	$("#loading").show();
}
function loaded() {
	$("#loading").hide();
}

function handleSubmit(e) {
	e.stopPropagation();
	e.preventDefault();

	var form = $(this);
	var url = form.attr("action");
	var action = form.find("[name='action']");
	action.val("Saving...");
	action.prop("disabled", true);
	// alert(url+form.serialize());
	$.ajax({
		"url": url,
		"data": form.serialize() + "&action=Save&ajaxify=1",
		"success": function(message) {
			action.val("Saved");
			action.prop("disabled", false);
			// console.log(message);
			window.setTimeout(function () {
				action.val("Save");
			}, 2000);
		}
	});
	return false;
}

function siteInit() {
	$("a.confirm").click(function(e) { //If a link has a confirm class, confrm the action
		var action = (this.title) ? this.title : "do this";
		action = action.substr(0,1).toLowerCase() + action.substr(1); //Lowercase the first char.
		var user_action = confirm("Are you sure you want to " + action + "?");

		if(!user_action) {
			e.stopPropagation();
			e.preventDefault();
			return false;
		}
	});

	$("form.ajaxify").submit(handleSubmit);

	if(window.init && typeof window.init == "function") init(); //If there is a function called init(), call it on load
}
jQuery(window).load(siteInit);

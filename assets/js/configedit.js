var cmeditor = null;
$(function() {
	$('#jstree-proton-1').on('changed.jstree', function (e, data) {
		var id = data.instance.get_node(data.selected).id;
		if(typeof $("#"+id).data("file") !== "undefined") {
			$.post( "ajax.php", {module: "configedit", command: "load", file: $("#"+id).data("file"), path: $("#"+id).data("path")},function( data ) {
				if(data.status) {
					cmeditor.setValue(data.contents);
					if(typeof CodeMirror.mimeModes[data.mime] !== "undefined") {
						cmeditor.setOption("mode", data.mime);
					} else {
						$.getScript( "assets/configedit/js/modes/"+data.modeFile+".js", function() {
							cmeditor.setOption("mode", data.mime);
						});
					}
					$("#editor").data("file", $("#"+id).data("file"));
					$("#editor").data("path", $("#"+id).data("path"));
					$("#editor").data("id", id);
					if(data.writable) {
						$("#message").addClass("hidden").text("");
						$("#save").prop("disabled", false);
						cmeditor.setOption("readOnly",false);
					} else {
						$("#message").removeClass("alert-success hidden").addClass("alert-danger").text(_("File is not writable"));
						$("#save").prop("disabled", true);
						cmeditor.setOption("readOnly",true);
					}
				} else {
					$("#message").removeClass("alert-success hidden").addClass("alert-danger").text(data.message);
					$("#save").prop("disabled", true);
				}
			});
		}
	}).jstree({
		'core': {
			'themes': {
				'name': 'proton',
				'responsive': true
			},
			'multiple': false
		}
	});

	cmeditor = CodeMirror.fromTextArea(document.getElementById("editor"), {
		lineNumbers: true,
		mode: "text/x-asterisk",
		matchBrackets: true,
	});

	$("#save").click(function() {
		if(typeof $("#editor").data("file") !== "undefined") {
			$("#save").prop("disabled", true).text(_("Saving..."));
			cmeditor.setOption("readOnly",true);
			$.post( "ajax.php", {module: "configedit", command: "save", file: $("#editor").data("file"), path: $("#editor").data("path"), contents: cmeditor.getValue()},function( data ) {
				if(data.status) {
					$("#message").removeClass("hidden alert-danger").addClass("alert-success").text(_("Saved. Make sure to Apply Config so that Asterisk will pickup your changes"));
					toggle_reload_button("show");
					var id = $("#editor").data("id"),icon = $('#'+id).find('i.jstree-icon.jstree-themeicon').first();
					if(cmeditor.getValue().trim() === "") {
						icon.removeClass('fa-file-text-o').addClass('fa-file-o');
					} else {
						icon.removeClass('fa-file-o').addClass('fa-file-text-o');
					}
				} else {
					$("#message").removeClass("alert-success hidden").addClass("alert-danger").text(data.message);
				}
				$("#save").prop("disabled", false).text(_("Save"));
				cmeditor.setOption("readOnly",false);
			});
		}
	});
});

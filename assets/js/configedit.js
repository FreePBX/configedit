var cmeditor = null, fileid = null;
$(function() {
	$('#jstree-proton-1').on('changed.jstree', function (e, data) {
		var id = data.instance.get_node(data.selected).id;
		if(typeof $("#"+id).data("file") !== "undefined") {
			fileid = id;
			loadFile(id, $("#"+id).data("file"), $("#"+id).data("path"), $("#"+id).data("type"));
		} else {
			fileid = null;
			$("#filemessage").text(_("Click a file on the left to edit"));
			if(cmeditor !== null) {
				cmeditor.setOption("readOnly",true);
				cmeditor.setValue("");
			}
			$("#save").prop("disabled", true);
			$("#delete").prop("disabled", true);
		}
	}).jstree({
		'core': {
			'themes': {
				'name': 'proton',
				'responsive': true
			},
			'check_callback' : true,
			'multiple': false
		}
	});

	cmeditor = CodeMirror.fromTextArea(document.getElementById("editor"), {
		lineNumbers: true,
		mode: "text/x-asterisk",
		matchBrackets: true,
	});

	cmeditor.setSize($(".col-md-8").width(), 350);
	$( window ).resize(function() {
		cmeditor.setSize($(".col-md-8").width(), 350);
	});

	$("#delete").click(function() {
		if(fileid === null || !confirm(_("Are you sure you want to delete this file?"))) {
			return;
		}
		$.post( "ajax.php", {module: "configedit", command: "delete", file: $("#editor").data("file"), path: $("#editor").data("path"), type: $("#editor").data("type")}, function( data ) {
			if(data.status) {
				cmeditor.setOption("readOnly",true);
				cmeditor.setValue("");
				$("#filemessage").text(_("Click a file on the left to edit"));
				$("#jstree-proton-1").jstree("delete_node",fileid);
			} else {
				alert(data.message);
			}
		});
	});
	$("#save").click(function() {
		if(typeof $("#editor").data("file") !== "undefined") {
			$("#save").prop("disabled", true).text(_("Saving..."));
			cmeditor.setOption("readOnly",true);
			$.post( "ajax.php", {module: "configedit", command: "save", file: $("#editor").data("file"), path: $("#editor").data("path"), contents: cmeditor.getValue(), type: $("#editor").data("type")},function( data ) {
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
$("#addfile").click(function() {
	var file = prompt(_("Please enter a valid (not in use) file name")),
			$this = this;
	if (file !== null) {
		$(this).prop("disabled",true);
		$.post( "ajax.php", {module: "configedit", command: "add", file: file},function( data ) {
			if(data.status) {
				var id = $('#jstree-proton-1').jstree('create_node', 'j1_1', { text:data.file,"icon":"fa fa-file-o"}, 'last');
				var el = $('#'+id);
				el.data("path",'/etc/asterisk');
				el.data("type", 'custom');
				el.data("file", data.file);
				$('#jstree-proton-1').jstree('deselect_all');
				$('#jstree-proton-1').jstree('select_node', id);
			} else {
				alert(data.message);
			}
		}).always(function(){
			$($this).prop("disabled",false);
		})
	}
});


function loadFile(id, file, path, type) {
	$.post( "ajax.php", {module: "configedit", command: "load", file: file, path: path, type: type},function( data ) {
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
			$("#editor").data("type", $("#"+id).data("type"));
			$("#editor").data("id", id);
			if(data.writable) {
				$("#message").addClass("hidden").text("");
				$("#save").prop("disabled", false);
				$("#delete").prop("disabled", false);
				cmeditor.setOption("readOnly",false);
			} else {
				$("#message").removeClass("alert-success hidden").addClass("alert-danger").text(_("File is not writable"));
				$("#save").prop("disabled", true);
				$("#delete").prop("disabled", true);
				cmeditor.setOption("readOnly",true);
			}
			$("#filemessage").text(sprintf(_("Working on %s"),$("#"+id).data("file")));
		} else {
			$("#message").removeClass("alert-success hidden").addClass("alert-danger").text(data.message);
			$("#save").prop("disabled", true);
			$("#delete").prop("disabled", true);
		}
	});
}

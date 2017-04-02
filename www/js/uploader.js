$(function() {
    
    

	//when the select all checkbox is clicked, toggle all the checkboxes
	$(".header-table .checkbox").click(function() {
		if ($(this).hasClass("unchecked")) {
			$(this).removeClass("unchecked");
			$(".uploader-table .checkbox").removeClass("unchecked");
		} else {
			$(this).addClass("unchecked");
			$(".uploader-table .checkbox").addClass("unchecked");
		}
	});

	//toggle individual checkboxes on click
	$(document).on('click', '.uploader-table .checkbox', function() {
		$(this).toggleClass("unchecked");
	});

	//clear the current file when the file input is clicked
	$("#upload-file").click(function() {
		$(this).val("");
	});

	//when the file changes, submit the form
	$("#upload-file").change(function() {
		$("#uploader-form").submit();
	});

	//this is the table row template to create new file entries
	function tableRowTemplate(file) {
		var row = [`<tr data-id="${file.id}">`,
			`   <td class="checkbox-cell">`,
			`       <div class="checkbox button unchecked"><i class="fa fa-check" aria-hidden="true"></i></div>`,
			`   </td>`,
			`   <td class="name"><input type="text" value="${file.name.substr(0, file.name.lastIndexOf("."))}" data-extension="${file.name.substr(file.name.lastIndexOf("."))}" class="name-input"></td>`,
			`   <td class="type"><i class="fa ${file.icon}" aria-hidden="true"></i> ${file.size}</td>`,
			`   <td class="date">${file.date}</td>`,
			`   <td class="download">`,
			`       <div class="download button"><i class="fa fa-arrow-down" aria-hidden="true"></i></div>`,
			`   </td>`,
			`   <td class="delete">`,
			`       <div class="delete button"><i class="fa fa-minus" aria-hidden="true"></i></div>`,
			`   </td>`,
			`</tr>`
		].join("\n");
		return row;
	}

	//populate the table with the most up-to-date rows
	function generateTableRows() {
		$.post("php/getFiles.php?XDEBUG_SESSION_START=xdebug", function(data) {
			$(".uploader-table tbody").html("");
			JSON.parse(data).forEach(function(file) {
				$(".uploader-table tbody").append(tableRowTemplate(file));
			}, "json");
		});
	}

	//invoke this on load
	generateTableRows();

	//when the form gets submitted
	$("#uploader-form").submit(function(e) {
		e.preventDefault();
		//make some form data to send over
		var formData = new FormData();
		$($("#upload-file").prop("files")).each(function(i, file) {
			formData.append("file " + i, file);
		});
		//make an ajax call to the upload script
		$.ajax({
			url: "php/uploadFile.php?XDEBUG_SESSION_START=xdebug",
			dataType: 'text',
			cache: false,
			contentType: false,
			processData: false,
			data: formData,
			type: 'post'
		}).done(function(e) {
			//generate the new table rows
			generateTableRows();
		});
	});

	function deleteFiles(rowIDs, callback) {
		$.post("php/deleteFile.php?XDEBUG_SESSION_START=xdebug", {
			rowIDs: rowIDs
		}, function(e) {
			callback(e);
		});
	}

	function updateFiles(rowIDs, fileNames, callback) {
		$.post("php/updateFile.php?XDEBUG_SESSION_START=xdebug", {
			rowIDs: rowIDs,
			fileNames: fileNames
		}, function(e) {
			callback(e);
		});
	}

	//on click of delete buttons in table rows
	$(document).on('click', '.uploader-table .delete', function(e) {
		e.stopPropagation();
		//TODO: show a loader
        $("#loader").show()
        
        
		//get this row's id and delete the row
		var id = $(this).closest("tr").data("id");
		$(this).closest("tr").remove();
		//delete the actual file
		deleteFiles([id], function(e) {
			//TODO: hide a loader here
		});
	});

	//for the group delete button click
	$(".group-buttons .delete").click(function() {
		//TODO: show a loader
        $("#loader").show()
        
		//push all the active ids to an array and remove the rows
		var ids = [];
		$(".uploader-table tbody").find("tr").each(function(i, row) {
			if (!$(row).find(".checkbox").hasClass("unchecked")) {
				ids.push($(row).data("id"));
				$(row).remove();
			}
		});
		//delete them all
		deleteFiles(ids, function(e) {
			//TODO: hide a loader here
		});
	});

	function downloadFiles(rowIDs) {
		//force a download by calling an iframe to the download script
		$("body").append("<iframe src='" + "php/downloadFile.php?XDEBUG_SESSION_START=xdebug&rowIDs=" + JSON.stringify(rowIDs) + "' style='display: none;'></iframe>");
	}

	//on click of download buttons in table rows
	$(document).on('click', '.uploader-table .download', function(e) {
		e.stopPropagation();
		//TODO: show a loader
        $("#loader").show()
        
		//get this row's id
		var id = $(this).closest("tr").data("id");
		//download the file
		downloadFiles([id], function(e) {
			//TODO: hide a loader here
		});
	});

	$(".group-buttons .download").click(function() {
		//push all the ids to an array
		var ids = [];
		$(".uploader-table tbody").find("tr").each(function(i, row) {
			if (!$(row).find(".checkbox").hasClass("unchecked"))
				ids.push($(row).data("id"));
		});
		//download them all as a zip
		downloadFiles(ids);
	});

	//when a key is pressed
	$(document).keypress(function(e) {
		//if it is the enter key
		if (e.which == 13 && e.target.classList.contains("name-input")) {
			//don't submit the form!
			e.preventDefault();
			//TODO: show a loader
            $("#loader").show()
            
			//get this input
			var nameInput = e.target;
			//push it's id an name to arrays
			var ids = [];
			ids.push($(e.target).closest("tr").data("id"));
			var names = [];
			//append the file extension to the name so that we know what type of file it is
			//the extension is stored in a data-extension attribute
			names.push($(e.target).val() + $(e.target).data("extension"));
			//update the file
			updateFiles(ids, names, function(e) {
				//TODO: hide a loader here
			});
		}
	});

});
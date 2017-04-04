
$(function() {
  /*
  ----------------  F i l e  T e m p l a t e ----------------
  */
	function fileTemplate(file) {
		var row = [
        `<div class="col-lg-4 col-sm-6 FileParent" data-id="${file.id}">`,
        `  <div class="file-box fileDL fileBlock1">`,
        `    <h1 class="fileName">${file.name}</h1>`,
        `    <h1 class="fileType"><i class="fa ${file.icon}" aria-hidden="true"></i></h1>`,
        `    <div class="file-box-caption">`,
        `      <h1 class="fileName"><input type="text" value="${file.name.substr(0, file.name.lastIndexOf("."))}" data-extension="${file.name.substr(file.name.lastIndexOf("."))}" class="name-input"></h1>`,
        `      <h2>${file.size} | ${file.date}</h2>`,
        `      <div class="file-box-caption-content">`,
        `        <a class="project-category selectFile" href="#"><h1 class="Check"><i class="fa fa-check" aria-hidden="true"></i></h1></a>`,
        `        <a class="project-category download" href="#">Download  <i class="fa fa-download" aria-hidden="true"></i></a>`,
        `        <a class="project-category delete" href="#">Delete  <i class="fa fa-trash" aria-hidden="true"></i></a>`,
        `      </div>`,
        `    </div>`,
        `  </div>`,
        `</div>`
		].join("\n");
		return row;
	}//E N D  F i l e  T e m p l a t e
  
  
  /*
  ----------------  P o p u l a t e  f i l e s  f r o m  D B ----------------
  */
	function generateTableRows() {
    $("#spinner").toggleClass("hidden");
		$.post("php/getFiles.php?XDEBUG_SESSION_START=xdebug", function(data) {
			$(".FillmeWithFiles").html("");
			JSON.parse(data).forEach(function(file) {
				$(".FillmeWithFiles").append(fileTemplate(file));
			}, "json");
		});
    $("#spinner").toggleClass("hidden");
	}
	//invoke on load
	generateTableRows();
  //E N D  P o p u l a t e  f i l e s  f r o m  D B
  
  
  /*
  ---------------- C R U D  H e l p e r  f u n c t i o n s ----------------
  */
  function downloadFiles(rowIDs) {
		//force a download by calling an iframe to the download script
		$("body").append("<iframe src='" + "php/downloadFile.php?XDEBUG_SESSION_START=xdebug&rowIDs=" + JSON.stringify(rowIDs) + "' style='display: none;'></iframe>");
	}
  
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
  
  //when a key is pressed
	$(document).keypress(function(e) {
		//if it is the enter key
		if (e.which == 13 && e.target.classList.contains("name-input")) {
			//don't submit the form!
			e.preventDefault();
			$("#spinner").toggleClass("hidden");
			//get the input
			var nameInput = e.target;
			//push its id and name to array
			var ids = [];
			ids.push($(e.target).closest(".FileParent").data("id"));
			var names = [];
			//append the file extension to the name so that we know what type of file it is
			//the extension is stored in a data-extension attribute
			names.push($(e.target).val() + $(e.target).data("extension"));
			//update the file
			updateFiles(ids, names, function(e) {
        $("#spinner").toggleClass("hidden");
				generateTableRows();
			});
		}
	});
  //E N D  C R U D H e l p e r  f u n c t i o n s
  
  
  /*
  ---------------- U p l o a d e r  f u n c t i o n s ----------------
  */
	$("#upload-file").click(function() {
		$(this).val("");
	});
	//when the file changes, submit the form
	$("#upload-file").change(function() {
		$("#uploader-form").submit();
	});
	//when the form gets submitted
	$("#uploader-form").submit(function(e) {
		e.preventDefault();
    $("#spinner").toggleClass("hidden");
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
    $("#spinner").toggleClass("hidden");
	});//E N D  U p l o a d e r  f u n c t i o n s
  
  
  /*
  ---------------- M u l t i  F i l e  B u t t o n s ----------------
  */
  //group   DELETE   button click
	$("#uploader-form .delete").click(function() {
		$("#spinner").toggleClass("hidden");
		//push all the active ids to an array and remove the rows
		var ids = [];
		$(".FillmeWithFiles").find(".FileParent .fileDL").each(function(i, row) {
			if ($(row).hasClass("selected")) {
				ids.push($(row).parent().data("id"));
				$(row).remove();
			}
		});
		deleteFiles(ids, function(e) {
      generateTableRows();
      $("#spinner").toggleClass("hidden");
    });
	});
  
  //group   DOWNLOAD   button click
  $("#uploader-form .download").click(function() {
		//push all the ids to an array
    $("#spinner").toggleClass("hidden");
		var ids = [];
		$(".FillmeWithFiles").find(".FileParent .fileDL").each(function(i, row) {
			if ($(row).hasClass("selected")) {
				ids.push($(row).parent().data("id"));
      }
		});
		//download them all as a zip
		downloadFiles(ids);
    $("#spinner").toggleClass("hidden");
	});
  
  //Toggle selection
	$(document).on('click', '.selectFile', function(e) {
    $(this).parent().closest('.fileDL').toggleClass("selected");
	});//E N D  M u l t i  F i l e  B u t t o n s
  
  
  /*
  ---------------- I n d i v i d u a l  F i l e  B u t t o n s ----------------
  */
	//on click of delete buttons in table rows
	$(document).on('click', '.file-box-caption-content .delete', function(e) {
		e.stopPropagation();
		$("#spinner").toggleClass("hidden");
		//get this row's id and delete the row
		var id = $(this).closest(".FileParent").data("id");
		$(this).closest(".FileParent").remove();
		//delete the actual file
		deleteFiles([id], function(e) {
			$("#spinner").toggleClass("hidden");
		});
	});
	//on click of download buttons in table rows
	$(document).on('click', '.file-box-caption-content .download', function(e) {
		e.stopPropagation();
		$("#spinner").toggleClass("hidden");
		//get this row's id
		var id = $(this).closest(".FileParent").data("id");
		//download the file
		downloadFiles([id], function(e) {
			$("#spinner").toggleClass("hidden");
		});
    $("#spinner").toggleClass("hidden");
	});//E N D  I n d i v i d u a l  F i l e  B u t t o n s

});//E N D  D O C U M E N T
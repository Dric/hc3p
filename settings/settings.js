$(document).ready(function(){
	$(document).on('click', 'input[name=sectionBackground]',function(){
	 	var selected = $(this).val();
	 	var $bgnI = $('#sectionBackgroundImageDisplay');
	 	var $bgnC = $('#sectionBackgroundColorDisplay');
	 	
	 	if (selected == 'image' || selected == 'pattern'){
	 		if (!$bgnI.is(':visible')){
				$bgnI.fadeIn();
			}
			if ($bgnC.is(':visible')){
				$bgnC.fadeOut();
			}
		}else if(selected == 'color'){
			if ($bgnI.is(':visible')){
				$bgnI.fadeOut();
			}
			if (!$bgnC.is(':visible')){
				$bgnC.fadeIn();
			}
		}else{
			if ($bgnI.is(':visible')){
				$bgnI.fadeOut();
			}
			if ($bgnC.is(':visible')){
				$bgnC.fadeOut();
			}
		}
	});
	
	$(document).on('click', '#globalStyleEditButton', function(){
		$('#formGlobalStyleEdit').hide().load('index.php?action=getSectionEditSettingsForm&editGlobalStyle=true', function(){
			
			$('#cancelEditSectionSettings').click(function(e){
				e.preventDefault();
				$('#formGlobalStyleEdit').fadeOut(200, 'swing', function(){$(this).html('')});
			});
			
			coolEffects();
			
			$('#saveEditSectionSettings').click(function(e){
				e.preventDefault();
				var sectionSettings = $('#editSectionSettings').serialize();
				
				$.ajax({
					type: 'POST',
					url: 'index.php?action=saveSection',
					data: sectionSettings
				}).done(function(data){
					if (console){console.log(data);}
					if (/alert\-error/i.test(data)){
						//Traitement des erreurs
						$('#formGlobalStyleEdit').html(data);
					}else{
						location.reload();
					}
				});
			});
		}).fadeIn();
	});
	
	$(document).on('click', '.insertNewSectionAfter', function(){
		var order = $(this).data('before');
		var after = $(this).data('after');
		
		$('.insertDiv').html('');
		
		$('#insertNewSectionDivAfterSection-'+after).hide().load('index.php?action=getSectionEditSettingsForm&order='+order, function(){
			
			$('#cancelEditSectionSettings').click(function(e){
				e.preventDefault();
				$('#formEditSectionSettings').fadeOut(200, 'swing', function(){$(this).html('')});
			});
			
			coolEffects();
			
			$('#saveEditSectionSettings').click(function(e){
				e.preventDefault();
				var sectionSettings = $('#editSectionSettings').serialize();
				
				$.ajax({
					type: 'POST',
					url: 'index.php?action=saveSection',
					data: sectionSettings
				}).done(function(data){
					if (console){console.log(data);}
					if (/alert\-error/i.test(data)){
						//Traitement des erreurs
						$('#insertNewSectionDivAfterSection-'+after).html(data);
					}else{
						location.reload();
					}
				});
			});
		}).fadeIn();
	});
	
	$(document).on('click', '.editSectionTabs', function(e){
		editSectionTabs($(this));
	});
	
	function editSectionTabs($e, noload){
		
		var dirname = $e.data('dirname');
		var name = $e.data('name');
		
		if (noload) {
			var oldHtml = $('#cancelEditSectionTabs').data('oldHtml')
		}else{
			var oldHtml =  $('#section-'+name).html();
		}
		
		$('.insertDiv').html('');
		
		// On désactive les autres boutons
		$('.editSectionTabs').attr('disabled', true);
		$('.insertNewSectionAfter').attr('disabled', true);
		$('.delSession').attr('disabled', true);
		console.log(noload);
		if (!noload){
			$('#section-'+name).load('index.php?action=getSectionEditTabs&dirname='+dirname, function(){
				loadEditSectionTabs(name, oldHtml, $e);
			});
		}else{
			loadEditSectionTabs(name, oldHtml, $e);
		}
	}
	
	function loadEditSectionTabs(name, oldHtml, $e){
		$('#cancelEditSectionTabs').data('oldHtml', oldHtml);
		
		$('#selectSectionBackgroundImage').on('click', function(e) {
			console.log('clicked !');
	    var src = $(this).attr('data-src');
	    var height = $(this).attr('data-height') || ($(window).height()*0.7);
	    var width = $(this).attr('data-width') || '100%';
	    $("#sectionLoadImageModal iframe").attr({
	    	'src':src,
      	'height': height,
      	'width': width
      });
		});
		
		$('#sectionBackgroundImage').on('input', function(){
			console.log('changed !');
			$('#sectionBackgroundSelectedImage img').hide().attr('src', $(this).val().replace('data/images', 'cache/thumbs')).fadeIn();
		});
		$("#sectionLoadImageModal").on('hidden.bs.modal', function(){
			$('#sectionBackgroundSelectedImage img').hide().attr('src', $('#sectionBackgroundImage').val().replace('data/images', 'cache/thumbs')).fadeIn();
		});
		
		// Mise en place des éditeurs Markdown
		$('.subSectionContentTextarea').editorify();
		
		$('.removeSubSection').click(function(e){
			e.preventDefault();
		});
		$('.removeSubSection').confirmation({
			title								: 'Confirmer la suppression ?',
			btnOkLabel					: 'Supprimer',
			btnOkIcon						: '',
			btnCancelLabel			:	'Annuler',
			btnCancelIcon				:	'',
			btnOKPreventDefault	: true,
			onConfirm						: function(){
				var textareasIDs = new Array();
				$('.subSectionContentTextarea').each(function(){
					textareasIDs.push = $(this).attr('id');
					var $editor = $(this).data('editor');
					//On sauvegarde le contenu de l'éditeur dans le textarea
		    	$editor.codemirror.save();
				});
				var subSection = $(this).attr('href');
				var sectionContent = $('#editSectionContent').serialize();
				var sectionSettings = $('#editSectionSettings').serialize();
				
				$.ajax({
					type: 'POST',
					url: 'index.php?action=removeSubSection',
					data: sectionContent + '&' + sectionSettings + '&sub=' + subSection
				}).done(function(data){
					if (console){console.log(data);}
					if (/alert\-error/i.test(data)){
						//Traitement des erreurs
						$(this).after(data);
					}else{
						$('#formEditSectionContent').html(data);
						editSectionTabs($e, true);
					}
				});
			}
		});
		
		// Clic sur le bouton d'insertion de sous-section
		$('.insertSubSection').click(function(e){
			e.preventDefault();
			var after = $(this).data('after');
			var textareasIDs = new Array();
			$('.subSectionContentTextarea').each(function(){
				textareasIDs.push = $(this).attr('id');
				var $editor = $(this).data('editor');
				//On sauvegarde le contenu de l'éditeur dans le textarea
	    	$editor.codemirror.save();
			});
			var sectionContent = $('#editSectionContent').serialize();
			var sectionSettings = $('#editSectionSettings').serialize();

			$.ajax({
				type: 'POST',
				url: 'index.php?action=insertSubSection',
				data: sectionContent + '&' + sectionSettings + '&after=' + after
			}).done(function(data){
				if (console){console.log(data);}
				if (/alert\-error/i.test(data)){
					//Traitement des erreurs
					$(this).after(data);
				}else{
					$('#formEditSectionContent').html(data);
					editSectionTabs($e, true);
				}
			});
		});
		
		// Clic sur le bouton de sauvegarde
		$('#editSectionTabsSave').click(function(e){
			e.preventDefault();
			var textareasIDs = new Array();
			$('.subSectionContentTextarea').each(function(){
				textareasIDs.push = $(this).attr('id');
				var $editor = $(this).data('editor');
				//On sauvegarde le contenu de l'éditeur dans le textarea
	    	$editor.codemirror.save();
			});
			
			var sectionContent = $('#editSectionContent').serialize();
			var sectionSettings = $('#editSectionSettings').serialize();
			
			$.ajax({
				type: 'POST',
				url: 'index.php?action=saveSection',
				data: sectionContent+'&'+sectionSettings
			}).done(function(data){
				if (console){console.log(data);}
				if (/alert\-error/i.test(data)){
					//Traitement des erreurs
					$('#section-'+name).html(data+$('#cancelEditSectionTabs').data('oldHtml'));
				}else{
					location.reload();
				}
			});
		});
		
		// Clic sur le bouton d'annulation de l'édition de section
		$('#cancelEditSectionTabs').click(function(e){
			e.preventDefault();
			
			var name 		= $(this).data('name');
			var dirname = $(this).data('dirname');
			var html 		= $(this).data('oldHtml');

			$('#section-'+name).load('index.php?action=getSection', {dirname: dirname});
			$('.editSectionTabs').removeAttr('disabled');
			$('.insertNewSectionAfter').removeAttr('disabled');
			$('.delSession').removeAttr('disabled');
		});
		
		coolEffects();
	}
	
	coolEffects();
	
	function coolEffects(){
		$('input[name=sectionTextColorInherited]').on('switchChange', function (e, data) {
			var selected = data.value;
			var $txtC = $('#sectionTextColorDisplay');
			//console.log(selected);
			if (selected){
				$txtC.fadeOut();
			}else{
				$txtC.fadeIn();
			}
		});
		$('.colorPickerEnabled').minicolors();
		if ($('.sliderEnabled').length > 0){
			$('.sliderEnabled').slider();
		}
		$('input[type=checkbox]').bootstrapSwitch({
			size: 		'small',
			onText:		'Oui',
			offText:	'Non'
		});
		$('#editSectionSettings').bootstrapValidator({
			message: 'Cette valeur est incorrecte !',
			fields: {
				sectionName: {
					message: 'Le nom est trop long !',
					validators: {
						notEmpty: {
							message: 'Le nom de la section est obligatoire !'
						},
						stringLength: {
							min: 1,
							max: 20,
							message: 'Le nom ne doit pas dépasser 20 caractères !'
						},
						regexp: {
							regexp: /^\w+$/,
							message: 'Le nom ne peut comporter que des lettres, des chiffres et des underscores ("_") !'
						}
					}				
				}
			}
		});
		$('[data-toggle="del-section-confirmation"]').confirmation({
			title								: 'Confirmer la suppression ?',
			btnOkLabel					: 'Supprimer',
			btnOkIcon						: '',
			btnCancelLabel			:	'Annuler',
			btnCancelIcon				:	'',
			btnOKPreventDefault	: true,
			onConfirm						: function(){
				var dirname = $(this).attr('href');
				$.ajax({
					type: 'POST',
					url: 'index.php?action=delSection',
					data: {
						dirname: dirname
					}
				}).done(function(data){
					if (/alert\-error/i.test(data)){
						//Traitement des erreurs
						$(this).after(data);
					}else{
						location.reload();
					}
				});
			}
		});
	}
	
	$.fn.editorify = function (options) {
    options = options || {};
    return this.each(function (index, element) {
      var editor;
      options.element = element;
      if (!$(element).data('editor')){
				editor = new Editor(options);
      	$(element).data('editor', editor);
      	editor.render();
			}
    });
	};
});
/* **************************************************************
*  Copyright notice
*
*  (c) Florian Rohland <info@florianrohland.de>
*
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 3 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
*  ************************************************************* */

// Set jQuery to noConflict mode
jQuery.noConflict();

// TinyMCE configuration
tinyMCE.init({
	// General options
	mode : "none",
	theme : "simple"
});

var closestAjaxContainer;
var init = true;

window.onhashchange = function() {
	init = false;
}

jQuery(document).ready(function($) {
	// TinyMCE editor
	$('body').on('click', '.tx_ezqueries_textarea_editor', function() {
		var textAreaID = $(this).attr('id');
		tinyMCE.execCommand('mceAddControl', false, $(this).attr('id'));
		if(tinyMCE.get(textAreaID) !== undefined) {
			tinyMCE.get(textAreaID).focus();
		}
	});

	// Date picker configuration
	$('body').on('click', 'input.tx_ezqueries_input_date', function() {
		var options = {
			dateFormat : $(this).attr('data-ezqueries-dateformat'),
			showOtherMonths : true,
			changeMonth : true,
			changeYear : true,
			showOn : 'focus',
			altField : $(this).closest('div.tx_ezqueries_datepicker').find('input.tx_ezqueries_input_date_hidden'),
			altFormat : $.datepicker.ATOM
		};
		$(this).datepicker(options).focus();
	});

	// Delete input symbol trigger
	/*$('body').on('mouseenter', 'div.tx_ezqueries_input', function(){
	$(this).find('div.tx_ezqueries_image_delete').show();
	}).on('mouseleave', 'div.tx_ezqueries_input', function(){
	$(this).find('div.tx_ezqueries_image_delete').hide();
	});*/

	// Delete input function
	$('body').on('click', 'div.tx_ezqueries_image_delete', function() {
		$(this).parent().find('input.tx_ezqueries_input').val('').trigger('change');
		$(this).parent().next().val('').trigger('change');
		$(this).parent().find('input.tx_ezqueries_input_hidden').val('').trigger('change');
		$(this).parent().find('textarea.tx_ezqueries_textarea').val('').trigger('change');

	});

	// Show help text
	/*$('body').on('click', 'div.tx_ezqueries_help_text_icon', function(){
	var helpText = $(this).find('div.tx_ezqueries_help_text').html();
	alert(helpText);
	});*/

	// Checkbox state changer
	$('body').on('click', 'input.tx_ezqueries_checkbox', function() {
		var $hiddenCheckbox = $(this).parent().find('input.tx_ezqueries_checkbox_hidden');

		if($hiddenCheckbox.val() == 1) {
			$hiddenCheckbox.val(0);
		} else {
			$hiddenCheckbox.val(1);
		}
	});

	// Form validation
	$('form.tx_ezqueries_form').validate();

	$('body').on('change keypress paste focus textInput input', 'input.tx_ezqueries_input', function() {
		$(this).valid();
	});

	$('body').on('change keypress paste focus textInput input', 'textarea.tx_ezqueries_textarea', function() {
		$(this).valid();
	});

	// Full-text search
	$('body').on('change', 'input.tx_ezqueries_input_search', function() {
		$(this).parent().find('input.tx_ezqueries_input_hidden').val($(this).val());
	});

	// Ajax upload
	$('body').on('focus', 'input.tx_ezqueries_input_upload', function() {
		var $this = $(this);
		var extensions = '';
		var extensionlist = '';
		var maxSize = 0;
		var minSize = 0;

		if($this.hasClass('tx_ezqueries_input_upload_image')) {
			extensions = 'images';
			extensionList = '.jpg, .jpeg, .gif, .png';
			maxSize = 5000000;
			minSize = 1;
		}
		if($this.hasClass('tx_ezqueries_input_upload_document')) {
			extensions = 'document';
			extensionList = '.pdf, .txt, .doc';
			maxSize = 8000000;
			minSize = 1;
		}
		var uploader = new qq.FileUploader({
			element : $this.parent().parent().find('.tx_ezqueries_upload_button')[0],
			action : $this.parent().parent().attr('class'),
			params : {
				folder : uploadFolderPath,
				allowedExtensions : extensions
			},
			sizeLimit : maxSize,
			minSizeLimit : minSize,
			onComplete : function(id, fileName, responseJSON) {
				if(responseJSON['success'] == true)
					$this.val(uploadFolder + responseJSON['fileName']);
				if( typeof (window.ezqueries_success) == 'function') {
					ezqueries_success();
				}
			},
			messages : {
				typeError : uploadErrors['typeError'] + "\n" + extensionList,
				sizeError : uploadErrors['sizeError'] + " " + maxSize / 1000000 + " MB.",
				minSizeError : uploadErrors['minSizeError'] + " " + minSize + " byte.",
				emptyError : uploadErrors['emptyError'],
				onLeave : uploadErrors['onLeave']
			}
		});
	});

	// History plugin
	$.history.init(function(hash) {
		if(/^!/.test(hash)) {
			hash = hash.replace('!', '');
			loadContent(hash, true, null, false);
		} else {
			loadContent(window.location.href, false, null, init);
		}
	}, {
		unescape : ",/"
	});

	// Onclick event for ezqueries links
	$('body').on('click', 'a.tx_ezqueries_link', function(event) {
		stopDefault(event);

		if($(this).closest('div#tx_ezqueries_popup').length == 0) {
			closestAjaxContainer = $(this).closest('div#tx_ezqueries_main');

			if($(this).closest('div.tx_ezqueries_ajax_content').length != 0) {
				closestAjaxContainer = $(this).closest('div.tx_ezqueries_ajax_content');
			}
			if($(this).closest('.tx_ezqueries_ajax_reload').length != 0) {
				closestAjaxContainer = $(this).closest('.tx_ezqueries_ajax_reload');
			}
			if($(this).parent().attr('data-ezqueries-closest-container') != null) {
				closestAjaxContainer = $(this).parent().attr('data-ezqueries-closest-container');
			}
			if($(this).parent().attr('data-ezqueries-closest') != null) {
				containerName = $(this).parent().attr('data-ezqueries-closest');
				closestAjaxContainer = $(this).closest(containerName);
			}
		}

		// Get container where to load the response HTML of the ajax request
		if($(this).attr('data-ezqueries-link-target') != null) {
			var linkTarget;
			var linkTarget_function = $(this).attr('data-ezqueries-link-target');
			var fn = window[linkTarget_function];
			if( typeof fn == 'function') {
				linkTarget = fn();
			} else {
				linkTarget = $(this).attr('data-ezqueries-link-target');
			}
			if($('body').find($(linkTarget)).length != 0) {
				var container = $('body').find($(this).attr('data-ezqueries-link-target'));
			} else {
				var container = $(this).closest('div#tx_ezqueries_main');
			}
		} else {
			if($(this).closest('div.tx_ezqueries_ajax_content').length != 0 && !$(this).hasClass('tx_ezqueries_link_popup') && !$(this).hasClass('tx_ezqueries_link_delete')) {
				var container = $(this).closest('div.tx_ezqueries_ajax_content');
			} else {
				var container = $(this).closest('div#tx_ezqueries_main');

				if($(this).hasClass('tx_ezqueries_link_search')) {
					container = $('body').find('div#tx_ezqueries_searchform');
				}

				if($(this).hasClass('tx_ezqueries_link_delete') || $(this).hasClass('tx_ezqueries_link_popup')) {
					container = $('body').find('div#tx_ezqueries_popup');
				}
			}
		}

		// Use additional container?
		if($(container).attr('id') == 'tx_ezqueries_main') {
			useContainer = false;
		} else {
			useContainer = true;
		}

		// Cache link
		var cachedLink = $(this);

		// Get URL
		var url = cachedLink.attr('href');
		//var setUrl = url.substr(url.lastIndexOf('/') + 1);
		var setUrl = url;

		if(url === undefined) {
			return;
		}

		// Set URL to container
		$(container).attr('data-ezqueries-ajax-url', setUrl);

		// Abort on this point if link was abort link in a container
		if(cachedLink.hasClass('tx_ezqueries_link_abort') && useContainer) {
			$(container).html('');
			if($('div#tx_ezqueries_popup').data('ui-dialog') !== undefined) {
				$('div#tx_ezqueries_popup').dialog('close');
			}
			return;
		}

		// Load JSON if its a ezqueries-json-link
		if($(cachedLink).attr('data-ezqueries-json') == 'true') {
			//url = url.substr(url.lastIndexOf('/') + 1);
			url = Base64.decode(url);
			$.ajax({
				type : "GET",
				url : url + '&type=527',
				dataType : "json",
				cache : false,
				success : function(data, textStatus, jqXHR) {
					if( typeof (window.ezqueries_json) == 'function') {
						ezqueries_json(data, null, $(cachedLink).attr('data-ezqueries-json-id'));
					}
				},
				error : function(jqXHR, textStatus, errorThrown) {
					if( typeof (window.ezqueries_error) == 'function') {
						ezqueries_error(jqXHR, textStatus, errorThrown);
					}
				}
			});
			return;
		}

		// Show loading icon
		if($(container).find('div.tx_ezqueries_loadingscreen').length == 0) {
			$(container).append('<div class="tx_ezqueries_loadingscreen"></div>');
		}
		$(container).find('div.tx_ezqueries_loadingscreen').show();
		$('body').css('cursor', 'progress');

		// Remove TinyMCE controls
		$('body').find('.tx_ezqueries_textarea_editor').each(function() {
			var id = $(this).attr('id');
			if(tinyMCE.get(id) !== undefined) {
				tinyMCE.execCommand('mceRemoveControl', true, id);
			}
		});

		// Load content (no additional container)
		if(!useContainer) {
			if(cachedLink.hasClass('tx_ezqueries_link_redirect')) {
				// Redirect to new page
				//url = url.substr(url.lastIndexOf('/') + 1);
				url = Base64.decode(url);

				if(navigator.appName == 'Microsoft Internet Explorer') {
					var ieLocation = document.createElement('a');
					ieLocation.href = url;
					document.body.appendChild(ieLocation);
					ieLocation.click();
					return;
				} else {
					window.location.href = url;
					return;
				}
			} else {
				// Load new page content and set deep link hash in the browser history
				$.history.load('!' + url);
				return;
			}
		}

		// Load content (additional container)
		//url = url.substr(url.lastIndexOf('/') + 1);
		var ajaxUrl = url;
		url = Base64.decode(url);

		$.ajax({
			type : "GET",
			url : url + '&type=526',
			dataType : "html",
			cache : false,
			success : function(html, status) {
				// On delete -  reload closest ajax container
				if(cachedLink.hasClass('tx_ezqueries_link_delete') && cachedLink.hasClass('tx_ezqueries_link_delete_confirmed')) {
					if($(html).find('div.tx_ezqueries_delete_error').length == 0) {
						if($(closestAjaxContainer).attr('data-ezqueries-delete-function') != null) {
							var delete_function = $(closestAjaxContainer).attr('data-ezqueries-delete-function');
							var fn = window[delete_function];
							if( typeof fn == 'function') {
								fn(closestAjaxContainer);
								// Close popup
								$('div#tx_ezqueries_popup').html('');
								if($('#tx_ezqueries_popup').data('ui-dialog') !== undefined) {
									$('div#tx_ezqueries_popup').dialog('close');
								}
							}
						} else {
							var ajaxUrl = $(closestAjaxContainer).attr('data-ezqueries-ajax-url');
							// Close popup
							$('div#tx_ezqueries_popup').html('');
							if($('#tx_ezqueries_popup').data('ui-dialog') !== undefined) {
								$('div#tx_ezqueries_popup').dialog('close');
							}
							// Reload content in the closest ajax container
							reloadContent(closestAjaxContainer);
						}
					} else {
						$('div#tx_ezqueries_popup').html($(html).find('div#tx_ezqueries_main').html());
					}
				} else {
					var dlgClass = '';
					if(cachedLink.hasClass('tx_ezqueries_link_delete')) {
						dlgClass = 'warning'
					}
					if(cachedLink.hasClass('tx_ezqueries_link_search')) {
						$(container).html($(html).find('div#tx_ezqueries_searchform').html());
					} else {
						$(container).html($(html).find('div#tx_ezqueries_main').html());
					}

					// Open Container as Popup
					if($(container).attr('id') == 'tx_ezqueries_popup') {
						$(container).dialog({
							title : '' + $(container).find('h2').first().hide().text(),
							dialogClass : dlgClass,
							resizable : false,
							modal : true,
							draggable : false,
							minHeight : 20,
							width : 'auto',
							close : function(event, ui) {
								$(container).html('');
								$(container).hide();
							}
						});
					} else {
						$(container).show();
					}
					if( typeof (window.ezqueries_success) == 'function') {
						ezqueries_success();
					}
					loadContentSuccess(container);
				}
			},
			error : function(jqXHR, textStatus, errorThrown) {
				if( typeof (window.ezqueries_error) == 'function') {
					ezqueries_error(jqXHR, textStatus, errorThrown);
				}
			}
		});
		$('body').css('cursor', 'default');
	});

	// Onsubmit event for ezqueries forms
	$('body').on('click', 'input.tx_ezqueries_submit', function(event) {
		stopDefault(event);

		var isSearch = false;

		// Get container which should be updated on submit
		var update_container = false;
		if($(this).closest('div#tx_ezqueries_popup').length != 0) {
			update_container = closestAjaxContainer;
		}

		// Get container where to load the response HTML of the ajax request
		var container = $(this).closest('div#tx_ezqueries_main');
		var useContainer = false;

		if($(this).closest('div#tx_ezqueries_popup').length != 0) {
			container = $(this).closest('div#tx_ezqueries_popup');
		} else {
			if($(this).closest('div.tx_ezqueries_ajax_content').length != 0) {
				container = $(this).closest('div.tx_ezqueries_ajax_content');
			}
		}

		if($(this).hasClass('tx_ezqueries_submit_search')) {
			container = $('div#tx_ezqueries_main');
			isSearch = true;
		}

		if($(this).parent().attr('data-ezqueries-submit-target') != null) {
			container = $(this).parent().attr('data-ezqueries-submit-target');
		}

		// Use additional container?
		if($(container).attr('id') == 'tx_ezqueries_main') {
			useContainer = false;
		} else {
			useContainer = true;
		}

		// Get TinyMCE values
		$('body').find('div.tx_ezqueries_textarea_editor').each(function() {
			var id = $(this).attr('id');
			if(tinyMCE.get(id) !== undefined) {
				var content = tinyMCE.get(id).getContent();
				var name = $(this).parent().find('textarea').attr('name');
				$(this).parent().find('textarea').val(content);
			}
			tinyMCE.execCommand('mceRemoveControl', true, id);
		});

		// Is data valid?
		if($(this).closest('form.tx_ezqueries_form').valid()) {
			if( typeof (window.ezqueries_form_valid) == 'function') {
				ezqueries_form_valid($(this));
			}

			// Show loading icon
			if($(container).find('div.tx_ezqueries_loadingscreen').length == 0) {
				$(container).append('<div class="tx_ezqueries_loadingscreen"></div>');
			}
			$(container).find('div.tx_ezqueries_loadingscreen').show();
			$('body').css('cursor', 'progress');

			// Cache submit input
			var cachedSubmit = $(this);
			var url = cachedSubmit.attr('name');

			// Ajax
			//url = url.substr(url.lastIndexOf('/') + 1);
			url = Base64.decode(url);

			// Data
			if($(cachedSubmit).hasClass('tx_ezqueries_empty_form')) {
				var data = '';
			} else {
				var data = cachedSubmit.closest('form.tx_ezqueries_form').serializeArray();
			}

			$.ajax({
				type : "POST",
				url : url + '&type=526',
				data : data,
				dataType : "html",
				cache : false,
				success : function(html, status, jqXHR) {

					// Redirect to another page
					if($(container).find('div.tx_ezqueries_redirect_after_submit').length > 0) {
						var cachedMain = $(html).find('div#tx_ezqueries_main');
						var mainUrl = $(cachedMain).attr('data-ezqueries-ajax-url');
						//mainUrl = mainUrl.substr(mainUrl.lastIndexOf('/') + 1);
						mainUrl = Base64.decode(mainUrl);

						// IE fix
						if(navigator.appName == 'Microsoft Internet Explorer') {
							var ieLocation = document.createElement('a');
							ieLocation.href = mainUrl;
							document.body.appendChild(ieLocation);
							ieLocation.click();
							return;
						} else {
							window.location.href = mainUrl;
							return;
						}
					}

					// Additional container?
					if(useContainer) {

						// Form submit successful
						if($(html).find('div.tx_ezqueries_ajaxupdate').length != 0) {
							// Update container
							if($(cachedSubmit).parent().attr('data-ezqueries-update-container') != null) {
								if($(cachedSubmit).parent().attr('data-ezqueries-update-container') == 'closest') {
									update_container = closestAjaxContainer;
								} else {
									if($(cachedSubmit).parent().attr('data-ezqueries-update-container') == 'none') {
										update_container = false;
									} else {
										update_container = $($(cachedSubmit).parent().attr('data-ezqueries-update-container'));
									}
								}
							}
							if(update_container != false) {
								if($(update_container).hasClass('tx_ezqueries_ajax_content') || $(update_container).hasClass('tx_ezqueries_ajax_reload') || $(update_container).attr('id') == 'tx_ezqueries_main') {
									reloadContent(update_container);
								} else {
									loadContentElements(update_container);
								}
							}

							// Load content
							$(container).html($(html).find('div#tx_ezqueries_main').html());
							loadContentElements(container);

							if( typeof (window.ezqueries_save_success) == 'function') {
								var message = '';
								$(html).find('.tx_ezqueries_message').each(function() {
									message += $(this).html();
									$(this).html('');
								});
								ezqueries_save_success(message, cachedSubmit);
							}
							if($(cachedSubmit).parent().attr('data-ezqueries-success-function') != null) {
								var success_function = $(cachedSubmit).parent().attr('data-ezqueries-success-function');
								var fn = window[success_function];
								if( typeof fn == 'function') {
									fn($(html).find('div#tx_ezqueries_main').html(), closestAjaxContainer);
								}
							}
						} else {// Save error or search result
							// Load content
							$(container).html($(html).find('div#tx_ezqueries_main').html());
							loadContentElements(container);

							if(isSearch == true) {
								if( typeof (window.ezqueries_success) == 'function') {
									ezqueries_success();
								}
								$(container).attr('data-ezqueries-ajax-url', $(html).find('div#tx_ezqueries_main').attr('data-ezqueries-ajax-url'));
							} else {
								if( typeof (window.ezqueries_save_error) == 'function') {
									var message = '';
									$(html).find('.tx_ezqueries_message').each(function() {
										message += $(this).html();
										$(this).html('');
									});
									ezqueries_save_error(message, cachedSubmit);
								}
							}
						}
					} else {
						//$(container).html($(html).find('div#tx_ezqueries_main').html());
						var cachedMain = $(html).find('div#tx_ezqueries_main');
						var mainUrl = $(cachedMain).attr('data-ezqueries-ajax-url');
						$.history.load('!' + mainUrl);

						if($('div#tx_ezqueries_main').find('div.tx_ezqueries_loadingscreen').length != 0) {
							$('div#tx_ezqueries_main').find('div.tx_ezqueries_loadingscreen').hide();
						}

						if($(html).find('div.tx_ezqueries_ajaxupdate').length != 0) {
							if( typeof (window.ezqueries_save_success) == 'function') {
								var message = '';
								$(html).find('.tx_ezqueries_message').each(function() {
									message += $(this).html();
									$(this).html('');
								});
								ezqueries_save_success(message, cachedSubmit);
							}
							if($(cachedSubmit).parent().attr('data-ezqueries-success-function') != null) {
								var success_function = $(cachedSubmit).parent().attr('data-ezqueries-success-function');
								var fn = window[success_function];
								if( typeof fn == 'function') {
									fn($(html).find('div#tx_ezqueries_main').html(), closestAjaxContainer);
								}
							}
						} else {
							if( typeof (window.ezqueries_save_error) == 'function') {
								var message = '';
								$(html).find('.tx_ezqueries_message').each(function() {
									message += $(this).html();
									$(this).html('');
								});
								ezqueries_save_error(message, cachedSubmit);
							}
						}
					}

					if($(cachedSubmit).parent().attr('data-ezqueries-close-popup') == 'false') {
						// Dont close popup
					} else {
						// Close popup
						if($('#tx_ezqueries_popup').data('ui-dialog') !== undefined) {
							$('div#tx_ezqueries_popup').dialog('close');
						}
					}

					$('body').css('cursor', 'default');
				},
				error : function(jqXHR, textStatus, errorThrown) {
					if( typeof (window.ezqueries_error) == 'function') {
						ezqueries_error(jqXHR, textStatus, errorThrown);
					}
				}
			});
		} else {
			if( typeof (window.ezqueries_form_not_valid) == 'function') {
				ezqueries_form_not_valid($(this));
			}
		}
	});
});

function loadContent(ajaxUrl, decodeUrl, ajaxContainer, isInit) {
	(function($) {
		var container;

		if(ajaxContainer != null) {
			container = ajaxContainer;
		} else {
			container = $('body').find('div#tx_ezqueries_main');
		}

		if($(container).find('div.tx_ezqueries_loadingscreen').length == 0) {
			$(container).append('<div class="tx_ezqueries_loadingscreen"></div>');
		}
		$(container).find('div.tx_ezqueries_loadingscreen').show();

		var url = ajaxUrl;
		if(decodeUrl == true) {
			//ajaxUrl = ajaxUrl.substr(ajaxUrl.lastIndexOf('/') + 1);
			url = Base64.decode(ajaxUrl);
			url = url + '&type=526';
		}

		if(isInit == true) {
			if( typeof (window.ezqueries_success) == 'function') {
				ezqueries_success();
			}
			$(container).find('div.tx_ezqueries_loadingscreen').remove();
			loadContentSuccess(container);
		} else {
			$.ajax({
				type : "GET",
				url : url,
				dataType : "html",
				cache : false,
				success : function(html, status, jqXHR) {
					$(container).html($(html).find('div#tx_ezqueries_main').html());
					$(container).attr('data-ezqueries-ajax-url', ajaxUrl);
					$(container).show();
					if( typeof (window.ezqueries_success) == 'function') {
						ezqueries_success();
					}
					loadContentSuccess(container);
				},
				error : function(jqXHR, textStatus, errorThrown) {
					if( typeof (window.ezqueries_error) == 'function') {
						ezqueries_error(jqXHR, textStatus, errorThrown);
					}
				}
			});
		}
		$('body').css('cursor', 'default');
	})(jQuery);
}

function reloadContent(ajaxContainer) {
	(function($) {
		var container = ajaxContainer;

		if($(container).find('div.tx_ezqueries_loadingscreen').length == 0) {
			$(container).append('<div class="tx_ezqueries_loadingscreen"></div>');
		}
		$(container).find('div.tx_ezqueries_loadingscreen').show();

		var url = $(container).attr('data-ezqueries-ajax-url');
		if(url.indexOf("php") == -1) {
			//url = url.substr(url.lastIndexOf('/') + 1);
			url = Base64.decode(url);
			url = url + '&type=526';
		}

		$.ajax({
			type : "GET",
			url : url,
			dataType : "html",
			cache : false,
			success : function(html, status) {
				$(container).html($(html).find('div#tx_ezqueries_main').html());
				if( typeof (window.ezqueries_success) == 'function') {
					ezqueries_success();
				}
				loadContentSuccess(container);
			},
			error : function(jqXHR, textStatus, errorThrown) {
				if( typeof (window.ezqueries_error) == 'function') {
					ezqueries_error(jqXHR, textStatus, errorThrown);
				}
			}
		});
		$('body').css('cursor', 'default');
	})(jQuery);
}

function loadContentSuccess(container) {
	// Get Content
	loadContentElements(container);
}

function loadContentElements(container, areas) {
	(function($) {

		// Filter for select
		$(container).find('.tx_ezqueries_select').each(function() {
			$(this).filterByText($(this).closest('.tx_ezqueries_select_wrapper').find('input.tx_ezqueries_select_filter_input'), true);
		});

		// Get Content via Ajax
		var isReplace = false;

		if(areas !== undefined) {
			var contentAreas;
			var ids = '';
			var counter = 0;

			$.each(areas, function() {
				if(counter !== 0) {
					ids += ',';
				}
				ids += '#' + $(this).attr('id');
				counter++;
			});
			contentAreas = $(container).find(ids);

		} else {
			var contentAreas = $(container).find('.tx_ezqueries_ajax_content');
		}

		$.each(contentAreas, function() {
			var contentArea = $(this);
			if(($(contentArea).is(':visible') && $(contentArea).attr('data-ezqueries-ajax-loading') == 'onShow') || $(contentArea).attr('data-ezqueries-ajax-loading') != 'onShow') {
				if($(contentArea).attr('data-ezqueries-ajax-loading') != 'false') {
					if($(contentArea).attr('data-ezqueries-ajax-loading') != 'true') {
						if($(contentArea).find('div.tx_ezqueries_loadingscreen_content').length == 0) {
							$(contentArea).append('<div class="tx_ezqueries_loadingscreen_content ' + $(contentArea).attr('data-ezqueries-ajax-loading') + '"></div>');
						}
					} else {
						if($(contentArea).find('div.tx_ezqueries_loadingscreen_content').length == 0) {
							$(contentArea).append('<div class="tx_ezqueries_loadingscreen_content"></div>');
						}
					}

					$(contentArea).find('div.tx_ezqueries_loadingscreen_content').show();
				}

				var url = $(this).attr('data-ezqueries-ajax-url');
				var originalUrl = $(this).attr('data-ezqueries-ajax-url');
				//url = url.substr(url.lastIndexOf('/') + 1);
				url = Base64.decode(url);

				var childAreas = new Array();
				var parent;

				if($(contentArea).attr('data-ezqueries-json') == 'true') {
					$.ajax({
						type : "GET",
						url : url + '&type=527',
						dataType : "json",
						cache : false,
						success : function(data, textStatus, jqXHR) {
							if( typeof (window.ezqueries_json) == 'function') {
								ezqueries_json(data, contentArea, $(contentArea).attr('data-ezqueries-json-id'));
							}
						},
						error : function(jqXHR, textStatus, errorThrown) {
							if( typeof (window.ezqueries_error) == 'function') {
								ezqueries_error(jqXHR, textStatus, errorThrown);
							}
						}
					});
				} else {
					$.ajax({
						type : "GET",
						url : url + '&type=526',
						dataType : "html",
						cache : false,
						success : function(html, status) {
							if($(html).find('div#tx_ezqueries_main').length != 0) {
								if($(contentArea).attr('data-ezqueries-replace') == 'true') {
									parent = $(contentArea).parent();
									$(contentArea).html($(html).find('div#tx_ezqueries_main').html());
									childAreas = $(contentArea).find('.tx_ezqueries_ajax_content');
									$(contentArea).replaceWith(function() {
										return $(this).html();
									});
									isReplace = true;
									$(parent).attr('data-ezqueries-ajax-url', originalUrl);
								} else {
									$(contentArea).html($(html).find('div#tx_ezqueries_main').html());
								}
							} else {
								if($(contentArea).attr('data-ezqueries-replace') == 'true') {
									$(contentArea).replaceWith($(html).find('div#tx_ezqueries_searchform').html());
								} else {
									$(contentArea).html($(html).find('div#tx_ezqueries_searchform').html());
								}
							}

							$(contentArea).attr('data-ezqueries-ajax-content-loaded', 'true');
							if( typeof (window.ezqueries_success) == 'function') {
								ezqueries_success();
							}

							if(isReplace == true && childAreas.length > 0) {
								loadContentElements(parent, childAreas);
							} else {
								loadContentElements(contentArea);
							}

						},
						error : function(jqXHR, textStatus, errorThrown) {
							if( typeof (window.ezqueries_error) == 'function') {
								ezqueries_error(jqXHR, textStatus, errorThrown);
							}
						}
					});

				}
			}
		});

	})(jQuery);
}

function stopDefault(evt) {
	if(evt && evt.preventDefault) {
		evt.preventDefault();
	} else if(window.event && window.event.returnValue) {
		window.event.returnValue = false;
	}
}
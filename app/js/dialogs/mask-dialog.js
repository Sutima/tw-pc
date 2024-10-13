const maskFunctions = {
		getMasks: function() {
			// Get masks
			$.ajax({
				url: "masks.php",
				type: "POST",
				dataType: "JSON"
			}).done(function(response) {
				if (response && response.masks) {
					tripwire.masks = response.masks;
					maskRendering.update(tripwire.masks);
					$("#dialog-masks #masks #default").html("");
					$("#dialog-masks #masks #owned").html("");
					$("#dialog-masks #masks #invited").html("");
					
					const iconBar = mask => 
						'<span class="icon_bar">' +
						(mask.optional ? '<a href="#" class="icon ' + (mask.joined ? 'closeIcon' : 'joinIcon') + '" data-tooltip="' + (mask.joined ? 'Remove from quick switch' : 'Add to quick switch') + '">' +
							(mask.joined ? '×' : '+') + '</a>' : '')
						+ '</span>';


					for (var x in response.masks) {
						var mask = response.masks[x];
						var node = $(''
							+ '<input type="radio" name="mask" id="mask'+x+'" value="'+mask.mask+'" class="selector" data-owner="'+mask.owner+'" data-admin="'+mask.admin+'" />'
							+ '<label for="mask'+x+'"><img src="'+mask.img+'" />'
							+ iconBar(mask)
							+ '<span class="source_bar ' + mask.joinedBy+'">&nbsp;</span>'
							+ '<span class="selector_label">'+maskRendering.renderMask(mask)
								+ ((mask.joined || mask.owner) ? ' <i data-icon="star" data-tooltip="On quick switch"></i>' : '')
							+ '</span></label>');

						$("#dialog-masks #masks #"+mask.type).append(node);
					}

					var node = $(''
						+ '<input type="checkbox" name="find" id="findp" value="personal" class="selector" disabled="disabled" />'
						+ '<label for="findp"><i data-icon="search" style="font-size: 3em; margin-left: 16px; margin-top: 16px; display: block;"></i>'
						+ '<span class="source_bar personal">&nbsp;</span>'
						+ '<span class="selector_label">Join (Just me)</span></label>');
					
					$("#dialog-masks #masks #invited").append(node);

					if (init.admin == "1") {
						var node = $(''
							+ '<input type="checkbox" name="find" id="findc" value="corporate" class="selector" disabled="disabled" />'
							+ '<label for="findc"><i data-icon="search" style="font-size: 3em; margin-left: 16px; margin-top: 16px; display: block;"></i>'
							+ '<span class="source_bar corporation">&nbsp;</span>'
							+ '<span class="selector_label">Join (Corp)</span></label>');
						
						$("#dialog-masks #masks #invited").append(node);
					}
					
					const activeMask = response.masks.find(x => x.active);
					$("#dialog-masks input[name='mask']").filter("[value='"+activeMask.mask+"']").attr("checked", true).trigger("change");

					// toggle mask admin icon
					document.getElementById('admin').style.display = activeMask.admin ? '' : 'none';
					
					// fix tooltips for new elements
					Tooltips.attach($("#dialog-masks").find("[data-tooltip]"));
				}
			});
		}, 
		updateActiveMask: function(newActive, afterFunction) {
				var maskChange = false;
				if (options.masks.active != newActive) {
					maskChange = true;
					options.masks.active = newActive;
					maskRendering.update(tripwire.masks, newActive);
				}

				options.save() // Performs AJAX
					.done(function() {
						if (maskChange) {
							// Reset signatures
							$("#sigTable span[data-age]").countdown("destroy");
							$("#sigTable tbody").empty()
							$("#signature-count").html(0);
							tripwire.signatures.list = {};
							tripwire.client.signatures = [];

							tripwire.refresh('change');
						}
						if(afterFunction) { afterFunction(); }
					});		
		},
};

$("#dialog-masks").dialog({
	autoOpen: false,
	width: 450,
	minHeight: 400,
	modal: true,
	buttons: {
		Save: function() {
			maskFunctions.updateActiveMask($("#dialog-masks input[name='mask']:checked").val(), () => {
				// toggle mask admin icon
				document.getElementById('admin').disabled = !$("#dialog-masks input[name='mask']:checked").data("admin");			
				$(this).dialog("close");
			});
		},
		Close: function() {
			$(this).dialog("close");
		}		
	}, 

		open: function() { maskFunctions.getMasks(); }
	
});

$("#mask-menu-link").click(function(e) {
	e.preventDefault();
	const elem = document.getElementById('mask-menu');
	elem.style.display = elem.style.display == 'none' ? '' : 'none';
});

$("#mask-link").click(function(e) {
	e.preventDefault();

	if ($(this).hasClass("disabled"))
		return false;
	
	$("#dialog-masks").dialog('open');
 });
 
 			// Mask selections
			$("#masks").on("change", "input.selector:checked", function() {
				if ($(this).data("owner")) {
					$("#maskControls #edit").removeAttr("disabled");
					$("#maskControls #delete").removeAttr("disabled");
				} else {
					$("#maskControls #edit").attr("disabled", "disabled");
					$("#maskControls #delete").attr("disabled", "disabled");
				}

				if ($(this).val() != 0.0 && $(this).val().split(".")[1] == 0) {
					$("#dialog-masks #leave").removeAttr("disabled");
				} else {
					$("#dialog-masks #leave").attr("disabled", "disabled");
				}
			});

const joinMask = mask => {
	const completeFunction = () => {
						$.ajax({
							url: "masks.php",
							type: "POST",
							data: {mask: mask, mode: "join"},
							dataType: "JSON"
						}).done(function(response) {
							if (response && response.result) {
								maskFunctions.getMasks();
								$("#dialog-joinMask").dialog("close");
							}
						});	
	};
	
	completeFunction(); // todo
};

			// Mask join
			$("#dialog-joinMask").dialog({
				autoOpen: false,
				resizable: false,
				dialogClass: "ui-dialog-shadow dialog-noeffect dialog-modal",
				buttons: {
					Add: function() {
						var mask = $("#dialog-joinMask #results input:checked");
						joinMask(mask.val());
					},
					Cancel: function() {
						$(this).dialog("close");
					}
				},
				create: function() {
					$("#dialog-joinMask form").submit(function(e) {
						e.preventDefault();

						$("#dialog-joinMask #results").html("");
						$("#dialog-joinMask #loading").show();
						$("#dialog-joinMask input[type='submit']").attr("disabled", "disabled");

						$.ajax({
							url: "masks.php",
							type: "POST",
							data: $(this).serialize(),
							dataType: "JSON"
						}).then(function(response) {
							if (response && response.results && response.results.length) {
								return tripwire.esi.fullLookup(response.eveIDs)
									.done(function(results) {
										if (results) {
											for (var x in results) {
												var mask = response.results[x];
												var node = $(''
													+ '<input type="radio" name="mask" id="mask'+mask.mask+'" value="'+mask.mask+'" class="selector" data-owner="false" data-admin="'+mask.admin+'" />'
													+ '<label for="mask'+mask.mask+'" style="width: 100%; margin-left: -5px;">'
													+ '	<img src="'+mask.img+'" />'
													+ '	<span class="selector_label">'+mask.label+'</span>'
													+ '	<div class="info">'
													+ '		'+results[x].name + '<br/>'
													+ '		'+(results[x].category == "character" ? results[x].corporation.name +'<br/>' : null)
													+ '		'+(results[x].alliance ? results[x].alliance.name : '')+'<br/>'
													+ '	</div>'
													+ '</label>');

												$("#dialog-joinMask #results").append(node);
											}
										}
									});
							} else if (response && response.error) {
								$("#dialog-error #msg").text(response.error);
								$("#dialog-error").dialog("open");
							} else {
								$("#dialog-error #msg").text("Unknown error");
								$("#dialog-error").dialog("open");
							}
						}).then(function() {
							$("#dialog-joinMask #loading").hide();
							$("#dialog-joinMask input[type='submit']").removeAttr("disabled");
						});
					})
				},
				close: function() {
					$("#dialog-joinMask #results").html("");
					$("#dialog-joinMask input[name='name']").val("");
				}
			});

			$("#dialog-masks #masks").on("click", "input[name='find']+label", function() {
				$("#dialog-joinMask input[name='find']").val($(this).prev().val());
				$("#dialog-joinMask").dialog("open");
			});

			// Mask leave
			$("#dialog-masks #masks").on("click", ".closeIcon", function() {
							var mask = $(this).closest("input.selector+label").prev();
							var send = {mode: "leave", mask: mask.val()};
							$.ajax({
								url: "masks.php",
								type: "POST",
								data: send,
								dataType: "JSON"
							}).done(function(response) {
								if (response && response.result) {
									maskFunctions.getMasks();

									$("#dialog-confirm").dialog("close");
								} else {
									$("#dialog-confirm").dialog("close");

									$("#dialog-error #msg").text("Unable to delete");
									$("#dialog-error").dialog("open");
								}
							});						
			});

			// Mask delete
			$("#maskControls #delete").click(function() {
				var mask = $("#masks input.selector:checked");
				$("#dialog-confirm #msg").text("Are you sure you want to delete this mask?");
				$("#dialog-confirm").dialog("option", {
					buttons: {
						Delete: function() {
							var send = {mode: "delete", mask: mask.val()};

							$.ajax({
								url: "masks.php",
								type: "POST",
								data: send,
								dataType: "JSON"
							}).done(function(response) {
								if (response && response.result) {
									maskFunctions.getMasks();
									$("#dialog-confirm").dialog("close");
								} else {
									$("#dialog-confirm").dialog("close");

									$("#dialog-error #msg").text("Unable to delete");
									$("#dialog-error").dialog("open");
								}
							});
						},
						Cancel: function() {
							$(this).dialog("close");
						}
					}
				}).dialog("open");
			});

			// User Create mask
			$("#dialog-createMask").dialog({
				autoOpen: false,
				dialogClass: "ui-dialog-shadow dialog-noeffect dialog-modal",
				buttons: {
					Create: function() {
						$("#dialog-createMask form").submit();
					},
					Cancel: function() {
						$(this).dialog("close");
					}
				},
				create: function() {
					$("#dialog-createMask #accessList").on("click", "#create_add+label", function() {
						$("#dialog-EVEsearch").dialog("open");
					});

					$("#dialog-createMask form").submit(function(e) {
						e.preventDefault();

						$.ajax({
							url: "masks.php",
							type: "POST",
							data: $(this).serialize(),
							dataType: "JSON"
						}).done(function(response) {
							if (response && response.result) {
								maskFunctions.getMasks();

								$("#dialog-createMask").dialog("close");
							} else if (response && response.error) {
								$("#dialog-error #msg").text(response.error);
								$("#dialog-error").dialog("open");
							} else {
								$("#dialog-error #msg").text("Unknown error");
								$("#dialog-error").dialog("open");
							}
						});
					});

					$("#dialog-createMask select").selectmenu({width: 100});
				},
				open: function() {
					$("#dialog-createMask input[name='name']").val("");
					$("#dialog-createMask #accessList :not(.static)").remove();
				}
			});

			$("#maskControls #create").click(function() {
				$("#dialog-createMask").dialog("open");
			});

			$("#dialog-createMask #accessList").on("click", ".maskRemove", function() {
				$(this).closest("input.selector+label").prev().remove();
				$(this).closest("label").remove();
			});

			$("#dialog-editMask").dialog({
				autoOpen: false,
				dialogClass: "ui-dialog-shadow dialog-noeffect dialog-modal",
				buttons: {
					Save: function() {
						$("#dialog-editMask form").submit();
					},
					Cancel: function() {
						$(this).dialog("close");
					}
				},
				create: function() {
					$("#dialog-editMask #accessList").on("click", ".maskRemove", function() {
						$(this).closest("input.selector+label").prev().attr("name", "deletes[]").hide();
						$(this).closest("label").hide();
					});

					$("#dialog-editMask #accessList").on("click", "#edit_add+label", function() {
						$("#dialog-EVEsearch").dialog("open");
					});

					$("#dialog-editMask form").submit(function(e) {
						e.preventDefault();

						$.ajax({
							url: "masks.php",
							type: "POST",
							data: $(this).serialize(),
							dataType: "JSON"
						}).done(function(response) {
							if (response && response.result) {
								$("#dialog-editMask").dialog("close");
							} else if (response && response.error) {
								$("#dialog-error #msg").text(response.error);
								$("#dialog-error").dialog("open");
							} else {
								$("#dialog-error #msg").text("Unknown error");
								$("#dialog-error").dialog("open");
							}
						});
					});
				},
				open: function() {
					var mask = $("#dialog-masks input[name='mask']:checked").val();
					$("#dialog-editMask input[name='mask']").val(mask);
					$("#dialog-editMask #accessList label.static").hide();
					$("#dialog-editMask #loading").show();
					$("#dialog-editMask #name").text($("#dialog-masks input[name='mask']:checked+label .selector_label").text());

					$.ajax({
						url: "masks.php",
						type: "POST",
						data: {mode: "edit", mask: mask},
						dataType: "JSON"
					}).then(function(response) {
						if (response && response.results && response.results.length) {
							return tripwire.esi.fullLookup(response.results)
								.done(function(results) {
									if (results) {
										for (var x in results) {
											if (results[x].category == "character") {
												var node = $(''
													+ '<input type="checkbox" checked="checked" onclick="return false" name="" id="edit_'+results[x].id+'_1373" value="'+results[x].id+'_1373" class="selector" />'
													+ '<label for="edit_'+results[x].id+'_1373">'
													+ '	<img src="https://image.eveonline.com/Character/'+results[x].id+'_64.jpg" />'
													+ '	<span class="selector_label">Character</span>'
													+ '	<div class="info">'
													+ '		'+results[x].name + '<br/>'
													+ '		'+results[x].corporation.name+'<br/>'
													+ '		'+(results[x].alliance ? results[x].alliance.name : '')+'<br/>'
													+ '		<input type="button" class="maskRemove" value="Remove" style="position: absolute; bottom: 3px; right: 3px;" />'
													+ '	</div>'
													+ '</label>');

												$("#dialog-editMask #accessList .static:first").before(node);
											} else if (results[x].category == "corporation") {
												var node = $(''
													+ '<input type="checkbox" checked="checked" onclick="return false" name="" id="edit_'+results[x].id+'_2" value="'+results[x].id+'_2" class="selector" />'
													+ '<label for="edit_'+results[x].id+'_2">'
													+ '	<img src="https://image.eveonline.com/Corporation/'+results[x].id+'_64.png" />'
													+ '	<span class="selector_label">Corporation</span>'
													+ '	<div class="info">'
													+ '		'+results[x].name+'<br/>'
													+ '		'+(results[x].alliance ? results[x].alliance.name : '')+'<br/>'
													+ '		<input type="button" class="maskRemove" value="Remove" style="position: absolute; bottom: 3px; right: 3px;" />'
													+ '	</div>'
													+ '</label>');

												$("#dialog-editMask #accessList .static:first").before(node);
											}
										}
									}
								});
						}
					}).then(function(response) {
						$("#dialog-editMask #accessList label.static").show();
						$("#dialog-editMask #loading").hide();
					});
				},
				close: function() {
					$("#dialog-editMask #accessList :not(.static)").remove();
				}
			});

			// EVE search dialog
			$("#dialog-EVEsearch").dialog({
				autoOpen: false,
				dialogClass: "ui-dialog-shadow dialog-noeffect dialog-modal",
				buttons: {
					Add: function() {
						if ($("#accessList input[value='"+$("#EVESearchResults input").val()+"']").length) {
							$("#dialog-error #msg").text("Already has access");
							$("#dialog-error").dialog("open");
							return false;
						}

						$("#EVESearchResults .info").append('<input type="button" class="maskRemove" value="Remove" style="position: absolute; bottom: 3px; right: 3px;" />');
						$("#EVESearchResults input:checked").attr("checked", "checked");
						$("#EVESearchResults input:checked").attr("onclick", "return false");

						var nodes = $("#EVESearchResults .maskNode:has(input:checked)");

						if ($("#dialog-createMask").dialog("isOpen"))
							$("#dialog-createMask #accessList .static:first").before(nodes);
						else if ($("#dialog-editMask").dialog("isOpen"))
							$("#dialog-editMask #accessList .static:first").before(nodes);

						$(this).dialog("close");
					},
					Close: function() {
						$(this).dialog("close");
					}
				},
				create: function() {
					$("#EVEsearch").submit(function(e) {
						e.preventDefault();

						if ($("#EVEsearch input[name='name']").val() == "") {
							return false;
						}

						$("#EVESearchResults, #searchCount").html("");
						$("#EVEsearch #searchSpinner").show();
						$("#EVEsearch input[type='submit']").attr("disabled", "disabled");
						$("#dialog-EVEsearch").parent().find(".ui-dialog-buttonpane button:contains('Add')").attr("disabled", true).addClass("ui-state-disabled");

						tripwire.esi.search($("#EVEsearch input[name='name']").val(), $("#EVEsearch input[name='category']:checked").val(), $("#EVEsearch input[name='exact']")[0].checked)
							.done(function(results) {
								if (results && (results.character || results.corporation)) {
									// limit results
									results = $.merge(results.character || [], results.corporation || []);
									total = results.length;
									results = results.slice(0, 10);
									return tripwire.esi.fullLookup(results)
										.done(function(results) {
											$("#EVEsearch #searchCount").html("Found: "+total+"<br/>Showing: "+(total<10?total:10));
											if (results) {
												for (var x in results) {
													if (results[x].category == "character") {
														var node = $(''
															+ '<div class="maskNode"><input type="checkbox" name="adds[]" id="find_'+results[x].id+'_1373" value="'+results[x].id+'_1373" class="selector" />'
															+ '<label for="find_'+results[x].id+'_1373">'
															+ '	<img src="https://image.eveonline.com/Character/'+results[x].id+'_64.jpg" />'
															+ '	<span class="selector_label">Character</span>'
															+ '	<div class="info">'
															+ '		'+results[x].name + '<br/>'
															+ '		'+results[x].corporation.name+'<br/>'
															+ '		'+(results[x].alliance ? results[x].alliance.name : '')+'<br/>'
															+ '	</div>'
															+ '</label></div>');

														$("#EVESearchResults").append(node);
													} else if (results[x].category == "corporation") {
														var node = $(''
															+ '<div class="maskNode"><input type="checkbox" name="adds[]" id="find_'+results[x].id+'_2" value="'+results[x].id+'_2" class="selector" />'
															+ '<label for="find_'+results[x].id+'_2">'
															+ '	<img src="https://image.eveonline.com/Corporation/'+results[x].id+'_64.png" />'
															+ '	<span class="selector_label">Corporation</span>'
															+ '	<div class="info">'
															+ '		'+results[x].name+'<br/>'
															+ '		'+(results[x].alliance ? results[x].alliance.name : '')+'<br/>'
															+ '	</div>'
															+ '</label></div>');

														$("#EVESearchResults").append(node);
													}
												}
											}
										}).always(function() {
											$("#EVEsearch #searchSpinner").hide();
											$("#EVEsearch input[type='submit']").removeAttr("disabled");
											$("#dialog-EVEsearch").parent().find(".ui-dialog-buttonpane button:contains('Add')").removeAttr("disabled").removeClass("ui-state-disabled");
										});
								} else {
									$("#dialog-error #msg").text("No Results");
									$("#dialog-error").dialog("open");

									$("#EVEsearch #searchSpinner").hide();
									$("#EVEsearch input[type='submit']").removeAttr("disabled");
									$("#dialog-EVEsearch").parent().find(".ui-dialog-buttonpane button:contains('Add')").removeAttr("disabled").removeClass("ui-state-disabled");
								}
							});
					});
				},
				close: function() {
					$("#EVEsearch input[name='name']").val("");
					$("#EVESearchResults, #searchCount").html("");
				}
			});
			
			$("#maskControls #edit").click(function() {
				$("#dialog-editMask").dialog("open");
			});

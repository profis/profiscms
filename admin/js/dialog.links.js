Ext.ns('PC.dialog');
PC.dialog.links = {
	show: function() {
		this.ln = PC.i18n.dialog.links;
		var dialog = this;
		this.bookmark = tinymce.activeEditor.selection.getBookmark('simple');
		this.general = {
			title: this.ln.general,
			ref: '_f',
			layout: 'form',
			padding: '6px 3px 3px 3px',
			border: false,
			autoScroll: true,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 80,
			labelAlign: 'right',
			defaults: {anchor: '96%'},
			items: [
				new Ext.form.TwinTriggerField({
					ref: '../../_href',
					fieldLabel: this.ln.href,
					selectOnFocus: true,
					//triggerClass: 'x-form-link-trigger',
					trigger1Class: 'x-form-folder-trigger',
					//page trigger
					onTrigger1Click: function() {
						//console.log(PC.admin._editor_ln_select.get('db_fld_redirect'));
						var trigger = this;
						var select_node_path = null;
						var ln = false;
						var field_val = this.getValue().match('pc_page:([^:]+)(?::([a-z]+))?');
						if (field_val && field_val.length && field_val.length > 1) {
							select_node_path = field_val[1];
							if (field_val.length > 2 && field_val[2]) {
								ln = field_val[2];
							}
						}
						if (this.getValue() == '') {
							if (PC.global.last_select_page_id && PC.global.last_select_page_lang) {
								select_node_path = PC.global.last_select_page_id;
								ln = PC.global.last_select_page_lang;
								PC.global.last_select_page_id = false;
								PC.global.last_select_page_lang = false;
							}
						}
						Show_redirect_page_window(function(url, lang, page_id){
							PC.global.last_select_page_id = page_id;
							PC.global.last_select_page_lang = lang;
							trigger.setValue(url);
							if (page_id) {
								Ext.Ajax.request({
									url: PC.global.BASE_URL + PC.global.ADMIN_DIR  + '/api/page_anchors/' + page_id + '/' + lang,
									success: function(result){
										var json_result = Ext.util.JSON.decode(result.responseText);
										var s = dialog.window._anchor.getStore();
										Ext.iterate(json_result, function(value, display_value) {
											this.add(new this.recordType({
												anchor: url + '#' + value,
												name: value
											}));
										}, s);
									}
								});
							}
						}, {get_route:true, select_node_path: select_node_path, ln: ln});
					},
					trigger2Class: 'x-form-link-trigger',
					onTrigger2Click: function() {
						var field = this;
						var params = {
							save_fn: function(url){
								field.setValue(url);
								field.fireEvent('change');
							},
							thumbnail_type: null,
							close_after_insert_forced: true
						};
						var src = field.getValue();
						if (/^gallery\//.test(src)) {
							params.select_id = src.substring(src.lastIndexOf('/')+1);
						}
						PC.dialog.gallery.show(params);
					}
					/*gallery trigger
					onTriggerClick: function() {
						var field = this;
						PC.dialog.gallery.show({
							save_fn: function(url){
								field.setValue(url);
							}
						});
					}*/
				}),
				{	ref: '../../_anchor',
					fieldLabel: this.ln.anchor,
					xtype: 'combo', mode: 'local',
					//store: dialog.Get_anchors(),
					store: {
						xtype: 'arraystore',
						fields: ['anchor', 'name'],
						idIndex: 0,
						data: [
							['', ' - ']
						]
					},
					valueField: 'anchor',
					displayField: 'name',
					triggerAction: 'all',
					listeners: {
						change: function(cb, value, old) {
							if (!value) {
								//dialog.window._href.setValue('');
								dialog.window._href.disable();
							}
							if (value.length) {
								dialog.window._href.setValue(value);
								dialog.window._href.disable();
							}
							else {
								dialog.window._href.enable();
							}
						},
						select: function(cb, record, index) {
							cb.fireEvent('change', cb, record.data.anchor, cb.getValue());
						},
						afterrender: function(combo) {
							var s = combo.getStore();
							var anchors = dialog.Get_anchors();
							Ext.iterate(anchors, function(value, display_value) {
								this.add(new this.recordType({
									anchor: value,
									name: display_value
								}));
							}, s);
						}
					}
				},
				{	ref: '../../_target',
					boxLabel: this.ln.open_in_new_window,
					xtype: 'checkbox'
				},
				{	ref: '../../_lightbox',
					boxLabel: this.ln.open_in_lightbox,
					xtype: 'checkbox'
				}
				,
				{	ref: '../../_nofollow',
					boxLabel: this.ln.nofollow,
					xtype: 'checkbox'
				}
			]
		};
		this.advanced = {
			title: this.ln.advanced,
			ref: '_f',
			layout: 'form',
			padding: '6px 3px 3px 3px',
			border: false,
			autoScroll: true,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 80,
			labelAlign: 'right',
			defaults: {anchor: '96%'},
			items: [
				{	ref: '../../_title',
					fieldLabel: this.ln.title,
					xtype: 'textfield'
				},
				{	ref: '../../_class',
					fieldLabel: this.ln._class,
					xtype: 'combo',
					mode: 'local',
					store: PC.utils.Get_classes_array('a'),
					triggerAction: 'all'
				},
				{	ref: '../../_id',
					fieldLabel: 'Id',
					xtype: 'textfield'
				},
				{	ref: '../../_style',
					fieldLabel: this.ln.style,
					xtype: 'textfield'
				}
			]
		};
		this.events = {
			title: this.ln.events,
            layout: 'column',
			bodyCssClass: 'x-border-layout-ct',
            items:[
				{	columnWidth:.48,
					layout: 'form',
					padding: '6px 0 3px 3px',
					border: false,
					autoScroll: true,
					bodyCssClass: 'x-border-layout-ct',
					labelWidth: 75,
					labelAlign: 'right',
					//defaults: {anchor:'100%', xtype:'textarea', height: 32},
					defaults: {anchor:'96%', xtype:'textfield'},
					items: [
						{	ref: '../../../_onfocus',
							fieldLabel: 'onfocus'
						},
						{	ref: '../../../_onblur',
							fieldLabel: 'onblur'
						},
						{	ref: '../../../_onclick',
							fieldLabel: 'onclick'
						},
						{	ref: '../../../_ondblclick',
							fieldLabel: 'ondblclick'
						},
						{	ref: '../../../_onmousedown',
							fieldLabel: 'onmousedown'
						},
						{	ref: '../../../_onmouseup',
							fieldLabel: 'onmouseup'
						}
					]
				},
				{	columnWidth:.52,
					layout: 'form',
					padding: '6px 3px 3px 0',
					border: false,
					autoScroll: true,
					bodyCssClass: 'x-border-layout-ct',
					labelWidth: 80,
					labelAlign: 'right',
					//defaults: {anchor:'96%', xtype:'textarea', height: 32},
					defaults: {anchor:'96%', xtype:'textfield'},
					items: [
						{	ref: '../../../_onmouseover',
							fieldLabel: 'onmouseover'
						},
						
						{	ref: '../../../_onmousemove',
							fieldLabel: 'onmousemove'
						},
						{	ref: '../../../_onmouseout',
							fieldLabel: 'onmouseout'
						},
						{	ref: '../../../_onkeypress',
							fieldLabel: 'onkeypress'
						},
						{	ref: '../../../_onkeydown',
							fieldLabel: 'onkeydown'
						},
						{	ref: '../../../_onkeyup',
							fieldLabel: 'onkeyup'
						}
					]
				}
			]
		};
		this.tabs = {
			xtype: 'tabpanel',
			activeTab: 0,
			flex: 1,
			items: [this.general, this.advanced, this.events],
			border: false
		};
		this.window = new PC.ux.Window({
			title: this.ln.title_insert,
			layout: 'vbox',
			layoutConfig: {
				align: 'stretch'
			},
			width: 500, height: 260,
			resizable: false,
			border: false,
			items: this.tabs,
			buttonAlign: 'left',
			buttons: [
				{xtype: 'tbfill'},
				{	ref: '../_insert',
					text: this.ln.insert,
					handler: function() {
						dialog.insertAction();
						dialog.window.close();
					}
				},
				{	text: Ext.Msg.buttonText.cancel,
					handler: function() {
						dialog.window.close();
					}
				}
			]
		});
		this.window.show();
		this.init();
	},
	Get_anchors: function() {
		var nodes = tinymce.activeEditor.dom.select('a.mceItemAnchor,img.mceItemAnchor');
		var anchors = {};
		var name;
		for (var i=0; i<nodes.length; i++) {
			name = nodes[i].getAttribute('name');
			anchors['#' + name] = name;
		}
		return anchors;
	},
	templates: {
		"window.open" : "window.open('${url}','${target}','${options}')"
	},
	getAnchorListHTML: function(id, target){
		var inst = tinymce.activeEditor;
		var nodes = inst.dom.select('a.mceItemAnchor,img.mceItemAnchor'), name, i;
		var html = "";

		html += '<select id="' + id + '" name="' + id + '" class="mceAnchorList" o2nfocus="tinyMCE.addSelectAccessibility(event, this, window);" onchange="this.form.' + target + '.value=';
		html += 'this.options[this.selectedIndex].value;">';
		html += '<option value="">---</option>';

		for (i=0; i<nodes.length; i++) {
			if ((name = inst.dom.getAttrib(nodes[i], "name")) != "")
				html += '<option value="#' + name + '">' + name + '</option>';
		}
		html += '</select>';
		return html;
	},
	checkPrefix: function(n){
		if (n.value && Validator.isEmail(n) && !/^\s*mailto:/i.test(n.value)) // && confirm(tinyMCEPopup.getLang('advlink_dlg.is_email')))
			n.value = 'mailto:' + n.value;

		if (/^\s*www\./i.test(n.value)) // && confirm(tinyMCEPopup.getLang('advlink_dlg.is_external')))
			n.value = 'http://' + n.value;
	},
	insertAction: function(){
		var inst = tinymce.activeEditor;
		var elm, elementArray, i;
		
		if (tinymce.isIE) inst.selection.moveToBookmark(this.bookmark);

		if (inst.selection.getRng().endOffset-inst.selection.getRng().startOffset == 0 && PC.dialog.links.last_position) {
			inst.selection.moveToBookmark(PC.dialog.links.last_position);
		}

		elm = inst.selection.getNode();
		this.checkPrefix(this.window._href);

		elm = inst.dom.getParent(elm, "A");

		// Remove element if there is no href
		if (!this.window._href.getValue()) {
			tinymce.execCommand("mceBeginUndoLevel");
			i = inst.selection.getBookmark();
			inst.dom.remove(elm, 1);
			inst.selection.moveToBookmark(i);
			tinymce.execCommand("mceEndUndoLevel");
			return;
		}

		tinymce.execCommand("mceBeginUndoLevel");

		// Create new anchor elements
		if (elm == null) {
			inst.getDoc().execCommand("unlink", false, null);
			tinymce.execCommand("CreateLink", false, "#mce_temp_url#", {skip_undo : 1});

			elementArray = tinymce.grep(inst.dom.select("a"), function(n) {return inst.dom.getAttrib(n, 'href') == '#mce_temp_url#';});
			for (i=0; i<elementArray.length; i++)
				this.setAllAttribs(elm = elementArray[i]);
		} else
			this.setAllAttribs(elm);

		// Don't move caret if selection was image
		if (elm) if (elm.childNodes.length != 1 || elm.firstChild.nodeName != 'IMG') {
			inst.focus();
			inst.selection.select(elm);
			inst.selection.collapse(0);
			//tinymce.storeSelection();
		}

		tinymce.execCommand("mceEndUndoLevel");
	},
	init: function(){
		//dynamically load validator utility
		var oHead = document.getElementsByTagName('HEAD').item(0);
		var oScript= document.createElement("script");
		oScript.type = "text/javascript";
		oScript.src="tiny_mce/utils/validate.js";
		oHead.appendChild( oScript);
		
		var w = this.window;
		var inst = tinymce.activeEditor;
		var elm = inst.selection.getNode();
		var action = "insert";
		var html;

		/*document.getElementById('hrefbrowsercontainer').innerHTML = getBrowserHTML('hrefbrowser','href','file','advlink');
		document.getElementById('popupurlbrowsercontainer').innerHTML = getBrowserHTML('popupurlbrowser','popupurl','file','advlink');
		document.getElementById('linklisthrefcontainer').innerHTML = getLinkListHTML('linklisthref','href');
		document.getElementById('anchorlistcontainer').innerHTML = getAnchorListHTML('anchorlist','href');
		document.getElementById('targetlistcontainer').innerHTML = getTargetListHTML('targetlist','target');*/

		/*// Link list
		html = getLinkListHTML('linklisthref','href');
		if (html == "")
			document.getElementById("linklisthrefrow").style.display = 'none';
		else
			document.getElementById("linklisthrefcontainer").innerHTML = html;
		*/
		// Resize some elements
		/*if (isVisible('hrefbrowser'))
			document.getElementById('href').style.width = '260px';

		if (isVisible('popupurlbrowser'))
			document.getElementById('popupurl').style.width = '180px';*/

		elm = inst.dom.getParent(elm, "A");
		if (elm != null && elm.nodeName == "A")
			action = "update";

		//formObj.insert.value = tinyMCEPopup.getLang(action, 'Insert', true); 

		if (action == "update") {
			w._insert.setText(this.ln.update);
			w.setTitle(this.ln.title_update);
			
			var href = inst.dom.getAttrib(elm, 'href');
			var onclick = inst.dom.getAttrib(elm, 'onclick');

			// Setup form data
			this.setFormValue('href', href);
			this.setFormValue('title', inst.dom.getAttrib(elm, 'title'));
			this.setFormValue('id', inst.dom.getAttrib(elm, 'id'));
			this.setFormValue('style', inst.dom.getAttrib(elm, "style"));
			this.setFormValue('onfocus', inst.dom.getAttrib(elm, 'onfocus'));
			this.setFormValue('onblur', inst.dom.getAttrib(elm, 'onblur'));
			this.setFormValue('onclick', onclick);
			this.setFormValue('ondblclick', inst.dom.getAttrib(elm, 'ondblclick'));
			this.setFormValue('onmousedown', inst.dom.getAttrib(elm, 'onmousedown'));
			this.setFormValue('onmouseup', inst.dom.getAttrib(elm, 'onmouseup'));
			this.setFormValue('onmouseover', inst.dom.getAttrib(elm, 'onmouseover'));
			this.setFormValue('onmousemove', inst.dom.getAttrib(elm, 'onmousemove'));
			this.setFormValue('onmouseout', inst.dom.getAttrib(elm, 'onmouseout'));
			this.setFormValue('onkeypress', inst.dom.getAttrib(elm, 'onkeypress'));
			this.setFormValue('onkeydown', inst.dom.getAttrib(elm, 'onkeydown'));
			this.setFormValue('onkeyup', inst.dom.getAttrib(elm, 'onkeyup'));
			this.setFormValue('target', (inst.dom.getAttrib(elm, 'target')=='_blank'));
			//this.setFormValue('rel', (inst.dom.getAttrib(elm, 'rel')=='lightbox'));
			this.setFormValue('class', inst.dom.getAttrib(elm, 'class'));

			if (href.charAt(0) == '#') {
				//debugger;
				var value = inst.dom.getAttrib(elm, 'href').substr(1);
				w._anchor.setValue(value);
				w._anchor.fireEvent('change', w._anchor, href, w._anchor.getValue());
			}
			w._class.setValue(inst.dom.getAttrib(elm, 'class'));
			w._target.setValue((inst.dom.getAttrib(elm, 'target') == '_blank'));
			w._lightbox.setValue((inst.dom.getAttrib(elm, 'rel') == 'lightbox'));
			w._nofollow.setValue((inst.dom.getAttrib(elm, 'rel') == 'nofollow'));
		}
		else {
			//addClassesToList('classlist', 'advlink_styles');
		}
	},
	parseFunction: function(onclick){
		var w = this.window;
		var onClickData = this.parseLink(onclick);
	},
	parseLink: function(link){
		link = link.replace(new RegExp('&#39;', 'g'), "'");

		var fnName = link.replace(new RegExp("\\s*([A-Za-z0-9\.]*)\\s*\\(.*", "gi"), "$1");

		// Is function name a template function
		var template = this.templates[fnName];
		if (template) {
			// Build regexp
			var variableNames = template.match(new RegExp("'?\\$\\{[A-Za-z0-9\.]*\\}'?", "gi"));
			var regExp = "\\s*[A-Za-z0-9\.]*\\s*\\(";
			var replaceStr = "";
			for (var i=0; i<variableNames.length; i++) {
				// Is string value
				if (variableNames[i].indexOf("'${") != -1)
					regExp += "'(.*)'";
				else // Number value
					regExp += "([0-9]*)";

				replaceStr += "$" + (i+1);

				// Cleanup variable name
				variableNames[i] = variableNames[i].replace(new RegExp("[^A-Za-z0-9]", "gi"), "");

				if (i != variableNames.length-1) {
					regExp += "\\s*,\\s*";
					replaceStr += "<delim>";
				} else
					regExp += ".*";
			}

			regExp += "\\);?";

			// Build variable array
			var variables = [];
			variables["_function"] = fnName;
			var variableValues = link.replace(new RegExp(regExp, "gi"), replaceStr).split('<delim>');
			for (var i=0; i<variableNames.length; i++)
				variables[variableNames[i]] = variableValues[i];

			return variables;
		}

		return null;
	},
	setFormValue: function(name, value){
		this.window['_'+name].setValue(value);
	},
	getLinkListHTML: function(elm_id, target_form_element, onchange_func){
		if (typeof(tinyMCELinkList) == "undefined" || tinyMCELinkList.length == 0)
			return "";

		var html = "";

		html += '<select id="' + elm_id + '" name="' + elm_id + '"';
		html += ' class="mceLinkList" onfoc2us="tinyMCE.addSelectAccessibility(event, this, window);" onchange="this.form.' + target_form_element + '.value=';
		html += 'this.options[this.selectedIndex].value;';

		if (typeof(onchange_func) != "undefined")
			html += onchange_func + '(\'' + target_form_element + '\',this.options[this.selectedIndex].text,this.options[this.selectedIndex].value);';

		html += '"><option value="">---</option>';

		for (var i=0; i<tinyMCELinkList.length; i++)
			html += '<option value="' + tinyMCELinkList[i][1] + '">' + tinyMCELinkList[i][0] + '</option>';

		html += '</select>';

		return html;

		// tinyMCE.debug('-- image list start --', html, '-- image list end --');
	},
	setAllAttribs: function(elm){
		var w = this.window;
		var href = w._href.getValue();
		var target = w._target.getValue();
		var lightbox = w._lightbox.getValue();
		var nofollow = w._nofollow.getValue();
		this.setAttrib(elm, 'href', href);
		this.setAttrib(elm, 'title');
		this.setAttrib(elm, 'target', target?'_blank':'');
		if (nofollow) {
			this.setAttrib(elm, 'rel', 'nofollow');
		} else if (lightbox) {
			this.setAttrib(elm, 'rel', 'lightbox');
		}
		else {
			this.setAttrib(elm, 'rel', '');
		}
		this.setAttrib(elm, 'id');
		this.setAttrib(elm, 'style');
		this.setAttrib(elm, 'class', w._class.getValue());
		this.setAttrib(elm, 'onfocus');
		this.setAttrib(elm, 'onblur');
		this.setAttrib(elm, 'onclick');
		this.setAttrib(elm, 'ondblclick');
		this.setAttrib(elm, 'onmousedown');
		this.setAttrib(elm, 'onmouseup');
		this.setAttrib(elm, 'onmouseover');
		this.setAttrib(elm, 'onmousemove');
		this.setAttrib(elm, 'onmouseout');
		this.setAttrib(elm, 'onkeypress');
		this.setAttrib(elm, 'onkeydown');
		this.setAttrib(elm, 'onkeyup');

		// Refresh in old MSIE
		if (tinyMCE.isMSIE5)
			elm.outerHTML = elm.outerHTML;
	},
	setAttrib: function(elm, attrib, value){
		var w = this.window;
		var valueElm = w['_'+attrib.toLowerCase()];
		var dom = tinymce.activeEditor.dom;

		if (typeof(value) == "undefined" || value == null) {
			value = "";

			if (valueElm)
				value = valueElm.getValue();
		}

		// Clean up the style
		if (attrib == 'style')
			value = dom.serializeStyle(dom.parseStyle(value), 'a');

		dom.setAttrib(elm, attrib, value);
	}
};
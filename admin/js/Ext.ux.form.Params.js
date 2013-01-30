Ext.ns('Ext.ux.form.Params');
Ext.ux.form.Params = {
	show: function(formItem) 
	{
		this.ln = PC.i18n.dialog.form;
		var dialog = this;
		this.formItem = formItem;
		var items=[], ed = tinymce.activeEditor, dom = ed.dom, plugin = ed.plugins.advform;
		var type=dom.getAttrib(dialog.formItem, 'class').split("mceItemForm_")[1];

		if(dialog.formItem.tagName.toLowerCase() == 'form') {
			ParentNode = dom.getParent(dialog.formItem, "FORM");
			dialog.formItem?ParentNode.nodeName:null;
			items.push({fieldLabel: this.ln.emails, ref: '../../_emails',xtype: 'textarea', height: 32});
			items.push({fieldLabel: this.ln.thank_you_text, ref: '../../_thankYouText',xtype: 'textarea', height: 64});
		} else {
			items.push({fieldLabel: this.ln.name, ref: '../../_name'});
			items.push({fieldLabel: this.ln.title, ref: '../../_title'});
			switch(type)
			{
				case 'label':
					items = [
						{fieldLabel: this.ln._for, ref: '../../_for'},
						{fieldLabel: this.ln.title, ref: '../../_title'}
					];
					break;
				case 'hidden':
					items = [
						{fieldLabel: this.ln.name, ref: '../../_name'},
						{fieldLabel: this.ln.value, ref: '../../_value'},
					];
				break;
				case 'text':
					items.push({fieldLabel: this.ln.value, ref: '../../_value'});
//					items.push({fieldLabel: this.ln.size, ref: '../../_size'});
					items.push({fieldLabel: this.ln.width, ref: '../../_width'});
					items.push({fieldLabel: this.ln.maxlength, ref: '../../_maxlength'});
					items.push({fieldLabel: this.ln.readonly, xtype: 'combo', ref: '../../_readonly',mode: 'local',width: 45,store:{xtype:'arraystore',storeId: 'margin_top',fields: ['value', 'display'],idIndex:0,data: [[false,PC.i18n.no],['readonly',PC.i18n.yes]]},editable: false,valueField: 'value',displayField: 'display',value: false,triggerAction: 'all'});
					items.push({fieldLabel: this.ln.required, xtype: 'combo', ref: '../../_required',mode: 'local',width: 45,store:{xtype:'arraystore',storeId: 'margin_top',fields: ['value', 'display'],idIndex:0,data: [[false,PC.i18n.no],['required',PC.i18n.yes]]},editable: false,valueField: 'value',displayField: 'display',value: false,triggerAction: 'all'});
				break;
				case 'password':
//					items.push({fieldLabel: this.ln.size, ref: '../../_size'});
					items.push({fieldLabel: this.ln.width, ref: '../../_width'});
					items.push({fieldLabel: this.ln.maxlength, ref: '../../_maxlength'});
					items.push({fieldLabel: this.ln.required, xtype: 'combo', ref: '../../_required',mode: 'local',width: 45,store:{xtype:'arraystore',storeId: 'margin_top',fields: ['value', 'display'],idIndex:0,data: [[false,PC.i18n.no],['required',PC.i18n.yes]]},editable: false,valueField: 'value',displayField: 'display',value: false,triggerAction: 'all'});
				break;
				case 'radio':
					items.push({fieldLabel: this.ln.value, ref: '../../_value'});
					items.push({fieldLabel: this.ln.checked, xtype: 'combo', ref: '../../_checked',mode: 'local',width: 45,store:{xtype:'arraystore',storeId: 'margin_top',fields: ['value', 'display'],idIndex:0,data: [[false,PC.i18n.no],['checked',PC.i18n.yes]]},editable: false,valueField: 'value',displayField: 'display',value: false,triggerAction: 'all'});
				break;
				case 'checkbox':
					items.push({fieldLabel: this.ln.value, ref: '../../_value'});
					items.push({fieldLabel: this.ln.checked, xtype: 'combo', ref: '../../_checked',mode: 'local',width: 45,store:{xtype:'arraystore',storeId: 'margin_top',fields: ['value', 'display'],idIndex:0,data: [[false,PC.i18n.no],['checked',PC.i18n.yes]]},editable: false,valueField: 'value',displayField: 'display',value: 0,triggerAction: 'all'});
					items.push({fieldLabel: this.ln.required, xtype: 'combo', ref: '../../_required',mode: 'local',width: 45,store:{xtype:'arraystore',storeId: 'margin_top',fields: ['value', 'display'],idIndex:0,data: [[false,PC.i18n.no],['required',PC.i18n.yes]]},editable: false,valueField: 'value',displayField: 'display',value: false,triggerAction: 'all'});
				break;
				case 'textarea':
//					items.push({fieldLabel: this.ln.cols, ref: '../../_cols'});
//					items.push({fieldLabel: this.ln.rows, ref: '../../_rows'});
					items.push({fieldLabel: this.ln.width, ref: '../../_width'});
					items.push({fieldLabel: this.ln.height, ref: '../../_height'});
					items.push({fieldLabel: this.ln.value, ref: '../../_textContent',xtype: 'textarea', height: 32});
					items.push({fieldLabel: this.ln.readonly, xtype: 'combo', ref: '../../_readonly',mode: 'local',width: 45,store:{xtype:'arraystore',storeId: 'margin_top',fields: ['value', 'display'],idIndex:0,data: [[false,PC.i18n.no],['readonly',PC.i18n.yes]]},editable: false,valueField: 'value',displayField: 'display',value: false,triggerAction: 'all'});
					items.push({fieldLabel: this.ln.required, xtype: 'combo', ref: '../../_required',mode: 'local',width: 45,store:{xtype:'arraystore',storeId: 'margin_top',fields: ['value', 'display'],idIndex:0,data: [[false,PC.i18n.no],['required',PC.i18n.yes]]},editable: false,valueField: 'value',displayField: 'display',value: false,triggerAction: 'all'});
				break;
				case 'select':
//					items.push({fieldLabel: this.ln.size, ref: '../../_size'});
					items.push({fieldLabel: this.ln.width, ref: '../../_width'});
					items.push({fieldLabel: this.ln.height, ref: '../../_height'});
					items.push({fieldLabel: this.ln.multiple, xtype: 'combo', ref: '../../_multiple',mode: 'local',width: 45,store:{xtype:'arraystore',storeId: 'margin_top',fields: ['value', 'display'],idIndex:0,data: [[false,PC.i18n.no],['multiple',PC.i18n.yes]]},editable: false,valueField: 'value',displayField: 'display',value: false,triggerAction: 'all'});
					items.push({fieldLabel: this.ln.required, xtype: 'combo', ref: '../../_required',mode: 'local',width: 45,store:{xtype:'arraystore',storeId: 'margin_top',fields: ['value', 'display'],idIndex:0,data: [[false,PC.i18n.no],['required',PC.i18n.yes]]},editable: false,valueField: 'value',displayField: 'display',value: false,triggerAction: 'all'});
				break;
				case 'file':
					items.push({fieldLabel: this.ln.maxuploadsize, ref: '../../_data-maxuploadsize'});
					items.push({fieldLabel: this.ln.required, xtype: 'combo', ref: '../../_required',mode: 'local',width: 45,store:{xtype:'arraystore',storeId: 'margin_top',fields: ['value', 'display'],idIndex:0,data: [[false,PC.i18n.no],['required',PC.i18n.yes]]},editable: false,valueField: 'value',displayField: 'display',value: false,triggerAction: 'all'});
				break;
				case 'image':
					items.push(/*{fieldLabel: this.ln.image_url, ref: '../../_src'}*/
						{	fieldLabel: this.ln.image_url,
							xtype:'trigger', ref: '../../_src',
							triggerClass: 'x-form-search-trigger',
							onTriggerClick: function() {
								var field = this;
								var params = {
									callee: 'image',
									save_fn: function(url, rec, callback, params){
										field.setValue(url);
										callback();
									}
								};
								var src = dialog.window._src.getValue();
								if (/^gallery\//.test(src)) {
									params.select_id = src.substring(src.lastIndexOf('/')+1);
								}
								PC.dialog.gallery.show(params);
							}
						}
					);
				break;
				case 'submit':
				case 'reset':
					items.push({fieldLabel: this.ln.value, ref: '../../_value'});
				break;
			}
		}
		this.general = 
		{
			title: this.ln.general,
			layout: 'form',
			border: false,
			padding: '6px 12px 0 3px',
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 120,
			labelAlign: 'right',
			defaults: {anchor:'100%', xtype:'textfield'},
			items: items
		};
		this.advanced = 
		{
			title: this.ln.advanced,
			layout: 'form',
			padding: '6px 3px 3px 3px',
			border: false,
			autoScroll: true,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 120,
			labelAlign: 'right',
			defaults: {anchor: '96%', xtype:'textfield'},
			items: [
				{	fieldLabel: this.ln.id,
					ref: '../../_id',
					allowBlank: (this.formItem.tagName.toLowerCase() != 'form')
				},
				{	ref: '../../_class',
					fieldLabel: this.ln._class,
					xtype: 'combo', 
					mode: 'local',
					store: PC.utils.Get_classes_array('form'),
					triggerAction: 'all'
				},
				{	fieldLabel: this.ln.style,
					ref: '../../_style'
				},
				{	ref: '../../_border',
					fieldLabel: this.ln.border,
					xtype: 'compositefield',
					border: false,
					autoHeight: true,
					style: 'padding:0',
					defaultType: 'textfield',
					defaults: {
						hideLabel: true,
						flex: 1
					},
					items: [
						{	xtype: 'combo', ref: '_size', disabled: true,
							mode: 'local',
							width: 45,
							store: {
								xtype: 'arraystore',
								storeId: 'margin_top',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10]]
							},
							editable: false,
							valueField: 'value',
							displayField: 'display',
							value: 1,
							triggerAction: 'all'
						},
						{	xtype: 'combo', ref: '_style',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'margin_right',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [
									['', ''],
									['solid',this.ln.solid],
									['dotted',this.ln.dotted],
									['dashed',this.ln.dashed]
								]
							},
							tpl: '<tpl for="."><div class="x-combo-list-item">{[values.display ? values.display : "&nbsp;"]}</div></tpl>',
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: function(cb, value, old){
									if (value == '') {
										dialog.window._border.innerCt._size.disable();
										dialog.window._border.innerCt._color.disable();
									} else {
										dialog.window._border.innerCt._size.enable();
										dialog.window._border.innerCt._color.enable();
									}
								},
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						},
						{	xtype: 'colorfield', ref: '_color', disabled: true,
							regex: /.*/,
							allowBlank: true,
							value: '',
							detectFontColor: function() {
								if (this.menu && this.menu.picker.rawValue) {
									var val = this.menu.picker.rawValue;
								} else {
									var pcol = PC.utils.color2Hex(this.value);
									//if (console && console.log) console.log(pcol);
									if (pcol) {
										var val = [
											parseInt(pcol.slice(0, 2), 16),
											parseInt(pcol.slice(2, 4), 16),
											parseInt(pcol.slice(4, 6), 16)
										];
									} else {
										this.el.setStyle('color', 'black');
										this.el.setStyle('background', 'white');
										return;
									}
								}
								var avg = val[0]*0.299 + val[1]*0.587 + val[2]*0.114;
								this.el.setStyle('color', (avg >= 128) ? 'black' : 'white');
							}
						}
					]
				},
				{	ref: '../../_margin',
					fieldLabel: this.ln.margin,
					xtype: 'compositefield',
					border: false,
					autoHeight:true,
					style: 'padding:0',
					defaultType: 'textfield',
					defaults: {
						hideLabel: true,
						flex: 1
					},
					items: 
					[
						{	xtype: 'combo', ref: '_top',
							style: 'border-top: 2px solid #CC3333;',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'margin_top',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: dialog.margin_changed,
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						},
						{	xtype: 'combo', ref: '_right',
							style: 'border-right: 2px solid #CC3333;',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'margin_right',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: dialog.margin_changed,
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						},
						{	xtype: 'combo', ref: '_bottom',
							style: 'border-bottom: 2px solid #CC3333',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'margin_bottom',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: dialog.margin_changed,
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						},
						{	xtype: 'combo', ref: '_left',
							style: 'border-left: 2px solid #CC3333',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'margin_left',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: dialog.margin_changed,
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						}
					]
				},
				{	ref: '../../_padding',
					fieldLabel: this.ln.padding,
					xtype: 'compositefield',
					border: false,
					autoHeight:true,
					style: 'padding:0',
					defaultType: 'textfield',
					defaults: {
						hideLabel: true,
						flex: 1
					},
					items: 
					[
						{	xtype: 'combo', ref: '_top',
							style: 'border-top: 2px solid #006633;',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'padding_top',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: dialog.padding_changed,
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						},
						{	xtype: 'combo', ref: '_right',
							style: 'border-right: 2px solid #006633;',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'padding_right',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: dialog.padding_changed,
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						},
						{	xtype: 'combo', ref: '_bottom',
							style: 'border-bottom: 2px solid #006633',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'padding_bottom',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: dialog.padding_changed,
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						},
						{	xtype: 'combo', ref: '_left',
							style: 'border-left: 2px solid #006633',
							mode: 'local',
							store: {
								xtype: 'arraystore',
								storeId: 'padding_left',
								fields: ['value', 'display'],
								idIndex: 0,
								data: [[0,0],[1,1],[2,2],[3,3],[4,4],[5,5],[6,6],[7,7],[8,8],[9,9],[10,10],[12,12],[15,15],[20,20]]
							},
							valueField: 'value',
							displayField: 'display',
							value: '',
							triggerAction: 'all',
							listeners: {
								change: dialog.padding_changed,
								select: function(cb, rec, idx) {
									cb.fireEvent('change', cb, cb.value, cb.originalValue);
								}
							}
						}
					]
				},
				{	ref: '../../_background_color',
					xtype: 'colorfield',
					fieldLabel: this.ln.background_color,
					regex: /.*/,
					allowBlank: true,
					value: '',
					detectFontColor: function() {
						if (this.menu && this.menu.picker.rawValue) {
							var val = this.menu.picker.rawValue;
						} else {
							var pcol = PC.utils.color2Hex(this.value);
							if (pcol) {
								var val = [
									parseInt(pcol.slice(0, 2), 16),
									parseInt(pcol.slice(2, 4), 16),
									parseInt(pcol.slice(4, 6), 16)
								];
							} else {
								this.el.setStyle('color', 'black');
								this.el.setStyle('background', 'white');
								return;
							}
						}
						var avg = val[0]*0.299 + val[1]*0.587 + val[2]*0.114;
						this.el.setStyle('color', (avg >= 128) ? 'black' : 'white');
					}
				}
			]
		};
		
		var eventItems = [
			{fieldLabel: this.ln.onfocus, ref: '../../_onfocus'},
			{fieldLabel: this.ln.onblur, ref: '../../_onblur'}
		];
		
		if(type != 'label') {
			if((type != 'select') && (type != 'radio') && (type != 'checkbox')) {
				eventItems.push ({fieldLabel: this.ln.onselect, ref: '../../_onselect'});
			}
			eventItems.push (
				{fieldLabel: this.ln.onchange, ref: '../../_onchange'}
			);
		};
		
		this.events = 
		{
			title: this.ln.events,
			layout: 'form',
			border: false,
			padding: '6px 12px 0 3px',
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 120,
			labelAlign: 'right',
			defaults: {anchor:'100%', xtype:'textfield'},
			items: eventItems
		};
		
		if(dialog.formItem.tagName.toLowerCase() == 'form')
		{
			var tabItems = [this.general,this.advanced];
		}
		else if(type == 'hidden')
		{
			var tabItems = [this.general,this.advanced];
			for(var i=0; i<this.advanced.items.length; i++) {
				switch(this.advanced.items[i].ref) {
					case '../../_style':
					case '../../_border':
					case '../../_margin':
					case '../../_padding':
					case '../../_background_color':
						this.advanced.items[i].disabled = true;
						this.advanced.items[i].hidden = true;
				}
			}
		}
		else if(type == 'select')
		{
			this.options = new Ext.grid.EditorGridPanel(
			{
				title: this.ln.options,
				border: false,
				bodyCssClass: 'x-border-layout-ct',
				clicksToEdit: 1,
				enableColumnHide: false,
				enableColumnMove: false,
				enableHdMenu: false,
				stripeRows: true,
				ref: '../_options',
				store: new Ext.data.JsonStore({
					autoDestroy: true,
					fields: ['textContent', 'value', 'selected', 'disabled']
				}),
				plugins: [
					new Ext.ux.dd.GridDragDropRowOrder({
						copy: false,
						scrollable: true,
						targetCfg: {}
					})
				],
				colModel: new Ext.grid.ColumnModel({
					defaults: { width: 120, sortable: false },
					columns: [
						{
							header: this.ln.label,
							sortable: true,
							width: 200,
							dataIndex: 'textContent',
							editor: {
								allowBlank: false
							}
						},
						{
							header: this.ln.value,
							width: 160,
							dataIndex: 'value',
							editor: {
								allowBlank: true
							}
						},
						{
							header: this.ln.selected,
							dataIndex: 'selected',
							editor: {
								xtype: 'combo',
								editable: false,
								triggerAction: 'all',
								store: [
									[false,PC.i18n.no],
									['selected',PC.i18n.yes]
								],
								lazyRender: true,
								listClass: 'x-combo-list-small'
							},
							renderer: {
								fn: function(value) {
									return value == 'selected' ? PC.i18n.yes : PC.i18n.no;
								},
								scope: this
							}
						},
						{
							header: this.ln.disabled,
							dataIndex: 'disabled',
							editor: {
								xtype: 'combo',
								editable: false,
								triggerAction: 'all',
								store: [
									[false,PC.i18n.no],
									['disabled',PC.i18n.yes]
								],
								lazyRender: true,
								listClass: 'x-combo-list-small'
							},
							renderer: {
								fn: function(value) {
									return value == 'disabled' ? PC.i18n.yes : PC.i18n.no;
								},
								scope: this
							}
						}
					]
				}),
				tbar: [{
					iconCls: 'icon-add',
					text: this.ln.new_option,
					handler : function(b, e){
						var grid = b.ownerCt.ownerCt;
						var store = grid.getStore();
						grid.stopEditing();
						store.loadData([{}], true);
						grid.startEditing(store.getCount()-1, 0);
					}
				},{
					iconCls: 'icon-delete',
					text: this.ln.delete_option,
					handler : function(b, e){
						var grid = b.ownerCt.ownerCt;
						var store = grid.getStore();
						var row = grid.getSelectionModel().getSelected();
						if(!row) {
							return false;
						}
						store.remove(row)
					}
				}
				],
				viewConfig: {
					forceFit: true
				},
				selModel: new Ext.grid.RowSelectionModel({singleSelect:true})
			});
			
			var tabItems = [this.general,this.options,this.advanced,this.events];
		}
		else
		{
			var tabItems = [this.general,this.advanced,this.events];
		}
		
		this.tabs = 
		{
			xtype: 'tabpanel',
			activeTab: 0,
			flex: 1,
			items: tabItems,
			border: false
		};
		this.window = new Ext.Window
		({
			title: this.ln.form_properties+' '+(type?type:''),
			layout: 'vbox',
			layoutConfig: {
				align: 'stretch'
			},
			width: 400,
			height: 300,
			resizable: false,
			border: false,
			items: this.tabs,
			buttonAlign: 'left',
			buttons: [
				{xtype:'tbfill'},
				{	text: Ext.Msg.buttonText.ok,
					handler: function() {
						//save settings
						//custom forms
						
						var attributes = plugin.attributes;
						var optionAttributes = plugin.optionAttributes;
						
						var packedAttributes = {};
						
						for (var i=0; i<attributes.length; i++) {
							var attribute=attributes[i];
							var obj = dialog.window['_' + attribute.name];
							if(typeof(obj) == 'object') {
								var val = obj.getValue();
								if(attribute.name == 'style') {
									// prepend border
									if (dialog.window._border) {
										if (dialog.window._border.innerCt._style.getValue() != '')
										{
											val = 'border:'
												+ dialog.window._border.innerCt._size.getValue() + 'px '
												+ dialog.window._border.innerCt._style.getValue() + ' '
												+ dialog.window._border.innerCt._color.getValue() + ';' + val;
										}
									}
									// prepend margin
									if (dialog.window._margin) {
										var margin = dialog.window._margin.innerCt;
										var sides = new Array();
										sides[0] = parseInt((margin._top.getValue()>0?margin._top.getValue():0));
										sides[1] = parseInt((margin._right.getValue()>0?margin._right.getValue():0));
										sides[2] = parseInt((margin._bottom.getValue()>0?margin._bottom.getValue():0));
										sides[3] = parseInt((margin._left.getValue()>0?margin._left.getValue():0));
										if (sides[0] > 0 || sides[1] > 0 || sides[2] > 0 || sides[3] > 0) {
											var margin_sheet = 'margin:'+sides.join('px ')+'px';
											val = margin_sheet+';'+val;
										}
									}
									// prepend padding
									if (dialog.window._padding) {
										var padding = dialog.window._padding.innerCt;
										var sides = new Array();
										sides[0] = parseInt((padding._top.getValue()>0?padding._top.getValue():0));
										sides[1] = parseInt((padding._right.getValue()>0?padding._right.getValue():0));
										sides[2] = parseInt((padding._bottom.getValue()>0?padding._bottom.getValue():0));
										sides[3] = parseInt((padding._left.getValue()>0?padding._left.getValue():0));
										if (sides[0] > 0 || sides[1] > 0 || sides[2] > 0 || sides[3] > 0) {
											var margin_sheet = 'padding:'+sides.join('px ')+'px';
											val = margin_sheet+';'+val;
										}
									}
									// prepend background-color
									if (dialog.window._background_color) {
										var bgcolor = dialog.window._background_color.getValue();
										if((typeof(bgcolor) != 'undefined') && (bgcolor != '')) {
											val = 'background-color:'+bgcolor+';'+val;
										}
									}
									// prepend width
									if(dialog.window._width) {
										var width = dialog.window._width.getValue();
										if((typeof(width) != 'undefined') && (width != '')) {
											val = 'width:'+width+'px;'+val;
										}
									}
									// prepend height
									if(dialog.window._height) {
										var height = dialog.window._height.getValue();
										if((typeof(height) != 'undefined') && (height != '')) {
											val = 'height:'+height+'px;'+val;
										}
									}
								} else if(attribute.name == 'data-maxuploadsize') {
									// convert KiB to bytes
									if(val != '') {
										val *= 1024;
									}
								}
								if(attribute.pack && (dialog.formItem.tagName.toLowerCase() != 'form')) {
									if((typeof(val) != 'undefined') && (val !== '') && (val !== false)) {
										packedAttributes[attribute.name] = val;
									}
								} else {
									if((typeof(val) != 'undefined') && (val !== '')) {
										dialog.formItem.setAttribute(attribute.name, val);
										if(attribute.name == 'style') {
											// we have to set _mce_style as well
											dialog.formItem.setAttribute('_mce_style', val);
										}
									} else {
										dialog.formItem.removeAttribute(attribute.name);
										if(attribute.name == 'style') {
											// we have to set _mce_style as well
											dialog.formItem.removeAttribute('_mce_style');
										}
									}
								}
							}
						}
						
						if (dialog.formItem.tagName.toLowerCase() == 'form') {
							var pfs = {};
							var emails = dialog.window._emails.getValue();
							if((typeof(emails) != 'undefined') && (emails != '')){
								pfs.emails = emails;
							}
							var thankYouText = dialog.window._thankYouText.getValue();
							if((typeof(thankYouText) != 'undefined') && (thankYouText != '')){
								pfs.thankYouText = thankYouText;
							}
							dialog.formItem.setAttribute('pcformsettings', plugin._serialize(pfs));
						} else {
							if(type == 'select') {
								var rows = dialog.window._options.getStore().getRange();
								var options = [];
								for (var i=0; i<rows.length; i++) {
									var optdata = {};
									for(var j=0; j<optionAttributes.length; j++) {
										var optname = optionAttributes[j];
										var optval = rows[i].data[optname];
										if((typeof(optval) != 'undefined') && (optval !== false) && ((optval !== '') || (optname == 'value'))) {
											optdata[optname] = optval;
										}
									}
									options.push(optdata);
								}
								packedAttributes.options = options;
							}
							dialog.formItem.setAttribute('data-advform', plugin._serialize(packedAttributes));
						}
						
						tinymce.activeEditor.addVisual();
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
		dialog.window.show();
		if (dialog.formItem) {
			var attributes = plugin.attributes;
			var tagName = dialog.formItem.tagName.toLowerCase();
			
			if (tagName == 'form') {
				var pfs = dialog.getAttrib(dialog.formItem, 'pcformsettings');
				if((typeof(pfs) != 'undefined') && (pfs != '')) {
					pfs = plugin._parse(pfs);
					if(typeof(pfs.emails) != 'undefined') {
						dialog.window._emails.setValue(pfs.emails);
					}
					if(typeof(pfs.thankYouText) != 'undefined') {
						dialog.window._thankYouText.setValue(pfs.thankYouText);
					}
				}
			} else {
				var packed = plugin._parse(dialog.getAttrib(dialog.formItem, 'data-advform'));
				
				if(type == 'select') {
					dialog.window._options.getStore().loadData(packed.options)
				}
			}
			
			for (var i=0; i<attributes.length; i++) {
				var attribute = attributes[i];
				var field = dialog.window['_' + attribute.name];
				var val = '';
				if(typeof(field) == 'object') {
					if (!attribute.pack || (tagName == 'form')) {
						val = dialog.getAttrib(dialog.formItem, attribute.name);
					} else if(typeof(packed[attribute.name]) != 'undefined') {
						val = packed[attribute.name];
					}
					if(attribute.name == 'style') {
						//parse margin
						if(dialog.window._margin) {
							var margin_start = val.lastIndexOf('margin:');
							if (margin_start >= 0) {
								var margin_end = val.indexOf(';', margin_start);
								var margin = val.substring(margin_start+7, margin_end).replace(/^\s+/,"");
								var sides = margin.split(' ');
								//cut margin from the styles
								val = val.substring(0, margin_start) + val.substring(margin_end+1);
								//update margin values
								margin = dialog.window._margin.innerCt;
								switch (sides.length) {
									case 0:
										margin._top.setValue(0);
										margin._right.setValue(0);
										margin._bottom.setValue(0);
										margin._left.setValue(0);
										break;
									case 1:
										margin._top.setValue(parseInt(sides[0]));
										margin._right.setValue(parseInt(sides[0]));
										margin._bottom.setValue(parseInt(sides[0]));
										margin._left.setValue(parseInt(sides[0]));
										break;
									case 2:
										margin._top.setValue(parseInt(sides[0]));
										margin._right.setValue(parseInt(sides[1]));
										margin._bottom.setValue(parseInt(sides[0]));
										margin._left.setValue(parseInt(sides[1]));
										break;
									case 3:
										margin._top.setValue(parseInt(sides[0]));
										margin._right.setValue(parseInt(sides[1]));
										margin._bottom.setValue(parseInt(sides[2]));
										margin._left.setValue(0);
										break;
									default: //4 or more
										margin._top.setValue(parseInt(sides[0]));
										margin._right.setValue(parseInt(sides[1]));
										margin._bottom.setValue(parseInt(sides[2]));
										margin._left.setValue(parseInt(sides[3]));
								}
							}
						}
						//parse padding
						if(dialog.window._padding) {
							var padding_start = val.lastIndexOf('padding:');
							if (padding_start >= 0) {
								var padding_end = val.indexOf(';', padding_start);
								var padding = val.substring(padding_start+8, padding_end).replace(/^\s+/,"");
								var sides = padding.split(' ');
								//cut padding from the styles
								val = val.substring(0, padding_start) + val.substring(padding_end+1);
								//update padding values
								padding = dialog.window._padding.innerCt;
								switch (sides.length) {
									case 0:
										padding._top.setValue(0);
										padding._right.setValue(0);
										padding._bottom.setValue(0);
										padding._left.setValue(0);
										break;
									case 1:
										padding._top.setValue(parseInt(sides[0]));
										padding._right.setValue(parseInt(sides[0]));
										padding._bottom.setValue(parseInt(sides[0]));
										padding._left.setValue(parseInt(sides[0]));
										break;
									case 2:
										padding._top.setValue(parseInt(sides[0]));
										padding._right.setValue(parseInt(sides[1]));
										padding._bottom.setValue(parseInt(sides[0]));
										padding._left.setValue(parseInt(sides[1]));
										break;
									case 3:
										padding._top.setValue(parseInt(sides[0]));
										padding._right.setValue(parseInt(sides[1]));
										padding._bottom.setValue(parseInt(sides[2]));
										padding._left.setValue(0);
										break;
									default: //4 or more
										padding._top.setValue(parseInt(sides[0]));
										padding._right.setValue(parseInt(sides[1]));
										padding._bottom.setValue(parseInt(sides[2]));
										padding._left.setValue(parseInt(sides[3]));
								}
							}
						}
						//parse border
						if(dialog.window._border) {
							var border_start = val.lastIndexOf('border:');
							if (border_start >= 0) {
								var border_end = val.indexOf(';', border_start);
								var border = val.substring(border_start+7, border_end).replace(/^\s+/,"");
								var sides = border.split(' ');
								//update border values
								border = dialog.window._border.innerCt;
								if (sides.length == 3) {
									//cut border from the styles
									val = val.substring(0, border_start) + val.substring(border_end+1);
									border._size.setValue(parseInt(sides[0])).enable();
									border._style.setValue(sides[1]);
									border._color.setValue(sides[2]).enable();
								}
							}
						}
						//parse background-color
						if(dialog.window._background_color) {
							var bgcolor_start = val.lastIndexOf('background-color:');
							if(bgcolor_start >= 0) {
								var bgcolor_end = val.indexOf(';', bgcolor_start);
								var bgcolor = val.substring(bgcolor_start+17, bgcolor_end).replace(/^\s+/,"");
								//cut background-color from the styles
								val = val.substring(0, bgcolor_start) + val.substring(bgcolor_end+1);
								dialog.window._background_color.setValue(bgcolor);
							}
						}
						//parse width
						if(dialog.window._width) {
							var width_start = val.lastIndexOf('width:');
							if(width_start >= 0) {
								var width_end = val.indexOf(';', width_start);
								var width = val.substring(width_start+6, width_end).replace(/^\s+/,"");
								//cut width from the styles
								val = val.substring(0, width_start) + val.substring(width_end+1);
								dialog.window._width.setValue(parseInt(width));
							}
						}
						//parse height
						if(dialog.window._height) {
							var height_start = val.lastIndexOf('height:');
							if(height_start >= 0) {
								var height_end = val.indexOf(';', height_start);
								var height = val.substring(height_start+7, height_end).replace(/^\s+/,"");
								//cut height from the styles
								val = val.substring(0, height_start) + val.substring(height_end+1);
								dialog.window._height.setValue(parseInt(height));
							}
						}
						//clean up style
						val = val.replace(/^\s+/,"").replace(/\s+$/,"");
					} else if(attribute.name == 'data-maxuploadsize') {
						// convert bytes to KiB
						if (val != '') {
							val /= 1024;
						}
					}
					field.setValue(val);
				}
			}
		}
	},
	margin_changed: function(cb, val, oldval) 
	{
		var current = cb.ref;
		var dialog = Ext.ux.form.Params;
		var margin = dialog.window._margin.innerCt;
	},
	padding_changed: function(cb, val, oldval) 
	{
		var current = cb.ref;
		var dialog = Ext.ux.form.Params;
		var margin = dialog.window._padding.innerCt;
	},
	getAttrib: function(e, at) 
	{
		var ed = tinymce.activeEditor, dom = ed.dom, v, v2;

		if (ed.settings.inline_styles) {
			switch (at) {
				case 'align':
					if (v = dom.getStyle(e, 'float'))
						return v;

					if (v = dom.getStyle(e, 'vertical-align'))
						return v;

					break;

				case 'hspace':
					v = dom.getStyle(e, 'margin-left')
					v2 = dom.getStyle(e, 'margin-right');

					if (v && v == v2)
						return parseInt(v.replace(/[^0-9]/g, ''));

					break;

				case 'vspace':
					v = dom.getStyle(e, 'margin-top')
					v2 = dom.getStyle(e, 'margin-bottom');
					if (v && v == v2)
						return parseInt(v.replace(/[^0-9]/g, ''));

					break;

				case 'border':
					v = 0;

					tinymce.each(['top', 'right', 'bottom', 'left'], function(sv) 
					{
						sv = dom.getStyle(e, 'border-' + sv + '-width');

						// False or not the same as prev
						if (!sv || (sv != v && v !== 0)) {
							v = 0;
							return false;
						}

						if (sv)
							v = sv;
					});

					if (v)
						return parseInt(v.replace(/[^0-9]/g, ''));

					break;
			}
		}

		if (v = dom.getAttrib(e, at))
			return v;

		return '';
	}
};

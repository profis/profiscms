Ext.namespace('PC.ux');

var ln =  {
	en: {
		title: 'Crud panel',
		_delete: {
			confirm_title: 'Deleting item',
			confirm_message: 'Delete item?'
		},
		button: {
			//_delete: 'Delete item'
		},
		error: {
			json: 'Invalid JSON data returned.',
			connection: 'Connection error.',
			did_not_save: 'Data has not been saved.',
			did_not_delete: 'Data were not deleted.',
			name: 'Length must be between 2 and 100 symbols',
			password: 'Length must be between 8 and 30 symbols',
			unique: 'This value is already taken',
			required: 'This field is required'
		}
	},
	lt: {
		title: 'Administravimo panelė',
		_delete: {
			confirm_title: 'Trynimas',
			confirm_message: 'Trinti?'
		},
		button: {
			
		},
		error: {
			json: 'Neteisingi JSON duomenys.',
			connection: 'Ryšio klaida.',
			did_not_save: 'Duomenys nebuvo išsaugoti.',
			did_not_delete: 'Duomenys nebuvo ištrinti.',
			name: 'Ilgumas turi būti tarp 2 ir 100 simbolių',
			password: 'Ilgumas turi būti tarp 8 ir 30 simbolių',
			unique: 'Ši reikšmė jau užimta',
			required: 'Privalomas laukas'
		}
	},
	ru: {
		title: 'Панель администрирования',
		_delete: {
			confirm_title: 'Удаление',
			confirm_message: 'Удалить?'
		},
		button: {
			
		},
		error: {
			json: 'Неверные данные JSON.',
			connection: 'Ошибка соединения.',
			did_not_save: 'Данные не были сохранены.',
			did_not_delete: 'Данные не были удалены.',
			name: 'Длина должна быть от 2 до 100 символов',
			password: 'Длина должна быть от 8 до 30 символов',
			unique: 'Это значение уже занято',
			required: 'Это поле обязательно для заполнения'
		}
	}
}

PC.utils.localize('pc_ux_crud', ln);

PC.ux.LocalCrud = Ext.extend(Ext.Panel, {
	
	auto_load: false,
	per_page: false,
	
	max_id: 0,
	
	layout: 'fit',
	
	id_property: id,
	
	constructor: function(config) {
		if (!config) {
			config = {};
		}
		this.ln = this.get_ln();

		if (config.api_url) {
			this.api_url = config.api_url;
		}

		if (config.ln) {
			if (this.ln.error && config.ln.error) {
				config.ln.error = Ext.apply(this.ln.error, config.ln.error);
			}
			Ext.apply(this.ln, config.ln);
			delete config.ln;
		}

		config = Ext.apply({
			tbar: this.get_tbar(),
			items: this.get_items()
        }, config);

        PC.ux.LocalCrud.superclass.constructor.call(this, config);
		
		this.set_titles();
    },
			
	get_items: function() {
		return this.get_grid();
	},
	
	get_default_ln: function() {
		return {};
	},
        
	get_ln: function() {
        var ln = {};
		ln = Ext.apply(ln, PC.i18n.pc_ux_crud);
		ln = Ext.apply(ln, this.get_default_ln());
		return ln;
	},
	
	set_titles: function() {
		if (!this.title) {
			this.title = this.ln.title;
		}
	},
	
	
	
	get_store_fields: function() {
		return [
				'id'
		];
	},
	
	get_grid_selection_model: function() {
		if (this.checkable) {
			return new Ext.grid.CheckboxSelectionModel({
				listeners: this.get_grid_selection_model_listeners(),
				editable: false,
				checkOnly: true
			});
		}
		else {
			return new Ext.grid.RowSelectionModel({
				listeners: this.get_grid_selection_model_listeners()
			});
		}
		
	},	
	
	get_grid_selection_model_listeners: function() {
		return {
			selectionchange: this.get_grid_selection_change_handler()
		}
	},	
	
	get_grid_config: function() {
		var config = {
			layout: 'fit'
		};
		if (this.checkable) {
			config.deferRowRender = false;
		}
		return config;
	},
	
	get_grid_columns: function() {
		return [
			{header: 'Id', dataIndex: 'id'}
		];
	},
			
	get_cell_dblclick_handler: function() {
		if (this.row_editing) {
			return false;
		}
		return Ext.createDelegate(function(grid, rowIndex, cellIndex, ev) {
			var record = grid.store.getAt(rowIndex);
			if (!record) return false;
			this.show_edit_window(record, ev);
			return false;
		}, this);
	},
	
	get_grid_listeners: function() {
		var listeners = {};
		var cell_dblclick_handler = this.get_cell_dblclick_handler();
		if (cell_dblclick_handler) {
			listeners.celldblclick = cell_dblclick_handler;
		}
		if (this.checkable) {
			listeners.render = Ext.createDelegate(function() {
				this.store.reload();
			}, this);
		}
		return listeners;
	},
	
	get_store_listeners: function() {
		var listeners = {};
		if (this.checkable) {
			listeners.load =  Ext.createDelegate(this.select_rows, this);
		}
		return listeners;
	},
	
	get_grid_: function () {
		var plugins = [];
		var store =  this.get_store();
		var columns = this.get_grid_columns();
		var config = {
			store: store,
			sm: this.get_grid_selection_model(),
			plugins: plugins,
			columns: columns,
			listeners: this.get_grid_listeners()
		};
		Ext.apply(config, this.get_grid_config());
		if (this.grid_id) {
			config.id = this.grid_id;
		}
		
		this.grid = new Ext.grid.GridPanel(config);
		//this.grid = new Ext.list.ListView(config);
		this.grid.pc_crud = this;
		return this.grid;
	},
	
	get_grid: function () {
		var plugins = [];
		var store =  this.get_store();
		var columns = this.get_grid_columns();
		
		if (this.row_editing) {
			plugins.push(this.get_row_editor());
			var edit_fields = this.get_edit_form_fields({});
			var edit_field_keys = {};
			Ext.each(edit_fields, function(field, index) {
				if (field.name) {
					edit_field_keys[field.name] = index + 1;
				}
			});
			Ext.each(columns, function(column, index) {
				if (column.dataIndex && edit_field_keys[column.dataIndex]) {
					column.editor = edit_fields[edit_field_keys[column.dataIndex] - 1];
				}
			});
			
		}
		
		var sm = this.get_grid_selection_model();
		
		if (this.checkable) {
			columns.unshift(sm);
		}
		
		var config = {
			store: store,
			sm: sm,
			plugins: plugins,
			columns: columns,
			listeners: this.get_grid_listeners()
		};
		Ext.apply(config, this.get_grid_config());
		if (this.grid_id) {
			config.id = this.grid_id;
		}
		if (this.per_page) {
			config.bbar = new Ext.PagingToolbar({
				store: store,
				displayInfo: true,
				pageSize: this.per_page,
				prependButtons: true
			});
			this._paging = config.bbar;
		}
		this.grid = new Ext.grid.GridPanel(config);
		//this.grid = new Ext.list.ListView(config);
		this.grid.pc_crud = this;
		return this.grid;
	},
	
	get_row_editor: function() {
		
		var re = new Ext.ux.grid.RowEditor({
			saveText: 'OK',
			clicksToEdit: 2,
			listeners: {
				afteredit_: Ext.createDelegate(function(editor, changes, record, a3) {
					var data = {
						names: {},
						other: changes
					};
					Ext.Ajax.request({
						url: this.api_url +'edit',
						params: {
							id: record.id,
							data: Ext.util.JSON.encode(data)
						},
						method: 'POST',
						callback: edit_ajax_response_callback
					});
				}, this)
			}
		});
		return re;
	},
	
	get_store: function(){
		var config = {
			autoLoad: this.auto_load,
			listeners: this.get_store_listeners(),
			//remoteSort: (this.per_page)?true:false,
			//root: 'list',
			//totalProperty: 'total',
			//idProperty: 'id',
			//data: {data: [], count : 0},
			fields: this.get_store_fields()
		};
		if (this.api_url_get) {
			Ext.apply(config, {
				url: this.api_url_get,
				method: 'POST',
				root: 'list',
				totalProperty: 'total',
				idProperty: this.id_property
			});
		}
		this.store = this._create_store(config);
		return this.store;
	},
	
	_create_store: function(config) {
		return new Ext.data.JsonStore(config);
	},
	
	
	edit_button_handler: function(data, renameWindow, renameDialog) {
		this.form_data = data;
		this.form_field_container = this.edit_window = renameWindow;
		Ext.apply(this.edit_record.data, data.other);
		if (!this.no_commit_after_edit) {
			this.edit_record.commit();
		}
		this.edit_window.close();
		
	},
			
			
	get_tbar: function () {
		return this.get_tbar_items();
	},
			
	get_tbar_buttons: function() {
		var buttons =  [
			this.get_button_for_add(),
			this.get_button_for_edit(),
			this.get_button_for_del()
		];
		if (this.sortable) {
			buttons.push(this.get_button_for_move_up());
			buttons.push(this.get_button_for_move_down());
		}
		return buttons;
	},
		
	get_tbar_filters: function() {
		return [];
	},
			
				
	apply_filters: function() {
		var button_container = this.get_button_container();
		if (this.tbar_filter_refs) {
			var filter_count = 0;
			Ext.each(this.tbar_filter_refs, function(filter_ref, index) {
				if (button_container[filter_ref]) {
					var filter_value = button_container[filter_ref].getValue();
					if (button_container[filter_ref]['_filter_name'] && filter_value) {
						filter_count++;
						this.store.setBaseParam('filters['+button_container[filter_ref]['_filter_name']+']', filter_value);
					}
				}
			}, this);
			if (filter_count) {
				if (!this.store_original_base_params) {
					this.store_original_base_params = this.store.baseParams;
				}
				this.store.load({
					params: {
						start: 0 // reset the start to 0 since you want the filtered results to start from the first page
					}
				});
			}
		}
	},
	
	remove_filters: function() {
		//dialog.store.setBaseParam('site', dialog.Initial_site_value);
		if (this.store_original_base_params) {
			//this.store.baseParams = this.store_original_base_params;
		}
		var button_container = this.get_button_container();
		if (this.tbar_filter_refs) {
			Ext.each(this.tbar_filter_refs, function(filter_ref, index) {
				if (button_container[filter_ref]) {
					var initial_value = '';
					if (button_container[filter_ref].initial_value) {
						 initial_value = button_container[filter_ref].initial_value;
					}
					button_container[filter_ref].setValue(initial_value);
					this.store.setBaseParam('filters['+button_container[filter_ref]['_filter_name']+']', undefined);
				}
			}, this);
		}

		this.store.load({
			params: {
				start: 0 // reset the start to 0 since you want the filtered results to start from the first page
			}
		});
		//filters.order_id.setValue('');
		//filters.search_phrase.setValue('');
		//filters.date_from.setValue(initial_date_from);
		//filters.date_to.setValue(initial_date_to);
	},
	
	get_filter_buttons: function() {
		return [
			{	icon:'images/zoom.png',
				handler: Ext.createDelegate(this.apply_filters, this)
			},
			{	icon:'images/zoom_out.png',
				handler: Ext.createDelegate(this.remove_filters, this)
			}
		];
	},
	
	get_tbar_items: function() {
		var items = this.get_tbar_buttons();
		var filters = this.get_tbar_filters();
		if (filters && filters.length) {
			/*keyup: function(field, event) {
											if(event.getKey() == 13) {
												applyFilter(mytab);
											}
										}
			*/
			Ext.each(filters, function(filter, index) {
				if (filter._filter_name) {
					if (!filter.listeners) {
						filter.listeners = {};
					}
					if (!filter.listeners.keyup) {
						filters[index].enableKeyEvents = true,
						filters[index].listeners.keyup = Ext.createDelegate(function(field, event) {
							if(event.getKey() == 13) {
								this.apply_filters();
							}
						}, this)
					};
				}
			}, this);
			items.push({xtype:'tbfill'});
			items = items.concat(filters, this.get_filter_buttons());
		}
		return items;
	},		
			
	get_button_for_add: function() {
		return {	
			ref: '../action_add',
			text: this.ln.button._add?this.ln.button._add:PC.i18n.add,
			icon: 'images/add.png',
			handler: Ext.createDelegate(this.button_handler_for_add, this)
		}
	},
	
	button_handler_for_add: function() {
		
		this._add_window = new PC.ux.Window({
			modal: true,
			//title: 'Window title',
			//closeAction: 'hide',
			width: 400,
			//height: 400,
			//layout: 'fit',
			layoutConfig: {
				align: 'stretch'
			},
			items: this.get_add_form()
		});
		
		this._add_window.show();
	},
	
	button_handler_for_add_submit: function() {
		if (this.add_form.getForm().isValid()) {
			var values = this.add_form.getForm().getValues();
			var p = new this.store.recordType(values); // create new record
			this.store.add([p]);
			this._add_window.close();
		}
		
	},
	
	button_handler_for_edit: function() {
		if (this.selected_record) {
			this.show_edit_window(this.selected_record);
		}
	},
	
	show_edit_window: function(record, ev) {
		if (!record) return false;
		//if (!record || !ev) return false;
		//var xy = ev.getXY();
		
		this.edit_record = record;
				
		var save_handler = this.get_edit_button_handler();
		
		var multiln_params = {
			title: PC.i18n.edit,
			values: record.data.names,
			//pageX: xy[0], pageY: xy[1],
			fields: this.get_edit_form_fields(record.data),
			Save: save_handler,
			no_ln_fields: this.no_ln_fields,
			window_width: this.edit_window_width
			//center_window: true
		};
		this.adjust_multiln_params(multiln_params);
		PC.dialog.multilnedit.show(multiln_params);
	},
			
	get_edit_button_handler: function() {
		return Ext.createDelegate(this.button_handler_for_edit_submit, this);
	},
	
	button_handler_for_edit_submit: function(form_data, form_window) {
		Ext.iterate(form_data.other, function(key, value){
			this.edit_record.set(key, value); 
		}, this);
		if (form_data.names) {
			this.edit_record.set('names', form_data.names); 
			if (!this.no_ln_fields) {
				this.edit_record.set('name', PC.utils.extractName(form_data.names));
			}
		}
		if (!this.no_commit_after_edit) {
			this.edit_record.commit();
		}
		
		if (form_window) {
			form_window.close();
		}
		
	},
	
	get_button_for_edit: function() {
		return {	
			ref: '../action_edit',
			text: this.ln.button._edit?this.ln.button._edit:PC.i18n.edit,
			icon: 'images/pencil.png',
			disabled: true,
			handler: Ext.createDelegate(this.button_handler_for_edit, this)
		}
	},
	
	get_button_for_refresh: function() {
		return {	
			ref: '../action_refresh',
			icon: 'images/refresh.gif',
			handler: Ext.createDelegate(function() {
				this.store.reload();
			}, this)
		};
	},	
	
	get_button_for_sync: function() {
		return [
			{	text: PC.i18n.save,
				iconCls: 'icon-save',
				ref: '../_action_sync',

				scope: this,
				handler: this.sync_grid
			}
		];
	},	
	
	get_button_for_del: function() {
		return {	
			ref: '../action_del',
			text: this.ln.button._delete?this.ln.button._delete:PC.i18n.del,
			icon: 'images/delete.png',
			handler: Ext.createDelegate(this.button_handler_for_del, this),
			disabled: true,
			_multi_select: true
		};
	},
			
	button_handler_for_del: function() {
		Ext.MessageBox.show({
			buttons: Ext.MessageBox.YESNO,
			title: this.ln._delete.confirm_title,
			msg: this.ln._delete.confirm_message,
			icon: Ext.MessageBox.WARNING,
			maxWidth: 320,
			fn: Ext.createDelegate(this.button_handler_for_del_submit, this)
		});
	},
	
	button_handler_for_del_submit: function(btn_id) {
		if (btn_id == 'yes') {
			var selected_records = this.grid.getSelectionModel().getSelections();
			if(selected_records.length>0) {
                for(var i=0;i<selected_records.length;i++) {
                    this.store.remove(selected_records[i]);
                }
            }
		}
	},		
	
	get_button_for_move_up: function() {
		return {	
			ref: '../action_move_up',
			text: this.ln.button._move_up?this.ln.button._move_up:PC.i18n.move_up,
			icon: 'images/arrow-up.gif',
			disabled: true,
			_multi_select: true,
			handler: Ext.createDelegate(this.button_handler_for_move_up, this)
		}
	},
			
	get_button_for_move_down: function() {
		return {	
			ref: '../action_move_down',
			text: this.ln.button._move_down?this.ln.button._move_down: PC.i18n.move_down,
			icon: 'images/arrow-down.gif',
			disabled: true,
			_multi_select: true,
			handler: Ext.createDelegate(this.button_handler_for_move_down, this)
		}
	},		
	
	button_handler_for_move_up: function() {
		this.move_selected_rows('up');
	},	
			
	button_handler_for_move_down: function() {
		this.move_selected_rows('down');
	},	
		
	get_add_form_fields: function() {
		return [];
	},	
		
	get_add_form: function() {
		this.add_form = new Ext.form.FormPanel({
			ref: '_f',
			//width: this.form_width,
			flex: 1,
			layout: 'form',
			padding: 6,
			border: false,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 100,
			labelAlign: 'right',
			defaults: {xtype: 'textfield', anchor: '100%'},
			items: this.get_add_form_fields(),
			frame: true,
			buttonAlign: 'center',
			buttons: [
				{	text: PC.i18n.save,
					iconCls: 'icon-save',
					ref: '../../_btn_save',
					handler: Ext.createDelegate(this.button_handler_for_add_submit, this)
				}
			]

		});
		return this.add_form;
	},		
		
	get_empty_edit_form_fields: function() {
		return this.get_add_form_fields(true);
	},	
		
	get_edit_form_fields: function(data) {
		var fields = this.get_empty_edit_form_fields();
		if (data) {
			Ext.each(fields, function(field) {
				if (data[field._fld]) {
					field.value = data[field._fld];
				}
			})
		}
		return fields;
	},	
		
		
	adjust_multiln_params: function(multiln_params) {

	},	
		
	get_grid_selection_change_handler: function () {
		return Ext.createDelegate(function(selModel) {
			this.on_grid_selection_change(selModel);
		}, this);
	},	
			
	get_button_container: function() {
		return this.grid.ownerCt;
	},
	
	get_dynamic_buttons: function(button_container) {
		if (!button_container) {
			button_container = this;
		}
		var buttons = [
			button_container.action_del,
			button_container.action_edit
		];
		if (button_container.action_move_up) {
			buttons.push(button_container.action_move_up);
		}
		if (button_container.action_move_down) {
			buttons.push(button_container.action_move_down);
		}
		return buttons;
	},
	
	update_buttons: function(select_length, sel_model) {
		var button_container = this.get_button_container();
		var buttons = this.get_dynamic_buttons(button_container);
		Ext.each(buttons, function(button, index) {
			if (!button) {
				return;
			}
			if (select_length == 1 || select_length > 1 && button._multi_select) {
				button.enable();
			}
			else {
				button.disable();
			}
		});
	},
	
	on_grid_selection_change: function(selModel) {
		var selected = selModel.getSelections();
		this.selected_id = false;
		if (selected.length) {
			this.selected_id = selected[0].id;
			this.selected_record = selected[0];
		}
		this.update_buttons(selected.length, selModel);
	},		
		
	/*
	get_grid_listeners: function() {
		var listeners = {};
		return listeners;
	},
	*/
		
		
	sync_grid: function() {
		if (this.checkable) {
			var post_params = {data: Ext.util.JSON.encode(this.get_selected_data())};
			post_params.delete_missing = true;
		}
		else {
			var post_params = {data: Ext.util.JSON.encode(this.get_store_data(true))};
		}
		post_params.base_params = Ext.util.JSON.encode(this.grid.store.baseParams);
		Ext.Ajax.request({
			url: this.api_url + 'sync',
			method: 'POST',
			params: post_params,
			callback: Ext.createDelegate(this.ajax_sync_respone_handler, this)
		});
	},
			
	ajax_sync_respone_handler: function(opts, success, response) {
		if (success && response.responseText) {
			try {
				var data = Ext.decode(response.responseText);
				if (data.success) {
					this.ajax_sync_success_respone_handler.defer(0, this, [data]);
					return;
				}
				else {
					error = data.error;
				}
			}
			catch(e) {
				var error = this.ln.error.json;
			};
		}
		else var error = this.ln.error.connection;
		Ext.MessageBox.show({
			title: PC.i18n.error,
			msg: (error?'<b>'+ error +'</b><br />':''),
			buttons: Ext.MessageBox.OK,
			icon: Ext.MessageBox.ERROR
		});
	},
			
	ajax_sync_success_respone_handler: function(data) {
		this.commit_records();
		if (this.reload_after_sync) {
			this.store.reload();
		}
	},
		
	commit_records: function() {
		this.store.commitChanges();
	},	
		
	select_rows: function() {
		this._rows_to_select = [];
		var store_data = this.get_store_data();
		Ext.iterate(store_data, function(data, index){
			if (data.checked) {
				this._rows_to_select.push(index);
			}
		}, this);
		
		if (!this.grid.rendered) {
			//this.grid.addListener('render', this.select_rows_when_rendered, this);
		}
		else {
			this.select_rows_when_rendered();
		}
		
	},
		
	select_rows_when_rendered: function() {
		var sm  = this.grid.getSelectionModel();
		sm.selectRows(this._rows_to_select);
	},
		
	get_store_data: function(modified_only) {
		if (modified_only) {
			return Ext.pluck(this.store.getModifiedRecords(), 'data');
		}
		return Ext.pluck(this.store.data.items, 'data');
	},		
		
	get_selected_data: function() {
		return Ext.pluck(this.grid.getSelectionModel().getSelections(), 'data');
	},
	
	pc_get_data: function() {
		var optionAttributes = this.get_store_fields();
		var rows = this.store.getRange();
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
		return options;
	},
			
	load_data: function(data) {
		if (data.list) {
			this.store.loadData(data.list);
		} 
		else {
			this.store.loadData(data);
		}
	},
	
	format_time_to_date: function(time){
		return new Date(time*1000).format('Y-m-d H:i');
	}
	
});
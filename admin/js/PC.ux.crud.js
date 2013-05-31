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

PC.ux.crud = Ext.extend(Ext.Panel, {
	api_url: '',
	per_page: false,
	auto_load: true,
	base_params: {},
	no_ln_fields: false,

	row_editing: false,

	layout: 'fit',

    constructor: function(config) {
		this.ln = this.get_ln();

		if (config.api_url) {
			this.api_url = config.api_url;
		}

		if (config.ln) {
			Ext.apply(this.ln, config.ln);
			delete config.ln;
		}

		config = Ext.apply({
			tbar: this.get_tbar(),
			items: this.get_items()
        }, config);

        PC.ux.crud.superclass.constructor.call(this, config);
		
		this.set_titles();
    },
	
	get_items: function() {
		return this.get_grid();
	},
	
	get_ln: function() {
		return PC.i18n.pc_ux_crud;
	},
	
	set_titles: function() {
		this.title = this.ln.title;
	},
	
	get_store: function(){
		var store_url =  this.api_url +'get/';
		if (this.store_admin_ln) {
			store_url += '?ln=' + PC.global.admin_ln
		}
		this.store = new Ext.data.JsonStore({
			url: store_url,
			method: 'POST',
			autoLoad: this.auto_load,
			remoteSort: (this.per_page)?true:false,
			root: 'list',
			totalProperty: 'total',
			idProperty: 'id',
			fields: this.get_store_fields(),
			perPage: this.per_page
		});
		if (this.per_page) {
			this.store.setBaseParam('limit', this.per_page);
		}
		return this.store;
	},
	
	get_store_fields: function() {
		return [
				'id'
		];
	},
	
	get_grid_selection_model: function() {
		return new Ext.grid.RowSelectionModel({
			listeners: {
				selectionchange: this.get_grid_selection_change_handler()
			}
		});
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
		return listeners;
	},
	
	get_grid_config: function() {
		return {};
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
				edit_field_keys[field.name] = index + 1;
			})
			Ext.each(columns, function(column, index) {
				if (column.dataIndex && edit_field_keys[column.dataIndex]) {
					column.editor = edit_fields[edit_field_keys[column.dataIndex] - 1];
				}
			})
			
		}
		
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
		if (this.per_page) {
			config.bbar = new Ext.PagingToolbar({
				store: store,
				displayInfo: true,
				pageSize: this.per_page,
				prependButtons: true
			});
		}
		this.grid = new Ext.grid.GridPanel(config);
		//this.grid = new Ext.list.ListView(config);
		this.grid.pc_crud = this;
		return this.grid;
	},
	
	get_tbar_filters: function() {
		return [];
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
	
	get_tbar_buttons: function() {
		return [
			this.get_button_for_add(),
			this.get_button_for_edit(),
			this.get_button_for_del()
		]
	},
	
	get_tbar: function () {
		return this.get_tbar_items();
	},
	
	get_button_for_add: function() {
		return {	
			ref: '../action_add',
			text: this.ln.button._add?this.ln.button._add:PC.i18n.add,
			icon: 'images/add.png',
			handler: this.get_button_handler_for_add()
		}
	},
	
	button_handler_for_edit: function() {
		if (this.selected_record) {
			this.show_edit_window(this.selected_record);
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
	
	get_button_for_del: function() {
		return {	
			ref: '../action_del',
			text: this.ln.button._delete?this.ln.button._delete:PC.i18n.del,
			icon: 'images/delete.png',
			handler: this.get_button_handler_for_delete(),
			disabled: true,
			_multi_select: true
		};
	},
	
	get_add_form_fields: function() {
		return [];
	},
	
	get_edit_form_fields: function(data) {
		var fields = this.get_add_form_fields(true);
		if (data) {
			Ext.each(fields, function(field) {
				if (data[field._fld]) {
					field.value = data[field._fld];
				}
			})
		}
		return fields;
	},
	
	get_grid_columns: function() {
		return [
			{header: 'Id', dataIndex: 'id'},
		];
	},
	
	
//	Ext.Ajax.request({
//		url: this.api_url + 'edit',
//		method: 'POST',
//		params: {id: this.edit_record.id, data: Ext.util.JSON.encode(data)},
//		callback: this.ajax_edit_respone_handler
//	});
	get_row_editor: function() {
		var edit_ajax_response_callback = Ext.createDelegate(function(opts, success, rspns) {
			if (success && rspns.responseText) {
				try {
					if (rspns.responseText == 'errors') {
						//alert('errors occured');
					}
					else {
						this.store.reload();
						return; // OK
					}
				} catch(e) {};
			}
		}, this);
		
		var re = new Ext.ux.grid.RowEditor({
			saveText: 'OK',
			clicksToEdit: 2,
			listeners: {
				afteredit: Ext.createDelegate(function(editor, changes, record, a3) {
					var data = {
						names: {},
						other: changes
					}
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
	
	get_save_error_message: function(error_data, form) {
		var field = error_data.field;
		if (form && form['_' + field]) {
			field = form['_' + field].fieldLabel;
		}
		var message = '';
		if (error_data.error) {
			message = error_data.error;
			if (this.ln.error[message]) {
				message = this.ln.error[message];
			}
			message = '<br />' + message;
		}
		return '<b>' + field + '</b>' + message;
	},
	
	ajax_add_response_success_handler: function (data) {
		var store = this.store;
		var n = new store.recordType(data);
		store.addSorted(n);
		if (!this.no_ln_fields) {
			n.set('name', PC.utils.extractName(data.names));
		}
		if (this.per_page) {
			store.reload();
		}
		if (this._add_success_callback) {
			this._add_success_callback();
		}
	},
	
	get_button_handler_for_add: function() {
		
		this.ajax_add_respone_handler = Ext.createDelegate(function(opts, success, response) {
			if (success && response.responseText) {
				try {
					var data = Ext.decode(response.responseText);
					if (data.success) {
						this.ajax_add_response_success_handler(data);
						return;
					}
					else {
						error = data.error;
						if (data.error_data) {
							error = this.get_save_error_message(data.error_data,  this.add_form);
						}
					}
				}
				catch(e) {
					var error = this.ln.error.json;
				};
			}
			else var error = this.ln.error.connection;
			Ext.MessageBox.show({
				title: PC.i18n.error,
				msg: (error?'<b>'+ error +'</b>':''),
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.ERROR
			});
		}, this);
		
		var save_handler = Ext.createDelegate(function(data, w, dlg, callback) {
			this._add_success_callback = callback;
			this.add_form = w;
			Ext.Ajax.request({
				url: this.api_url + 'create',
				method: 'POST',
				params: Ext.apply({data: Ext.util.JSON.encode(data)}, this.base_params),
				callback: this.ajax_add_respone_handler
			});
			return true;
		}, this);
		return Ext.createDelegate(function() {
			PC.dialog.multilnedit.show({
				title: PC.i18n.menu.addNew,
				fields: this.get_add_form_fields(),
				Save: save_handler,
				close_in_callback: true,
				no_ln_fields: this.no_ln_fields,
				window_width: this.add_window_width
			});
		}, this);
	},
	
	ajax_edit_response_success_handler: function (data, form_data) {
		if (form_data) {
			Ext.iterate(form_data.other, function(key, value){
				this.edit_record.set(key, value); 
			}, this);
			if (form_data.names) {
				this.edit_record.set('names', form_data.names); 
				if (!this.no_ln_fields) {
					this.edit_record.set('name', PC.utils.extractName(form_data.names));
				}
			}
			this.edit_record.commit();
		}
		if (!this.per_page) {
			var ss = this.store.getSortState();
			if (ss) {
				this.store.sort(ss.field, ss.direction);
			}
		}
		if (this.per_page) {
			this.store.reload();
		}
	},
	
	get_ajax_edit_response_handler: function() {
		return Ext.createDelegate(function(opts, success, response) {
			if (success && response.responseText) {
				try {
					var data = Ext.decode(response.responseText);
					if (data.success) {
						this.ajax_edit_response_success_handler(data, this.form_data);
						if (this.edit_window) {
							this.edit_window.close();
						}
						return;
					}
					else {
						error = data.error;
						if (data.error_data) {
							var form_field_container = this.form_field_container;
							if (!form_field_container) {
								form_field_container = this.edit_form;
							}
							error = this.get_save_error_message(data.error_data, form_field_container);
						}
					}
					
				} catch(e) {
					var error = this.ln.error.json;
				};
			}
			else {
				var error = this.ln.error.connection;
			}
			Ext.MessageBox.show({
				title: PC.i18n.error,
				msg: error?error:this.ln.error.did_not_save,
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.ERROR
			});
		}, this);
	},
	
	get_edit_button_handler: function() {
		this.ajax_edit_respone_handler = this.get_ajax_edit_response_handler();
		return Ext.createDelegate(this.edit_button_handler, this);
	},
	
	edit_button_handler: function(data, renameWindow, renameDialog) {
		this.form_data = data;
		this.form_field_container = this.edit_window = renameWindow;
		Ext.Ajax.request({
			url: this.api_url + 'edit',
			method: 'POST',
			params: {id: this.edit_record.id, data: Ext.util.JSON.encode(data)},
			callback: this.ajax_edit_respone_handler
		});
	},
	
	show_edit_window: function(record, ev) {
		if (!record) return false;
		//if (!record || !ev) return false;
		//var xy = ev.getXY();
		
		this.edit_record = record;
				
		var save_handler = this.get_edit_button_handler();
		
		PC.dialog.multilnedit.show({
			title: PC.i18n.menu.rename,
			values: record.data.names,
			//pageX: xy[0], pageY: xy[1],
			fields: this.get_edit_form_fields(record.data),
			Save: save_handler,
			no_ln_fields: this.no_ln_fields,
			window_width: this.edit_window_width
			//center_window: true
		});
	},
	
	get_button_handler_for_update: function() {
		
	},
	
	get_button_handler_for_delete: function() {
		var ln = this.ln;
		var selected_records = false;
		this.ajax_del_response_success_handler = function (data) {
			if (this.per_page) {
				this.grid.store.reload();
			}
			else {
				if (selected_records) {
					this.store.remove(selected_records);
				}
			}
		}
		
		this.ajax_del_respone_handler = Ext.createDelegate(function(opts, success, response) {
			if (success && response.responseText) {
				try {
					var data = Ext.decode(response.responseText);
					if (data.success) {
						this.ajax_del_response_success_handler(data);
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
				msg: (error?'<b>'+ error +'</b><br />':'') +this.ln.error.did_not_delete,
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.ERROR
			});
		}, this);
		
		var delete_handler_confirmed = Ext.createDelegate(function(btn_id) {
			if (btn_id == 'yes') {
				var ids = this.get_selected_ids();
				selected_records = this.grid.getSelectionModel().getSelected();
				if (!ids.length) {
					return;
				}
				Ext.Ajax.request({
					url: this.api_url + 'delete',
					method: 'POST',
					params: {ids: Ext.util.JSON.encode(ids)},
					callback: this.ajax_del_respone_handler
				});
			}
		}, this);
		
		return function(b, e) {
			Ext.MessageBox.show({
				buttons: Ext.MessageBox.YESNO,
				title: ln._delete.confirm_title,
				msg: ln._delete.confirm_message,
				icon: Ext.MessageBox.WARNING,
				maxWidth: 320,
				fn: delete_handler_confirmed
			});
		}
	},
	
	get_selected_ids: function() {
		var selected = this.grid.selModel.getSelections();
		if (!selected.length) return false;
		var ids = '';
		var id_array = [];
		for (var a=0; selected[a]; a++) {
			if (ids != '') ids += ',';
			ids += selected[a].data.id;
			id_array.push(selected[a].data.id);
		}
		return id_array;
		return ids;
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
	
	get_store_data: function() {
		return Ext.pluck(this.store.data.items, 'data');
	},
	
	load_data: function(data) {
		this.store.loadData(data);
	},
	
	format_time_to_date: function(time){
		return new Date(time*1000).format('Y-m-d H:i');
	}
	
});


Ext.apply(Ext.form.VTypes, {
    daterange : function(val, field) {
        var date = field.parseDate(val);

        if(!date){
            return false;
        }
        if (field.startDateField) {
            var start = Ext.getCmp(field.startDateField);
            if (!start.maxValue || (date.getTime() != start.maxValue.getTime())) {
                start.setMaxValue(date);
                start.validate();
            }
        }
        else if (field.endDateField) {
            var end = Ext.getCmp(field.endDateField);
            if (!end.minValue || (date.getTime() != end.minValue.getTime())) {
                end.setMinValue(date);
                end.validate();
            }
        }
        /*
         * Always return true since we're only using this vtype to set the
         * min/max allowed values (these are tested for after the vtype test)
         */
        return true;
    },

    password_match : function(val, field) {
		if (field.ownerCt._password) {
			return (val == field.ownerCt._password.getValue());
        }
        return true;
    },

    password_matchText : 'Passwords do not match'
});
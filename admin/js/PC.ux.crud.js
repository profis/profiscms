Ext.namespace('PC.ux');

PC.ux.crud = Ext.extend(PC.ux.LocalCrud, {
	api_url: '',
	per_page: false,
	reload_after_save: false,
	reload_after_insert: true,
	auto_load: true,
	base_params: {},
	no_ln_fields: false,

	row_editing: false,

	sortable: false,
	sort_field: 'position',

	get_store: function(){
		var store_url =  this.api_url +'get/';
		if (this.store_admin_ln) {
			store_url += '?ln=' + PC.global.admin_ln;
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

    	
	get_grid_: function () {
		var plugins = [];
		var store =  this.get_store();
		var columns = this.get_grid_columns();
		
		if (this.row_editing) {
			plugins.push(this.get_row_editor());
			var edit_fields = this.get_edit_form_fields({});
			var edit_field_keys = {};
			Ext.each(edit_fields, function(field, index) {
				edit_field_keys[field.name] = index + 1;
			});
			Ext.each(columns, function(column, index) {
				if (column.dataIndex && edit_field_keys[column.dataIndex]) {
					column.editor = edit_fields[edit_field_keys[column.dataIndex] - 1];
				}
			});
			
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
			this._paging = config.bbar;
		}
		this.grid = new Ext.grid.GridPanel(config);
		//this.grid = new Ext.list.ListView(config);
		this.grid.pc_crud = this;
		return this.grid;
	},
	


	
		
	
	move_selected_rows: function(direction){
		direction = direction || 'up';
		var records = this.grid.selModel.getSelections();
		if (!records.length) return;

		var first_index = -1;
		var last_index = -1;

		
		Ext.iterate(records, function(record, index) {
			var my_index = this.grid.getStore().indexOf(record);
			if (first_index == -1) {
				first_index = my_index;
			}
			else if (my_index < first_index) {
				first_index = my_index;
			}
			if (last_index == -1) {
				last_index = my_index;
			}
			else if (my_index > last_index){
				last_index = my_index;
			}
		}, this);

		var row_to_move = false;
		var new_position = -1;
		if (direction == 'up') {
			row_to_move = this.grid.store.getAt(first_index - 1);
			new_position = last_index;
		} 
		else {
			row_to_move = this.grid.store.getAt(last_index + 1);
			new_position = first_index;
		}

		if (row_to_move) {
			this.grid.getStore().remove(row_to_move);
			//var deleted_fields = this.grid.getStore()._deletedFields;
			//delete deleted_fields[row_to_move.id];
			this.grid.getStore()._update_positions = true;
			this._update_positions = true;
			this.grid.getStore().insert(new_position, row_to_move);
			this.save_positions();
		}

	},
			
	save_positions: function() {
		if (this._update_positions) {
			var positions = [];
			Ext.iterate(this.store.getRange(), function(record, index) {
				positions.push(record.data.id);
			}, this);
			
			Ext.Ajax.request({
				url: this.api_url +'set_positions',
				params: {
					positions: Ext.util.JSON.encode(positions)
				},
				method: 'POST'
			});
		}
	},
	
	get_add_form_fields: function() {
		return [];
	},
	
	_render_cell_yes_no: function(value, metaData, record, rowIndex, colIndex, store) {
		if (value == 1) {
			return PC.i18n.yes;
		}
		return PC.i18n.no;
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
	
	_after_insert: function() {

	},
			
	_after_update: function() {

	},
	
	_after_save: function() {

	},
	
	ajax_add_response_success_handler: function (data) {
		var store = this.store;
		var n = new store.recordType(data);
		store.addSorted(n);
		if (!this.no_ln_fields) {
			n.set('name', PC.utils.extractName(data.names));
		}
		if (this.per_page || this.reload_after_save || this.reload_after_insert) {
			store.reload();
		}
		if (this._add_success_callback) {
			this._add_success_callback();
		}
		this._after_insert();
		this._after_save();
	},
	
	ajax_add_respone_handler: function(opts, success, response) {
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
	},
	
	button_handler_for_add: function() {
		var save_handler = Ext.createDelegate(function(data, w, dlg, callback) {
			this._add_success_callback = callback;
			this.add_form = w;
			Ext.Ajax.request({
				url: this.api_url + 'create',
				method: 'POST',
				params: Ext.apply({data: Ext.util.JSON.encode(data)}, this.base_params),
				callback: Ext.createDelegate(this.ajax_add_respone_handler, this)
			});
			return true;
		}, this);
		
		var multiln_params = {
			title: PC.i18n.menu.addNew,
			fields: this.get_add_form_fields(),
			Save: save_handler,
			close_in_callback: true,
			no_ln_fields: this.no_ln_fields,
			window_width: this.add_window_width
		};
		this.adjust_multiln_params(multiln_params);
		PC.dialog.multilnedit.show(multiln_params);
		//PC.dialog.multilnedit.show.defer(0, this, [multiln_params]);
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
		if (this.per_page || this.reload_after_save) {
			this.store.reload();
		}
		this._after_update();
		this._after_save();
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
			params: Ext.apply({id: this.edit_record.id, data: Ext.util.JSON.encode(data)}, this.base_params),
			callback: this.ajax_edit_respone_handler
		});
	},
	
	
	get_button_handler_for_update: function() {
		
	},
	
	ajax_del_response_success_handler: function(opts, success, response) {
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
	},
	
	ajax_del_respone_handler: function(data) {
		if (this.per_page) {
			this.grid.store.reload();
		}
		else {
			if (this.selected_records) {
				this.store.remove(this.selected_records);
			}
		}
	},
		
	button_handler_for_del_submit: function(btn_id) {
		if (btn_id == 'yes') {
			var ids = this.get_selected_ids();
			this.selected_records = this.grid.getSelectionModel().getSelected();
			if (!ids.length) {
				return;
			}
			Ext.Ajax.request({
				url: this.api_url + 'delete',
				method: 'POST',
				params: {ids: Ext.util.JSON.encode(ids)},
				callback: Ext.createDelegate(this.ajax_del_respone_handler, this)
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
	
	
	
	get_tbar_buttons: function() {
		var buttons =  [
			this.get_button_for_add(),
			this.get_button_for_edit(),
			this.get_button_for_refresh(),
			this.get_button_for_del()
		];
		if (this.sortable) {
			buttons.push(this.get_button_for_move_up());
			buttons.push(this.get_button_for_move_down());
		}
		return buttons;
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
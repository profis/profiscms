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
			did_not_delete: 'Data were not deleted.'
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
			did_not_delete: 'Duomenys nebuvo ištrinti.'
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
			did_not_delete: 'Данные не были удалены.'
		}
	}
}

PC.utils.localize('pc_ux_crud', ln);

PC.ux.crud = Ext.extend(Ext.Panel, {
	api_url: '',
	per_page: false,

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
			layout: 'fit',
			tbar: this.get_tbar(),
			items: this.get_grid()
        }, config);

        PC.ux.crud.superclass.constructor.call(this, config);
		
		this.set_titles();
    },
	
	get_ln: function() {
		return PC.i18n.pc_ux_crud;
	},
	
	set_titles: function() {
		this.title = this.ln.title;
	},
	
	get_store: function(){
		this.store = new Ext.data.JsonStore({
			url: this.api_url +'get',
			method: 'POST',
			autoLoad: true,
			remoteSort: true,
			root: 'list',
			totalProperty: 'total',
			idProperty: 'id',
			fields: this.get_store_fields(),
			perPage: this.per_page
		});
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
	
	get_grid_listeners: function() {
		return {
			celldblclick: Ext.createDelegate(function(grid, rowIndex, cellIndex, ev) {
				var record = grid.store.getAt(rowIndex);
				if (!record) return false;
				this.show_edit_window(record, ev);
				return false;
			}, this)
		}
		
	},
	
	get_grid: function () {
		this.grid = new Ext.grid.GridPanel({
			store: this.get_store(),
			sm: this.get_grid_selection_model(),
			//plugins: dialog.expander,
			columns: this.get_grid_columns(),
			listeners: this.get_grid_listeners()
		});
		return this.grid;
	},
	
	get_tbar: function () {
		return [
			this.get_button_for_add(),
			this.get_button_for_del()
		]
	},
	
	get_button_for_add: function() {
		return {	
			ref: '../action_add',
			text: this.ln.button._add?this.ln.button._add:PC.i18n.add,
			icon: 'images/add.png',
			handler: this.get_button_handler_for_add()
		}
	},
	
	get_button_for_del: function() {
		return {	
			ref: '../action_del',
			text: this.ln.button._delete?this.ln.button._delete:PC.i18n.del,
			icon: 'images/delete.png',
			handler: this.get_button_handler_for_delete(),
			disabled: true
		};
	},
	
	get_add_form_fields: function() {
		return [];
	},
	
	get_edit_form_fields: function(data) {
		var fields = this.get_add_form_fields();
		Ext.each(fields, function(field) {
			if (data[field._fld]) {
				field.value = data[field._fld];
			}
		})
		return fields;
	},
	
	get_grid_columns: function() {
		return [
			{header: 'Id', dataIndex: 'id'},
		];
	},
	
	get_button_handler_for_add: function() {
		
		this.ajax_add_response_success_handler = function (data) {
			var store = this.store;
			var n = new store.recordType(data);
			store.addSorted(n);
			n.set('name', PC.utils.extractName(data.names));
			//refresh attribute selection store
			//PC.plugin.pc_shop.attributes.Store.reload();
		}
		
		this.ajax_add_respone_handler = Ext.createDelegate(function(opts, success, response) {
			if (success && response.responseText) {
				try {
					var data = Ext.decode(response.responseText);
					if (data.success) {
						this.ajax_add_response_success_handler(data);
						return;
					}
					else error = data.error;
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
		
		var save_handler = Ext.createDelegate(function(data, w, dlg) {
			Ext.Ajax.request({
				url: this.api_url + 'create',
				method: 'POST',
				params: {data: Ext.util.JSON.encode(data)},
				callback: this.ajax_add_respone_handler
			});
			return true;
		}, this);
		return Ext.createDelegate(function() {
			PC.dialog.multilnedit.show({
				title: PC.i18n.menu.rename,
				fields: this.get_add_form_fields(),
				Save: save_handler
			});
		}, this);
	},
	
	show_edit_window: function(record, ev) {
		if (!record || !ev) return false;
		var xy = ev.getXY();
		
		this.edit_record = record;
		
		this.ajax_edit_response_success_handler = function (data, form_data) {
			Ext.iterate(form_data.other, function(key, value){
				this.edit_record.set(key, value); 
			}, this);
			if (form_data.names) {
				this.edit_record.set('names', form_data.names); 
				this.edit_record.set('name', PC.utils.extractName(form_data.names));
			}
			this.edit_record.commit();
		}
		
		this.ajax_edit_respone_handler = Ext.createDelegate(function(opts, success, response) {
			if (success && response.responseText) {
				try {
					var data = Ext.decode(response.responseText);
					if (data.success) {
						this.ajax_edit_response_success_handler(data, this.form_data);
						this.edit_window.close();
						//this.store.reload();
						return;
					}
					else error = data.error;
				} catch(e) {
					var error = this.ln.error.json;
				};
			}
			else {
				var error = this.ln.error.connection;
			}
			Ext.MessageBox.show({
				title: PC.i18n.error,
				msg: (error?'<b>'+ error +'</b><br />':'') + this.ln.error.did_not_save,
				buttons: Ext.MessageBox.OK,
				icon: Ext.MessageBox.ERROR
			});
		}, this);
		
		var save_handler = Ext.createDelegate(function(data, renameWindow, renameDialog) {
			this.form_data = data;
			this.edit_window = renameWindow;
			Ext.Ajax.request({
				url: this.api_url + 'edit',
				method: 'POST',
				params: {id: this.edit_record.id, data: Ext.util.JSON.encode(data)},
				callback: this.ajax_edit_respone_handler
			});
		}, this);
		
		PC.dialog.multilnedit.show({
			title: PC.i18n.menu.rename,
			values: record.data.names,
			pageX: xy[0], pageY: xy[1],
			fields: this.get_edit_form_fields(record.data),
			Save: save_handler
		});
	},
	
	get_button_handler_for_update: function() {
		
	},
	
	get_button_handler_for_delete: function() {
		var ln = this.ln;
		this.ajax_del_response_success_handler = function (data) {
			this.grid.store.reload();
		}
		
		this.ajax_del_respone_handler = Ext.createDelegate(function(opts, success, response) {
			if (success && response.responseText) {
				try {
					var data = Ext.decode(response.responseText);
					if (data.success) {
						this.ajax_del_response_success_handler(data);
						return;
					}
					else error = data.error;
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
	
	update_buttons: function(enable) {
		var button_container = this.get_button_container();
		var buttons = [
			button_container.action_del
		];
		Ext.each(buttons, function(button, index) {
			if (!button) {
				return;
			}
			if (enable) {
				button.enable();
			}
			else {
				button.disable();
			}
		});
	},
	
	on_grid_selection_change: function(selModel) {
		var selected = selModel.getSelections();
		this.update_buttons(selected.length);
	}
	
});

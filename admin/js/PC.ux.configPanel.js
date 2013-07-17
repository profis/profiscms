Ext.namespace('PC.ux');

var ln =  {
	en: {
		title: 'Config panel',
		save: {
			success: {
				label: 'Status',
				message: 'Changes saved successfully.'
			}
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
		title: 'Nustatymų panelė',
		save: {
			success: {
				label: 'Statusas',
				message: 'Nustatymai sėkmingai pakeisti.'
			}
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
		title: 'Панель настроек',
		save: {
			success: {
				label: 'Статус',
				message: 'Изменения успешно сохранены.'
			}
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

PC.utils.localize('config_panel', ln);

PC.ux.configPanel =Ext.extend(Ext.Panel, {
	controller: '',
	api_url: 'api/plugin/config/config/',
	
	constructor: function(config) {
		this.ln = this.get_ln();
		if (config.ln) {
			Ext.apply(this.ln, config.ln);
			delete config.ln;
		}

		config = Ext.apply({
			//tbar: this.get_tbar(),
			items: this.get_items()
        }, config);

        PC.ux.configPanel.superclass.constructor.call(this, config);
		
		this.set_titles();
    },
	
	get_ln: function() {
		if (!PC.i18n.config_panel) {
			return {};
		}
		return PC.i18n.config_panel;
	},
			
	set_titles: function() {
		this.title = this.ln.title;
	},
	
	get_items: function() {
		return this.get_form();
	},
	
	get_form: function() {
		this.form = new Ext.form.FormPanel({
			ref: '_f',
			//width: this.form_width,
			layout: 'form',
			padding: 6,
			border: false,
			bodyCssClass: 'x-border-layout-ct',
			labelWidth: 100,
			labelAlign: 'top',
			defaults: {xtype: 'textfield', anchor: '100%'},
			items: this.get_form_fields(),
			frame: true,
			buttonAlign: 'center',
			buttons: [
				{	text: PC.i18n.save,
					iconCls: 'icon-save',
					ref: '../../_btn_save',
					handler: Ext.createDelegate(this.handler_for_save, this)
				}
			]
		});
		Ext.Ajax.request({
			url: this.api_url + 'get/' + this.controller,
			callback: Ext.createDelegate(this.ajax_load_response_handler, this)
		});
		return this.form;
	},
	
	ajax_load_response_handler: function(opts, success, response) {
		if (success && response.responseText) {
			try {
				var data = Ext.decode(response.responseText);
				if (data.success) {
					Ext.iterate(this.form.getForm().items.items, function(field, index){
						if (field.name && data.data[field.name]) {
							field.setValue(data.data[field.name]);
						}
					}, this);
					
					return;
				}
				else {
					
				}

			} catch(e) {
				var error = this.ln.error.json;
			};
		}
	},
	
	handler_for_save: function() {
		if(!this.form.getForm().isValid()){
			return;
		}
		
		var data = this.form.getForm().getFieldValues();
		Ext.Ajax.request({
			url: this.api_url + 'save/' + this.controller,
			method: 'POST',
			params: {controller: this.controller, data: Ext.util.JSON.encode(data)},
			callback: Ext.createDelegate(this.ajax_save_response_handler, this)
		});
		
	},
			
	ajax_save_response_handler: function(opts, success, response) {
		var data = Ext.decode(response.responseText);
		if (data.success) {
			Ext.Msg.alert(this.ln.save.success.label, this.ln.save.success.message);
		}
	},
	
	get_form_fields: function() {
		return [];
	}
});


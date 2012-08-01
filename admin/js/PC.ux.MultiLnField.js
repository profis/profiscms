Ext.namespace('PC.ux');

PC.ux.MultiLnField = function(config) {
	Ext.applyIf(config, {
		triggerClass: 'x-form-multiln-trigger',
		editorCfg: {},
		onTriggerClick: function() {
			if (this._queue) return;
			if (!PC.global.pid) return;
			
			this._fldname = this.id.replace(/^db_fld_/i, '');
			this._queue = [];
			this._accum = {};
			PC.global.ln_select.getStore().each(function(rec) {
				var ln = rec.get('ln_id');
				this._queue.push(ln);
				// ***** LOAD REQUEST *****
				Ext.Ajax.request({
					_fld: this,
					url: 'ajax.pagetree.php',
					params: {
						id: PC.global.pid,
						ln: ln
					},
					method: 'POST',
					callback: function(opts, success, rspns) {
						var t = opts._fld;
						if (!t._queue) return;
						if (success && rspns.responseText) {
							try {
								var data = Ext.decode(rspns.responseText);
								t._accum[opts.params.ln] = data[t._fldname];
								t._queue.remove(opts.params.ln);
								if (!t._queue.length) {
									delete t._queue;
									// all loaded
									// ***** MULTILNEDIT DIALOG *****
									var multilncfg = {
										_fld: t,
										_id: opts.params.id,
										values: t._accum,
										title: '',
										callback: function(vals, w) {
											var t = w._fld;
											if (vals) {
												w._ok_btn.disable();
												w._queue2 = [];
												for (var ln in vals) {
													if (vals[ln] != w.values[ln]) {
														w._queue2.push(ln);
														// ***** SAVE REQUEST *****
														var rqp = {
															_window: w,
															url: 'ajax.pagetree.php',
															params: {
																id: w._id,
																ln: ln
															},
															method: 'POST',
															callback: function(opts, success, rspns) {
																var w = opts._window;
																var t = w._fld;
																if (success && rspns.responseText) {
																	try {
																		var data = Ext.decode(rspns.responseText);
																		if (t._fldname == 'name') {
																			var node = PC.tree.component.getNodeById(opts.params.id);
																			node.attributes._names[data.ln] = data.name;
																			PC.tree.component.localizeNode(node);
																		}
																		if ((data.pid == PC.global.pid) && (data.ln == PC.global.ln)) {
																			// Update field
																			t.setValue(data[t._fldname]);
																			t.originalValue = t.getValue();
																			// Update alias field
																			if (t._fldname == 'name') {
																				var fld = Ext.getCmp('db_fld_alias');
																				fld.setValue(data.alias);
																				fld.originalValue = fld.getValue();
																			}
																			// Update previous versions
																			Ext.getCmp('grid_archive').getStore().loadData(data.archive.concat([[0, data.last_update, data.update_by, true]]));
																		}
																		w._queue2.remove(data.ln);
																		if (!w._queue2.length) {
																			// all done
																			w.close();
																		}
																		return; // OK
																	} catch(e) {};
																}
																Ext.MessageBox.show({
																	title: PC.i18n.error,
																	msg: PC.i18n.msg.error.data.save,
																	buttons: Ext.MessageBox.OK,
																	icon: Ext.MessageBox.ERROR
																});
																w._ok_btn.enable();
																delete t._queue;
															}
														};
														rqp.params[t._fldname] = vals[ln];
														Ext.Ajax.request(rqp);
													}
												}
												return !w._queue2.length;
											}
										}
									};
									if (t.editorCfg) Ext.apply(multilncfg, t.editorCfg);
									PC.dialog.multilnedit.show(multilncfg);
								}
								return; // OK
							} catch(e) {};
						}
						Ext.MessageBox.show({
							title: PC.i18n.error,
							msg: PC.i18n.msg.error.data.load,
							buttons: Ext.MessageBox.OK,
							icon: Ext.MessageBox.ERROR
						});
						delete t._queue;
					}
				});
			}, this);
		}
	});
	// call parent constructor
	PC.ux.MultiLnField.superclass.constructor.call(this, config);
};

Ext.extend(PC.ux.MultiLnField, Ext.form.TriggerField, {});

Ext.ComponentMgr.registerType('profis_multilnfield', PC.ux.MultiLnField);

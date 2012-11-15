//There are windows which should be closed when clicked outside:
PC.global.temp_windows = new Array;
PC.global.temp_window_open_event = false;


PC.global.temp_window_callback = function(event, target) {
	if (Ext.getBody().hasClass('x-body-masked')) {
		return;
	}

	var target_el = new Ext.Element(target);

	if (Ext.Msg) {
		var msg_dialog = Ext.Msg.getDialog();
		if (msg_dialog && msg_dialog.id) {
			var was_msg_clicked = target_el.up('#' + msg_dialog.id);
			if (was_msg_clicked) {
				return;
			}
		}
	}

	Ext.each(PC.global.temp_windows, function(w, index) {
			if (w.hidden || w.pc_just_showed) {
				w.pc_just_showed = false;
				return;
			}

			if (PC.global.temp_window_has_opened_child(w)) {
				return;
			}

			var am_i_child = target_el.up('#' + w.id);
			if (!am_i_child && !target_el.hasClass('ext-el-mask') && target_el.parent().getStyle('position') != 'absolute') {
				//console.log(' Hiding this window!')
				w.hide();
			}
		}
	);
}

PC.global.temp_window_has_opened_child = function(w) {
	var has_open_child = false;
	if (!w.pc_temp_window_children) {
		return false;
	}
	Ext.iterate(w.pc_temp_window_children, function(index, w_child) {
		if (typeof w_child === 'object') {
			if (!w_child.hidden) {
				has_open_child = true;
				return false;
			}
		}
		else if (typeof w_child === 'string') {
			
		}
		
	});
	return has_open_child;
};

PC.global.temp_window_listener_beforeshow = function(window) {
	this.pc_just_showed = true;
	Ext.getBody().on('click', PC.global.temp_window_callback);
}

PC.global.temp_window_listener_beforehide = function(window) {
	Ext.getBody().un('click', PC.global.temp_window_callback);
}

Ext.namespace('PC.ux');

PC.ux.Window = function(config) {
	PC.ux.Window.superclass.constructor.call(this, config);
	this.addListener('move', function(window, x, y) {
		if (x < 0 && y < 0) window.setPagePosition(0, 0);
		else if (x < 0) window.setPagePosition(0, y);
		else if (y < 0) window.setPagePosition(x, 0);
	});
	
	if (!this.pc_temp_window_children) {
		this.pc_temp_window_children = {};
	}
	
	if (config.pc_temp_window) {
		this.pc_set_temp_window_true();
	}
	
	this.addListener('beforehide', function(w) {
		if (!w.pc_temp_window_children) {
			return;
		}
		var my_window = w;
		Ext.iterate(w.pc_temp_window_children, function(index, w_child) {
			try {
				if (typeof w_child === 'object') {
					if (!w_child.hidden) {
						w_child.hide();
					}
				}
				else if (typeof w_child === 'string') {
					var comp = Ext.getCmp(w_child);
					if (comp) {
						if (typeof comp.hideMenu == 'function') {
							comp.hideMenu();
						}
						else if (typeof comp.hide == 'function') {
							if (!comp.hidden) {
								comp.hide();
							}
						}
						
					}
				}
				
			}
			catch(err) {
			}
		});
	});

}

Ext.extend(PC.ux.Window, Ext.Window, {
	pc_set_temp_window : function(temp_window) {
		if (temp_window) {
			this.pc_set_temp_window_true();
		}
		else {
			this.pc_set_temp_window_false();
		}
	},
	
	pc_set_temp_window_true : function () {
		this.addListener('beforeshow', PC.global.temp_window_listener_beforeshow);
		this.addListener('beforehide', PC.global.temp_window_listener_beforehide);
		PC.global.temp_windows.push(this);
	},
	
	pc_set_temp_window_false : function () {
		this.removeListener('beforeshow', PC.global.temp_window_listener_beforeshow);
		this.removeListener('beforehide', PC.global.temp_window_listener_beforehide);
		PC.global.temp_windows.remove(this);
	},
	
	initDraggable : function(){
		this.dd = new PC.ux.Window.DD(this);
	}
});


PC.ux.Window.DD = Ext.extend(Ext.Window.DD, {
  
	startDrag : function(){
		var editor_page = Ext.get('pc_editor_page_page');
		if (editor_page) {
			editor_page.mask();
		}		
		PC.ux.Window.DD.superclass.startDrag.call(this);
	},
   
	endDrag : function(e){
		var editor_page = Ext.get('pc_editor_page_page');
		if (editor_page) {
			editor_page.unmask();
		}
		PC.ux.Window.DD.superclass.endDrag.call(this, arguments);
    }
   
});



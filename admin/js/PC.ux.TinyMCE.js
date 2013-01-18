Ext.namespace('PC.ux');
tinymce.create('tinymce.plugins.Profis', {
	/*init: function(editor, url) {
		alert('init '+ editor.id);
	},*/
    createControl: function(n, cm) {
        switch (n) {
            case 'insert_quotes':
                var c = cm.createSplitButton('insert_quotes', {
                    title: PC.i18n.editor.insert_quotes,
					image: 'images/quotes.png',
					onclick: function() {
						var selection = tinyMCE.activeEditor.selection.getContent();
						switch (PC.global.ln) {
							case 'lt':
								tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,'&bdquo;'+selection+'&ldquo;'); //„This sentence is surrounded by &bdquo; and &ldquo;, which are a type of quotation marks.“ 
								break;
							case 'ru':
								tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,'&laquo;'+selection+'&raquo;'); //«This sentence is surrounded by &laquo; and &raquo;, which are a type of quotation marks.» 
								break;
							default:
								tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,'&ldquo;'+selection+'&rdquo;'); //“This sentence is surrounded by &ldquo; and &rdquo;, which are a type of quotation marks.” 
						}
					}
                });
                c.onRenderMenu.add(function(c, m) {
                    m.add({title : '&ldquo; &rdquo;', onclick : function() {
						var selection = tinyMCE.activeEditor.selection.getContent();
						tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,'&ldquo;'+selection+'&rdquo;');
                    }});
                    m.add({title : '&bdquo; &ldquo;', onclick : function() {
						var selection = tinyMCE.activeEditor.selection.getContent();
                        tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,'&bdquo;'+selection+'&ldquo;');
                    }});
					m.add({title : '&laquo; &raquo;', onclick : function() {
						var selection = tinyMCE.activeEditor.selection.getContent();
                        tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,'&laquo;'+selection+'&raquo;');
                    }});
                });
                return c;
			case 'forms':
				var meniuFunction = function ShowMeniu(){
					var DOM = tinymce.DOM;
					e = DOM.get(c.id);
					p2 = DOM.getPos(e);
					var qt = Ext.ux.form.Meniu;
					if (qt.window) {
						if (qt.window.isVisible()) qt.window.hide();
						else qt.show(p2.x+15, p2.y+16, this);
					}
					else qt.show(p2.x+15, p2.y+16, this);
				};
				var c = cm.createSplitButton('forms', {
					title: PC.i18n.forms,
					image: 'images/forms.png',
					onclick: meniuFunction
					
				});
				c.showMenu = meniuFunction;
				return c;
			case 'changecase':
                var c = cm.createSplitButton('changecase', {
                    title: PC.i18n.editor.change_case,
					image: 'images/changecase.png',
					onclick: function() {
						c.showMenu();
					}
                });
                c.onRenderMenu.add(function(c, m) {
                    m.add({title : PC.i18n.editor.lowercase, onclick: function() {
						var selection = tinyMCE.activeEditor.selection.getContent();
						tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,selection.toLowerCase());
                    }});
                    m.add({title : PC.i18n.editor.uppercase, onclick: function() {
						var selection = tinyMCE.activeEditor.selection.getContent();
						tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,selection.toUpperCase());
                    }});
					m.add({title : PC.i18n.editor.capitalize, onclick: function() {
						var selection = tinyMCE.activeEditor.selection.getContent();
						tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,selection.toLowerCase().capitalize());
                    }});
					m.add({title : PC.i18n.editor.sentence_case, onclick: function() {
						var selection = tinyMCE.activeEditor.selection.getContent();
						tinyMCE.execInstanceCommand(tinymce.activeEditor.id,"mceInsertContent",false,selection.sentenceCase());
                    }});
                });
                return c;
			case 'table':
                var c = cm.createSplitButton('table', {
                    title: 'table.desc',
					image: 'images/table.png',
					onclick: function() {
						tinymce.execCommand('mceInsertTable');
					}
                });
				c.showMenu = function() {
					var t = this;
					var DOM = tinymce.DOM;
					e = DOM.get(t.id);
					p2 = DOM.getPos(e);
					var qt = PC.dialog.quicktable;
					if (qt.window) {
						if (qt.window.isVisible()) qt.window.hide();
						else qt.show(p2.x+15, p2.y+16, this);
					}
					else qt.show(p2.x+15, p2.y+16, this);
				};
                return c;
        }
        return null;
    }
});

// Register plugin with a short name
tinymce.PluginManager.add('insert_quotes', tinymce.plugins.Profis);
tinymce.PluginManager.add('changecase', tinymce.plugins.Profis);

PC.ux.TinyMCE = function(config) {
	var cfg = {
		actionMode: 'wrap',
		tinymceSettings: {
			language: PC.global.admin_ln,
			theme: 'advanced',
			skin: 'o2k7',
			plugins: 'autolink,profis_search,profis_link,-insert_quotes,safari,style,layer,table,advhr,advimage,advlist,emotions,iespell,insertdatetime,preview,media,print,contextmenu,paste,directionality,noneditable,visualchars,nonbreaking,xhtmlxtras,template,save,advform',
			theme_advanced_buttons1: 'save,|,undo,redo,search,|,cut,copy,paste,|,link,unlink,anchor,|,table,|,hr,pc_pagebreak,charmap,insert_quotes,|,gallery,gmaps,media,|,forms,|,code',
			theme_advanced_buttons2: 'editstyles,styleselect,removeformat,|,bold,italic,underline,strikethrough,changecase,|,sub,sup,|,forecolor,backcolor,|,bullist,numlist,|,justifyleft,justifycenter,justifyright,justifyfull,|,outdent,indent',
			theme_advanced_buttons3: '',
			theme_advanced_toolbar_location: 'top',
			theme_advanced_toolbar_align: 'left',
			theme_advanced_statusbar_location: 'none',
			theme_advanced_resizing: false,
			media_strict: false, //cms media works only in non-strict mode
			//extended_valid_elements: 'object[id|style|width|height|classid|codebase],embed[src|type|width|height|flashvars|wmode],a[name|href|target|title|onclick|class],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|style],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],input[required|name|style|class|type|value|tabindex|maxlength|readonly|size|id],textarea[rows|cols|required|name|style|class|tabindex|readonly|id]',
			extended_valid_elements: '@[id|class|style|title|dir<ltr?rtl|lang|xml::lang|onclick|ondblclick|onmousedown|onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|onkeydown|onkeyup],'
				+'object[id|style|width|height|classid|codebase|marker_options],embed[src|type|width|height|flashvars|wmode],'
				+'a[name|href|target|title|onclick|class|rel],'
				+'img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name|style],'
				+'hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style|title|id],'
				+'input[required|name|style|class|type|value|tabindex|maxlength|readonly|size|id|checked|placeholder|src|data-maxuploadsize|onfocus|onblur|onchange|onselect],'
				+'textarea[rows|cols|required|name|style|class|tabindex|readonly|id|placeholder|onfocus|onblur|onchange|onselect],'
				+'iframe[width|height|src|frameborder|scrolling|marginheight|marginwidth],fb:like,'
				+'label[for|onfocus|onblur],'
				+'select[size|name|required|multiple|disabled|onfocus|onblur|onchange],'
				+'option[disabled|label|selected|value=],'
				+'form[action|accept|accept-charset|enctype|method|pcformsettings]'
				+'noindex,',
			invalid_elements : "script,style",
			template_external_list_url: 'example_template_list.js',
			content_css: PC.global.BASE_URL+'themes/'+ Get_site()[2] +'/custom.css?'+new Date().getTime(),
			//custom editor css by theme: PC.global.BASE_URL+'media/editor.css, '+ 
			save_onsavecallback: 'PC.editors.Save',
			file_browser_callback: false,
			accessibility_warnings: false,
			entity_encoding: 'raw',
			document_base_url: PC.global.BASE_URL,
			setup: function(ed) {
				ed.addButton('editstyles', {
					title: PC.i18n.edit_styles,
					onclick: function() {
						PC.dialog.styles.show();
					}
				});
				ed.addButton('gallery', {
					title: PC.i18n.gallery,
					image: 'images/picture20.png',
					onclick: function() {
						PC.dialog.gallery.show();
					}
				});
				ed.addButton('gmaps', {
					title: PC.i18n.dialog.gmaps.title,
					image: 'images/gmaps_marker20.png',
					onclick: function() {
						PC.dialog.gmaps.show();
					}
				});
				ed.addButton('pc_pagebreak', {
					title: 'Insert page break',
					image: 'images/document_break.png',
					onclick: function() {
						tinyMCE.execInstanceCommand(tinymce.activeEditor.id, 'mceInsertContent', false, '╬');
					}
				});
				/*ed.addButton('tables', {
					title: PC.i18n.dialog.tables.title,
					onclick: function() {
						PC.dialog.tables.show(ed.parents);
					}
				});*/
				/* temporarily disabled due to the lack of the dynamic tinymce buttons control
				ed.addButton('preview_page', {
					title: PC.i18n.menu.preview,
					image: 'images/eye20.png',
					onclick: function() {
						var n = PC.tree.component.getNodeById(PC.global.pid);
						Preview_page(n);
					}
				});*/
				ed.onDblClick.add(function(ed, e) {
					var target_class = e.target.className;
					if (target_class == 'mceItemGmap') {
						var after_show_callback = function() {
							PC.dialog.gmaps.edit_map(e.target);
						}
						PC.dialog.gmaps.show(after_show_callback);
					}
					else if (target_class == 'mceItemFlash') {
						PC.dialog.media.show(e.target);
					}
					else if (e.target.nodeName == 'IMG' && !/mce.+/.test(target_class)) {
						PC.dialog.image.show(e.target);
					}
					else if (target_class == 'mceItemAnchor') {
						PC.dialog.anchor.show();
					}
					else if (e.target.nodeName == 'A') {
						PC.dialog.links.show();
					}
					else if (/mceItemForm_.+/.test(target_class)) {
						Ext.ux.form.Params.show(e.target);
					}
				});
				//ed.addCommand('mceGalleryImage', function() {});
				//remove preloader mask when the last editor has finished loading
				ed.onPostRender.add(function(ed){
					if (ed.id == 'db_fld_info3') {
					//if (ed.id == 'absolut_tinymce') {	
						Load_home_page_on_tree_load();
						var mask = Ext.get('loading-mask');
						if (mask) mask.remove();
					}
				});
				PC.hooks.Init('tinymce.setup', {editor: ed});
			},
			paste_preprocess: function(pl, o) {
				if (/<\w+(\s+[^>])*>/i.test(o.content)) var rich = true;
				if (rich) {
					var answer = confirm(PC.i18n.msg.paste_as_plain)
					if (answer) pl.editor.pasteAsPlainText = true;
				}
			},
			paste_postprocess: function(pl, o) {
				//remove all borders
				o.node.innerHTML = o.node.innerHTML.replace(/border="\d+"/mig, 'border="0"');
				//remove cellspacing/padding
				//o.node.innerHTML = o.node.innerHTML.replace(/(cellpadding|cellspacing)="\d+"/mig, '');
				//remove borders in table: .replace(/(<table.+?)border="\d+"(.+?<\/table>)/mig, '$1border="0"$2')
				//remove <p> tags in table
				o.node.innerHTML = o.node.innerHTML.replace(/(<td[^>]*>)\s*<p>(.+?)<\/p>\s*(<\/td>)/mig, '$1$2$3');
				setTimeout(function(){
					tinymce.editors[0].addVisual();
				}, 50);
			},
			paste_text_replacements : [
				//Copy paste from tiny_mce\plugins\paste\editor_plugin.js and modified:
				[/\u2026/g, "..."],
				//Profis: new line:
				[/[\x93\x94\u201d]/g, '"'],
				//Profis: commented line:
				//[/[\x93\x94\u201c\u201d]/g, '"'],
				[/[\x60\x91\x92\u2018\u2019]/g, "'"]

			]
		}
	};
	if (Ext.isGecko || Ext.isOpera || Ext.isChrome)
		cfg.tinymceSettings.theme_advanced_buttons2 = cfg.tinymceSettings.theme_advanced_buttons2.replace(/,paste,/, ',');

	PC.hooks.Init('tinymce.config', {
		config: cfg,
		buttons1: cfg.tinymceSettings.theme_advanced_buttons1,
		buttons2: cfg.tinymceSettings.theme_advanced_buttons2,
		buttons3: cfg.tinymceSettings.theme_advanced_buttons3
	});
	
	if (!PC.ux.TinyMCE.WindowManager)
		PC.ux.TinyMCE.WindowManager = Ext.extend(tinymce.WindowManager, {
			// THIS BLOCK COPY-PASTED FROM Ext.ux.TinyMCE
			/** ----------------------------------------------------------
				Config parameters:
				editor - reference to TinyMCE intstance.
				mangager - WindowGroup to use for the popup window. Could be empty.
			*/
			constructor: function(cfg) {
				PC.ux.TinyMCE.WindowManager.superclass.constructor.call(this, cfg.editor);
				// Set window group
				this.manager = cfg.manager;
			},
			
			/** ----------------------------------------------------------
			*/
			alert: function(txt, cb, s) {
				Ext.MessageBox.alert("", txt, function() {
					if (!Ext.isEmpty(cb)) {
						cb.call(this);
					}
				}, s);
			},
			
			/** ----------------------------------------------------------
			*/
			confirm: function(txt, cb, s) {
				Ext.MessageBox.confirm("", txt, function(btn) {
					if (!Ext.isEmpty(cb)) {
						cb.call(this, btn == "yes");
					}
				}, s);
			},
			
			/** ----------------------------------------------------------
			*/
			open: function(s, p) {
				//alert('open '+s.width+'x'+s.height);
				
				s = s || {};
				p = p || {};
				
				this.features = s;
				this.params = p;
				
				if (!s.type)
					this.bookmark = this.editor.selection.getBookmark('simple');
				
				var wincfg = {
					modal: true,
					constrain: true,
					bufferResize: false,
					manager: this.manager,
					layout: 'fit',
					items: [{
						xtype: 'box',
						autoEl: {
							tag: 'iframe',
							frameborder: 0, // IE hack
							style: 'overflow:hidden',
							scrolling: 'no',
							src: s.url || s.file,
							onload: "var x=Ext.getCmp(Ext.get(this).findParent('.x-window').id);if(!x)alert('notfound'); if(x && x._onload)x._onload.call(x,this)"
						},
						style: {
							border: 0
						},
						listeners: {
							render: function(box) {
								var win = box.ownerCt;
								var avail = win.container.getViewSize();
								win.boxMaxWidth = avail.width;
								win.boxMaxHeight = avail.height;
								win.setSize(box.getWidth() + win.getFrameWidth(), box.getHeight() + win.getFrameHeight());
								delete win.boxMaxWidth;
								delete win.boxMaxHeight;
							}
						}
					}],
					_onload: function(iframe) {
						this.setTitle(iframe.contentWindow.document.title);
					}
				}
				
				if (s.width) s.width = parseInt(s.width);
				if (s.height) s.height = parseInt(s.height);
				if (s.left) s.left = parseInt(s.left);
				if (s.top)	s.top = parseInt(s.top);
				
				if (s.width) wincfg.items[0].style.width = s.width+'px';
				if (s.height) wincfg.items[0].style.height = s.height+'px';
				if (s.left) wincfg.x = s.left;
				if (s.top) wincfg.y = s.top;
				
				if (s.name) wincfg.title = s.name;
				if (s.resizable !== undefined)
					wincfg.resizable = s.resizable && (s.resizable != 'false');
				if (s.maximizable !== undefined)
					wincfg.maximizable = s.maximizable && (s.maximizable != 'false');
				
				p.mce_width = s.width;
				p.mce_height = s.height;
				p.mce_auto_focus = s.auto_focus;
				
				var win = new PC.ux.Window(wincfg);
				
				p.mce_window_id = win.getId();
				
				win.show();
				/*win.show(null, function() {
					if (s.left && s.top)
						win.setPagePosition(s.left, s.top);
					var pos = win.getPosition();
					s.left = pos[0];
					s.top = pos[1];
					this.onOpen.dispatch(this, s, p);
				}, this);*/
				
				return win;
			},

			/** ----------------------------------------------------------
			*/
			close: function(win) {
				// Probably not inline
				if (!win.tinyMCEPopup || !win.tinyMCEPopup.id) {
					PC.ux.TinyMCE.WindowManager.superclass.close.call(this, win);
					return;
				}
				
				var w = Ext.getCmp(win.tinyMCEPopup.id);
				if (w) {
					this.onClose.dispatch(this);
					w.close();
				}
			},

			/** ----------------------------------------------------------
			*/
			setTitle: function(win, ti) {
				// Probably not inline
				if (!win.tinyMCEPopup || !win.tinyMCEPopup.id) {
					PC.ux.TinyMCE.WindowManager.superclass.setTitle.call(this, win, ti);
					return;
				}
				
				var w = Ext.getCmp(win.tinyMCEPopup.id);
				if (w) w.setTitle(ti);
			},

			/** ----------------------------------------------------------
			*/
			resizeBy: function(dw, dh, id) {
				var win = Ext.getCmp(id);
				if (win) {
					// reverse w/h -> dw/dh
					var iframe = win.getComponent(0).el.dom;
					var iwin = iframe.contentWindow;
					var dim = iwin.tinyMCEPopup ?
						iwin.tinyMCEPopup.dom.getViewPort.call(iwin.tinyMCEPopup.dom, iwin) :
						tinymce.DOM.getViewPort.call(tinymce.DOM, iframe.contentWindow);
					var wantw = dim.w + dw;
					var wanth = dim.h + dh;
					//alert('resize to: '+wantw+'x'+wanth);
					
					var avail = win.container.getViewSize();
					win.boxMaxWidth = avail.width;
					win.boxMaxHeight = avail.height;
					win.setSize(wantw + win.getFrameWidth(), wanth + win.getFrameHeight());
					delete win.boxMaxWidth;
					delete win.boxMaxHeight;
				}
			},

			/** ----------------------------------------------------------
			*/
			focus: function(id) {
				var w = Ext.getCmp(id);
				if (w) w.setActive(true);
			}
		});
	
	PC.utils.applyProps(cfg, config);
	
	this.addEvents('editorcreated', 'paste');
	
	// call parent constructor
	PC.ux.TinyMCE.superclass.constructor.call(this, cfg);
	
	this.on({});
};

Ext.extend(PC.ux.TinyMCE, Ext.form.Field, {
	defaultAutoCreate: {
		tag: 'textarea'
	},
	isDirty: function() {
		return PC.ux.TinyMCE.superclass.isDirty.call(this);
	},
	initTinyMCE: function() {
		//this.tinymceSettings.content_css = PC.global.BASE_URL+'style.php?site='+PC.global.site;
		this.tinymceSettings.content_css = PC.global.BASE_URL+'admin/editor.php?id='+PC.global.site
				+', '+ PC.global.BASE_URL+'themes/'+ Get_site()[2] +'/custom.css?'+new Date().getTime();
		//custom editor css by theme: PC.global.BASE_URL+'themes/'+ PC.global.SITES[PC.global.site][2] +'/editor.css, '+ 
		var id = this.getId();
		this.ed = new tinymce.Editor(id, this.tinymceSettings);
		this.container.dom.style.opacity = 0;
		this.ed.render();
		this.ed.onInit.add(function(mce, evt) {
			this.ed.windowManager = new PC.ux.TinyMCE.WindowManager({
				editor: this.ed,
				manager: this.manager
			});
			if (this.lastSize)
				this.onResize(this.lastSize.width, this.lastSize.height, this.lastSize.width, this.lastSize.height);
			this.fireEvent('editorcreated', mce);
			(function() {
				this.container.dom.style.opacity = '';
			}).defer(1, this);
		}.createDelegate(this));
		this.ed.onPaste.add(function(mce, evt) {
			this.fireEvent('paste', mce, evt);
		}.createDelegate(this));
		//always append this div(clear:both) on the end of all editors so floating media wont step out of the editor window
		/*this.ed.onInit.add(function(ed){
			PC.b = ed.getBody();
			PC.b.setAttribute('height', '100%');
			/*var div = d.createElement('div');
			div.setAttribute('style', 'height:200px;background:#aaa;clear:both');
			d.body.appendChild(div);
			console.log(div);* /
		});*/
	},
	onRender: function(ct, position) {
		PC.ux.TinyMCE.superclass.onRender.call(this, ct, position);
		
		this.initTinyMCE();
	},
    onResize: function(adjWidth, adjHeight, rawWidth, rawHeight) {
		PC.ux.TinyMCE.superclass.onResize.call(this, adjWidth, adjHeight, rawWidth, rawHeight);
		if (typeof adjWidth == 'number')
			if (this.ed.theme) {
				var table = this.ed.getContainer().getElementsByTagName('table')[0];
				var iframe = this.ed.getContentAreaContainer().getElementsByTagName('iframe')[0];
				var dw = table.offsetWidth - iframe.offsetWidth;
				var dh = table.offsetHeight - iframe.offsetHeight;
				var w = adjWidth - dw;
				var h = adjHeight - dh;
				this.ed.theme.resizeTo(w, h);
			}
	},
	getRawValue: function() {
		if (this.ed.serializer)
			this.el.dom.value = this.ed.getContent();
		return PC.ux.TinyMCE.superclass.getRawValue.call(this);
	},
	getValue: function() {
		if (this.ed.serializer)
			this.el.dom.value = this.ed.getContent();
		return PC.ux.TinyMCE.superclass.getValue.call(this);
	},
	setValue: function(value) {
		PC.ux.TinyMCE.superclass.setValue.call(this, value);
		if (this.ed)
			this.ed.setContent(value ? value : '');
	},
	restart: function() {
		this.el.dom.value = this.ed.getContent();
		
		tinymce.execCommand('mceRemoveControl', true, this.ed.id);
		this.ed.destroy(true);
		
		this.initTinyMCE();
	}
});

Ext.ComponentMgr.registerType('profis_tinymce', PC.ux.TinyMCE);



PC.ux.VirtualTinyMCE = function(config) {
	PC.ux.VirtualTinyMCE.superclass.constructor.call(this, config);
};

Ext.extend(PC.ux.VirtualTinyMCE, Ext.form.Field, {
	defaultAutoCreate: {
		tag: 'textarea'
	},
	restart: function() {
		
	}
});

Ext.ComponentMgr.registerType('profis_virtual_tinymce', PC.ux.VirtualTinyMCE);

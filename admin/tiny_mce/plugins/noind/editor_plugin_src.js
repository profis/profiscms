(function() {
	//tinymce.PluginManager.requireLangPack('noind');
	tinymce.create('tinymce.plugins.NoindPlugin', {
		init : function(ed, url) {
		this.editor = ed;
			ed.addCommand('mceNoind', function() {

				ed.windowManager.open({
					file: url + '/dialog.htm',
					width: 120,
					height: 70,
					inline: 1
				}, {
					plugin_url : url
					//some_custom_arg : 'custom arg'
				});


			});
 
		   ed.addButton('noind', {
				title: '<noindex>',
				cmd: 'mceNoind',
				image: url + '/img/example.gif'
		   });
		   
		   //ed.addShortcut('ctrl+k', false, 'mceNoind');
 
			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('noind', n.nodeName == 'IMG');
			});
		   

		   
		   
		   
		   
		  },
		 
		   createControl : function(n, cm) {
		       return null;
		   },
		 
		  getInfo : function() {
		   return {
			longname : 'Noindex Plugin',
			author: 'Owlman',
			authorurl: 'http://owlman.net/',
			version: "1.0"
		   };
		  }
	 });
 

   tinymce.PluginManager.add('noind', tinymce.plugins.NoindPlugin);
})();

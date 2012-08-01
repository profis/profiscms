(function() {
	tinymce.create('tinymce.plugins.profis_search', {
		init : function(ed, url) {
			function open(m) {
				//ProfisCMS replacement
				PC.dialog.search.show();
				if (m == 'replace') {
					PC.dialog.search.tabs.setActiveTab('search-tab-replace');
				}
			};
			// Register commands
			ed.addCommand('mceSearch', function() {
				open('search');
			});
			ed.addCommand('mceReplace', function() {
				open('replace');
			});
			// Register buttons
			ed.addButton('search', {title: PC.i18n.find, cmd: 'mceSearch'});
			ed.addButton('replace', {title: PC.i18n.replace, cmd: 'mceReplace'});
			ed.addShortcut('ctrl+f', PC.i18n.find, 'mceSearch');
		}
	});
	// Register plugin
	tinymce.PluginManager.add('profis_search', tinymce.plugins.profis_search);
})();
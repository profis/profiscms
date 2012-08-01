Ext.ns('PC.plugins', 'PC.plugin');
PC.plugins.GetUrl = function(plugin) {
	return PC.global.BASE_URL + PC.global.directories.plugins +'/'+ plugin +'/';
}
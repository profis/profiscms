Ext.ns('PC.hooks');
PC.hooks.list = {};
PC.hooks.Register = function(hook, callback) {
	if (PC.hooks.list[hook] == undefined) PC.hooks.list[hook] = [];
	PC.hooks.list[hook].push(callback);
}
PC.hooks.Init = function(hook, params, finish_callback) {
	var count = 0;
	if (typeof PC.hooks.list[hook] == 'object') {
		Ext.iterate(PC.hooks.list[hook], function(callback){
			callback(params);
			count++;
		});
	}
	if (typeof finish_callback == 'function') finish_callback(count);
	return true;
}
PC.hooks.Count = function(hook) {
	if (PC.hooks.list[hook] == undefined) return 0;
	if (typeof PC.hooks.list[hook] != 'object') return 0;
	return PC.hooks.list[hook].length;
}
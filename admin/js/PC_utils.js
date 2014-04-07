if (typeof console == 'undefined') console = {log: function(){}};

Ext.namespace('PC.utils');

PC.getPluginFromID = function(id) {
	if (!/^[a-z0-9\-_]+\//.test(id)) return false;
	var pl = id.substring(0, id.indexOf('/'));
	return pl;
}

PC.getOwnerPlugin = function(node) {
	if (node.attributes.controller != undefined) {
		return node.attributes.controller;
	}
	return PC.getPluginFromID(node.id);
}

PC.utils.getFlagOffsets = function(lang) {
	var result = [0, 0];
	if (lang.match(/^[a-z]{2}$/)) {
		if (lang=='en') lang='us';
		result[0] = (lang.charCodeAt(0) - 96) * 16;
		result[1] = (lang.charCodeAt(1) - 96) * 11;
	}
	//return result;
	return '-'+result[0]+'px -'+result[1]+'px';
}

PC.utils.utf8_encode = function(input) {
	// Copy-pasted from md5.js
	var output = "";
	var i = -1;
	var x, y;
	
	while (++i < input.length) {
		/* Decode utf-16 surrogate pairs */
		x = input.charCodeAt(i);
		y = i + 1 < input.length ? input.charCodeAt(i + 1) : 0;
		if (0xD800 <= x && x <= 0xDBFF && 0xDC00 <= y && y <= 0xDFFF) {
			x = 0x10000 + ((x & 0x03FF) << 10) + (y & 0x03FF);
			i++;
		}
		
		/* Encode output as utf-8 */
		if (x <= 0x7F)
			output += String.fromCharCode(x);
		else if (x <= 0x7FF)
			output += String.fromCharCode(0xC0 | ((x >>> 6 ) & 0x1F),
			                              0x80 | ( x         & 0x3F));
		else if (x <= 0xFFFF)
			output += String.fromCharCode(0xE0 | ((x >>> 12) & 0x0F),
			                              0x80 | ((x >>> 6 ) & 0x3F),
			                              0x80 | ( x         & 0x3F));
		else if (x <= 0x1FFFFF)
			output += String.fromCharCode(0xF0 | ((x >>> 18) & 0x07),
			                              0x80 | ((x >>> 12) & 0x3F),
			                              0x80 | ((x >>> 6 ) & 0x3F),
			                              0x80 | ( x         & 0x3F));
	}
	return output;
}

PC.utils.applyProps = function(to, from) {
	if (typeof from != 'object') return;
	if (typeof to != 'object') to = {};
	for (var x in from) {
		if (from.hasOwnProperty(x)) {
			if (typeof from[x] == 'object' && typeof to[x] == 'object') {
				arguments.callee(to[x], from[x]);
			}
			else
				to[x] = from[x];
		}
	}
}

// Internationalization
// I....18.letters....n
PC.utils.localize = function(path, langs) {
	var p = '';
	if (typeof path == 'string') {
		// validation & cleanup
		Ext.each(path.split('.'), function(i) {
			if (i != '')
				p = p + '.' + i;
		});
	}
	
	if (typeof langs == 'object') {
		for (var x in langs) {
			Ext.namespace('PC.langs.'+x+p);
			//eval('PC.langs.'+x+p) = langs[x];
			PC.utils.applyProps(eval('PC.langs.'+x+p), langs[x]);
		}
	}
	
	Ext.namespace('PC.i18n'+p);
	PC.utils.applyProps(eval('PC.i18n'+p), eval('PC.langs.en'+p));
	if (PC.global.admin_ln != 'en') {
		PC.utils.applyProps(eval('PC.i18n'+p), eval('PC.langs.'+PC.global.admin_ln+p));
	}
}
PC.utils.extractName = function(names, callback, cfg) {
	if (typeof cfg != 'object') var cfg = {};
	var name = '', greyOut = false;
	if (typeof names != 'object') {
		name = '...';
		greyOut = true;
	}
	else if (names[PC.global.admin_ln] != undefined && names[PC.global.admin_ln] != '') {
		name = names[PC.global.admin_ln];
	}
	else {
		greyOut = true;
		Ext.iterate(names, function(ln, nameMock) {
			if (nameMock != '') {
				name = nameMock;
				return false;
			}
		});
		if (name == '') name = '...';
	}
	if (cfg.greyOut != undefined) greyOut = cfg.greyOut;
	if (greyOut) name = '<span style="color: #666"><i>'+ name +'</i></span>';
	if (typeof callback == 'function') callback(name);
	return name;
}

PC.utils.htmlspecialchars = function(str) {
	return str.replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, '&#039;').replace(/"/g, '&quot;');
}

PC.utils.color2Hex = function(color) {
	if (!color || color=='transparent') return '';
	var probe = window._color_probe;
	if (!probe) {
		probe = document.createElement('textarea');
		probe.setAttribute('style', 'display:none; position:absolute');
		document.body.appendChild(probe);
		window._color_probe = probe;
	}
	probe.style.color = 'transparent';
	
	try {
		probe.style.color = color;
	} catch(e) {
		return false;
	}
	
	var d2h = function(d) { return ((d/16)&15).toString(16) + (d%16).toString(16); }
	if (window.getComputedStyle) {
		var m, parsed = window.getComputedStyle(probe, null).getPropertyValue('color').toLowerCase();
		if (m = parsed.match(/^#?([0-9a-f]{6})$/)) {
			return m[1];
		} else if (m = parsed.match(/^#?([0-9a-f])([0-9a-f])([0-9a-f])$/)) {
			return m[1]+m[1]+m[2]+m[2]+m[3]+m[3];
		} else if (m = parsed.match(/rgb\s*\((\d+),\s*(\d+),\s*(\d+)\)/)) {
			return d2h(m[1]) + d2h(m[2]) + d2h(m[3]);
		} else if (m = parsed.match(/rgba\s*\((\d+),\s*(\d+),\s*(\d+),\s*(\d+)\)/)) {
			if (m[4] == 0)
				return '';
			else
				return d2h(m[1]) + d2h(m[2]) + d2h(m[3]);
		}
	} else {
		if (probe.createTextRange) {
			var r = probe.createTextRange();
			if (r.queryCommandValue) {
				var c = r.queryCommandValue('ForeColor');
				return d2h(c&255) + d2h((c/256)&255) + d2h((c/65536)&255);
			}
		}
	}
	return false;
}

// cookies
PC.utils.getCookie = function(name) {
	var start = document.cookie.indexOf( name + "=" );
	var len = start + name.length + 1;
	if ( ( !start ) && ( name != document.cookie.substring( 0, name.length ) ) ) {
		return null;
	}
	if ( start == -1 ) return null;
	var end = document.cookie.indexOf( ';', len );
	if ( end == -1 ) end = document.cookie.length;
	return unescape( document.cookie.substring( len, end ) );
}

/**
* Sets cookie
* @param {String} name    A cookie name.
* @param {String} value    A cookie value.
* @param {String} expires = null   Cookie expiration in ours
* @param {String} path = null
* @param {String} domain = null
* @param {String} secure = null
*/
PC.utils.setCookie = function(name, value, expires, path, domain, secure) {
	var today = new Date();
	today.setTime( today.getTime() );
	if ( expires ) {
		expires = expires * 1000 * 60 * 60;
	}
	var expires_date = new Date( today.getTime() + (expires) );
	document.cookie = name+'='+escape( value ) +
		( ( expires ) ? ';expires='+expires_date.toGMTString() : '' ) + //expires.toGMTString()
		( ( path ) ? ';path=' + path : '' ) +
		( ( domain ) ? ';domain=' + domain : '' ) +
		( ( secure ) ? ';secure' : '' );
}

PC.utils.deleteCookie = function(name, path, domain) {
	if ( PC.utils.getCookie( name ) ) document.cookie = name + '=' +
			( ( path ) ? ';path=' + path : '') +
			( ( domain ) ? ';domain=' + domain : '' ) +
			';expires=Thu, 01-Jan-1970 00:00:01 GMT';
}

PC.utils.loadScript = function(src, callback){
	var body = document.getElementsByTagName('body')[0];
	var s = document.createElement('script');
	s.setAttribute('type', 'text/javascript');
	s.setAttribute('src', src);
	if (typeof callback == 'function') {
		s.onreadystatechange = function () {
		   if (this.readyState == 'complete') callback();
		}
		s.onload = callback;
	}
	return body.appendChild(s);
}

PC.utils.escape = function(text) {
  return text.replace(/\W/g, function (chr) {
    return '&#' + chr.charCodeAt(0) + ';';
  });
}

PC.utils.getComboArrayFromObject = function(object) {
	var array = [];
	Ext.iterate(object, function(value, index) {
		array.push([index, value]);
	})
	return array;
}

/**
 * Clone Function
 * @param {Object/Array} o Object or array to clone
 * @return {Object/Array} Deep clone of an object or an array
 * @author Ing. Jozef Sak?lo�
 */
Ext.ns('Ext.ux.util');
Ext.ux.util.clone = function(o) {
    if(!o || 'object' !== typeof o) {
        return o;
    }
    if('function' === typeof o.clone) {
        return o.clone();
    }
    var c = '[object Array]' === Object.prototype.toString.call(o) ? [] : {};
    var p, v;
    for(p in o) {
        if(o.hasOwnProperty(p)) {
            v = o[p];
            if(v && 'object' === typeof v) {
                c[p] = Ext.ux.util.clone(v);
            }
            else {
                c[p] = v;
            }
        }
    }
    return c;
}; // eo function clone  
Array.prototype.has=function(v){
	for (i=0; i<this.length; i++){
	if (this[i]==v) return i;
	}
	return false;
};
function isEmpty(ob){
   for(var i in ob){ return false;}
  return true;
};
String.prototype.capitalize = function(){
	return this.replace( /(^|\s)([a-z])/g , function(m,p1,p2){ return p1+p2.toUpperCase(); } );
};
/* This script and many more are available free online at
The JavaScript Source!! http://javascript.internet.com
Created by: Pittimann | http://www.webdeveloper.com/forum/showthread.php?t=41676 */
String.prototype.sentenceCase = function(){
	val = this;
	result=new Array();
	result2='';
	count=0;
	endSentence=new Array();
	for (var i=1;i<val.length;i++){
		if(val.charAt(i)=='.'||val.charAt(i)=='!'||val.charAt(i)=='?'){
			endSentence[count]=val.charAt(i);
			count++
		}
	}
	var val2=val.split(/[.|?|!]/);
	if(val2[val2.length-1]=='')val2.length=val2.length-1;
	for (var j=0;j<val2.length;j++){
		val3=val2[j];
		if(val3.substring(0,1)!=' ')val2[j]=' '+val2[j];
		var temp=val2[j].split(' ');
		var incr=0;
		if(temp[0]==''){
			incr=1;
		}
		temp2=temp[incr].substring(0,1);
		temp3=temp[incr].substring(1,temp[incr].length);
		temp2=temp2.toUpperCase();
		temp3=temp3.toLowerCase();
		temp[incr]=temp2+temp3;
		for (var i=incr+1;i<temp.length;i++){
			temp2=temp[i].substring(0,1);
			temp2=temp2.toLowerCase();
			temp3=temp[i].substring(1,temp[i].length);
			temp3=temp3.toLowerCase();
			temp[i]=temp2+temp3;
		}
		if(endSentence[j]==undefined)endSentence[j]='';
		result2+=temp.join(' ')+endSentence[j];
	}
	if(result2.substring(0,1)==' ')result2=result2.substring(1,result2.length);
	return result2;
};
function colorToHex(color) {
    if (color.substr(0, 1) === '#') {
        return color;
    }
    var digits = /(.*?)rgb\((\d+), (\d+), (\d+)\)/.exec(color);
    
    var red = parseInt(digits[2]);
    var green = parseInt(digits[3]);
    var blue = parseInt(digits[4]);
    
    var rgb = blue | (green << 8) | (red << 16);
    return digits[1] + '#' + rgb.toString(16);
};
PC.utils.Get_classes_array = function(valid_tag){
	var store = new Array;
	if (tinymce.activeEditor != undefined) {
		Ext.each(tinymce.activeEditor.dom.getClasses(valid_tag), function(cls){
			store.push(cls['class']);
		});
	}
	return store;
};
function var_dump(o, show) {
	var out = '';
	for (var i in o) out += i + ": " + o[i] + "\n";
	if (show) alert(out);
	else return out;
};
function removeParent(parent) {
	while (parent.firstChild) {
		parent.parentNode.insertBefore(parent.firstChild, parent);
	}
	return parent.parentNode.removeChild(parent);
}

function pc_shorten_text(string, max, tail) {
	max = max || 100;
	tail = tail || '...';
	return string.length > max ? string.substr(0,max-1) + tail : string;
}

<?php
# ProfisCMS - Opensource Content Management System Copyright (C) 2011 JSC "ProfIS"
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
$auth->Authenticate();
if ($auth->Is_authenticated()) if (!$auth->Authorize('core', 'access_admin')) {
	echo 'You don\'t have permissions to access admin panel.';
	die;
}
// Ext languages -> $ext_langs -> $ext_ln_js
$ext_langs = array();
foreach (glob('ext/src/locale/ext-lang-*.js') as $v) {
	if (preg_match('#[\/]ext-lang-(([a-z]+)(_[a-z]+)?)\.js$#i', $v, $m)) {
		$ext_langs[] = array(
			'lang' => $m[1],
			'ln' => strtolower($m[2]),
			'js' => $v
		);
	}
}
function _sort_ext_langs($a, $b) {
	$cmp = strcmp($a['ln'], $b['ln']);
	//if ($cmp == 0) $cmp = strlen($a['lang']) - strlen($b['lang']);
	//if ($cmp == 0) $cmp = strcmp($b['lang'], $a['lang']);
	if ($cmp == 0) $cmp = strcmp($a['lang'], $b['lang']);
	return $cmp;
}
usort($ext_langs, '_sort_ext_langs');
$ext_ln_js = array();
foreach ($ext_langs as $v) {
	if (!isset($ext_ln_js[$v['ln']]))
		$ext_ln_js[$v['ln']] = $v['js'];
}

//get valid admin languages
$admin_languages = array();
foreach (glob('locale/PC.*.js') as $language) {
	if (preg_match("/locale\/PC\.([a-z]{2,3})\.js/", $language, $match)) {
		$admin_languages[$match[1]] = $cfg['languages'][$match[1]];
	}
}
//detect language
if (isset($admin_languages[v($_SESSION['auth_data']['language'])])) {
	$admin_ln = $_SESSION['auth_data']['language'];
}
elseif (!isset($cfg['admin_ln'])) foreach (get_accept_languages() as $v) {
	if (isset($ext_ln_js[$v['ln']])) {
		$admin_ln = $v['ln'];
		break;
	}
}
else $admin_ln = v($cfg['admin_ln'], 'en');
//$admin_ln = 'ru'; //force this language

if ($auth->Is_authenticated()) return;

// *** Login screen ***
if (core_get('no_login_form')) die();
if (!$auth->Can_auth()) {
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	$banned = true;
}
else {
	$banned = false;
	$_SESSION['auth_data']['salt'] = random_filename(rand(60, 64));
}
header('Cache-Control: no-cache');
$error = $auth->Get_error();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Profis CMS <?php echo PC_VERSION; ?></title>
	<base href="<?php echo htmlspecialchars($cfg['url']['base'].$cfg['directories']['admin']); ?>/" />
	<link rel="stylesheet" type="text/css" href="ext/resources/css/ext-all.css" />
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<script type="text/javascript" src="ext/adapter/ext/ext-base.js"></script>
	<script type="text/javascript" src="ext/ext-all.js"></script>
	<script type="text/javascript" src="js/md5-min.js"></script>
	<script type="text/javascript" src="locale/<?php echo $admin_ln; ?>/"></script>
	<script type="text/javascript" src="js/PC_utils.js"></script>
	<script type="text/javascript">
	var auth_salt = '<?php echo $_SESSION['auth_data']['salt']; ?>';
	var banned = <?php echo ($banned?'true':'false'); ?>;
	<?php if ($error !== false) echo "var error = '",$error,"';"; ?>
	if (!window.PC) PC = {};
	PC.global = {};
	PC.global.ln = PC.global.admin_ln = '<?php echo $admin_ln; ?>';
	// Path to the blank image must point to a valid location on your server
	Ext.BLANK_IMAGE_URL = 'ext/resources/images/default/s.gif';

	// Main application entry point
	Ext.onReady(function() {
		PC.utils.localize();
		
		function submit_login_form() {
			Ext.getCmp('auth_hash').setRawValue(hex_hmac_md5(auth_salt, Ext.getCmp('auth_pass').getValue()));
			Ext.getCmp('login_form').getForm().submit();
		}
		
		function submit_on_enter(fld, e) {
			if (e.getKey() == e.ENTER) {
				submit_login_form();
			}
		};
		
		var header = {
			xtype: 'box',
			html: '<div class="login_header">&nbsp; Profis CMS <?php echo PC_VERSION; ?><\/div>'
		};
		
		var login_form = new Ext.form.FormPanel({
			id: 'login_form',
			border: true,
			width: 260,
			labelAlign: 'right',
			labelWidth: 120,
			defaultType: 'textfield',
			baseCls: 'x-window',
			method: 'POST',
			standardSubmit: true,
			url: '',
			buttonAlign: 'right',
			buttons: [
				{	text: 'OK',
					handler: submit_login_form
				}
			],
			footerCfg: {
				style: 'padding:0 32px 0 0'
			},
			items: [
				{	allowBlank: false,
					fieldLabel: PC.i18n.auth.login,
					name: 'auth_user',
					style: {marginTop: '1px'},
					id: 'auth_user',
					<?php if (isset($_POST['auth_user'])) { ?>
					value: <?php echo json_encode($_POST['auth_user']); ?>,
					<?php } elseif (core_get('demo_mode')) { ?>
					value: 'demo',
					<?php } ?>
					maxLength: 32,
					width: 125,
					listeners: {specialkey: submit_on_enter}
				},
				{	allowBlank: false,
					fieldLabel: PC.i18n.auth.password,
					name: 'auth_pass',
					style: {marginTop: '1px'},
					inputType: 'password',
					width: 125,
					id: 'auth_pass',
					<?php if (core_get('demo_mode')) { ?>
					value: 'demo',
					<?php } ?>
					//submitValue: false,
					listeners: {specialkey: submit_on_enter}
				},
				/*{	fieldLabel: PC.i18n.language,
					width: 125,
					//id: 'admin_language',
					hiddenName : 'admin_language',
					xtype: 'combo',
					mode: 'local',
					store: {
						xtype: 'arraystore',
						fields: ['value', 'display'],
						idIndex: 0,
						data: [[null,'&nbsp;'],<?php
							$t = 0;
							foreach ($admin_languages as $code=>$lang) {
								if ($t>0) echo ',';
								echo '[\'',$code,'\',\'',$lang,'\']';
								$t++;
							}
						?>]
					},
					displayField: 'display',
					valueField: 'value',
					value: '<?php echo $admin_ln; ?>',
					forceSelection: true,
					triggerAction: 'all',
					listeners: {specialkey: submit_on_enter}
				},*/
				{	xtype: 'hidden',
					name: 'auth_hash',
					id: 'auth_hash'
				}
			]
		});
		var view = new Ext.Viewport({
			layout: 'vbox',
			layoutConfig: {
				align: 'center'
			},
			items: [
				{	flex: 1,
					border: false
				}, header, login_form,
				{	flex: 1,
					border: false
				}
			]
		});
		function Can_auth() {
			if (!banned) {
				//view.insert(2, login_form);
				//view.doLayout();
				<?php if (isset($_POST['auth_user']) && $_POST['auth_user']) { ?>
				Ext.getCmp('auth_pass').focus();
				<?php } else { ?>
					Ext.getCmp('auth_user').focus();
				<?php } ?>
			} else {
				login_form.hide();
				Ext.Msg.show({
					icon: Ext.MessageBox.WARNING,
					title: PC.i18n.auth.banned_title,
					msg: PC.i18n.auth.banned_msg,
					buttons: Ext.MessageBox.OK
				});
			}
		}
		if (typeof variable != 'undefined') {
			Ext.Msg.show({
				icon: Ext.MessageBox.INFO,
				title: PC.i18n.auth.login_error,
				msg: (error=='login_data'?PC.i18n.auth.invalid:(error=='database'?PC.i18n.auth.database_error:PC.i18n.auth.unknown_error)),
				buttons: Ext.MessageBox.OK,
				fn: Can_auth
			});
		} else {
			Can_auth();
		}
	});
	</script>
</head>
<body></body></html>
<?php exit;
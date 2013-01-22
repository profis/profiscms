Ext.namespace('PC.langs');

PC = {};
PC.langs = {};
PC.langs.en = {
	yes: 'Yes',
	no: 'No',
	all: 'All',
	date: 'Date',
	time: 'Time',
	search: 'Search page',
	search_empty: 'Search',
	search_pages: 'Search pages',
	custom: 'Custom',
	core: 'Core',
	logout: 'Logout',
	clear_cache: 'Clear cache',
	no_title: 'No title',
	home: 'Home page',
	find: 'Find',
	replace: 'Replace',
	error: 'Error',
	warning: 'Warning',
	styles: 'Styles',
	gallery: 'Gallery',
	quotes: 'Quotes',
	load: 'Load',
	save: 'Save',
	cancel: 'Cancel',
	add: 'Add',
	edit: 'Edit',
	copy: 'Copy',
	del: 'Delete',
	move_left: 'Move left',
	move_right: 'Move right',
	move_up: 'Move up',
	move_down: 'Move down',
	close: 'Close',
	seo: 'SEO',
	name: 'Name',
	custom_name: 'Name in text',
	link: 'Link',
	seo_link: 'User friendly URL',
	seo_permalink: 'Permalink',
	title: 'Title',
	desc: 'Description',
	keyword: 'Keyword',
	keywords: 'Keywords',
	reference_id: 'Reference ID',
	from: 'From',
	to: 'To',
	sel_redir_dst: 'Select redirect destination',
	last_update: 'Last update',
	save_time: 'Save time',
	user: 'User',
	plugins: 'Modules',
	site: 'Site',
	language: 'Language',
	default_language: 'Default language',
	show_menu_in: 'Show menu in',
	edit_styles: 'Edit styles',
	apply: 'Apply',
	create_new_page: 'Create new page',
	bin: 'Recycle bin',
	attention: 'Attention',
	delete_page_that_has_shortcuts: 'This page has shortcuts from other pages. If you delete it, these pages will stop working.',
	inactive_site_page: 'Show this page when this site is inactive',
	auth: {
		login: 'Login',
		password: 'Password',
		banned_title: 'You are banned',
		banned_msg: 'Due to the repetitive invalid data entered, you are temporarily banned.<br />Try to log in again in a while.',
		invalid: 'Invalid username or password specified.',
		database_error: 'Database error',
		unknown_error: 'Unknown error',
		login_error: 'Login error', 
		permissions: { 
			types: {
				core: {
					access_admin: {
						title: 'Access admin',
						description: 'Access admin panel'
					},
					admin: {
						title: 'Super admin',
						description: 'Access to everything: sites, pages, all plugins etc.'
					},
					pages: {
						title: 'Pages',
						description: 'Access to the page tree'
					},
					page_nodes: {
						title: 'Page nodes',
						description: 'Manage access to certain pages in tree'
					},
					plugins: {
						title: 'Plugins',
						description: 'Manage access to plugins (permissions of custom plugin actions should be configured separately)'
					}
				}
			}
		}
	},
	editor: {
		insert_quotes: 'Insert quotes',
		change_case: 'Change case',
		lowercase: 'Lowercase',
		uppercase: 'Uppercase',
		capitalize: 'Capitalize',
		sentence_case: 'Sentence'
	},
	menu: {
		menu: 'Menu',
		preview: 'Preview',
		shortcut_to: 'Shortcut to',
		new_page: 'New page',
		new_subpage: 'New subpage',
		rename: 'Rename',
		addNew: 'Add new'
	},
	tab: {
		text: 'Text',
		info: 'Info 1',
		info2: 'Info 2',
		info3: 'Info 3',
		seo: 'SEO',
		properties: 'Properties'
	},
	page: {
		properties: 'Page properties',
		type: 'Page type',
		front: 'Front page',
		route_lock: "Don't update URL automatically",
		hot: 'Hot',
		nomenu: "Don't show in menu",
		published: 'Published',
		show_period: 'Show only during this period',
		shortcut_from: 'Shortcut from'
	},
	prev_ver: {
		s: 'Previous versions'
		//reset: 'Reset to current version'
	},
	msg: {
		paste_as_plain: 'The text you want to paste seems to be copied from Word. Do you want to clean it before pasting?',
		error: {
			page: {
				create: 'Error creating page',
				del: 'Error deleting page {0}',
				move: 'Error moving page {0}',
				rename: 'Error renaming page'
			},
			trash: {
				empty: 'Error emptying trash bin'
			},
			prev_ver: {
				load: 'Error loading previous version',
				del: 'Error deleting previous versions'
			},
			data: {
				load: 'Error loading data',
				save: 'Error saving data'
			},
			plugins: 'Failed to get modules'
		},
		load: {
			prev_ver: 'Loading previous version...'
		},
		title: {
			confirm: 'Confirm',
			loading: 'Loading...',
			saving: 'Saving...',
			save: 'You have unsaved data!'
		},
		confirm_delete: 'Are you sure you want to delete {0}?',
		loading: 'Loading, please wait...',
		saving: 'Saving, please wait...',
		save: 'Would you like to save your changes?',
		perm_del: 'Are you sure you want to permanently delete {0}?',
		del_prev_ver: 'Are you sure you want to delete {0} previous version(s)?',
		empty_trash: 'Are you sure you want to delete all pages in trash permanently?',
		logout: 'Are you sure you want to logout?',
		move_menu_warning: 'Moving menu in the page tree impacts the menu arrangement on the site. Do you wish to continue?'
	},
	align: {
		left: 'Left',
		center: 'Center',
		right: 'Right',
		justify: 'Justify'
	},
	dialog: {
		styles: {
			property: 'Attribute',
			value: 'Value',
			cls: 'Style class',
			tag: 'Tag',
			sites: 'Sites',
			any: 'any',
			all: 'All',
			advanced: 'Advanced',
			pangram: 'Pack my box with five dozen liquor jugs',
			tags: {
				p: 'Paragraph',
				table: 'Table',
				tr: 'Table row',
				td: 'Table cell',
				img: 'Image',
				span: 'Text',
				a: 'Link',
				div: 'Container',
				h1: 'Heading 1',
				h2: 'Heading 2',
				h3: 'Heading 3',
				h4: 'Heading 4',
				h5: 'Heading 5',
				h6: 'Heading 6'
			},
			error: {
				cls: {
					whitespace: 'You cannot use spaces here',
					empty: 'CSS class cannot be empty',
					badchars: 'CSS class has invalid characters',
					exists: 'This CSS class already exists'
				},
				prop: {
					empty: 'CSS property cannot be empty',
					badchars: 'CSS property has invalid characters',
					exists: 'This CSS property already exists'
				},
				value: {
					badchars: 'CSS value has invalid characters'
				}
			},
			form: {
				font: 'Font',
				font_size: 'Font size (in pixels)',
				line_height: 'Line height',
				line_height_normal: 'Normal',
				color: 'Color',
				background_color: 'Background color',
				font_weight: 'Font weight',
				font_style: 'Font style',
				text_decoration: 'Text decoration',
				text_align: 'Text align',
				text_indent: 'Text indent (in pixels)',
				text_transform: 'Text transform',
				border_collapse: 'Collapse borders',
				vertical_align: 'Vertical align',
				border: 'Border (size, style & color)',
				margin: 'Margin (in pixels)',
				padding: 'Padding (in pixels)'
			},
			weight: {
				bold: 'Bold',
				normal: 'Normal'
			},
			style: {
				italic: 'Italic',
				oblique: 'Oblique',
				normal: 'Normal'
			},
			decor: {
				underline: 'Underline',
				line_through: 'Line through',
				overline: 'Overline',
				none: 'Normal'
			},
			xform: {
				uppercase: 'Upper case',
				lowercase: 'Lower case',
				capitalize: 'Capitalize'
			},
			top: 'Top',
			middle: 'Middle',
			bottom: 'Bottom',
			solid: 'Solid',
			dotted: 'Dotted',
			dashed: 'Dashed',
			select_style_class: 'Please select style class'
		},
		anchor: {
			title: 'Insert anchor',
			title_update: 'Update anchor',
			insert: 'Insert',
			update: 'Update',
			anchor_field: 'Name'
		},
		tablecell: {
			title: 'Table cell properties',
			general: 'General',
			advanced: 'Advanced',
			alignment: 'Alignment',
			valign: 'Vertical alignment',
			top: 'Top',
			middle: 'Middle',
			bottom: 'Bottom',
			width: 'Width',
			height: 'Height',
			_class: 'Style class',
			id: 'ID',
			style: 'Style',
			bg_image: 'Background image',
			bg_color: 'Background color',
			border_color: 'Border color',
			target_cell: 'Current cell',
			target_row: 'All cells in row',
			target_col: 'All cells in column',
			target_all: 'All cells in table',
			wrap: 'Word wrap'
		},
		colorpicker: {
			custom_color: 'Custom',
			pick_custom: 'Pick your color'
		},
		gallery: {
			sync: 'Synchronize with FTP',
			proportions: 'Constrain proportions',
			title: {
				gallery: 'Gallery',
				category: 'Category',
				trash: 'Trash',
				settings: 'Settings',
				upload: 'Upload',
				filter_unused: 'Filter unused files',
				search: 'Search',
				uploader: 'Upload',
				image_cropper: 'Image cropper'
			},
			search: {
				empty_text: 'Search gallery'
			},
			new_filename: 'New filename',
			type: 'Type',
			cannot_be_default_type: 'Cannot be default type',
			too_short: 'Too short',
			too_long: 'Too long',
			max_w: 'Max width',
			max_h: 'Max height',
			width_cannot_be_more_than: 'Width cannot be more than 2000 pixels',
			width_cannot_be_less_than: 'Width cannot be less than 5 pixels',
			height_cannot_be_more_than: 'Height cannot be more than 2000 pixels',
			height_cannot_be_less_than: 'Height cannot be less than 5 pixels',
			quality_percentage: 'Quality, %',
			max_quality: 'Maximum quality cannot exceed 100%',
			min_quality: 'Minimum quality should not be less than 5%',
			cropper_loading: 'Please wait, cropper will start when image has finished loading...',
			mark_unused: 'Mark unused files',
			close_after_insert: 'Close gallery after insert',
			close_after_click_outside: 'Close gallery after clicking outside it',
			size: 'Size',
			files_selected_suffix: ' files selected',
			no_files: 'There are no files in the selected category,<br />You can upload some files by pressing button in the toolbar above',
			no_files_in_trashed: 'There are no files in the selected category',
			thumbnails: {
				thumbnail: 'Thumbnail',
				small: 'Small',
				large: 'Large',
				group: {
					_default: 'Default',
					custom: 'Custom'
				},
				get: {
					error_title: 'Unable to get thumbnail types'
				},
				edit: {
					error_title: 'Thumbnail type was not changed'
				},
				_delete: {
					error_title: 'Thumbnail type was not deleted'
				}
			},
			button: {
				ok: 'I\'m sure',
				close: 'Close',
				cancel: 'I will think about it',
				save: 'Save',
				canceledit: 'Cancel'
			},
			action: {
				open: 'Open',
				new_category: 'New category',
				new_category_inside: 'New category inside',
				cut: 'Cut',
				copy: 'Copy',
				paste: 'Paste',
				rename: 'Rename',
				_delete: 'Delete',
				move_to_trash: 'Move to trash',
				preview: 'Preview',
				edit_thumb: 'Edit',
				edit: {
					edit: 'Edit',
					small: 'Small',
					large: 'Large'
				},
				insert: {
					insert: 'Insert',
					small: 'Small',
					large: 'Large',
					link: 'Link',
					original: 'Original',
					as_uploaded: 'maximum size'
				},
				view: {
					view: 'View',
					icons: 'Icons',
					detailed: 'Detailed',
					sort_by_name: 'Sort by name',
					sort_by_size: 'Sort by size',
					sort_by_modified: 'Sort by date modified',
					
					asc: 'ASC',
					desc: 'DESC'
				},
				sort: {
					sort: 'Sort',
					byname: 'By name',
					bysize: 'By size'
				},
				copy_link: 'Copy link',
				copy_to_clipboard: 'Copy to clipboard'
			},
			category: {
				create: {
					error_title: 'Category was not created',
					default_name: 'Category'
				},
				rename: {
					error_title: 'Category was not renamed'
				},
				_delete: {
					confirmation: {
						title: 'Delete category',
						message: 'Are you sure to <b>permanently delete</b> this category and all its\' files?'
					},
					error_title: 'Category was not deleted'
				}
			},
			file: {
				in_use: '<img class="ico" src="images/cancel.png" alt="" /> This file is used in your pages, it would be no longer available if you\'ll delete it!',
				remove: {
					confirmation: {
						title: 'Delete file',
						message: 'Do you really want to <b>permanently delete</b> this file?'
					},
					error_title: 'File was not deleted'
				},
				rename: {
					error_title: 'Nepavyko pervadinti failo'
				}
			},
			image: {
				edit: {
					error_title: 'Image thumbnail was not saved'
				}
			},
			files: {
				in_use: '<img class="ico" src="images/cancel.png" alt="" /> Selection includes files that is in use by your pages, they would be no longer available if you\'ll delete it!',
				remove: {
					confirmation: {
						title: 'Delete files',
						message: 'Do you really want to <b>permanently delete</b> selected files?</b>'
					},
					error_title: 'Files was not deleted',
					request_results_title: 'Delete files results'
				},
				restore: {
					confirmation: {
						title: 'Restore file',
						message: 'Do you really want to <b>restore selected files?</b>'
					},
					error_title: 'Error while restoring files'
				}
			},
			trash: {
				title: {
					empty: 'Empty trash',
					restore: 'Restore',
					_delete: 'Delete permanently'
				},
				col: {
					date_trashed: 'Date trashed',
					title: 'Title',
					categories: 'Categories',
					files: 'Files'
				},
				category: {
					confirmation: {
						title: 'Trash category',
						message: 'Are you sure to move category and all its\' files<br />to the trash?'
					},
					error_title: 'Category was not trashed'
				},
				file: {
					confirmation: {
						title: 'Trash file',
						message: 'Are you sure you wan\'t to move this file to the trash?'
					},
					error_title: 'File was not trashed'
				},
				files: {
					confirmation: {
						title: 'Trash files',
						message: 'Are you sure you wan\'t to move selected files to the trash?'
					},
					error_title: 'Files was not trashed',
					request_results_title: 'Trash files results'
				},
				restore: {
					category: {
						error_title: 'Category was not restored'
					},
					file: {
						error_title: 'File was not restored'
					}
				},
				empty: {
					confirmation: {
						title: 'Empty trash',
						message: 'Do you really want to empty trash?'
					},
					error_title: 'Trash was not purged'
				}
			},
			error: {
				connection: {
					title: 'Connection problem',
					message: 'There was an error while trying to send request to the server.<br />If it\'s not a first time this happens, please contact your site administrator.'
				},
				//errors returned from the proxy
				database: 'Database error',
				category_id: 'Category id you specified was incorrect',
				already_trashed: 'Category/file is already trashed, you can\'t trash it twice ;)',
				category_not_found: 'Category with the specified id was not found',
				file_id: 'File id you specified was incorrect',
				category: 'Format of the category name was incorrect',
				parent: 'Incorrect parent category id specified (parent doesn\'t exist?)',
				position: 'Invalid position specified',
				_private: 'If category is private, value should be \'1\', otherwise - \'0\'',
				create_directory: 'System was unable to create directory for the category',
				file_not_in_a_trash: 'File is not in a trash',
				file_not_found: 'File with the specified id was not found',
				category_not_in_a_trash: 'Category is not in a trash',
				//thumbnails
				thumbnail_type: 'Thumbnail type format you specified was incorrect',
				thumbnail_not_found: 'Thumbnail type was not found',
				cannot_change_default_type: 'You cannot change the name of the default thumbnail type',
				max_width: 'Max. width format you specified was incorrect',
				max_height: 'Max. height format you specified was incorrect',
				quality: 'Quality format you specified was incorrect',
				no_changes: 'No changes found',
				filename: 'Įvestame failo pavadinime rasti neleistini simboliai',
				rename_file: 'Nepavyko pervadinti failo',
				file_already_exists: 'Failas tokiu pavadinimu jau yra sukurtas',
				change_default_type_name: 'You cannot change the name of the default thumbnail type',
				change_default_type_resize: 'You cannot change resize type of the default thumbnail type'
			},
			resize: 'Resize',
			normal: 'Normal',
			adaptive: 'Adaptive',
			semi_adaptive: 'Semi adaptive'
		},
		gmaps: {
			title: 'Google Maps',
			geocoder_emptytext: 'Search',
			geocoder_error: 'Geocode was not successful for the following reason: ',
			latitude: 'Latitude',
			longitude: 'Longitude',
			width: 'Width',
			height: 'Height',
			insert: 'Insert',
			update: 'Update'
		},
		image: {
			image_properties: 'Edit image',
			general: 'General',
			advanced: 'Advanced',
			image_url: 'Image URL',
			no_large_on_click: 'Don\'t show large image on click',
			title: 'Title',
			description: 'Description',
			alignment: 'Alignment',
			left: 'Left',
			right: 'Right',
			dimensions: 'Dimensions',
			proportions: 'Constrain proportions',
			id: 'Id',
			_class: 'Style class',
			style: 'Style',
			rel: 'Lightbox group',
			border: 'Border',
			solid: 'Solid',
			dotted: 'Dotted',
			dashed: 'Dashed',
			margin: 'Margin'
		},
		form: {
			form_properties: 'Edit form',
			general: 'General',
			options: 'Options',
			advanced: 'Advanced',
			events: 'JavaScript Events',
			_for: 'For',
			name: 'Name',
			emails: 'Email\(s\)',
			thank_you_text: 'Thank you text',
			title: 'Title',
			id: 'Id',
			_class: 'Style class',
			style: 'Style',
			border: 'Border',
			solid: 'Solid',
			dotted: 'Dotted',
			dashed: 'Dashed',
			margin: 'Margin',
			background_color: 'Background color',
			onfocus: 'OnFocus',
			onblur: 'OnBlur',
			onselect: 'OnSelect',
			onchange: 'OnChange',
			padding: 'Padding',
			insert_form: 'Insert Form',
			insert_file: 'File Input',
			insert_text: 'Text Input',
			insert_hidden: 'Hidden Value',
			insert_pass: 'Password Input',
			insert_checkbox: 'Checkbox',
			insert_radio: 'Radiobox',
			insert_textarea: 'Textarea',
			insert_listbox: 'Listbox',
			insert_reset: 'Reset Button',
			insert_image: 'Image Button',
			insert_submit: 'Submit Button',
			insert_label: 'Label',
			specific: 'Specific',
			value: 'Value',
			size: 'Size',
			maxlength: 'Maxlength',
			readonly: 'Readonly',
			required: 'Required',
			checked: 'Checked',
			selected: 'Selected',
			disabled: 'Disabled',
			image_url: 'Image URL',
			cols: 'Columns',
			rows: 'Rows',
			multiple: 'Multiple',
			maxuploadsize: 'Max file size (KiB)',
			label: 'Label',
			new_option: 'Add option',
			delete_option: 'Delete option',
			width: 'Width',
			height: 'Height'
		},
		links: {
			title_insert: 'Insert link',
			title_update: 'Update link',
			insert: 'Insert',
			update: 'Update',
			general: 'General',
			href: 'Link URL',
			anchor: 'Anchor',
			open_in_new_window: 'Open link in new window',
			open_in_lightbox: 'Open link in Lightbox',
			t_self: 'Self',
			t_blank: 'Blank',
			t_parent: 'Parent',
			t_top: 'Top',
			advanced: 'Advanced',
			title: 'Title',
			_class: 'Style class',
			style: 'Style',
			events: 'Events'
		},
		media: {
			title: 'Insert media',
			title_update: 'Edit media',
			general: 'General',
			advanced: 'Advanced',
			update: 'Update',
			enter_media_url: 'Choose from gallery or enter media URL that you want to embed.',
			url: 'File URL',
			error: 'Error',
			not_supported: 'This media type is currently not supported.',
			unable_to_identify: 'System was unable to identify media type.',
			put_link_here: 'Put your media link here',
			dimensions: 'Dimensions',
			proportions: 'Constrain proportions',
			id: 'Id',
			border: 'Border',
			solid: 'Solid',
			dotted: 'Dotted',
			dashed: 'Dashed',
			margin: 'Margin',
			style: 'Style',
			insert: 'Insert',
			poster: 'Poster',
			skin: 'Player skin'
		},
		tablerow: {
			title: 'Table row properties',
			general: 'General',
			advanced: 'Advanced',
			alignment: 'Alignment',
			left: 'Left',
			center: 'Center',
			right: 'Right',
			valign: 'Vertical alignment',
			top: 'Top',
			bottom: 'Bottom',
			_class: 'Style class',
			height: 'Height',
			bg_color: 'Background color',
			id: 'Id',
			style: 'Style',
			bg_image: 'Background image',
			apply_row: 'Row',
			apply_all: 'All',
			apply_odd: 'Odd',
			apply_even: 'Even',
			update: 'Update'
		},
		search: {
			title: 'Find/replace',
			find: 'Find',
			find_what: 'Find what',
			find_next: 'Find next',
			replace: 'Replace',
			replace_all: 'Replace all',
			replace_with: 'Replace with',
			direction: 'Direction',
			up: 'Up',
			down: 'Down',
			match_case: 'Match case',
			not_found: 'Nothing was found',
			replaced: 'Matches replaced'
		},
		source: {
			title: 'HTML Source Editor',
			wrap: 'Word wrap'
		},
		mergetablecells: {
			title: 'Merge table cells'
		},
		tables: {
			title: 'Insert table',
			title_update: 'Update table',
			cols: 'Columns',
			rows: 'Rows',
			width: 'Width',
			height: 'Height',
			cellpadding: 'Cellpadding',
			cellspacing: 'Cellspacing',
			alignment: 'Alignment',
			left: 'Left',
			center: 'Center',
			right: 'Right',
			_class: 'Style class',
			border: 'Border',
			solid: 'Solid',
			dotted: 'Dotted',
			dashed: 'Dashed',
			bg_image: 'Background image',
			style: 'Style',
			insert: 'Insert',
			more: 'More'
		},
		plugins: {
			title: 'Modules manager',
			restart_admin_title: 'Restart admin',
			restart_admin_confirm_question: 'Would you like to restart admin to see activated plugins?'
		}
	},
	mod: {
		domains: {
			selfname: 'Domains',
			mask: 'Mask',
			error: {
				mask_empty: 'Domain mask cannot be empty',
				mask_exists: 'This domain mask already exists'
			},
			mask_example: 'Mask ex.: <b>*.com</b> or <b>mycompany.*</b> or <b>*.company.com</b>'
		},
		users: {
			selfname: 'Users',
			user: 'User',
			pass_new: 'New password',
			pass_repeat: 'Repeat',
			update: 'Update',
			msg: {
				del_confirm: 'Are you sure you want to delete user {0}?',
				del_confirm_title: 'Confirm user deletion'
			},
			error: {
				save: "Can't save",
				user_empty: "Username can't be empty",
				pass_diff: "Passwords don't match",
				pass_blank: 'Blank passwords are not allowed',
				update: 'Error updating user',
				del: 'Error deleting user'
			}
		},
		backup: {
			selfname: 'Backup',
			backup: 'Backup',
			restore: 'Restore',
			restart: 'Restart',
			backup_restore: 'Backup / Restore',
			file_name: 'File name',
			file_size: 'File size',
			created: 'Created',
			download: 'Download',
			msg: {
				restore: 'Are you sure you want to restore backup "{0}"?',
				restore_ok: 'Backup restored successfully',
				create_before_restore: 'Do you want to create a backup before restoring?',
				del: 'Are you sure you want to delete selected backups?'
			},
			error: {
				create: 'Error creating backup',
				restore: 'Error restoring backup {0}',
				rename: 'Error renaming backup {0}',
				del: 'Error deleting files',
				del_some: 'Some files could not be deleted'
			},
			reset_settings: 'Reset settings'
		},
		variables: {
			selfname: 'Variables',
			category: 'Category',
			all_sites: 'All sites',
			show_langs: 'Show languages',
			choose_new_value: 'Choose a new value for variable {0}:',
			choose_new_value_title: 'Choose a new value',
			explode: 'Explode',
			implode: 'Implode',
			translate: 'Translate',
			error: {
				name_empty: 'Name cannot be empty',
				name_in_use: 'This name is already in use'
			},
			lock: 'Lock one value for all languages'
		},
		config: {
			selfname: 'Settings'//,
		},
		sites_langs: {
			selfname: 'Sites & Languages',
			theme: 'Theme',
			id: 'ID',
			name: 'Name',
			activated: 'Activated',
			msg: {
				site_delete_title: 'Confirm site deletion',
				site_delete: 'Are you sure you want to delete this site and all its pages?',
				r_u_sure: 'Are you sure?',
				del_all_pages: 'ALL PAGES FOR THESE SITES WILL BE DELETED!'
			},
			error: {
				site_empty: 'Site name cannot be empty',
				id_bad: 'ID must be two lower-case letters',
				id_exists: 'This language ID already exists',
				lang_empty: 'Language name cannot be empty',
				lang_none: 'There must be at least one language'
			}
		},
		forms: {
			selfname: 'Saved forms',
			ip_address: 'IP address',
			field_contents: 'Field contents'
		}
	},
	no_description: 'No description',
	pages: {
		empty_bin: 'Empty bin'
	}
};
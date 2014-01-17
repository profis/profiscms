Ext.namespace('PC.langs');

PC.langs.ru = {
	yes: 'Yes',
	no: 'No',
	all: 'Bсе',
	date: 'Дата',
	time: 'Время',
	search: 'Cтраница поиска',
	search_empty: 'Поиск',
	search_pages: 'Искать страницу',
	custom: 'Дополнительные',
	core: 'Обязательные',
	logout: 'Выйти',
	clear_cache: 'Очистить кэш',
	no_title: 'Нет названия',
	home: 'Стартовая страница', 
	find: 'Найти',
	replace: 'Заменить',
	error: 'Ошибка',
	warning: 'Предупреждение',
	styles: 'Стили',
	gallery: 'Галерея',
	quotes: 'Кавычки',
	load: 'Загрузить',
	save: 'Сохранить',
	cancel: 'Отменить',
	add: 'Добавить',
	edit: 'Изменить',
	change: 'Изменить',
	copy: 'Копировать',
	del: 'Удалить',
    move_left: 'Влево',
    move_right: 'Вправо',
    move_up: 'Вверх',
    move_down: 'Вниз',
	
	not_set: 'Не установленный',
	price_not_set: 'Не установленна',
	
	close: 'Закрыть',
	seo: 'SEO',
	name: 'Название',
	custom_name: 'Название в тексте',
	link: 'Ссылка',
	seo_link: 'SEO ссылка',
	seo_permalink: 'Постоянная ссылка',
	title: 'Заголовок',
	desc: 'Описание',
	keyword: 'Ключевое слово',
	keywords: 'Ключевые слова',
	reference_id: 'Идентификационный ID',
	from: 'От',
	to: 'До',
	sel_redir_dst: 'Выберите перенаправление',
	last_update: 'Последнее обновление',
	save_time: 'Время сохранения',
	user: 'Пользователь',
	plugins: 'Модули',
	site: 'Сайт',
	language: 'Язык',
	country: 'Страна',
	currency_code: 'Код валюты',
	default_language: 'Язык по умолчанию',
	show_menu_in: 'Язык в меню',
	edit_styles: 'Редактировать стили',
	apply: 'Применить',
	create_new_page: 'Создать новую страницу',
	bin: 'Корзина',
	attention: 'Внимание',
	delete_page_that_has_shortcuts: 'На эту страницу ссылаются другие страницы. Если вы удалите её, то они перестанут функционировать.',
	inactive_site_page: 'Показывать эту страницу, если этот сайт не активен',
	auth: {
		login: 'Логин',
		password: 'Пароль',
		banned_title: 'Ваш аккаунт заблокирован',
		banned_msg: 'Ваш аккаунт временно заблокирован из-за повторного ввода неверных данных.<br />Попробуйте подключиться чуть позже.',
		invalid: 'Неверно указано имя пользователя или пароль.',
		database_error: 'Ошибка базы данных',
		unknown_error: 'Неизвестная ошибка',
		login_error: 'Ошибка подключения',
		permissions: {
			types: {
				core: {
					access_admin: {
						title: 'Access admin',
						description: 'Доступ в панель управления'
					},
					admin: {
						title: 'Super admin',
						description: 'Управление всем: сайтами, страницами, плагинами и т.д.'
					},
					pages: {
						title: 'pages',
						description: 'Доступ к дереву страниц'
					},
					page_nodes: {
						title: 'page_nodes',
						description: 'Управление доступом к определенным страницам дерева'
					},
					plugins: {
						title: 'plugins',
						description: 'Доступ к плагинам (доступ к внутренним частям плагинов конфигурируется отдельно)'
					}
				}
			}
		}
	},
	editor: {
		insert_quotes: 'Вставить кавычки',
		change_case: 'Изменить величину букв',
		lowercase: 'Все буквы строчные',
		uppercase: 'Все буквы заглавные',
		capitalize: 'Первые буквы заглавные',
		sentence_case: 'Первая буква предложения заглавная'
	},
	menu: {
		menu: 'Меню',
		preview: 'Просмотр',
		shortcut_to: 'Ссылка на',
		new_page: 'Новая страница',
		new_subpage: 'Новая подстраница',
		rename: 'Переименовать',
		addNew: 'Добавить'
	},
	tab: {
		text: 'Текст',
		info: 'Инфо 1',
		info2: 'Инфо 2',
		info3: 'Инфо 3',
		info_mobile: 'Для мобильных',
		seo: 'SEO',
		properties: 'Свойства'
	},
	page: {
		properties: 'Свойства страницы',
		type: 'Тип страницы',
		front: 'Титульная страница',
		route_lock: 'Не обновлять ссылку автоматически',
		hot: 'Выделить в меню сайта',
		nomenu: 'Не показывать в меню сайта',
		published: 'Опубликовать на сайте',
		show_period: 'Показывать только в этот период',
		shortcut_from: 'Ссылка из',
		source_id: 'Источник',
		target: 'Открыть в новом окне'
	},
	prev_ver: {
		s: 'Предыдущие версии'
		//reset: 'Сбросить на текущую версию'
	},
	msg: {
		paste_as_plain: 'Текст, который вы хотите вставить, похож на копируемый из Microsoft Word. Вы хотите очистить его перед вставкой?',
		error: {
			page: {
				create: 'Ошибка при создании страницы',
				del: 'Ошибка при удалении страницы {0}',
				move: 'Ошибка при перемещении страницы {0}',
				rename: 'Ошибка при переименовании страницы'
			},
			trash: {
				empty: 'Ошибка при очистке мусорки'
			},
			prev_ver: {
				load: 'Ошибка при загрузке предыдущей версии',
				del: 'Ошибка при удалении предыдущих версий'
			},
			data: {
				load: 'Ошибка при загрузке данных',
				save: 'Ошибка при сохранении данных'
			},
			plugins: 'Не удалось получить список модулей'
		},
		load: {
			prev_ver: 'Загрузка предыдущей версии...'
		},
		title: {
			confirm: 'Подтвердите',
			loading: 'Загрузка...',
			saving: 'Сохранение...',
			save: 'Вы не сохранили информацию!'
		},
		confirm_delete: 'Вы уверены, что вы хотите удалить {0}?',
		loading: 'Загрузка, подождите...',
		saving: 'Сохранение, подождите...',
		save: 'Сохранить изменения?',
		perm_del: 'Вы действительно хотите навсегда удалить {0}?',
		del_prev_ver: 'Вы действительно хотите удалить предыдущую версию {0}?',
		empty_trash: 'Вы действительно хотите навсегда удалить все страницы из мусорки?',
		logout: 'Вы действительно хотите выйти из программы?',
		move_menu_warning: 'Перенос меню в дереве каталогов влияет на порядок вывода меню на сайте. Продолжить?'
	},
	align: {
		left: 'Слева',
		center: 'По центру',
		right: 'Справа',
		justify: 'По всей строке'
	},
	dialog: {
		styles: {
			property: 'Свойство',
			value: 'Значение',
			cls: 'Класс стилей',
			tag: 'Метка',
			sites: 'Сайты',
			any: 'любой',
			all: 'Все',
			advanced: 'Дополнительно',
			pangram: 'Съешь ещё этих мягких французских булок, да выпей же чаю',
			tags: {
				p: 'Параграф',
				table: 'Таблица',
				tr: 'Строка таблицы',
				td: 'Ячейки таблицы',
				img: 'Изображение',
				span: 'Текст',
				a: 'Ссылка',
				div: 'Контейнер',
				h1: 'Заголовок 1',
				h2: 'Заголовок 2',
				h3: 'Заголовок 3',
				h4: 'Заголовок 4',
				h5: 'Заголовок 5',
				h6: 'Заголовок 6'
			},
			error: {
				cls: {
					whitespace: 'Вы не можете оставлять здесь промежутки',
					empty: 'Класс CSS не может быть пустой',
					badchars: 'Класс CSS содержит недопустимые символы',
					exists: 'Этот класс CSS уже существует'
				},
				prop: {
					empty: 'Свойство CSS не может быть пустым',
					badchars: 'Свойство CSS содержит недопустимые символы',
					exists: 'Это свойство CSS уже существует'
				},
				value: {
					badchars: 'Значение CSS содержит недопустимые символы'
				}
			},
			form: {
				font: 'Шрифт',
				font_size: 'Размер шрифта',
				line_height: 'Высота строки',
				line_height_normal: 'Нормальная',
				color: 'Цвет',
				background_color: 'Цвет фона',
				font_weight: 'Толщина шрифта',
				font_style: 'Стиль шрифта',
				text_decoration: 'Подчёркивание',
				text_align: 'Выравнивание',
				text_indent: 'Красная строка',
				text_transform: 'Преобразование текста',
				border_collapse: 'Одинарная линия на стыке рамок',
				vertical_align: 'Вертикальное выравнивание',
				border: 'Рамка (размер, стиль и цвет)',
				margin: 'Край (в пикселях)',
				padding: 'Отступ (в пикселях)'
			},
			weight: {
				bold: 'Выделенный',
				normal: 'Обычный'
			},
			style: {
				italic: 'Курсив',
				oblique: 'Наклонный',
				normal: 'Обычный'
			},
			decor: {
				underline: 'Подчёркнутый',
				line_through: 'Перечёркнутый',
				overline: 'Линия сверху',
				none: 'Обычный'
			},
			xform: {
				uppercase: 'Заглавные буквы',
				lowercase: 'Строчные буквы',
				capitalize: 'С заглавных букв'
			},
			top: 'Сверху',
			middle: 'По центру',
			bottom: 'Снизу',
			solid: 'Однородный',
			dotted: 'Пунктиром',
			dashed: 'Штрихами',
			select_style_class: 'Выберите класс CSS стилей'
		},
		anchor: {
			title: 'Вставить якорь',
			title_update: 'Обновить якорь',
			insert: 'Вставить',
			update: 'Обновить',
			anchor_field: 'Название'
		},
		tablecell: {
			title: 'Свойства ячейки',
			general: 'Основные',
			advanced: 'Дополнительные',
			alignment: 'Выравнивание',
			valign: 'Вертикальное выравнивание',
			top: 'По верху',
			middle: 'Вертикально по центру',
			bottom: 'По низу',
			width: 'Ширина',
			height: 'Высота',
			_class: 'Класс CSS стилей',
			id: 'Id',
			style: 'Стиль',
			bg_image: 'Изображение на фоне',
			bg_color: 'Цвет фона',
			border_color: 'Цвет границы',
			target_cell: 'Данная ячейка',
			target_row: 'Все ячейки в строке',
			target_col: 'Все ячейки в столбикe',
			target_all: 'Все ячейки в таблице',
			wrap: 'Перенос слов'
		},
		colorpicker: {
			custom_color: 'Настройка цвета',
			pick_custom: 'Выберите цвет'
		},
		gallery: {
			sync: 'Синхронизация с FTP',
			proportions: 'Сохранить пропорции',
			title: {
				gallery: 'Галерея',
				category: 'Категория',
				trash: 'Мусорка',
				settings: 'Настройки',
				upload: 'Загрузить',
				filter_unused: 'Отфильтровать неиспользуемые файлы',
				search: 'Поиск',
				uploader: 'Загрузить',
				image_cropper: 'Задать параметры изображения'
			},
			search: {
				empty_text: 'Искать в галерее'
			},
			new_filename: 'Новое название файла',
			type: 'Тип',
			cannot_be_default_type: 'Нельзя выбрать тип по умолчанию',
			too_short: 'Слишком короткое',
			too_long: 'Слишком днинное',
			max_w: 'Макс. ширина',
			max_h: 'Макс. высота',
			width_cannot_be_more_than: 'Ширина не может быть более 2000 пикселей',
			width_cannot_be_less_than: 'Ширина не может быть менее 5 пикселей',
			height_cannot_be_more_than: 'Высота не может быть более 2000 пикселей',
			height_cannot_be_less_than: 'Высота не может быть менее 5 пикселей',
			quality_percentage: 'Качество, в %',
			max_quality: 'Максимальное качество не может быть более 100%',
			min_quality: 'Минимальное качество не может быть менее 5%',
			cropper_loading: 'Пожалуйста подождите, задать параметры можно только после загрузки изображения...',
			mark_unused: 'Пометить неиспользуемые файлы',
			close_after_insert: 'Закрыть галерею, вставив изображение',
			close_after_click_outside: 'Закрывать галерею после нажатия за её пределы', 
			size: 'Размер',
			files_selected_suffix: 'Выбранные файлы',
			no_files: 'Выбранная категория пуста.<br />Вы можете добавить файлы, нажав кнопку в панели управления сверху',
			no_files_in_trashed: 'Выбранная категория пуста.',
			thumbnails: {
				thumbnail: 'Превью изображения',
				small: 'Маленького',
				large: 'Большого размера',
				group: {
					_default: 'По умолчанию',
					custom: 'По выбору'
				},
				get: {
					error_title: 'Невозможно просмотреть превью изображения'
				},
				edit: {
					error_title: 'Тип превью изображения не был изменен'
				},
				_delete: {
					error_title: 'Тип превью изображения не был удален'
				}
			},
			button: {
				ok: 'Я уверен',
				close: 'Закрыть',
				cancel: 'Отменить',
				save: 'Сохранить',
				canceledit: 'Отменить'
			},
			action: {
				open: 'Открыть',
				new_category: 'Новая категория',
				new_category_inside: 'Новая категория внутри',
				cut: 'Вырезать',
				copy: 'Копировать',
				paste: 'Вставить',
				rename: 'Переименовать',
				clear_thumb_cache: 'Очистить кэш',
				_delete: 'Удалить',
				move_to_trash: 'Удалить в мусорку',
				preview: 'Просмотр',
				edit_thumb: 'Изменить',
				edit: {
					edit: 'Изменить',
					small: 'Маленького размера',
					large: 'Большого размера'
				},
				insert: {
					insert: 'Вставить',
					small: 'Маленького размера',
					large: 'Большого размера',
					link: 'Ссылка',
					original: 'Оригинальные',
					as_uploaded: 'Максимальный размер'
				},
				view: {
					view: 'Посмотреть',
					icons: 'Иконки',
					detailed: 'Детализированый список',
					
					sort_by_name: 'Сортировать по названию',
					sort_by_size: 'Сортировать по размеру',
					sort_by_modified: 'Сортировать по дате',
					
					asc: 'по восходящему',
					desc: 'по нисходящему'
				},
				sort: {
					sort: 'Сортировать',
					byname: 'По названию',
					bysize: 'По размеру'
				},
				copy_link: 'Копировать ссылку',
				copy_to_clipboard: 'Копировать в буфер обмена'
			},
			category: {
				create: {
					error_title: 'Категория не была создана',
					default_name: 'Категория'
				},
				rename: {
					error_title: 'Категория не была переименована'
				},
				_delete: {
					confirmation: {
						title: 'Удалить категорию',
						message: 'Вы <b>действительно хотите навсегда удалить</b> эту категорию и все её файлы?'
					},
					error_title: ''
				}
			},
			file: {
				in_use: '<img class="ico" src="images/cancel.png" alt="" /> Этот файл используется и будет недоступен, если вы его удалите!',
				remove: {
					confirmation: {
						title: 'Удалить файл',
						message: 'Вы действительно хотите <b>навсегда удалить</b> этот файл?'
					},
					error_title: 'Файл не был удален'
				},
				rename: {
					error_title: 'Не удалось переименовать файл'
				}
			},
			image: {
				edit: {
					error_title: 'Превью изображение не было сохранено'
				}
			},
			files: {
				in_use: '<img class="ico" src="images/cancel.png" alt="" /> Среди выбранных файлов имеются используемые файлы. Они будут недоступны, если вы их удалите!',
				clear_thumb_cache: {
					confirmation: {
						title: 'Очистить кэш',
						message: 'Вы действительно хотите очистить кэш?'
					},
					error_title: 'Кэш не был очищен',
					request_results_title: 'Результат'
				},
				remove: {
					confirmation: {
						title: 'Удалить файлы',
						message: 'Вы действительно хотите<b>навсегда удалить</b> выбранные файлы?</b>'
					},
					error_title: 'Файлы не были удалены',
					request_results_title: 'Результат удаления файлов'
				},
				restore: {
					confirmation: {
						title: 'Восстановить файл',
						message: 'Вы действительно хотите <b>восстановить выбранные файлы?</b>'
					},
					error_title: 'Ошибка при восстановлении файлов'
				}
			},
			trash: {
				title: {
					empty: 'Очистить мусорку',
					restore: 'Восстановить',
					_delete: 'Удалить'
				},
				col: {
					date_trashed: 'Дата удаления',
					title: 'Названия',
					categories: 'Категории',
					files: 'Файлы'
				},
				category: {
					confirmation: {
						title: 'Поместить категорию в мусорку',
						message: 'Вы действительно хотите поместить категорию и все её файлы <br /> в мусорку?'
					},
					error_title: 'Категория не была помещена в мусорку'
				},
				file: {
					confirmation: {
						title: 'Поместить файл в мусорку',
						message: 'Вы действительно хотите поместить этот файл в мусорку?'
					},
					error_title: 'Файл не был помещен в мусорку'
				},
				files: {
					confirmation: {
						title: 'Поместить файлы в мусорку',
						message: 'Вы действительно хотите поместить выбранные файлы в мусорку?'
					},
					error_title: 'Файлы не были помещены в мусорку',
					request_results_title: 'Результат помещения файлов в мусорку'
				},
				restore: {
					category: {
						error_title: 'Категория не была восстановлена'
					},
					file: {
						error_title: 'Файл не был восстановлен'
					}
				},
				empty: {
					confirmation: {
						title: 'Очистить мусорку',
						message: 'Вы действительно хотите очистить мусорку?'
					},
					error_title: 'Мусорка не была очищена'
				}
			},
			clear_thumb_cache: {
				category: {
					confirmation: {
						title: 'Очистить кэш',
						message: 'Вы действительно хотите очистить кэш?'
					},
					error_title: 'Кэш не был очищен'
				}
			},
			error: {
				connection: {
					title: 'Проблема соединения с сервером',
					message: 'Возникла ошибка при попытке соединения с сервором.<br />Если это будет повторяться, пожалуйста, свяжитесь с администратором сайта.'
				},
				//errors returned from the proxy
				database: 'Ощибка базы данных',
				category_id: 'ID категории не верный',
				already_trashed: 'Категория/файл уже помещен в мусорку, вы не можете сделать этого дважды',
				category_not_found: 'Категория с данным ID не найдена',
				file_id: 'ID файла не верный',
				category: 'Формат названия категории не верный',
				parent: 'Указан неверный ID родительской категории (родительской категории не существует?)',
				position: 'Указана неверная позиция',
				_private: 'Если категория является закрытой, значение должно быть \'1\', в других случаях - \'0\'',
				create_directory: 'Невозможно создать каталог для категории',
				file_not_in_a_trash: 'Файла нет в мусорке',
				file_not_found: 'Файл с указанным ID не был найден',
				category_not_in_a_trash: 'Категории нет в мусорке',
				//thumbnails
				thumbnail_type: 'Выбранный тип превью изображения не верный',
				thumbnail_not_found: 'Тип превью изображения не найден',
				cannot_change_default_type: 'Вы не можете переименовать установленный тип превью',
				max_width: 'Указанная максимально допустимая ширина не верна',
				max_height: 'Указанная максимально допустимая высота не верна',
				quality: 'Указанный формат качества не верный',
				no_changes: 'Изменения не обнаружены',
				filename: 'В названии файла обнаружены недопустимые символы',
				rename_file: 'Не удалось переименовать файл',
				file_already_exists: 'Файл с таким названием уже существует',
				change_default_type_name: 'Вы не можете поменять название для установленного по умолчанию типа превью',
				change_default_type_resize: 'Вы не можете поменять кадрирование для установленного по умолчанию типа превью'
			},
			resize: 'Кадрирование',
			normal: 'Нормальный',
			adaptive: 'Aдаптивный',
			semi_adaptive: 'Полу-aдаптивный'
		},
		gmaps: {
			title: 'Карты',
			geocoder_emptytext: 'Поиск',
			geocoder_error: 'Не удалось установить место по указанному адресу: ',
			latitude: 'Долгота',
			longitude: 'Широта',
			width: 'Ширина',
			height: 'Высота',
			insert: 'Вставить',
			update: 'Обновить'
		},
		image: {
			image_properties: 'Изменить изображение',
			general: 'Общие',
			advanced: 'Дополнительные',
			image_url: 'URL изображения',
			no_large_on_click: 'Не показывать большое изображение при нажатии на него',
			title: 'Заголовок',
			description: 'Описание',
			alignment: 'Выравнивание',
			left: 'Слева',
			right: 'Справа',
			dimensions: 'Размеры',
			proportions: 'Сохранить пропорции',
			id: 'ID',
			_class: 'Класс стилей',
			style: 'Стиль',
			rel: 'Lightbox группа',
			border: 'Рамка',
			solid: 'Однородный',
			dotted: 'Пунктиром',
			dashed: 'Штрихами',
			margin: 'Край'
		},
		links: {
			title_insert: 'Вставить ссылку',
			title_update: 'Изменить ссылку',
			insert: 'Вставить',
			update: 'Изменить',
			general: 'Общие',
			href: 'Ссылка',
			anchor: 'Якорь',
			open_in_new_window: 'Oткрыть ссылку в новом окне',
			open_in_lightbox: 'Oткрыть ссылку в Lightbox окне',
			nofollow: 'Добавить rel="nofollow"',
			t_self: 'Не определена',
			t_blank: 'Новое окно',
			t_parent: 'Родительская категория',
			t_top: 'Вверху',
			advanced: 'Дополнительные',
			title: 'Название',
			_class: 'Класс стилей',
			style: 'Стиль',
			events: 'События'
		},
		media: {
			title: 'Вставить медиа-файл',
			title_update: 'Изменить медиа-файл',
			general: 'Основные',
			advanced: 'Дополнительные',
			update: 'Обновить',
			enter_media_url: 'Выберите в Галерее или введите URL медиа-файла, который хотите вставить в текст',
			url: 'URL медиа-файла',
			error: 'Ошибкa',
			not_supported: 'Данный тип медиа-файлов не поддерживается',
			unable_to_identify: 'Невозможно определить тип медиа-файлa.',
			put_link_here: 'Вставте ссылку на медиа-файл',
			dimensions: 'Размеры',
			proportions: 'Сохранить пропорции',
			id: 'ID',
			border: 'Рамка',
			solid: 'Однородный',
			dotted: 'Пунктиром',
			dashed: 'Штрихами',
			margin: 'Край',
			style: 'Стиль',
			insert: 'Вставить',
			poster: 'Стоп-кадр',
			skin: 'Скин'
		},
		tablerow: {
			title: 'Свойства строки',
			general: 'Основные',
			advanced: 'Дополнительные',
			alignment: 'Выравнивание',
			left: 'Слева',
			center: 'По центру',
			right: 'Справа',
			valign: 'Вертикальное выравнивание',
			top: 'Сверху',
			bottom: 'Внизу',
			_class: 'Класс стилей',
			height: 'Высота',
			bg_color: 'Цвет фона',
			id: 'ID',
			style: 'Стиль',
			bg_image: 'Изображение на фоне',
			apply_row: 'Строка',
			apply_all: 'Все',
			apply_odd: 'Нечетные',
			apply_even: 'Четные',
			update: 'Обновить'
		},
		search: {
			title: 'Найти/заменить',
			find: 'Найти',
			find_what: 'Что найти',
			find_next: 'Найти следующее',
			replace: 'Заменить',
			replace_all: 'Заменить все',
			replace_with: 'Заменить на',
			direction: 'Направление',
			up: 'Вверх',
			down: 'Вниз',
			match_case: 'Соответствие',
			not_found: 'Ничего не найдено',
			replaced: 'Соответствия заменены'
		},
		source: {
			title: 'Редактор кода HTML',
			wrap: 'Перенос слов'
		},	
		mergetablecells: {
			title: 'Объединить ячейки'
		},
		tables: {
			title: 'Вставить таблицу',
			title_update: 'Обновить таблицу',
			cols: 'Столбики',
			rows: 'Строки',
			width: 'Ширина',
			height: 'Высота',
			cellpadding: 'Отступ',
			cellspacing: 'Расстояние',
			alignment: 'Выравнивание',
			left: 'Слева',
			center: 'По центру',
			right: 'Справа',
			_class: 'Класс стилей',
			border: 'Рамка',
			solid: 'Однородный',
			dotted: 'Пунктиром',
			dashed: 'Штрихами',
			bg_image: 'Изображение на фоне',
			style: 'Стиль',
			insert: 'Вставить',
			more: 'Подробнее'
		},
		plugins: {
			title: 'Менеджер плагинов',
			restart_admin_title: 'Перезагрузить панель администратора',
			restart_admin_confirm_question: 'Перезагрузить панель администратора, чтобы увидеть список активных модулей?'
		}
	},
	mod: {
		domains: {
			selfname: 'Домены',
			mask: 'Маска',
			error: {
				mask_empty: 'Маска домена не может быть пустой',
				mask_exists: 'Такая маска домена уже существует'
			},
			mask_example: 'Пример маски: <b>*.com</b> или <b>mycompany.*</b> или <b>*.company.com</b>'
		},
		users: {
			selfname: 'Пользователи',
			user: 'Пользователь',
			pass_new: 'Новый пароль',
			pass_repeat: 'Повторите',
			update: 'Обновить',
			msg: {
				del_confirm: 'Вы действительно хотите удалить пользователя {0}?',
				del_confirm_title: 'Подтвердите удаление пользователя'
			},
			error: {
				save: 'Не удалось сохранить',
				user_empty: 'Имя пользователя не может быть пустым',
				pass_diff: 'Пароли не совпадают',
				pass_blank: 'Пустые пароли запрещены',
				update: 'Ошибка при обновлении пользователя',
				del: 'Ошибка при удалении пользователя'
			}
		},
		backup: {
			selfname: 'Резервные копии',
			backup: 'Создать',
			restore: 'Восстановить',
			restart: 'Перезагрузить',
			backup_restore: 'Создать / восстановить',
			file_name: 'Имя файла',
			file_size: 'Размер',
			created: 'Создан',
			download: 'Загрузить',
			msg: {
				restore: 'Восстановить резервную копию "{0}"?',
				restore_ok: 'Резервная копия успешно восстановлена',
				create_before_restore: 'Создать резервную копию перед восстановлением?',
				del: 'Удалить выбранные резервные копии?'
			},
			error: {
				create: 'Ошибка при создании резервной копии',
				restore: 'Ошибка при восстановлении резервной копии {0}',
				rename: 'Ошибка при переименовывании резервной копии {0}',
				del: 'Ошибка при удалении файлов',
				del_some: 'Некоторые файлы не удалось удалить'
			},
			reset_settings: 'Перезагрузить настройки'
		},
		variables: {
			selfname: 'Переменные',
			category: 'Категория',
			all_sites: 'Все сайты',
			show_langs: 'Показывать языки',
			choose_new_value: 'Выберите новое значение переменной {0}:',
			choose_new_value_title: 'Выберите новое значение',
			explode: 'Разбить',
			implode: 'Собрать',
			translate: 'Перевести',
			error: {
				name_empty: 'Имя не может быть пустым',
				name_in_use: 'Это имя уже используется'
			},
			lock: 'Блокировка одного значения для всех языков'
		},
		config: {
			selfname: 'Настройки'//,
		},
		sites_langs: {
			selfname: 'Сайты и языки',
			theme: 'Шаблон',
			id: 'ID',
			name: 'Название',
			activated: 'Активированный',
			msg: {
				site_delete_title: 'Подтвердите удаление сайта',
				site_delete: 'Вы действительно хотите удалить этот сайт и все его страницы?',
				r_u_sure: 'Вы уверены?',
				del_all_pages: 'ВСЕ СТРАНИЦЫ ЭТИХ САЙТОВ БУДУТ УДАЛЕНЫ!'
			},
			error: {
				site_empty: 'Имя сайта не может быть пустым',
				id_bad: 'ID должен состоять из двух строчных букв',
				id_exists: 'Этот ID языка уже существует',
				lang_empty: 'Название языка не может быть пустым',
				lang_none: 'Должен быть хотя бы один язык'
			}
		}
	},
	no_description: 'Без описания',
	pages: {
		empty_bin: 'Очистить мусорку'
	}
};
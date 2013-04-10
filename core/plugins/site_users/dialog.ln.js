var ln = {
	en: {
		selfname: 'Site users',
		email: 'Email',
		login: 'Nickname',
		name: 'Name',
		banned: 'Ban user',
		pass_new: 'New password',
		pass_repeat: 'Repeat password',
		update: {
			success: 'User has been saved successfully.',
			error: 'There was an error while trying to update user.'
			
		},
		passwords_doesnt_match: 'Passwords doesn`t match',
		error_incorrect_email: 'Enter valid email',
		_delete: {
			confirmation: 'Confirmation',
			confirm_message: 'Are you sure you want to delete this user?',
			error: 'There was an error while trying to delete user.'
		},
		date_registered: 'Date registered',
		last_login: 'Last login'
	},
	lt: {
		selfname: 'Svetainės vartotojai',
		email: 'El. paštas',
		login: 'Slapyvardis',
		name: 'Vardas Pavardė',
		banned: 'Banas',
		pass_new: 'Naujas slaptažodis',
		pass_repeat: 'Pakartokite slaptažodį',
		update: {
			success: 'Vartotojo duomenys sėkmingai atnaujinti',
			error: 'Vartotojo duomenys nebuvo išsaugoti'
			
		},
		passwords_doesnt_match: 'Slaptažodžiai turi sutapti',
		error_incorrect_email: 'Įveskite taisyklingą el. paštą',
		_delete: {
			confirmation: 'Patvirtinimas',
			confirm_message: 'Ar jūs tikrai norite panaikinti šį vartotoją?',
			error: 'Vartotojo panaikinti nepavyko'
		},
		date_registered: 'Registracijos data',
		last_login: 'Pask. prisijungimas'
	},
	 ru: {
        selfname: 'Пользователи сайта',
        email: 'Эл. почта',
		login: 'Псевдоним',
		name: 'Имя Фамилия',
		banned: 'Бан',
        pass_new: 'Новый пароль',
        pass_repeat: 'Повторите пароль',
        update: {
            success: 'Учетная запись пользователя успешно сохранена.',
            error: 'При попытке обновить данные учетной записи пользователя произошла ошибка.'
           
        },
        passwords_doesnt_match: 'Введённые пароли не совпадают',
		error_incorrect_email: 'Введите правильную э-почту',
        _delete: {
            confirmation: 'Подтверждение',
            confirm_message: 'Вы действительно хотите удалить учетную запись пользователя?',
            error: 'При попытке удвлить учетную запись пользователя произошла ошибка.'
        },
		date_registered: 'Дата регистрации',
        last_login: 'Последнее подсоединение'
    }
};

PC.utils.localize('mod.site_users', ln);
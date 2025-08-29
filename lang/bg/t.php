<?php

return [
    'login' => [
        'invalid_credentials' => 'Невалидни данни за вход.',
        'disabled_account'    => 'Няма достъп поради деактивиран акаунт.',
        'user_not_found'      => 'Потребителят не съществува!',
        'unexpected_error'    => 'Неочаквана грешка!',
        'key_locked'          => 'Ключът е заключен от администратор.',
        "register_complete"   => 'Пълната регистрация е приключена',

        'validation' => [
            'user_name_required' => 'Полето потребителско име е задължително.',
            'user_name_string'   => 'Потребителското име трябва да е текст.',
            'password_required'  => 'Полето парола е задължително.',
            'password_string'    => 'Паролата трябва да е текст.',
        ],
    ],

    'middleware' => [
        'unauthorized' => 'Нямате достъп',
        'forbidden_role' => 'Забранено – Недостатъчна роля',
        'missing_device_key' => 'Липсва ключ на устройството',
        'invalid_device' => 'Невалидно или деактивирано устройство.',
    ],

    'transaction' => [
        'invalid_input' => 'Невалидни данни.',
        'invalid_nonce' => 'Невалиден nonce.',
        'invalid_or_expired_nonce' => 'Невалиден или изтекъл nonce.',
        'device_error' => 'Грешка с устройството.',
        'device_mismatch' => 'Устройството не принадлежи на посочения търговец.',
        'key_issue' => 'Проблем с ключа.',
        'invalid_transaction_key' => 'Ключът за транзакция е деактивиран или невалиден.',
        'user_inactive' => 'Потребителят е неактивен.',
        'receiver_inactive' => 'Сметката на получателя е неактивна.',
        'inactive_user' => 'Неактивен потребител.',
        'sender_inactive' => 'Сметката на изпращача е неактивна.',
        'invalid_pin' => 'Невалиден PIN.',
        'invalid_sender_pin' => 'Невалиден PIN на изпращача.',
        'insufficient_funds' => 'Недостатъчно средства.',
        'balance_issue' => 'Проблем със салдото.',
        'unexpected_error' => 'Неочаквана грешка',
        'unexpected_error_details' => 'Възникна неочаквана грешка. Моля, опитайте отново по-късно.',
    ],

    'company' => [
        'not_found' => 'Не е намерена компания.',
        'already_exists' => 'Компанията вече съществува.',
        'deleted' => 'Компанията беше изтрита.',
        'device_not_authorized' => 'Устройството не е оторизирано за тази фирма.',
        'legal_forms' => [
            'ad'   => 'АД',
            'ead'  => 'ЕАД',
            'eood' => 'ЕООД',
            'et'   => 'ЕТ',
            'ood'  => 'ООД',
        ],
    ],

    'device' => [
        'not_found' => 'Устройството не е намерено',
        'user_not_found' => 'Потребителят не е намерен',
    ],

    'fiscalization' => [
        'invalid_nonce' => [
            'short_error' => 'Невалиден nonce.',
            'error' => 'Невалиден или изтекъл nonce.',
        ],
        'invalid_device' => [
            'short_error' => 'Невалидно устройство.',
            'error' => 'Информацията за устройството липсва или е невалидна.',
        ],
        'user_not_found' => [
            'short_error' => 'Потребителят не е намерен.',
            'error' => 'Потребителят, свързан с устройството, не може да бъде намерен.',
        ],
        'company_mismatch' => [
            'short_error' => 'Несъответствие на фирмата.',
            'error' => 'Фирмата не принадлежи на автентикирания потребител.',
        ],
        'transaction_not_found' => [
            'short_error' => 'Транзакцията не е намерена.',
            'error' => 'Посочено е несъществуващо ID на транзакция.',
        ],
        'missing_nonce' => [
            'short_error' => 'Липсва nonce.',
            'error' => 'Nonce на транзакцията липсва или е невалиден.',
        ],
        'invalid_items' => [
            'short_error' => 'Невалидни артикули.',
            'error' => 'Списъкът с артикули не може да бъде обработен коректно.',
        ],
        'invalid_item_entry' => [
            'short_error' => 'Невалиден запис за артикул.',
            'error' => 'Всеки артикул трябва да има ID и числово количество.',
        ],
        'item_not_found' => [
            'short_error' => 'Артикулът не е намерен.',
            'error' => 'Посоченият артикул не принадлежи на този потребител.',
        ],
        'items_total_mismatch' => [
            'short_error' => 'Несъответствие в сумата на артикулите.',
            'error' => 'Междинната сума на артикулите не съвпада с декларираната обща сума.',
        ],
        'paid_amount_issue' => [
            'short_error' => 'Проблем със сумата в брой.',
            'error' => 'Платената в брой сума е по-малка от общата.',
        ],
        'invalid_signature' => [
            'short_error' => 'Невалиден подпис.',
            'error' => 'Подписът на транзакцията е невалиден.',
        ],
        'transaction_not_approved' => [
            'short_error' => 'Транзакцията не е одобрена.',
            'error' => 'Само одобрени транзакции могат да бъдат фискализирани.',
        ],
        'amount_mismatch' => [
            'short_error' => 'Несъответствие на сумата.',
            'error' => 'Общата сума не съвпада със сумата на транзакцията.',
        ],
        'duplicate_fiscal_record' => [
            'short_error' => 'Дублиран фискален запис.',
            'error' => 'Съществува дублиран фискален запис за тази транзакция.',
        ],
        'fiscal_key_issue' => [
            'short_error' => 'Проблем с фискалния ключ.',
            'error' => 'Фискалният ключ е грешен или деактивиран.',
        ],
        "pcs" => "бр.", 
        "kg" => "кг", 
        "L" => "Л", 
        "g" => "г.", 
        "mL" => "мл."
    ],

    'receipt' => [
        'uic' => 'ЕИК: :number',
        'vat_number' => 'НОМЕР ЗДДС: FC:number',
        'cash_register' => 'Касов апарат :register, Магазин :store, Оператор :operator',
        'total' => 'ОБЩО:',
        'paid_cash' => 'Платено (в брой)',
        'change' => 'Ресто',
        'paid_card' => 'Платено (с карта)',
        'mock_bank_header' => '# MOCKSYS ПЛАЩАНЕ С КАРТА #',
        'entered_by_hand' => '# Въведено на ръка',
        'account_number' => '# Номер на сметка: :masked',
        'transaction_signature' => 'Подпис на транзакцията',
        'pin_required' => '# ИЗИСКВА СЕ PIN #',
        'thank_you' => '# БЛАГОДАРИМ ВИ ЗА ПОКУПКАТА #',
        'keep_receipt' => '# СЪХРАНЕТЕ КАСОВАТА БЕЛЕЖКА #',
        'items_count' => ':count АРТИКУЛ/А',
        'system_fiscal_record' => 'СИСТЕМЕН ФИСКАЛЕН ЗАПИС',
    ],

    'transfer' => [
        'sender_or_receiver_missing' => 'Подателят или получателят не съществуват.',
        'sender_receiver_match'      => 'Подателят и получателят не могат да са еднакви.',
        'invalid_pin'                => 'Невалиден ПИН (парола).',
        'invalid_pin_detail'         => 'Прехвърляне на PSU :amount към сметка неуспешно поради невалиден ПИН.',
        'non_positive'               => 'Сумата трябва да бъде положително число!',
        'non_positive_detail'        => 'Прехвърляне на PSU :amount към сметка неуспешно поради неположителна сума.',
        'insufficient_balance'       => 'Недостатъчен баланс!',
        'insufficient_balance_detail' => 'Прехвърляне на PSU :amount към сметка неуспешно поради недостатъчен баланс на подателя от PSU :balance.',
        'inactive_sender'            => 'Вашата сметка в момента е неактивна!',
        'inactive_sender_detail'     => 'Прехвърляне на PSU :amount към сметка неуспешно поради неактивен подател.',
        'inactive_receiver'          => 'Сметката на получателя в момента е неактивна!',
        'inactive_receiver_detail'   => 'Прехвърляне на PSU :amount към сметка неуспешно поради неактивен получател.',
        'success'                    => 'Прехвърлянето беше успешно!',
        'unexpected_error'           => 'Неочаквана грешка!',
    ],
];

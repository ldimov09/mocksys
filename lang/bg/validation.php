<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Полето :attribute трябва да бъде прието.',
    'accepted_if' => 'Полето :attribute трябва да бъде прието, когато :other е :value.',
    'active_url' => 'Полето :attribute трябва да бъде валиден URL адрес.',
    'after' => 'Полето :attribute трябва да бъде дата след :date.',
    'after_or_equal' => 'Полето :attribute трябва да бъде дата след или равна на :date.',
    'alpha' => 'Полето :attribute може да съдържа само букви.',
    'alpha_dash' => 'Полето :attribute може да съдържа само букви, цифри, тирета и долни черти.',
    'alpha_num' => 'Полето :attribute може да съдържа само букви и цифри.',
    'and' => 'и още :count грешка|и още :count грешки',
    'any_of' => 'Полето :attribute е невалидно.',
    'array' => 'Полето :attribute трябва да бъде масив.',
    'ascii' => 'Полето :attribute може да съдържа само еднобайтови буквено-цифрови знаци и символи.',
    'before' => 'Полето :attribute трябва да бъде дата преди :date.',
    'before_or_equal' => 'Полето :attribute трябва да бъде дата преди или равна на :date.',
    'between' => [
        'array' => 'Полето :attribute трябва да има между :min и :max елемента.',
        'file' => 'Полето :attribute трябва да бъде между :min и :max килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде между :min и :max.',
        'string' => 'Полето :attribute трябва да бъде между :min и :max символа.',
    ],

    'boolean' => 'Полето :attribute трябва да бъде вярно или невярно.',
    'can' => 'Полето :attribute съдържа неразрешена стойност.',
    'confirmed' => 'Потвърждението на полето :attribute не съвпада.',
    'contains' => 'Полето :attribute липсва задължителна стойност.',
    'current_password' => 'Паролата е неправилна.',
    'date' => 'Полето :attribute трябва да бъде валидна дата.',
    'date_equals' => 'Полето :attribute трябва да бъде дата равна на :date.',
    'date_format' => 'Полето :attribute трябва да съвпада с формата :format.',
    'decimal' => 'Полето :attribute трябва да има :decimal десетични знака.',
    'declined' => 'Полето :attribute трябва да бъде отказано.',
    'declined_if' => 'Полето :attribute трябва да бъде отказано, когато :other е :value.',
    'different' => 'Полето :attribute и :other трябва да са различни.',
    'digits' => 'Полето :attribute трябва да бъде :digits цифри.',
    'digits_between' => 'Полето :attribute трябва да бъде между :min и :max цифри.',
    'dimensions' => 'Полето :attribute има невалидни размери на изображението.',
    'distinct' => 'Полето :attribute има дублирана стойност.',
    'doesnt_end_with' => 'Полето :attribute не трябва да завършва с едно от следните: :values.',
    'doesnt_start_with' => 'Полето :attribute не трябва да започва с едно от следните: :values.',
    'email' => 'Полето :attribute трябва да бъде валиден имейл адрес.',
    'ends_with' => 'Полето :attribute трябва да завършва с едно от следните: :values.',
    'enum' => 'Избраното :attribute е невалидно.',
    'exists' => 'Избраното :attribute е невалидно.',
    'extensions' => 'Полето :attribute трябва да има едно от следните разширения: :values.',
    'file' => 'Полето :attribute трябва да бъде файл.',
    'filled' => 'Полето :attribute трябва да има стойност.',

    'gt' => [
        'array'   => 'Полето :attribute трябва да има повече от :value елемента.',
        'file'    => 'Полето :attribute трябва да бъде по-голямо от :value килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде по-голямо от :value.',
        'string'  => 'Полето :attribute трябва да бъде повече от :value символа.',
    ],
    'gte' => [
        'array'   => 'Полето :attribute трябва да има :value елемента или повече.',
        'file'    => 'Полето :attribute трябва да бъде по-голямо или равно на :value килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде по-голямо или равно на :value.',
        'string'  => 'Полето :attribute трябва да бъде по-голямо или равно на :value символа.',
    ],
    'hex_color' => 'Полето :attribute трябва да бъде валиден шестнадесетичен цвят.',
    'image' => 'Полето :attribute трябва да бъде изображение.',
    'in' => 'Избраното :attribute е невалидно.',
    'in_array' => 'Полето :attribute трябва да съществува в :other.',
    'in_array_keys' => 'Полето :attribute трябва да съдържа поне един от следните ключове: :values.',
    'integer' => 'Полето :attribute трябва да бъде цяло число.',
    'ip' => 'Полето :attribute трябва да бъде валиден IP адрес.',
    'ipv4' => 'Полето :attribute трябва да бъде валиден IPv4 адрес.',
    'ipv6' => 'Полето :attribute трябва да бъде валиден IPv6 адрес.',
    'json' => 'Полето :attribute трябва да бъде валиден JSON низ.',
    'list' => 'Полето :attribute трябва да бъде списък.',
    'lowercase' => 'Полето :attribute трябва да бъде с малки букви.',
    'lt' => [
        'array'   => 'Полето :attribute трябва да има по-малко от :value елемента.',
        'file'    => 'Полето :attribute трябва да бъде по-малко от :value килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде по-малко от :value.',
        'string'  => 'Полето :attribute трябва да бъде по-малко от :value символа.',
    ],
    'lte' => [
        'array'   => 'Полето :attribute не трябва да има повече от :value елемента.',
        'file'    => 'Полето :attribute трябва да бъде по-малко или равно на :value килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде по-малко или равно на :value.',
        'string'  => 'Полето :attribute трябва да бъде по-малко или равно на :value символа.',
    ],

    'mac_address' => 'Полето :attribute трябва да бъде валиден MAC адрес.',
    'max' => [
        'array'   => 'Полето :attribute не трябва да има повече от :max елемента.',
        'file'    => 'Полето :attribute не трябва да бъде по-голямо от :max килобайта.',
        'numeric' => 'Полето :attribute не трябва да бъде по-голямо от :max.',
        'string'  => 'Полето :attribute не трябва да бъде повече от :max символа.',
    ],
    'max_digits' => 'Полето :attribute не трябва да има повече от :max цифри.',
    'mimes' => 'Полето :attribute трябва да бъде файл от тип: :values.',
    'mimetypes' => 'Полето :attribute трябва да бъде файл от тип: :values.',
    'min' => [
        'array'   => 'Полето :attribute трябва да има поне :min елемента.',
        'file'    => 'Полето :attribute трябва да бъде поне :min килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде поне :min.',
        'string'  => 'Полето :attribute трябва да бъде поне :min символа.',
    ],
    'min_digits' => 'Полето :attribute трябва да има поне :min цифри.',
    'missing' => 'Полето :attribute трябва да липсва.',
    'missing_if' => 'Полето :attribute трябва да липсва, когато :other е :value.',
    'missing_unless' => 'Полето :attribute трябва да липсва, освен ако :other е :value.',
    'missing_with' => 'Полето :attribute трябва да липсва, когато :values са налични.',
    'missing_with_all' => 'Полето :attribute трябва да липсва, когато :values са налични.',
    'multiple_of' => 'Полето :attribute трябва да бъде кратно на :value.',
    'not_in' => 'Избраното :attribute е невалидно.',
    'not_regex' => 'Форматът на полето :attribute е невалиден.',
    'numeric' => 'Полето :attribute трябва да бъде число.',
    'password' => [
        'letters' => 'Полето :attribute трябва да съдържа поне една буква.',
        'mixed' => 'Полето :attribute трябва да съдържа поне една главна и една малка буква.',
        'numbers' => 'Полето :attribute трябва да съдържа поне една цифра.',
        'symbols' => 'Полето :attribute трябва да съдържа поне един символ.',
        'uncompromised' => 'Даденото :attribute се е появило в изтичане на данни. Моля, изберете различно :attribute.',
    ],
    'present' => 'Полето :attribute трябва да присъства.',
    'present_if' => 'Полето :attribute трябва да присъства, когато :other е :value.',
    'present_unless' => 'Полето :attribute трябва да присъства, освен ако :other е :value.',
    'present_with' => 'Полето :attribute трябва да присъства, когато :values са налични.',
    'present_with_all' => 'Полето :attribute трябва да присъства, когато :values са налични.',
    'prohibited' => 'Полето :attribute е забранено.',
    'prohibited_if' => 'Полето :attribute е забранено, когато :other е :value.',
    'prohibited_if_accepted' => 'Полето :attribute е забранено, когато :other е прието.',
    'prohibited_if_declined' => 'Полето :attribute е забранено, когато :other е отказано.',
    'prohibited_unless' => 'Полето :attribute е забранено, освен ако :other е в :values.',
    'prohibits' => 'Полето :attribute забранява :other да присъства.',
    'regex' => 'Форматът на полето :attribute е невалиден.',
    'required' => 'Полето :attribute е задължително.',
    'required_array_keys' => 'Полето :attribute трябва да съдържа записи за: :values.',
    'required_if' => 'Полето :attribute е задължително, когато :other е :value.',
    'required_if_accepted' => 'Полето :attribute е задължително, когато :other е прието.',
    'required_if_declined' => 'Полето :attribute е задължително, когато :other е отказано.',
    'required_unless' => 'Полето :attribute е задължително, освен ако :other е в :values.',
    'required_with' => 'Полето :attribute е задължително, когато :values са налични.',
    'required_with_all' => 'Полето :attribute е задължително, когато :values са налични.',
    'required_without' => 'Полето :attribute е задължително, когато :values не са налични.',
    'required_without_all' => 'Полето :attribute е задължително, когато никое от :values не е налично.',
    'same' => 'Полето :attribute трябва да съвпада с :other.',

    'size' => [
        'array'   => 'Полето :attribute трябва да съдържа :size елемента.',
        'file'    => 'Полето :attribute трябва да бъде :size килобайта.',
        'numeric' => 'Полето :attribute трябва да бъде :size.',
        'string'  => 'Полето :attribute трябва да бъде :size символа.',
    ],
    'starts_with' => 'Полето :attribute трябва да започва с едно от следните: :values.',
    'string' => 'Полето :attribute трябва да бъде текст.',
    'timezone' => 'Полето :attribute трябва да бъде валидна часова зона.',
    'unique' => 'Полето :attribute вече е заето.',
    'uploaded' => 'Неуспешно качване на полето :attribute.',
    'uppercase' => 'Полето :attribute трябва да бъде с главни букви.',
    'url' => 'Полето :attribute трябва да бъде валиден URL адрес.',
    'ulid' => 'Полето :attribute трябва да бъде валиден ULID.',
    'uuid' => 'Полето :attribute трябва да бъде валиден UUID.',


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'user_name' => 'потребителско име',
        'password'  => 'парола',
        "sender_account_number" => "номер на сметка на изпращача",
        "merchant_id" => "ID на търговец",
        "amount" => "сума",
        "transaction_key" => "ключ за транзакции",
        "sender_pin" => "ПИН на изпращача",
        "nonce" => "еднократен номер",
        'name' => 'име',
        'manager_name' => 'име на управителя',
        'address' => 'адрес',
        'legal_form' => 'правна форма',
        'device_name' => 'име на устройството',
        'device_address' => 'адрес',
        'description' => 'описание',
        'number' => 'номер',
        'status' => 'статус',
        'device_key' => 'ключ на устройството',
        "price" => "цена",
        "short_name" => "кратко име",
        "unit" => "мерна единица",
        "business_name"          => "име на бизнес",
        "business_username"      => "потребителско име на бизнес",
        "business_password"      => "парола на бизнес",
        "user_username"          => "потребителско име на потребител",
        "user_password"          => "парола на потребител",
        "company_name"           => "име на фирма",
        "company_address"        => "адрес на фирма",
    ],
];

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

    'accepted' => 'Ce champ doit être accepté.',
    'accepted_if' => 'Ce champ doit être accepté quand :other a la valeur :value.',
    'active_url' => 'Ce n\'est pas une URL valide.',
    'after' => 'La date doit être postérieure au :date.',
    'after_or_equal' => 'La date doit être postérieure ou égale au :date.',
    'alpha' => 'Ce champ ne doit contenir que des lettres.',
    'alpha_dash' => 'Ce champ ne doit contenir que des lettres, des chiffres et des tirets.',
    'alpha_num' => 'Ce champ ne doit contenir que des lettres et des chiffres.',
    'array' => 'Ce champ doit être un tableau.',
    'ascii' => 'Ce champ ne doit contenir que des caractères alphanumériques et des symboles à un octet.',
    'before' => 'La date doit être antérieure au :date.',
    'before_or_equal' => 'La date doit être antérieure ou égale au :date.',
    'between' => [
        'array' => 'Le tableau doit contenir entre :min et :max éléments.',
        'file' => 'La taille du fichier doit être comprise entre :min et :max kilo-octets.',
        'numeric' => 'La valeur doit être comprise entre :min et :max.',
        'string' => 'Le texte doit contenir entre :min et :max caractères.',
    ],
    'boolean' => 'Ce champ doit être vrai ou faux.',
    'can' => 'Ce champ contient une valeur non autorisée.',
    'confirmed' => 'Le champ de confirmation ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => 'Ce n\'est pas une date valide.',
    'date_equals' => 'La date doit être égale à :date.',
    'date_format' => 'Ce champ ne correspond pas au format :format.',
    'decimal' => 'Ce champ doit comporter :decimal décimales.',
    'declined' => 'Ce champ doit être décliné.',
    'declined_if' => 'Ce champ doit être décliné quand :other a la valeur :value.',
    'different' => 'Ce champ et :other doivent être différents.',
    'digits' => 'Ce champ doit contenir :digits chiffres.',
    'digits_between' => 'Ce champ doit contenir entre :min et :max chiffres.',
    'dimensions' => 'L\'image a des dimensions invalides.',
    'distinct' => 'Ce champ a une valeur en double.',
    'doesnt_end_with' => 'Ce champ ne doit pas finir par une des valeurs suivantes : :values.',
    'doesnt_start_with' => 'Ce champ ne doit pas commencer par une des valeurs suivantes : :values.',
    'email' => 'Ce champ doit être une adresse email valide.',
    'ends_with' => 'Ce champ doit se terminer par une des valeurs suivantes : :values.',
    'enum' => 'La valeur sélectionnée est invalide.',
    'exists' => 'La valeur sélectionnée est invalide.',
    'file' => 'Ce champ doit être un fichier.',
    'filled' => 'Ce champ doit avoir une valeur.',
    'gt' => [
        'array' => 'Le tableau doit contenir plus de :value éléments.',
        'file' => 'La taille du fichier doit être supérieure à :value kilo-octets.',
        'numeric' => 'La valeur doit être supérieure à :value.',
        'string' => 'Le texte doit contenir plus de :value caractères.',
    ],
    'gte' => [
        'array' => 'Le tableau doit contenir au moins :value éléments.',
        'file' => 'La taille du fichier doit être supérieure ou égale à :value kilo-octets.',
        'numeric' => 'La valeur doit être supérieure ou égale à :value.',
        'string' => 'Le texte doit contenir au moins :value caractères.',
    ],
    'image' => 'Ce champ doit être une image.',
    'in' => 'La valeur sélectionnée est invalide.',
    'in_array' => 'Ce champ n\'existe pas dans :other.',
    'integer' => 'Ce champ doit être un entier.',
    'ip' => 'Ce champ doit être une adresse IP valide.',
    'ipv4' => 'Ce champ doit être une adresse IPv4 valide.',
    'ipv6' => 'Ce champ doit être une adresse IPv6 valide.',
    'json' => 'Ce champ doit être un document JSON valide.',
    'lowercase' => 'Ce champ doit être en minuscules.',
    'lt' => [
        'array' => 'Le tableau doit contenir moins de :value éléments.',
        'file' => 'La taille du fichier doit être inférieure à :value kilo-octets.',
        'numeric' => 'La valeur doit être inférieure à :value.',
        'string' => 'Le texte doit contenir moins de :value caractères.',
    ],
    'lte' => [
        'array' => 'Le tableau doit contenir au plus :value éléments.',
        'file' => 'La taille du fichier doit être inférieure ou égale à :value kilo-octets.',
        'numeric' => 'La valeur doit être inférieure ou égale à :value.',
        'string' => 'Le texte doit contenir au plus :value caractères.',
    ],
    'mac_address' => 'Ce champ doit être une adresse MAC valide.',
    'max' => [
        'array' => 'Le tableau ne peut pas contenir plus de :max éléments.',
        'file' => 'La taille du fichier ne peut pas dépasser :max kilo-octets.',
        'numeric' => 'La valeur ne peut pas être supérieure à :max.',
        'string' => 'Le texte ne peut pas contenir plus de :max caractères.',
    ],
    'max_digits' => 'Ce champ ne doit pas avoir plus de :max chiffres.',
    'mimes' => 'Le fichier doit être de type : :values.',
    'mimetypes' => 'Le fichier doit être de type : :values.',
    'min' => [
        'array' => 'Le tableau doit contenir au moins :min éléments.',
        'file' => 'La taille du fichier doit être d\'au moins :min kilo-octets.',
        'numeric' => 'La valeur doit être supérieure ou égale à :min.',
        'string' => 'Le texte doit contenir au moins :min caractères.',
    ],
    'min_digits' => 'Ce champ doit avoir au moins :min chiffres.',
    'missing' => 'Ce champ doit être manquant.',
    'missing_if' => 'Ce champ doit être manquant quand :other a la valeur :value.',
    'missing_unless' => 'Ce champ doit être manquant sauf si :other a la valeur :value.',
    'missing_with' => 'Ce champ doit être manquant quand :values est présent.',
    'missing_with_all' => 'Ce champ doit être manquant quand :values sont présents.',
    'multiple_of' => 'La valeur doit être un multiple de :value.',
    'not_in' => 'La valeur sélectionnée est invalide.',
    'not_regex' => 'Le format de ce champ est invalide.',
    'numeric' => 'Ce champ doit être un nombre.',
    'password' => [
        'letters' => 'Le mot de passe doit contenir au moins une lettre.',
        'mixed' => 'Le mot de passe doit contenir au moins une majuscule et une minuscule.',
        'numbers' => 'Le mot de passe doit contenir au moins un chiffre.',
        'symbols' => 'Le mot de passe doit contenir au moins un symbole.',
        'uncompromised' => 'Le mot de passe a été compromis dans une fuite de données. Veuillez choisir un autre mot de passe.',
    ],
    'present' => 'Ce champ doit être présent.',
    'prohibited' => 'Ce champ est interdit.',
    'prohibited_if' => 'Ce champ est interdit quand :other a la valeur :value.',
    'prohibited_unless' => 'Ce champ est interdit à moins que :other est dans :values.',
    'prohibits' => 'Ce champ interdit la présence de :other.',
    'regex' => 'Le format de ce champ est invalide.',
    'relatable' => 'Ce champ n\'est pas associé à cette ressource.',
    'required' => 'Ce champ est obligatoire.',
    'required_array_keys' => 'Ce champ doit contenir des entrées pour : :values.',
    'required_if' => 'Ce champ est obligatoire quand :other est :value.',
    'required_if_accepted' => 'Ce champ est obligatoire quand :other est accepté.',
    'required_unless' => 'Ce champ est obligatoire sauf si :other est dans :values.',
    'required_with' => 'Ce champ est obligatoire quand :values est présent.',
    'required_with_all' => 'Ce champ est obligatoire quand :values sont présents.',
    'required_without' => 'Ce champ est obligatoire quand :values est absent.',
    'required_without_all' => 'Ce champ est obligatoire quand aucun de :values n\'est présent.',
    'same' => 'Ce champ doit être identique à :other.',
    'size' => [
        'array' => 'Le tableau doit contenir :size éléments.',
        'file' => 'La taille du fichier doit être de :size kilo-octets.',
        'numeric' => 'La valeur doit être :size.',
        'string' => 'Le texte doit contenir :size caractères.',
    ],
    'starts_with' => 'Ce champ doit commencer par une des valeurs suivantes : :values.',
    'string' => 'Ce champ doit être une chaîne de caractères.',
    'timezone' => 'Ce champ doit être un fuseau horaire valide.',
    'unique' => 'La valeur est déjà utilisée.',
    'uploaded' => 'Le fichier n\'a pas pu être téléchargé.',
    'uppercase' => 'Ce champ doit être en majuscules.',
    'url' => 'Ce format est invalide.',
    'ulid' => 'Ce champ doit être un ULID valide.',
    'uuid' => 'Ce champ doit être un UUID valide.',

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
        'name' => 'nom',
        'username' => 'nom d\'utilisateur',
        'email' => 'adresse email',
        'first_name' => 'prénom',
        'last_name' => 'nom',
        'password' => 'mot de passe',
        'password_confirmation' => 'confirmation du mot de passe',
        'city' => 'ville',
        'country' => 'pays',
        'address' => 'adresse',
        'phone' => 'téléphone',
        'mobile' => 'portable',
        'age' => 'âge',
        'sex' => 'sexe',
        'gender' => 'genre',
        'day' => 'jour',
        'month' => 'mois',
        'year' => 'année',
        'hour' => 'heure',
        'minute' => 'minute',
        'second' => 'seconde',
        'title' => 'titre',
        'content' => 'contenu',
        'description' => 'description',
        'excerpt' => 'extrait',
        'date' => 'date',
        'time' => 'heure',
        'available' => 'disponible',
        'size' => 'taille',
    ],

];

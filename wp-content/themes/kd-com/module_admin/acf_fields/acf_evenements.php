<?php
if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array(
        'key' => 'group_event_details',
        'title' => 'Détails de l\'événement',
        'fields' => array(
            // Type d'événement
            array(
                'key' => 'field_event_type',
                'label' => 'Type d\'événement',
                'name' => 'event_type',
                'type' => 'radio',
                'choices' => array(
                    'single' => 'Événement ponctuel (une seule date)',
                    'multi_day' => 'Événement sur plusieurs jours',
                    'recurring' => 'Événement récurrent',
                ),
                'layout' => 'vertical',
                'default_value' => 'single',
                'required' => 1,
            ),
            // Date de l'événement (ponctuel)
            array(
                'key' => 'field_event_date',
                'label' => 'Date de l\'événement',
                'name' => 'event_date',
                'type' => 'date_picker',
                'required' => 1,
                'display_format' => 'd/m/Y',
                'return_format' => 'Y-m-d',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_event_type',
                            'operator' => '==',
                            'value' => 'single',
                        ),
                    ),
                ),
            ),
            // Date de début (plusieurs jours ou récurrent)
            array(
                'key' => 'field_event_start_date',
                'label' => 'Date de début',
                'name' => 'event_start_date',
                'type' => 'date_picker',
                'required' => 1,
                'display_format' => 'd/m/Y',
                'return_format' => 'Y-m-d',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_event_type',
                            'operator' => '==',
                            'value' => 'multi_day',
                        ),
                    ),
                    array(
                        array(
                            'field' => 'field_event_type',
                            'operator' => '==',
                            'value' => 'recurring',
                        ),
                    ),
                ),
            ),
            // Date de fin (plusieurs jours ou récurrent)
            array(
                'key' => 'field_event_end_date',
                'label' => 'Date de fin',
                'name' => 'event_end_date',
                'type' => 'date_picker',
                'required' => 1,
                'display_format' => 'd/m/Y',
                'return_format' => 'Y-m-d',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_event_type',
                            'operator' => '==',
                            'value' => 'multi_day',
                        ),
                    ),
                    array(
                        array(
                            'field' => 'field_event_type',
                            'operator' => '==',
                            'value' => 'recurring',
                        ),
                    ),
                ),
            ),
            // Fréquence de récurrence
            array(
                'key' => 'field_event_recurrence',
                'label' => 'Fréquence de récurrence',
                'name' => 'event_recurrence',
                'type' => 'select',
                'choices' => array(
                    'daily' => 'Tous les jours',
                    'weekly_monday' => 'Tous les lundis',
                    'weekly_tuesday' => 'Tous les mardis',
                    'weekly_wednesday' => 'Tous les mercredis',
                    'weekly_thursday' => 'Tous les jeudis',
                    'weekly_friday' => 'Tous les vendredis',
                    'weekly_saturday' => 'Tous les samedis',
                    'weekly_sunday' => 'Tous les dimanches',
                    'monthly' => 'Tous les mois (même jour)',
                ),
                'default_value' => 'weekly_saturday',
                'required' => 1,
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_event_type',
                            'operator' => '==',
                            'value' => 'recurring',
                        ),
                    ),
                ),
            ),
            // Heure de l'événement
            array(
                'key' => 'field_event_time',
                'label' => 'Heure de l\'événement',
                'name' => 'event_time',
                'type' => 'time_picker',
                'required' => 1,
                'display_format' => 'H:i',
                'return_format' => 'H:i',
            ),
            // Lieu de l'événement
            array(
                'key' => 'field_event_location',
                'label' => 'Lieu de l\'événement',
                'name' => 'event_location',
                'type' => 'text',
                'required' => 1,
            ),
            // Adresse pour la carte (optionnel)
            array(
                'key' => 'field_event_address',
                'label' => 'Adresse complète (pour la carte)',
                'name' => 'event_address',
                'type' => 'textarea',
                'instructions' => 'Saisissez l\'adresse complète pour afficher la carte.',
            ),
            // Type de tarif : Gratuit ou Payant
            array(
                'key' => 'field_event_price_type',
                'label' => 'Type de tarif',
                'name' => 'event_price_type',
                'type' => 'radio',
                'choices' => array(
                    'free' => 'Gratuit',
                    'paid' => 'Payant',
                ),
                'layout' => 'horizontal',
                'default_value' => 'free',
            ),
            // Lien de billetterie principal (si payant)
            array(
                'key' => 'field_event_ticket_link',
                'label' => 'Lien vers la billetterie',
                'name' => 'event_ticket_link',
                'type' => 'url',
                'required' => 0,
                'placeholder' => 'https://...',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_event_price_type',
                            'operator' => '==',
                            'value' => 'paid',
                        ),
                    ),
                ),
            ),
            // Repeater pour les tarifs (si payant)
            array(
                'key' => 'field_event_prices',
                'label' => 'Tarifs',
                'name' => 'event_prices',
                'type' => 'repeater',
                'instructions' => 'Ajoutez les différents tarifs pour cet événement.',
                'required' => 0,
                'collapsed' => 'field_event_price_label',
                'min' => 0,
                'layout' => 'table',
                'button_label' => 'Ajouter un tarif',
                'conditional_logic' => array(
                    array(
                        array(
                            'field' => 'field_event_price_type',
                            'operator' => '==',
                            'value' => 'paid',
                        ),
                    ),
                ),
                'sub_fields' => array(
                    array(
                        'key' => 'field_event_price_label',
                        'label' => 'Libellé du tarif',
                        'name' => 'price_label',
                        'type' => 'text',
                        'required' => 1,
                        'placeholder' => 'Ex : Tarif normal, Tarif réduit, VIP',
                    ),
                    array(
                        'key' => 'field_event_price_amount',
                        'label' => 'Montant',
                        'name' => 'price_amount',
                        'type' => 'text',
                        'required' => 1,
                        'placeholder' => 'Ex : 20€',
                    ),
                ),
            ),
            // Afficher la carte OpenStreetMap ?
            array(
                'key' => 'field_event_show_map',
                'label' => 'Afficher une carte interactive ?',
                'name' => 'event_show_map',
                'type' => 'true_false',
                'default_value' => 0,
                'ui' => 1,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'evenements',
                ),
            ),
        ),
    ));

endif;
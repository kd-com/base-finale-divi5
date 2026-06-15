<?php
if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array(
        'key' => 'faq_event_details',
        'title' => 'Détails de la faq',
        'fields' => array(
            
            array(
                'key' => 'field_faq_answer',
                'label' => 'Réponse',
                'name' => 'faq_answer',
                'type' => 'wysiwyg',
                'required' => 1,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'kd_faq',
                ),
            ),
        ),
    ));

endif;
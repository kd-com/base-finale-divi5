<?php
if(function_exists('acf_add_local_field_group')):

    acf_add_local_field_group(array(
        'key' => 'client_event_details',
        'title' => 'Détails du client',
        'fields' => array(
            
            array(
                'key' => 'field_client_link',
                'label' => 'Lien',
                'name' => 'client_link',
                'type' => 'url',
                'required' => 0,
            ),
            array(
                'key' => 'field_client_logo',
                'label' => 'Logo',
                'name' => 'client_logo',
                'type' => 'image',
                'required' => 1,
                'return_format' => 'url',
                'library'       => 'all',
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'slider_client',
                ),
            ),
        ),
    ));

endif;
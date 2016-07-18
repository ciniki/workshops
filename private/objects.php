<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_workshops_objects($ciniki) {
    
    $objects = array();
    $objects['workshop'] = array(
        'name'=>'Workshops',
        'sync'=>'yes',
        'table'=>'ciniki_workshops',
        'fields'=>array(
            'name'=>array(),
            'permalink'=>array(),
            'url'=>array(),
            'description'=>array(),
            'reg_flags'=>array(),
            'num_tickets'=>array(),
            'start_date'=>array(),
            'end_date'=>array(),
            'times'=>array(),
            'primary_image_id'=>array('ref'=>'ciniki.images.image'),
            'long_description'=>array(),
            ),
        'history_table'=>'ciniki_workshop_history',
        );
    $objects['image'] = array(
        'name'=>'Image',
        'sync'=>'yes',
        'table'=>'ciniki_workshop_images',
        'fields'=>array(
            'workshop_id'=>array('ref'=>'ciniki.workshops.workshop'),
            'name'=>array(),
            'permalink'=>array(),
            'webflags'=>array(),
            'image_id'=>array('ref'=>'ciniki.images.image'),
            'description'=>array(),
            'url'=>array(),
            ),
        'history_table'=>'ciniki_workshop_history',
        );
    $objects['file'] = array(
        'name'=>'File',
        'sync'=>'yes',
        'table'=>'ciniki_workshop_files',
        'fields'=>array(
            'workshop_id'=>array('ref'=>'ciniki.workshops.workshop'),
            'extension'=>array(),
            'name'=>array(),
            'permalink'=>array(),
            'webflags'=>array(),
            'description'=>array(),
            'org_filename'=>array(),
            'publish_date'=>array(),
            'binary_content'=>array('history'=>'no'),
            ),
        'history_table'=>'ciniki_workshop_history',
        );
    $objects['registration'] = array(
        'name'=>'Registration',
        'sync'=>'yes',
        'table'=>'ciniki_workshop_registrations',
        'fields'=>array(
            'workshop_id'=>array('ref'=>'ciniki.workshops.workshop'),
            'customer_id'=>array('ref'=>'ciniki.customers.customer'),
            'invoice_id'=>array('ref'=>'ciniki.pos.invoice'),
            'num_tickets'=>array(),
            'customer_notes'=>array(),
            'notes'=>array(),
            ),
        'history_table'=>'ciniki_workshop_history',
        );
    
    return array('stat'=>'ok', 'objects'=>$objects);
}
?>

<?php
//
// Description
// -----------
// This function will return the history for an element in the workshop images.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant to get the history for.
// workshop_image_id:   The ID of the workshop image to get the history for.
// field:               The field to get the history for.
//
// Returns
// -------
//  <history>
//      <action date="2011/02/03 00:03:00" value="Value field set to" user_id="1" />
//      ...
//  </history>
//  <users>
//      <user id="1" name="users.display_name" />
//      ...
//  </users>
//
function ciniki_workshops_imageHistory($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        'workshop_image_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Image'), 
        'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Field'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //
    // Check access to tnid as owner, or sys admin
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'private', 'checkAccess');
    $rc = ciniki_workshops_checkAccess($ciniki, $args['tnid'], 'ciniki.workshops.imageHistory');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
    return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.workshops', 'ciniki_workshop_history', 
        $args['tnid'], 'ciniki_workshop_images', $args['workshop_image_id'], $args['field']);
}
?>

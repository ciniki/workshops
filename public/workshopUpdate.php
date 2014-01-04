<?php
//
// Description
// ===========
// This method will update an workshop in the database.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business the workshop is attached to.
// name:			(optional) The new name of the workshop.
// url:				(optional) The new URL for the workshop website.
// description:		(optional) The new description for the workshop.
// start_date:		(optional) The new date the workshop starts.  
// end_date:		(optional) The new date the workshop ends, if it's longer than one day.
// 
// Returns
// -------
// <rsp stat='ok' />
//
function ciniki_workshops_workshopUpdate(&$ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
        'workshop_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Workshop'), 
		'name'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Name'), 
		'permalink'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Permalink'), 
		'url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'URL'), 
		'description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Description'), 
		'num_tickets'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Number of Tickets'),
		'reg_flags'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Registration Flags'),
		'start_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Start Date'), 
		'end_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'End Date'), 
		'primary_image_id'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Image'), 
		'long_description'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Long Description'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'private', 'checkAccess');
    $rc = ciniki_workshops_checkAccess($ciniki, $args['business_id'], 'ciniki.workshops.workshopUpdate'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// Get the existing workshop details
	//
	$strsql = "SELECT uuid FROM ciniki_workshops "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['workshop_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.workshops', 'workshop');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['workshop']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1462', 'msg'=>'Workshop not found'));
	}
	$workshop = $rc['workshop'];

	if( isset($args['name']) ) {
		$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 \-]/', '', strtolower($args['name'])));
		//
		// Make sure the permalink is unique
		//
		$strsql = "SELECT id, name, permalink FROM ciniki_workshops "
			. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
			. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
			. "AND id <> '" . ciniki_core_dbQuote($ciniki, $args['workshop_id']) . "' "
			. "";
		$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.workshops', 'workshop');
		if( $rc['stat'] != 'ok' ) {
			return $rc;
		}
		if( $rc['num_rows'] > 0 ) {
			return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1463', 'msg'=>'You already have an workshop with this name, please choose another name'));
		}
	}

	//
	// Update the workshop in the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectUpdate');
	return ciniki_core_objectUpdate($ciniki, $args['business_id'], 'ciniki.workshops.workshop', $args['workshop_id'], $args);
}
?>

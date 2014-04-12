<?php
//
// Description
// -----------
// This method will add a new workshop for the business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to add the workshop to.
// name:			The name of the workshop.
// url:				(optional) The URL for the workshop website.
// description:		(optional) The description for the workshop.
// start_date:		(optional) The date the workshop starts.  
// end_date:		(optional) The date the workshop ends, if it's longer than one day.
//
// Returns
// -------
// <rsp stat="ok" id="42">
//
function ciniki_workshops_workshopAdd(&$ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'name'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Name'), 
		'permalink'=>array('required'=>'no', 'blank'=>'no', 'name'=>'Permalink'), 
		'url'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'URL'), 
		'description'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Description'), 
		'num_tickets'=>array('required'=>'no', 'default'=>'0', 'blank'=>'yes', 'name'=>'Number of Tickets'),
		'reg_flags'=>array('required'=>'no', 'default'=>'0', 'blank'=>'no', 'name'=>'Registration Flags'),
		'start_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'Start Date'), 
		'end_date'=>array('required'=>'no', 'blank'=>'yes', 'type'=>'date', 'name'=>'End Date'), 
		'times'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Hours'), 
		'primary_image_id'=>array('required'=>'no', 'default'=>'0', 'blank'=>'yes', 'name'=>'Image'), 
		'long_description'=>array('required'=>'no', 'default'=>'', 'blank'=>'yes', 'name'=>'Long Description'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	if( !isset($args['permalink']) || $args['permalink'] == '' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'makePermalink');
		$args['permalink'] = ciniki_core_makePermalink($ciniki, $args['name']);
//		$args['permalink'] = preg_replace('/ /', '-', preg_replace('/[^a-z0-9 \-]/', '', strtolower($args['name'])));
	}

	//
	// Check access to business_id as owner
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'private', 'checkAccess');
	$ac = ciniki_workshops_checkAccess($ciniki, $args['business_id'], 'ciniki.workshops.workshopAdd');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Check the permalink doesn't already exist
	//
	$strsql = "SELECT id FROM ciniki_workshops "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' " 
		. "AND permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.workshops', 'workshop');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( $rc['num_rows'] > 0 ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1464', 'msg'=>'You already have an workshop with this name, please choose another name'));
	}

	//
	// Add the workshop to the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectAdd');
	return ciniki_core_objectAdd($ciniki, $args['business_id'], 'ciniki.workshops.workshop', $args);
}
?>

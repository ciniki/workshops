<?php
//
// Description
// -----------
// This method will return the list of actions that were applied to an element of an workshop. 
// This method is typically used by the UI to display a list of changes that have occured 
// on an element through time. This information can be used to revert elements to a previous value.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the details for.
// workshop_id:			The ID of the workshop to get the history for.
// field:				The field to get the history for. This can be any of the elements 
//						returned by the ciniki.workshops.get method.
//
// Returns
// -------
// <history>
// <action user_id="2" date="May 12, 2012 10:54 PM" value="Workshop Name" age="2 months" user_display_name="Andrew" />
// ...
// </history>
//
function ciniki_workshops_workshopHistory($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'workshop_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Workshop'), 
		'field'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'field'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'private', 'checkAccess');
	$rc = ciniki_workshops_checkAccess($ciniki, $args['business_id'], 'ciniki.workshops.workshopHistory');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	if( $args['field'] == 'start_date' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
		return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.workshops', 'ciniki_workshop_history', $args['business_id'], 'ciniki_workshops', $args['workshop_id'], $args['field'],'date');
	}
	if( $args['field'] == 'end_date' ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistoryReformat');
		return ciniki_core_dbGetModuleHistoryReformat($ciniki, 'ciniki.workshops', 'ciniki_workshop_history', $args['business_id'], 'ciniki_workshops', $args['workshop_id'], $args['field'], 'date');
	}

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbGetModuleHistory');
	return ciniki_core_dbGetModuleHistory($ciniki, 'ciniki.workshops', 'ciniki_workshop_history', $args['business_id'], 'ciniki_workshops', $args['workshop_id'], $args['field']);
}
?>

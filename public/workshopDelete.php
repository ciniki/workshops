<?php
//
// Description
// -----------
// This method will delete a workshop from the business.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business the workshop is attached to.
// workshop_id:			The ID of the workshop to be removed.
//
// Returns
// -------
// <rsp stat="ok">
//
function ciniki_workshops_workshopDelete(&$ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'workshop_id'=>array('required'=>'yes', 'default'=>'', 'blank'=>'yes', 'name'=>'Workshop'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'private', 'checkAccess');
	$ac = ciniki_workshops_checkAccess($ciniki, $args['business_id'], 'ciniki.workshops.workshopDelete');
	if( $ac['stat'] != 'ok' ) {
		return $ac;
	}

	//
	// Get the uuid of the workshop to be deleted
	//
	$strsql = "SELECT uuid FROM ciniki_workshops "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND id = '" . ciniki_core_dbQuote($ciniki, $args['workshop_id']) . "' "
		. "";
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQuery');
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.workshops', 'workshop');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['workshop']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'1459', 'msg'=>'The workshop does not exist'));
	}
	$workshop_uuid = $rc['workshop']['uuid'];

	//
	// Start transaction
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionStart');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionRollback');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbTransactionCommit');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDelete');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'objectDelete');
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbAddModuleHistory');
	$rc = ciniki_core_dbTransactionStart($ciniki, 'ciniki.workshops');
	if( $rc['stat'] != 'ok' ) { 
		return $rc;
	}   

	//
	// Remove the images
	//
	$strsql = "SELECT id, uuid, image_id FROM ciniki_workshop_images "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND workshop_id = '" . ciniki_core_dbQuote($ciniki, $args['workshop_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.workshops', 'image');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.workshops');
		return $rc;
	}
	if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
		$images = $rc['rows'];
		
		foreach($images as $iid => $image) {
			$rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.workshops.image', 
				$image['id'], $image['uuid'], 0x04);
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.workshops');
				return $rc;	
			}
		}
	}

	//
	// Remove the files for the workshop
	//
	$strsql = "SELECT id, uuid "
		. "FROM ciniki_workshop_files "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND workshop_id = '" . ciniki_core_dbQuote($ciniki, $args['workshop_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.workshops', 'file');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.workshops');
		return $rc;
	}
	if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
		$files = $rc['rows'];
		foreach($files as $fid => $file) {
			$rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.workshops.file', 
				$file['id'], $file['uuid'], 0x04);
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.workshops');
				return $rc;	
			}
		}
	}

	//
	// Remove the registrations
	//
	$strsql = "SELECT id, uuid "
		. "FROM ciniki_workshop_registrations "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND workshop_id = '" . ciniki_core_dbQuote($ciniki, $args['workshop_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.workshops', 'registration');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.workshops');
		return $rc;
	}
	if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'private', 'registrationDelete');
		$registrations = $rc['rows'];
		foreach($registrations as $rid => $registration) {
			$rc = ciniki_core__registrationDelete($ciniki, $args['business_id'], 
				$registration['id'],$registration['uuid']);
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.workshops');
				return $rc;
			}
		}
	}

	//
	// Remove any registration questions for this workshop
	//
/*	$strsql = "SELECT id, uuid "
		. "FROM ciniki_workshop_registration_questions "
		. "WHERE business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND workshop_id = '" . ciniki_core_dbQuote($ciniki, $args['workshop_id']) . "' "
		. "";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.workshops', 'question');
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.workshops');
		return $rc;
	}
	if( isset($rc['rows']) && count($rc['rows']) > 0 ) {
		$questions = $rc['rows'];
		foreach($questions as $qid => $question) {
			$rc = ciniki_core_objectDelete($ciniki, 'ciniki.workshops.question', $question['id'], $question['uuid'],
				array('business_id'=>$args['business_id']), 0x04);
			if( $rc['stat'] != 'ok' ) {
				ciniki_core_dbTransactionRollback($ciniki, 'ciniki.workshops');
				return $rc;	
			}
		}
	}
*/

	//
	// Remove the workshop from any web collections
	//
	if( isset($ciniki['business']['modules']['ciniki.web']) 
		&& ($ciniki['business']['modules']['ciniki.web']['flags']&0x08) == 0x08
		) {
		ciniki_core_loadMethod($ciniki, 'ciniki', 'web', 'hooks', 'webCollectionDeleteObjRef');
		$rc = ciniki_web_hooks_collectionDeleteObjRef($ciniki, $args['business_id'],
			array('object'=>'ciniki.workshops.workshop', 'object_id'=>$args['workshop_id']));
		if( $rc['stat'] != 'ok' ) {	
			ciniki_core_dbTransactionRollback($ciniki, 'ciniki.workshops');
			return $rc;
		}
	}

	//
	// Remove the workshop
	//
	$rc = ciniki_core_objectDelete($ciniki, $args['business_id'], 'ciniki.workshops.workshop', 
		$args['workshop_id'], $workshop_uuid, 0x04);
	if( $rc['stat'] != 'ok' ) {
		ciniki_core_dbTransactionRollback($ciniki, 'ciniki.workshops');
		return $rc;
	}

	//
	// Commit the transaction
	//
	$rc = ciniki_core_dbTransactionCommit($ciniki, 'ciniki.workshops');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Update the last_change date in the business modules
	// Ignore the result, as we don't want to stop user updates if this fails.
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'businesses', 'private', 'updateModuleChangeDate');
	ciniki_businesses_updateModuleChangeDate($ciniki, $args['business_id'], 'ciniki', 'workshops');

//	$ciniki['syncqueue'][] = array('push'=>'ciniki.workshops.workshop',
//		'args'=>array('delete_uuid'=>$workshop_uuid, 'delete_id'=>$args['workshop_id']));

	return array('stat'=>'ok');
}
?>

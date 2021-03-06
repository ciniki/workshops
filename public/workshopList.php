<?php
//
// Description
// -----------
// This method will return the list of workshops for a tenant.  It is restricted
// to tenant owners and sysadmins.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:     The ID of the tenant to get workshops for.
//
// Returns
// -------
// <upcoming>
//      <workshop id="41" name="Workshop name" url="http://www.ciniki.org/" description="Workshop description" start_date="Jul 18, 2012" end_date="Jul 20, 2012" />
// </upcoming>
// <past />
//
function ciniki_workshops_workshopList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'), 
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];
    
    //  
    // Check access to tnid as owner, or sys admin. 
    //  
    ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'private', 'checkAccess');
    $ac = ciniki_workshops_checkAccess($ciniki, $args['tnid'], 'ciniki.workshops.workshopList');
    if( $ac['stat'] != 'ok' ) { 
        return $ac;
    }   

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki);
    
    //
    // Load the upcoming workshops
    //
    $strsql = "SELECT id, name, url, description, "
        . "DATE_FORMAT(start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS start_date, "
        . "DATE_FORMAT(end_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS end_date "
        . "FROM ciniki_workshops "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND (end_date >= DATE(NOW()) OR start_date >= DATE(NOW())) "
        . "ORDER BY ciniki_workshops.start_date ASC "
        . "";

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.workshops', 'workshops', 'workshop', array('stat'=>'ok', 'workshops'=>array()));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    
    $rsp = array('stat'=>'ok', 'upcoming'=>$rc['workshops']);

    //
    // Load the past workshops
    //
    $strsql = "SELECT id, name, url, description, "
        . "DATE_FORMAT(start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS start_date, "
        . "DATE_FORMAT(end_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS end_date "
        . "FROM ciniki_workshops "
        . "WHERE tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ((ciniki_workshops.end_date > ciniki_workshops.start_date AND ciniki_workshops.end_date < DATE(NOW())) "
            . "OR (ciniki_workshops.end_date < ciniki_workshops.start_date AND ciniki_workshops.start_date <= DATE(NOW())) "
            . ") "
        . "ORDER BY ciniki_workshops.start_date ASC "
        . "";

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbRspQuery');
    $rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'ciniki.workshops', 'workshops', 'workshop', array('stat'=>'ok', 'workshops'=>array()));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $rsp['past'] = $rc['workshops'];

    return $rsp;
}
?>

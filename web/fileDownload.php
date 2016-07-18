<?php
//
// Description
// ===========
// This function will return the file details and content so it can be sent to the client.
//
// Returns
// -------
//
function ciniki_workshops_web_fileDownload($ciniki, $business_id, $workshop_permalink, $file_permalink) {

    //
    // Get the file details
    //
    $strsql = "SELECT ciniki_workshop_files.id, "
        . "ciniki_workshop_files.name, "
        . "ciniki_workshop_files.permalink, "
        . "ciniki_workshop_files.extension, "
        . "ciniki_workshop_files.binary_content "
        . "FROM ciniki_workshops, ciniki_workshop_files "
        . "WHERE ciniki_workshops.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND ciniki_workshops.permalink = '" . ciniki_core_dbQuote($ciniki, $workshop_permalink) . "' "
        . "AND ciniki_workshops.id = ciniki_workshop_files.workshop_id "
        . "AND ciniki_workshop_files.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
        . "AND CONCAT_WS('.', ciniki_workshop_files.permalink, ciniki_workshop_files.extension) = '" . ciniki_core_dbQuote($ciniki, $file_permalink) . "' "
        . "AND (ciniki_workshop_files.webflags&0x01) = 0 "      // Make sure file is to be visible
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.workshops', 'file');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['file']) ) {
        return array('stat'=>'noexist', 'err'=>array('pkg'=>'ciniki', 'code'=>'1417', 'msg'=>'Unable to find requested file'));
    }
    $rc['file']['filename'] = $rc['file']['name'] . '.' . $rc['file']['extension'];

    return array('stat'=>'ok', 'file'=>$rc['file']);
}
?>

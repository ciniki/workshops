<?php
//
// Description
// ===========
// This function will return the file details and content so it can be sent to the client.
//
// Returns
// -------
//
function ciniki_workshops_web_fileDownload($ciniki, $tnid, $workshop_permalink, $file_permalink) {

    //
    // Get the file details
    //
    $strsql = "SELECT ciniki_workshop_files.id, "
        . "ciniki_workshop_files.name, "
        . "ciniki_workshop_files.permalink, "
        . "ciniki_workshop_files.extension, "
        . "ciniki_workshop_files.binary_content "
        . "FROM ciniki_workshops, ciniki_workshop_files "
        . "WHERE ciniki_workshops.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_workshops.permalink = '" . ciniki_core_dbQuote($ciniki, $workshop_permalink) . "' "
        . "AND ciniki_workshops.id = ciniki_workshop_files.workshop_id "
        . "AND ciniki_workshop_files.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND CONCAT_WS('.', ciniki_workshop_files.permalink, ciniki_workshop_files.extension) = '" . ciniki_core_dbQuote($ciniki, $file_permalink) . "' "
        . "AND (ciniki_workshop_files.webflags&0x01) = 0 "      // Make sure file is to be visible
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.workshops', 'file');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( !isset($rc['file']) ) {
        return array('stat'=>'noexist', 'err'=>array('code'=>'ciniki.workshops.27', 'msg'=>'Unable to find requested file'));
    }
    $rc['file']['filename'] = $rc['file']['name'] . '.' . $rc['file']['extension'];

    return array('stat'=>'ok', 'file'=>$rc['file']);
}
?>

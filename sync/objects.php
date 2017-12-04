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
function ciniki_workshops_sync_objects($ciniki, &$sync, $tnid, $args) {
    ciniki_core_loadMethod($ciniki, 'ciniki', 'workshops', 'private', 'objects');
    return ciniki_workshops_objects($ciniki);
}
?>

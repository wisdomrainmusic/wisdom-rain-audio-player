jQuery( document ).ready( function( $ ) {
    var trackList = $( '#wrap-tracks-list' );

    if ( ! trackList.length ) {
        return;
    }

    var placeholderTitle = trackList.data( 'placeholderTitle' );
    var placeholderUrl = trackList.data( 'placeholderUrl' );
    var placeholderDuration = trackList.data( 'placeholderDuration' );
    var labelRemove = trackList.data( 'labelRemove' );

    $( '#wrap-add-track' ).on( 'click', function() {
        var index = trackList.find( '.wrap-track-item' ).length;
        var newItem =
            '<div class="wrap-track-item">' +
                '<input type="text" name="wrap_tracks[' + index + '][title]" placeholder="' + placeholderTitle + '" />' +
                '<input type="text" name="wrap_tracks[' + index + '][url]" placeholder="' + placeholderUrl + '" />' +
                '<input type="text" name="wrap_tracks[' + index + '][duration]" placeholder="' + placeholderDuration + '" />' +
                '<button type="button" class="button wrap-remove-track">' + labelRemove + '</button>' +
            '</div>';

        trackList.append( newItem );
    } );

    trackList.on( 'click', '.wrap-remove-track', function() {
        $( this ).closest( '.wrap-track-item' ).remove();
    } );
} );

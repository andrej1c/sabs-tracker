jQuery( document ).ready( function ( $ ) {
    $( '.sabs_add_row' ).on( 'click', function ( e ) {
        e.preventDefault();
        var $this = $( this );
        var $closest_tr = $this.closest( 'tr' );

        add_new_row( $closest_tr, true );

    } );

    function add_new_row( $closest_tr, prev ) {
        var $closest_tbody = $closest_tr.closest( 'tbody' );
        var tr_count = $closest_tbody.find( 'tr' );

        var max = 0;
        tr_count.each( function ( key, tr ) {
            var id = parseInt( $( tr ).attr( 'id' ) );
            if ( id > max ) {
                max = id;
            }
        } );
        if ( prev ) {
            var $clone = $closest_tr.prev().clone( true );
        } else {
            var $clone = $closest_tr.clone( true );
        }

        $clone.attr( 'id', ( max + 1 ) );
        $clone.find( '.sabs_user_id' ).attr( 'name', 'sabs_tracker_user_category[user_category][' + ( max + 1 ) + '][user_id]' );
        $clone.find( '.sabs_user_id' ).val( -1 );
        $clone.find( '.sabs_category_id' ).attr( 'name', 'sabs_tracker_user_category[user_category][' + ( max + 1 ) + '][category_id]' );
        $clone.find( '.sabs_category_id' ).val( -1 );
        $clone.insertBefore( $closest_tr );
    }
    
    $( '.sabs_remove_row' ).on( 'click', function ( e ) {
        e.preventDefault();
        var $this = $( this );
        var $closest_tr = $this.closest( 'tr' ); //to remove
        var $closest_tbody = $this.closest( 'tbody' );
        var tr_count = $closest_tbody.find( 'tr' ).length;

        if ( tr_count < 3 ) {
            add_new_row( $closest_tr, false );
        }
        $closest_tr.remove();
    } );
} );
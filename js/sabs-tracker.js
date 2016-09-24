jQuery( document ).ready( function ( $ ) {
    $( '.sabs_add_row' ).on( 'click', function ( e ) {
        e.preventDefault();
        var $this = $( this );
        var $closest_tr = $this.closest( 'tr' );
        $(".chosen-select").chosen("destroy");
        add_new_row( $closest_tr, true );
        $(".chosen-select").chosen();
    } );
    $(".chosen-select").chosen();

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
    
    var limitYouth = limits.youth_limit;
    var limitVolunteers = limits.volunteers_limit;
    var limitTotal = limits.total_limit;

    var youthCategory = categories.youth_category;
    var volunteersCategory = categories.volunteers_category;

    $( "form#post" ).submit( function () {
        var referer = $( 'input[name$="post_type"]' );
        if ( referer.length > 0 && referer.val().indexOf( "sabs_schedule" ) > -1 ) {
            var checkedTotal = $( "#categorychecklist input:checkbox:checked" ).length;
            var checkedYouth = $( "#category-" + youthCategory + " input:checkbox:checked" ).length;
            var checkedVolunteers = $( "#category-" + volunteersCategory + " input:checkbox:checked" ).length;
            
            if ( 0 != limitYouth && checkedYouth > limitYouth ) {
                // Hide the ajax loading image (gets fired when you hit the publish/update button)
                $( ".spinner" ).hide();

                // Remove the class that disables the publish/update button after it's clicked
                $( "#publish" ).removeClass( 'button-primary-disabled' );

                // Now fire off the alert
                alert( "Warning: You have selected more attendees then is allowed in Youth category. Allowed number is " + limitYouth);

                // And return false
//                return false;
            }
            if ( 0 != limitVolunteers && checkedVolunteers > limitVolunteers ) {
                // Hide the ajax loading image (gets fired when you hit the publish/update button)
                $( ".spinner" ).hide();

                // Remove the class that disables the publish/update button after it's clicked
                $( "#publish" ).removeClass( 'button-primary-disabled' );

                // Now fire off the alert
                alert( "Warning: You have selected more attendees then is allowed in Volunteers category. Allowed number is " + limitVolunteers);
                // And return false
//                return false;
            }
            if ( 0 != limitTotal && checkedTotal > limitTotal ) {
                // Hide the ajax loading image (gets fired when you hit the publish/update button)
                $( ".spinner" ).hide();

                // Remove the class that disables the publish/update button after it's clicked
                $( "#publish" ).removeClass( 'button-primary-disabled' );

                // Now fire off the alert
                alert( "Warning: You have selected more attendees then is allowed in total. Allowed number is " + limitTotal);

                // And return false
//                return false;
            }
        }

    } );
} );
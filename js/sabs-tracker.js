jQuery( document ).ready( function ( $ ) {
    $(".chosen-select").chosen();
    
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
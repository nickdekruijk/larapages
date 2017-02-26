// The url used to login, will be set later by template 
var lp_adminpath=false;

// What to do when a 401 occurs when doing Ajax calls. Probably redirect to login page.
function lp_401() {
    document.location=document.location;
}

// What to do when a 500 occurs when doing Ajax calls.
function lp_500(xhr) {
	var message='Sorry, an error occured. You may need te login again.';
	if (xhr && xhr.responseText) 
		message+='\n\n'+$(xhr.responseText).find('.exception_message').text();
		
    if (confirm(message))
        document.location=document.location;
}

// Change the size of the .editview based on the .listview width
// Must be called everytime the .listview content changes
function lp_sizeEditor() {
    // Fix the listview width since it sometimes seems to be one pixel off
    $('.listview').width('auto');
    $('.listview').width($('.listview').width()+1); // +1 to fix wrapping in firefox
    
    // Set the header width to match listview width
    $('.listview .header').css('width',$('.listview').outerWidth());
    // Set editview left position based on the listview width. -0.3 makes it a hairline on retina displays
    $('.editview').css('left',$('.listview').outerWidth()+parseInt($('.listview').css('left'))-0.3);
}

// Helper function that formats the file sizes
function lp_formatFileSize(bytes) {
    bytes=parseInt(bytes);
    if (bytes >= 1000000000)
        return (bytes / 1000000000).toFixed(2) + ' GB';
    if (bytes >= 1000000)
        return (bytes / 1000000).toFixed(2) + ' MB';
    return (bytes / 1000).toFixed(2) + ' KB';
}

// Display an iFrame and fade background
function lp_modalFrame(src) {
    $('body').append('<div class="modal modalBackground"></div>');
    $('body').append('<div class="modal modalFrame"><iframe src="'+src+'"></iframe>');
    $('.modalBackground').click(function() {
       $('.modal').detach(); 
    });
}

function lp_listviewEvents(id) {
    // Clicking the open/close pointer before a treeview item
    $('.listview LI'+(id?'[data-id="'+id+'"]':'')+'>DIV>SPAN:first-child').click(function() {
        if ($(this).hasClass('ui-sortable-helper')) return false;
        $(this).parent().parent().toggleClass('open');
        lp_sizeEditor();
        return false;
    });
    
    // Click a treeview item, open the editor
    $('.listview LI'+(id?'[data-id="'+id+'"]':'')).click(function() {
        if ($(this).hasClass('ui-sortable-helper')) return false;
        $(this).addClass('open');
        $('.listview LI').removeClass('active');
        $(this).addClass('active');
        lp_show($('.listview').data('model'),$(this).data('id'))
        return false;
    });
    
    // Click a table item, open the editor
    $('.listview TR'+(id?'[data-id="'+id+'"]':'')).click(function() {
        $('.listview TR').removeClass('active');
        $(this).addClass('active');
        lp_show($('.listview').data('model'),$(this).data('id'))
        return false;
    });
    
    // Minimize search field
    $('.listview .searchclose').click(function() {
        $('.listview #search').val('').change();
        $('.listview .searchclose, .listview #search').removeClass('show');
    });
    
    // Expand search field
    $('.listview #search').focus(function() {
        $('.listview .searchclose, .listview #search').addClass('show');
    });
    
    // Hide listview items not matching the search value
    $('.listview #search').change(function() {
        var search=$('.listview #search').val().toUpperCase();
        $('.listview LI, .listview TR').each(function() {
            var t=$(this).text().toUpperCase();
            if (t.indexOf(search)!==-1) 
                $(this).removeClass('hidden');
            else
                $(this).addClass('hidden');
        });
    });
    // Let keyup trigger change too
    $('.listview #search').keyup(function() {
        $('.listview #search').change();
    });
    
    // Delete a listview item
    $('.listview .delete').click(function() {
        lp_listDestroy(this); 
    });
    
}

// Check if .listview has expanded set, if so expand the treeview where needed.
function lp_listviewExpand() {
    if ($('.listview').data('expanded')>0) {
        var e=$('.listview').data('expanded');
        // Need a better way for this:
        if (e>=5) $('.listview>UL>LI>UL>LI>UL>LI>UL>LI>UL>LI').addClass('open');
        if (e>=4) $('.listview>UL>LI>UL>LI>UL>LI>UL>LI').addClass('open');
        if (e>=3) $('.listview>UL>LI>UL>LI>UL>LI').addClass('open');
        if (e>=2) $('.listview>UL>LI>UL>LI').addClass('open');
        if (e>=1) $('.listview>UL>LI').addClass('open');
        lp_sizeEditor();
    }
}

$(document).ready(function() {
    lp_sizeEditor();

    // Make sure Ajax calls always give the right token
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    $('.listview .toggle').click(function() {
        $('.listview').toggleClass('hidden');
        if ($('.listview').hasClass('hidden')) {
            $('.listview').css('left',-$('.listview').outerWidth()+$('.listview .toggle').innerWidth());
        } else {
            $('.listview').css('left',0);
        }
        lp_sizeEditor();
    })
    
});

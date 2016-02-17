// Show the editor for the selected item
function lp_show(modelId,id) {
    $('.editview').addClass('loading');
    // Clear the current form first
    $('.editview FORM').trigger('reset');
    // Load items with Ajax GET
    $.ajax({
        cache:'false',
        url:'/'+lp_adminpath+'/'+modelId+'/'+id,
        statusCode: {
            401: lp_401,
        },
        success: function(data,status,xhr) {
            $('.editview').removeClass('hidden').removeClass('loading').removeClass('new');
            $('.editview .header H2>SPAN').text(id);
            for (field in data) {
                // Checkboxes need different handling
                if ($('#field_'+field).attr('type')=='checkbox')
                    $('#field_'+field).prop('checked',data[field]>0);
                else
                    $('#field_'+field).val(data[field]);
                // Update tinymce editors with new data, including check if data=null
                if ($('#field_'+field).hasClass('tinymce')) {
                    tinymce.get('field_'+field).setContent(data[field]?data[field]:'');
                }
                // Simulate a password blur so passwords are shown as ********
                $('.editview INPUT[type=password]').blur();
            }
        },
        error: function(xhr,status,error) {
            alert(status)
        },
    })
}

// Save a new item
function lp_store(modelId) {
    // Reuse lp_update for this, but id=false means lp_update will save a new item instead of update
    lp_update(modelId,false)
}

// Update the item
function lp_update(modelId,id) {
    $('.editview').addClass('loading');
    // First save all tinymce editors content to their normal elements
    $('.tinymce').each(function() {
        ed = tinyMCE.get(this.id).save();
    })
    
    // If id>0 then we want to update the item, else create new one, if id<0 the controller will use the parent from that id
    if (id>0)
        var action=id+'/update';
    else if (id<0)
        var action='store/'+(0-id);
    else
        var action='store/0';

    // Update with Ajax POST
    $.ajax({
        method:'post',
        dataType:'json',
        cache:'false',
        url:'/'+lp_adminpath+'/'+modelId+'/'+action,
        statusCode: {
            401: lp_401,
//            500: lp_500,
        },
        data: $('.editview FORM').serialize(),
        success: function(data,status,xhr) {
            if (data.table && data.success) {
                var scrollPosition=$('.listview').scrollTop();
                $('.listview>TABLE').detach();
                $('.listview').append(data.success);
                $('.listview').scrollTop(scrollPosition);
                lp_listviewEvents();
                $('.listview TR[data-id='+id+']').click();
            } else if (data.success) {
                if (id>0) {
                    // Refresh the listview item in case title or active state changed
                    $('.listview LI.active>DIV').detach();
                    $('.listview LI.active').prepend(data.success);
                } else if (data.parent==0) {
                    $('.listview>UL').append('<li data-id="'+data.id+'">'+data.success+'</li>');
                    lp_listviewEvents(data.id);
                    $('.listview LI[data-id='+data.id+']').click();
                } else {
                    // Add item to listview
                    // Check if LI has and child UL
                    if ($('.listview LI[data-id='+data.parent+']>UL').length)
                        $('.listview LI[data-id='+data.parent+']>UL').append('<li data-id="'+data.id+'">'+data.success+'</li>');
                    else
                        $('.listview LI[data-id='+data.parent+']').append('<ul><li data-id="'+data.id+'">'+data.success+'</li><ul>');
                    // Add the click events to the newly created element and open it
                    lp_listviewEvents(data.id);
                    $('.listview LI[data-id='+data.id+']').click();
                }
            }
            lp_sizeEditor();
            $('.editview').removeClass('loading');
        },
        error: function(xhr,status,error) {
            if (xhr.status==500)
                lp_500(xhr)
            else if (xhr.status==422) {
                // 422 means Laravel validation failed
                var error='';
                // Walk thru the fields that didn't validate and format a nice error.
                for (i in xhr.responseJSON) {
                    // Set focus on the first field with an error
                    if (!error) $('#field_'+i).focus();
                    error+=xhr.responseJSON[i]+'\n';
                }
                if (error)
                    alert(error);
                else
                    alert(status+': '+xhr.responseText);
            } else
                alert(status+': '+xhr.responseText);
            $('.editview').removeClass('loading');
        },
    })
}

// Delete the item
function lp_destroy(modelId,id) {
    $('.editview').addClass('loading');
    // Using a timeout so the loading screen actualy shows before the confirm dialog
    setTimeout(function () {
        if (confirm('Are your sure?')) {
            $.ajax({
                method:'post',
                dataType:'json',
                cache:'false',
                url:'/'+lp_adminpath+'/'+modelId+'/'+id+'/destroy',
                success: function(data,status) {
                    $('.listview .active').detach();
                    lp_sizeEditor();
                    $('.editview').removeClass('loading').addClass('hidden');
                },
                error: function(xhr,status,error) {
                    alert(status);
                },
            });
        } else {
            $('.editview').removeClass('loading');
        }
    },50);
}

// Change the parent of the id, checking oldparent just in case
function lp_changeParent(modelId,id,parent,oldparent) {
    $('.editview, .listview').addClass('loading');
    $.ajax({
        method:'post',
        cache:'false',
        data: 'parent='+parent+'&oldparent='+oldparent,
        url:'/'+lp_adminpath+'/'+modelId+'/'+id+'/changeparent',
        statusCode: {
            401: lp_401,
            500: lp_500,
        },
        success: function(data,status,xhr) {
            if (data)
                alert(data);
            lp_saveSorting($('.listview').data('model'),parent);
        },
        error: function(xhr,status,error) {
            alert(status);
            $('.editview, .listview').removeClass('loading');
        },
    });
}

// Save the sorting to the database
function lp_saveSorting(modelId,parent) {
    $('.editview, .listview').addClass('loading');
    var ids='';
    if (parent>0)
        $('LI[data-id='+parent+']>UL>LI').each(function() {
            if (ids) ids+=',';
            ids+=parseInt($(this).data('id'));
        });
    else
        $('.listview>UL>LI').each(function() {
            if (ids) ids+=',';
            ids+=parseInt($(this).data('id'));
        });
    $.ajax({
        method:'post',
        cache:'false',
        data: 'ids='+ids,
        url:'/'+lp_adminpath+'/'+modelId+'/'+parent+'/sort',
        statusCode: {
            401: lp_401,
            500: lp_500,
        },
        success: function(data,status,xhr) {
            if (data)
                alert(data);
            $('.editview, .listview').removeClass('loading');
        },
        error: function(xhr,status,error) {
            alert(status);
            console.log(xhr,status,error);
            $('.editview, .listview').removeClass('loading');
        },
    });
}

// Initialize the nestedSortable script
function lp_nestedSortable() {
    $('.listview>UL').nestedSortable({
        handle: 'div',
        items: 'li',
        listType: 'ul',
        isTree: true,
        toleranceElement: '> div',
        sort: function(a,b) {
            // When starting sorting store the current parent in data-oldparent so we can use it on relocate to check if parent changed
            var id=$(b.item).data('id');
            var parent=$('LI[data-id='+id+']').parent().parent().data('id');
            $('LI[data-id='+id+']').data('oldparent',parent);
        },
        revert: function() {
            alert('revert');  
        },
        relocate: function(a,b) {
            // Done dragging, see what's changed
            var id=$(b.item).data('id');
            var parent=$('LI[data-id='+id+']').parent().parent().data('id');
            var oldparent=$('LI[data-id='+id+']').data('oldparent');
            $('LI[data-id='+id+']').data('oldparent',null)
            // Did parent change? Save new parent do database
            if (parent!=oldparent)
                lp_changeParent($('.listview').data('model'),id,parent,oldparent);
            else
                lp_saveSorting($('.listview').data('model'),parent);
            lp_sizeEditor();
        }
    }).disableSelection();
    $('.listview').on('scroll', function() {
        $('.listview').scrollLeft(0);
    });
}

var lp_media=false;
var lp_mediaTarget=false;

function lp_addMediaClose(save) {
    $('.modal').detach();
    if (save)
        $(lp_mediaTarget).parent().next('TEXTAREA').val(lp_media);
    lp_mediaTarget=false;
    lp_media=false;
}

// Open the media browser in mini mode to allow selecting (multiple) files
function lp_addMedia(target) {
    lp_mediaTarget=target;
    lp_media=$(lp_mediaTarget).parent().next('TEXTAREA').val();
    lp_modalFrame('/'+lp_adminpath+'/media/mini');
}

$(document).ready(function() {

    lp_listviewEvents(false);
    lp_listviewExpand();
    lp_nestedSortable();

    if ($('.editview INPUT.date').length) $('.editview INPUT.date').datepicker({
        showButtonPanel: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
    });
    if ($('.editview INPUT.datetime').length) $('.editview INPUT.datetime').datetimepicker({
        showButtonPanel: true,
        changeMonth: true,
        changeYear: true,
        dateFormat: 'yy-mm-dd',
        timeFormat: 'HH:mm:ss',
    });
    
    $('.editview .media .add').click(function() {
        lp_addMedia(this);
    });
    $('.editview BUTTON.close').click(function() {
        $('.listview .active').removeClass('active');
        $('.editview').addClass('hidden');
        $('.editview').removeClass('new');
        $('.editview FORM').trigger('reset');
        return false;
    })
    $('.editview BUTTON.new').click(function() {
        $('.listview .active').removeClass('active');
        $('.editview FORM').trigger('reset');
        $('.editview').removeClass('hidden').addClass('new');
        return false;
    })
    $('.editview BUTTON.save').click(function() {
        lp_update($('.listview').data('model'),$('.listview .active').data('id'))
        return false;
    })
    $('.editview BUTTON.copy').click(function() {
        lp_update($('.listview').data('model'),-$('.listview .active').data('id'))
        return false;
    })
    $('.editview BUTTON.delete').click(function() {
        lp_destroy($('.listview').data('model'),$('.listview .active').data('id'))
        return false;
    })
    
    $('.editview INPUT[type=password]').focus(function() {
        if ($(this).val()=='********') $(this).val('');
    })
    $('.editview INPUT[type=password]').blur(function() {
        if ($(this).val()=='') $(this).val('********');
    })


});
// Get the folder path for the id
function lp_getFolders(id) {
    if (id==0) return '';
    var ids=String(id).split('-');
    var str='';
    var folder='';
    for (id in ids) {
        str+=ids[id];
        folder+='/'+$('LI[data-id="'+str+'"]>DIV>SPAN:eq(1)').text();
        str+='-';
    }
    $('#uploadfile').fileupload('option','url','/admin/media/store?folder='+encodeURIComponent(folder));
    return folder;
}

function lp_fileselect(target) {
    var checkbox=$(target).parent().find('INPUT[type=checkbox]');
    var file=$('.editview .header H2>SPAN').text()+'/'+$(target).parent().find('SPAN.title').text();
    $(target).parent().toggleClass('selected');
    checkbox.prop('checked',$(target).parent().hasClass('selected'));

    file=file.substr(1);
    var newmedia='';
    var found=false;
    var oldmedia=window.parent.lp_media.split('\n');
    for (f in oldmedia) {
        if (oldmedia[f]==file && !$(target).parent().hasClass('selected')) 
            found=true;
        else {
            if (oldmedia[f]==file && $(target).parent().hasClass('selected')) found=true;
            newmedia+=oldmedia[f]+'\n';
        }
    }
    if (!found) newmedia+=file;
    window.parent.lp_media=newmedia.trim();
}

function lp_fileselectCheck() {
    var media=window.parent.lp_media.split('\n');
    var folder=$('.editview .header H2>SPAN').text();
    $('.editview SPAN.title').each(function() {
        for (f in media) {
            if (folder+'/'+$(this).text()=='/'+media[f]) {
                $(this).parent().find('INPUT[type=checkbox]').prop('checked',true);
                $(this).parent().toggleClass('selected');
            }
        }
    });
}

// Show the editor for the selected item
function lp_show(modelId,id) {
    //$('.editview').addClass('loading');
    // Clear the current form first
    $('.editview .media').html('');
    // Load items with Ajax GET
    $.ajax({
        cache:'false',
        url:'/admin/'+modelId+'/'+id,
        data:'folder='+lp_getFolders(id),
        statusCode: {
            401: lp_401,
        },
        success: function(data,status,xhr) {
            $('.editview').removeClass('loading').removeClass('hidden');
            $('.editview .header H2>SPAN').text(lp_getFolders(id));
            $('.editview UL.media').html(data);
            $('.editview UL.media SPAN.size').each(function() {
                $(this).text(lp_formatFileSize($(this).text()));
            });
            if ($('.editview').hasClass('mini')) {
                lp_fileselectCheck()
                $('.editview SPAN').click(function() {
                    lp_fileselect(this);
                });
            }
            $('.editview SPAN.title').click(function() {
                if (!$('.editview').hasClass('mini'))
                    lp_editTitle(this);
            })
            $('.editview SPAN.actions>A.delete').click(function() {
                lp_destroyMedia(this);
                return false;
            })
            
        },
        error: function(xhr,status,error) {
            alert(status)
        },
    })
}

// Create a new media folder
function lp_newFolder() {
    var oldId=$('.listview LI.active').data('id');
    // Ask for the folder name
    var folder=prompt('Folder name to create in "'+$('.editview .header H2>SPAN').text()+'"');
    // No folder given, cancel
    if (!folder) return false;
    // Create folder with Ajax POST
    $('.editview').addClass('loading');
    $.ajax({
        cache:'false',
        dataType: 'json',
        method:'post',
        url:'/admin/media/newfolder',
        data:'folder='+encodeURIComponent($('.editview .header H2>SPAN').text()+'/'+folder),
        statusCode: {
            401: lp_401,
            500: lp_500,
        },
        success: function(data,status,xhr) {
            if (data.error) {
                alert(data.error);
            } else if (data.success) {
                $('.listview>UL').detach();
                $('.listview').append(data.success);
                lp_listviewEvents(false);
                lp_listviewExpand();
                $('.listview LI[data-id='+oldId+']>UL>LI[data-folder="'+folder.replace(/"/g, '\\"')+'"]').addClass('active');
                lp_openActive();
            }
            $('.editview').removeClass('loading');
        },
        error: function(xhr,status,error) {
            alert(status)
        },
    })
}

// Delete folder
function lp_listDestroy(target) {
    $('.editview').addClass('loading');
    // Using a timeout so the loading screen actualy shows before the confirm dialog
    var id=$(target).parent().parent().data('id');
    var folder=lp_getFolders(id);
    setTimeout(function () {
        if (confirm('Are you sure you want to delete '+folder+'?')) {
            $.ajax({
                method:'post',
                url:'/admin/media/destroyFolder', 
                data:'folder='+folder,
                cache:'false',
                statusCode: {
                    401: lp_401,
                    500: lp_500,
                },
                success: function(data,status,xhr) {
                    if (data) 
                        alert(data);
                    else {
                        $(target).parent().parent().fadeOut().removeClass('active');
                        lp_openActive();
                    }
                    $('.editview').removeClass('loading');
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

// Delete file
function lp_destroyMedia(target) {
    $('.editview').addClass('loading');
    var title=$(target).parent().parent().find('SPAN.title').text();
    // Using a timeout so the loading screen actualy shows before the confirm dialog
    setTimeout(function () {
        if (confirm('Are you sure you want to delete '+title+'?')) {
            $.ajax({
                method:'post',
                data: 'file='+encodeURIComponent(title)+'&folder='+encodeURIComponent($('.editview .header H2>SPAN').text()),
                cache:'false',
                url:'/admin/media/destroy',
                success: function(data,status) {
                    if (data) 
                        alert(data);
                    else
                        $(target).parent().parent().fadeOut();
                    $('.editview').removeClass('loading');
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

// Edit the file title/name
function lp_editTitle(target) {
    if ($(target).hasClass('editing')) return false;
    var title=$(target).text();
    var width=$(target).outerWidth()-6;
    $(target).addClass('editing');
    $(target).html('<input type="text" name="title" data-old="'+title+'" value="'+title+'">');
    $(target).find('input')[0].setSelectionRange(0,title.lastIndexOf('.'));
    $(target).find('input').css('width',width).focus().blur(function() {
        if ($(this).data('old')!=$(this).val() && $(this).val()) {
            title=$(this).val();
            $.ajax({
                method:'post',
                data: 'title='+encodeURIComponent(title)+'&file='+encodeURIComponent($(this).data('old'))+'&folder='+encodeURIComponent($('.editview .header H2>SPAN').text()),
                cache:'false',
                url:'/admin/media/rename',
                success: function(data,status) {
                    if (data) 
                        alert(data);
                },
                error: function(xhr,status,error) {
                    alert(status);
                },
            });
        }
        $(target).text(title);
        $(target).removeClass('editing');
    }).keyup(function(e) {
        var keyCode=e.keyCode || e.which;
        if (keyCode==13) $(this).blur();
        if (keyCode==27) { $(this).val($(this).data('old')); $(this).blur(); }
    });
}

function lp_openActive() {
    if ($('.listview LI.active').length)
        $('.listview LI.active').click();
    else
        $('.listview LI[data-id=0]').click();
}

$(document).ready(function() {

    lp_listviewEvents(false);
    lp_listviewExpand();
    lp_openActive();
                
    $('.editview BUTTON.upload').click(function() {
        $('#uploadfile').click();
        return false;
    })
    $('.editview BUTTON.newfolder').click(function() {
        lp_newFolder();
        return false;
    })
    $('.editview BUTTON.close').click(function() {
        window.parent.lp_addMediaClose(true);
        return false;
    })
    $('.editview BUTTON.cancel').click(function() {
        window.parent.lp_addMediaClose(false);
        return false;
    })
    $('#uploadfile').fileupload({
        dataType: 'json',
        url: '/admin/media/store?folder=',
//         dropZone: $('.editview'),
        add: function (e, data) {
            var tpl = $('<li><div></div><span class="button"></span>'+data.files[0].name+' (<span class="message">'+lp_formatFileSize(data.files[0].size)+', <span class="perc">0</span>%</span>)</li>');
            data.context = tpl.appendTo($('#upload UL'));
            tpl.find('SPAN.button').click(function() {
                if (!tpl.hasClass('done') && !tpl.hasClass('error'))
                    jqXHR.abort();
                tpl.fadeOut(function() {
                    tpl.remove();
                });    
            })
            if (parseInt($('#uploadfile').attr('data-maxUploadSize'))*1024*1024<data.files[0].size)
                tpl.addClass('error').find('span.message').text(lp_formatFileSize(data.files[0].size)+', file is too large to upload');
            else if (!data.files[0].type)
                tpl.addClass('error').find('span.message').text('Sorry, can\'t upload folders');
            else
                var jqXHR = data.submit();
        },
        progress: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            data.context.find('div').css('width', progress+'%');
            data.context.find('span.perc').text(progress);
            if (progress == 100)
                data.context.addClass('done');
        },
        done: function (e, data) {
            if (data.result.status=='success') {
                $('.listview LI.active').click();
                setTimeout(function() {
                    data.context.fadeOut(1000,function() {
                        data.context.remove();
                    });    
                },3000)
            } else {
                data.context.addClass('error').removeClass('done').find('span.message').text(data.result.status);
            }
        },
        fail: function (e, data) {
            console.log('fail',data);
        }
    });

});
    

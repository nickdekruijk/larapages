@extends('laraPages::admin')

@section('title')
{{ config('larapages.media.nicename', 'Media') }}
@endsection

@section('content')
<div class="listview media noselect{{ $mini?' mini':'' }}" data-model="<?=$media->controllerUrl?>" data-expanded="<?=isset($media->options['expanded']) && $media->options['expanded']>0?$media->options['expanded']:''?>">
    <div class="header">
        <span class="toggle"></span>
        <h2>Folders</h2>
    </div>
    {!! $data !!}
</div>
<div class="editview hidden{{ $mini?' mini':'' }}">
    <div class="loaderbg"></div>
    <div class="loader"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
    <form id="upload" method="post" action="" enctype="multipart/form-data">
        <div class="header">
            <h2>Folder: <span></span></h2>
            <button class="close">Ok</button>
            <button class="upload">Upload (Max <?=$media->maxUploadSize()?> MB)</button>
            <button class="newfolder">New Folder</button>
            <button class="cancel">Cancel</button>
        </div>
		<div id="drop">
			<input data-maxUploadSize="<?=$media->maxUploadSize()?>" id="uploadfile" type="file" name="upl" multiple>
    		<ul>
    		</ul>
		</div>
	</form>
    <ul class="media list">
    </ul>
</div>
@endsection

@section('scripts')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/blueimp-file-upload/9.5.7/jquery.iframe-transport.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/blueimp-file-upload/9.5.7/jquery.fileupload.min.js"></script>
    <script src="/vendor/larapages/js/admin.js"></script>
    <script>lp_adminpath="{{ config('larapages.adminpath') }}"</script>
    <script src="/vendor/larapages/js/media.js"></script>
@endsection

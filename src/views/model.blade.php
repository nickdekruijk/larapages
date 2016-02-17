@extends('laraPages::admin')

@section('content')
<div class="listview noselect" data-model="{{ $modelId }}" data-expanded="<?=isset($model->pagesAdmin['expanded']) && $model->pagesAdmin['expanded']>0?$model->pagesAdmin['expanded']:''?>">
    <div class="header">
        <span class="toggle"></span>
        <h2>{{ isset($model->pagesAdmin['nicename'])?$model->pagesAdmin['nicename']:ucfirst(str_plural($modelId)) }}</h2>
    </div>
    {!! $data !!}
</div>
<div class="editview hidden">
    <div class="loaderbg"></div>
    <div class="loader"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
    <form>
    <div class="header">
        <h2>id: <span></span></h2>
        <button type="submit" class="save">Save</button>
        <button class="copy">Save a Copy</button>
        <button class="new">New</button>
        <button class="delete">Delete</button>
        <button class="close">Close</button>
    </div>
    <table>
    @foreach($model->getFillable() as $field)
        <tr>
            <td><label for="field_{{ $field }}">{{ str_replace('_',' ',$field) }}</label></td>
            <?php 
                $maxlength='255';
                $type=explode(',',isset($model->pagesAdmin['type'][$field])?$model->pagesAdmin['type'][$field]:false,2);
                if (isset($type[1])) $option=$type[1]; else $option=false;
                $type=$type[0];
                if (is_numeric($type) && $type>0) {
                    $maxlength=$type; 
                    $type='string';
                }
            ?>
            <td>
            @if ($type=='date' || $type=='datetime')
            	<input id="field_{{ $field }}" class="{{ $type }} " name="{{ $field }}" type="date">
            @elseif ($type=='boolean')
            	<input id="field_{{ $field }}" class="{{ $type }} " name="{{ $field }}" value="1" type="checkbox">
            @elseif ($type=='radio')
            	@foreach (explode('|',$option) as $n=>$value)
            	<input id="field_{{ $field }}_{{ $n }}" class="{{ $type }} " name="{{ $field }}" value="{{ $value }}" type="radio">
                <label class="radio" for="field_{{ $field }}_{{$n}}">{{ str_replace('_',' ',ucfirst($value)) }}</label>
                @endforeach
            @elseif ($type=='text' || $type=='longtext' || $type=='mediumtext')
            	<textarea id="field_{{ $field }}" class="{{ isset($model->pagesAdmin['tinymce'][$field])?'tinymce ':''}}{{ $type }} " name="{{ $field }}" data-tinymce="{{ isset($model->pagesAdmin['tinymce'][$field])?$model->pagesAdmin['tinymce'][$field]:'' }}"></textarea>
            @elseif ($type=='password') 
            	<input id="field_{{ $field }}" class="{{ $type }} " name="{{ $field }}" type="password">
            @elseif ($type=='media') 
                <div class="media" data-max="{{ $option }}"><div></div><span class="add iconplus"></span></div>
            	<textarea id="field_{{ $field }}" class="{{ $type }} " name="{{ $field }}"></textarea>
            @else
            	<input placeholder="{{ $type!='string'?$type:'' }}" id="field_{{ $field }}" size="{{ $maxlength }}" maxlength="{{ $maxlength }}" class="{{ $type }} " name="{{ $field }}" type="text">
            @endif
            </td>
        @if (isset($model->pagesAdmin['validate'][$field]) && in_array('confirmed',explode('|',$model->pagesAdmin['validate'][$field])))
        </tr>
        <tr>
            <td><label for="field_{{ $field }}">Confirm {{ str_replace('_',' ',$field) }}</label></td>
            <td>
            	<input id="field_{{ $field }}_confirmation" class="{{ $type }} " name="{{ $field }}_confirmation" type="password">
            </td>
        </tr>
        @endif
        </tr>
    @endforeach
    </table>
    </form>
</div>
@endsection

@section('scripts')
    <link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
	<link href="//cdn.jsdelivr.net/jquery.ui.timepicker.addon/1.4.5/jquery-ui-timepicker-addon.min.css" rel="stylesheet">
    <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script src="//cdn.jsdelivr.net/jquery.ui.timepicker.addon/1.4.5/jquery-ui-timepicker-addon.min.js"></script>
    <script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
    <script src="/vendor/larapages/js/nestedSortable.min.js"></script>
    <script src="/vendor/larapages/js/admin.js"></script>
    <script>lp_adminpath="{{ config('larapages.adminpath') }}"</script>
    <script src="/vendor/larapages/js/model.js"></script>
    <script>
        tinymce.init({
    	    selector:'.tinymce',
    	    theme: 'modern',
    	    menubar: false,
    	    paste_as_text: true,
    	    content_css: '/vendor/larapages/css/tinymce.css',
            browser_spellcheck: true,
            convert_urls : false,
    	    spellchecker_rpc_url: 'spellchecker.php',
    	    //autoresize_max_height: $(window).height()-158,
    	    plugins: [
        	    "autoresize",
        	    //"shy",
                "advlist autolink link image lists hr anchor charmap", // print preview spellchecker pagebreak
                "searchreplace wordcount visualblocks code media visualchars", // fullscreen fullpage visualchars insertdatetime nonbreaking
                "table paste " // save textcolor contextmenu emoticons template directionality
            ],
            toolbar: "code visualblocks visualchars | undo redo | styleselect | bold italic | bullist numlist outdent indent | link anchor | image media | searchreplace | shy pagebreak charmap", // | alignleft aligncenter alignright alignjustify | forecolor backcolor emoticons insertfile underline
            style_formats: [
                {title: 'Intro', block: 'p', styles: {'font-size':'1.2em', 'margin-bottom':'30px', 'line-height':'1.5em'}},
                {title: 'H2', block: 'h2'},
                {title: 'H3', block: 'h3'},
                {title: 'H4', block: 'h4'},
                {title: 'H5', block: 'h5'},
                {title: 'H6', block: 'h6'},
            ],
    	});
	</script>
@endsection

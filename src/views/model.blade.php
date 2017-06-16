@extends('laraPages::admin')

@section('title')
{{ isset($model->pagesAdmin['nicename'])?$model->pagesAdmin['nicename']:ucfirst(str_plural($modelId)) }}
@endsection

@section('content')
<div class="listview noselect" data-model="{{ $modelId }}" data-expanded="<?=isset($model->pagesAdmin['expanded']) && $model->pagesAdmin['expanded']>0?$model->pagesAdmin['expanded']:''?>">
    <div class="header">
        <span class="toggle"></span>
        <span class="searchclose">&times;</span>
        <input placeholder="Search" id="search">
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
        @if (isset($model->pagesAdmin['preview']))
        <button class="preview" target="_blank" data-href="{{url($model->pagesAdmin['preview'])}}">Preview</button>
        @endif
        <button class="delete">Delete</button>
        <button class="close">Close</button>
    </div>
    <table>
    @foreach($model->getFillable() as $field)
        <tr>
            <td><label for="field_{{ $field }}">{{ isset($model->pagesAdmin['rename'][$field]) ? $model->pagesAdmin['rename'][$field] : ucfirst(str_replace('_', ' ', str_replace('_id', '', $field))) }}</label></td>
            <?php 
                $maxlength='255';
                $type=explode(',',isset($model->pagesAdmin['type'][$field])?$model->pagesAdmin['type'][$field]:false,2);
                if (isset($type[1])) $option=$type[1]; else $option=false;
                $type=$type[0];
                if (!$type and isset($model->getCasts()[$field]))
                    $type = $model->getCasts()[$field];
                if (is_numeric($type) && $type>0) {
                    $maxlength=$type; 
                    $type='string';
                }
            ?>
            <td>
            @if ($type=='date' || $type=='datetime')
            	<input id="field_{{ $field }}" class="{{ $type }} " name="{{ $field }}" type="text">
            @elseif ($type=='boolean')
            	<input id="field_{{ $field }}" class="{{ $type }} " name="{{ $field }}" value="1" type="checkbox">
            @elseif ($type=='radio')
            	@foreach (explode('|',$option) as $n=>$value)
            	<?php
                	@list($value, $label) = explode('=>', $value, 2);
                	if (!$label) $label = str_replace('_',' ',ucfirst($value));
                ?>
            	<input id="field_{{ $field }}_{{ $n }}" class="{{ $type }} " name="{{ $field }}" value="{{ $value }}" type="radio">
                <label class="radio" for="field_{{ $field }}_{{ $n }}">{{ $label }}</label>
                @endforeach
            @elseif ($type=='text' || $type=='longtext' || $type=='mediumtext')
            	<textarea id="field_{{ $field }}" class="{{ isset($model->pagesAdmin['tinymce'][$field])?'tinymce ':''}}{{ $type }} " name="{{ $field }}" data-tinymce="{{ isset($model->pagesAdmin['tinymce'][$field])?$model->pagesAdmin['tinymce'][$field]:'' }}"></textarea>
            @elseif ($type=='password') 
            	<input id="field_{{ $field }}" class="{{ $type }} " name="{{ $field }}" type="password">
            @elseif ($type=='join') 
                <select id="field_{{ $field }}" class="{{ $type }}" name="{{ $field }}">
                    <?php 
                        $option=explode(',', $option);
                        $scope=isset($option[2])?$option[2]:false;
                    ?>
                    <option value=""></option>
                    @foreach($scope?(new $option[0])->$scope()->get():(new $option[0])->orderBy($option[1])->get() as $opt)
                    <option value="{{ $opt['id'] }}">{{ $opt[$option[1]] }}</option>
                    @endforeach
                </select>
            @elseif ($type=='templates') 
                <select id="field_{{ $field }}" class="templates" name="{{ $field }}">
                    <option value=""></option>
                    @foreach($model->pagesAdmin['templates'] as $template=>$options)
                    <option value="{{ $template }}" data-hide="{{ empty($options['hide'])?'':$options['hide'] }}">{{ $options['name'] }}</option>
                    @endforeach
                </select>
            @elseif ($type=='media') 
                <div class="media" data-max="{{ $option }}"><div></div><span class="add iconplus"></span></div>
            	<textarea id="field_{{ $field }}" class="{{ $type }} {{ $option>1?'multiple':'single' }}" name="{{ $field }}"></textarea>
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
    @if (!empty($model->pagesAdmin['belongsToMany']))
        @foreach($model->pagesAdmin['belongsToMany'] as $field=>$options)
            @php (list($remoteModel, $method)=explode(',', $options))
            <tr>
                <td><label>{{ $field }}</label></td>
                <td>
                    @foreach((new $remoteModel)->get() as $row)
                        <input id="field_many_{{str_slug($field)}}_{{ $row->id }}" class="many" name="many_{{str_slug($field)}}[]" value="{{$row->id}}" type="checkbox">
                        <label for="field_many_{{str_slug($field)}}_{{ $row->id }}" class="many">{{$row[$row->pagesAdmin['index']]}}</label>
                    @endforeach
                </td>
            </tr>
        @endforeach
    @endif
    </table>
    </form>
</div>
@endsection

@section('scripts')
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.min.css">
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.css">
    <script src="//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-ui-timepicker-addon/1.6.3/jquery-ui-timepicker-addon.min.js"></script>
    <script src="//cdn.tinymce.com/4/tinymce.min.js"></script>
    <script src="{{asset('/vendor/larapages/js/nestedSortable.min.js')}}"></script>
    <script src="{{asset('/vendor/larapages/js/admin.js')}}"></script>
    <script>lp_adminpath="{{ url(config('larapages.adminpath')) }}"</script>
    <script>lp_mediafolder="{{ config('larapages.media.folder') }}"</script>
    <script src="{{asset('/vendor/larapages/js/model.js')}}"></script>
    <script>
        tinymce.init({
    	    selector:'.tinymce',
    	    theme: 'modern',
    	    menubar: false,
    	    branding: false,    	    
    	    paste_as_text: true,
    	    content_css: "{{ asset(isset($model->pagesAdmin['tinymce_css'])?$model->pagesAdmin['tinymce_css']:'/vendor/larapages/css/tinymce.css') }}",
            browser_spellcheck: true,
            convert_urls : false,
			file_browser_callback: function(input_id, input_value, type, win){
				lp_mediaTarget=input_id;
				lp_media=''; //input_value;
				lp_modalFrame(lp_adminpath+'/media/mini');
				return false;
			},
			spellchecker_rpc_url: 'spellchecker.php',
    	    //autoresize_max_height: $(window).height()-158,
    	    plugins: [
        	    "autoresize",
        	    //"shy",
                "advlist autolink link image lists hr anchor charmap", // print preview spellchecker pagebreak
                "searchreplace wordcount visualblocks code media visualchars", // fullscreen fullpage visualchars insertdatetime nonbreaking
                "table paste " // save textcolor contextmenu emoticons template directionality
            ],
            toolbar: "code visualblocks | undo redo | styleselect | bold italic | bullist numlist outdent indent | link anchor | image media | searchreplace | shy pagebreak charmap", // | alignleft aligncenter alignright alignjustify | forecolor backcolor emoticons insertfile underline visualchars
@if (isset($model->pagesAdmin['tinymce_formats']))
            style_formats: [
	         	{!! $model->pagesAdmin['tinymce_formats'] !!}  
	        ],
@endif            
    	});
	</script>
@endsection

@extends('laraPages::admin')

@section('content')
<div id="modelVue"><div class="container">
    <div class="listview noselect" data-model="{{ $modelId }}" data-expanded="<?=isset($model->pagesAdmin['expanded']) && $model->pagesAdmin['expanded']>0?$model->pagesAdmin['expanded']:''?>">
        <div class="header">
            <span class="toggle"></span>
            <h2>{{ isset($model->pagesAdmin['nicename'])?$model->pagesAdmin['nicename']:ucfirst(str_plural($modelId)) }}</h2>
        </div>
        {!! $data !!}
    </div>
    <div class="editview hidden2">
        <div class="loaderbg"></div>
        <div class="loader"><span></span><span></span><span></span><span></span><span></span><span></span><span></span><span></span></div>
        <form>
        <div class="header">
            <h2>id: @{{ id }}</h2>
            <button type="submit" class="save">Save</button>
            <button class="copy">Save a Copy</button>
            <button class="new">New</button>
            <button class="delete">Delete</button>
            <button class="close">Close</button>
        </div>
        <table>
        @foreach($model->getFillable() as $field)
            <tr>
                <td><label for="field_{{ $field }}">{{ isset($model->pagesAdmin['rename'][$field])?$model->pagesAdmin['rename'][$field]:ucfirst(str_replace('_',' ',$field)) }}</label></td>
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
        </table>
        </form>
    </div>
</div></div>
@endsection

@section('scripts')
    <script src="//cdnjs.cloudflare.com/ajax/libs/vue/1.0.21/vue.min.js"></script>
    <script>
        new Vue({
            el: 'body',
            data: {
                id:'test2',
            }
        });
    </script>
@endsection

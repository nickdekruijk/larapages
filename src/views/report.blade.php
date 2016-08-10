@extends('laraPages::admin')

@section('title')
{{ $report }}
@endsection

@section('content')

<article>
<h2>{{ $report }}</h2>
<table class="report">
    @foreach ($data as $rowId=>$row)
    @if ($rowId==0)
    <tr>
        @foreach ($row as $field=>$value)
        <th>{{ $field }}</th>
        @endforeach
    </tr>
    @endif
    <tr>
        @foreach ($row as $field=>$value)
        <td>{{ $value }}</td>
        @endforeach
    </tr>
    @endforeach
</table>
</article>

@endsection

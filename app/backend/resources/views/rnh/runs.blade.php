@extends('rnh.layout')

@section('content')
<h1>Release runs</h1>

<div class="card">
<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Status</th>
        <th>Started</th>
        <th>Finished</th>
        <th>Output</th>
        <th>Summary</th>
    </tr>
    @foreach($runs as $run)
        <tr>
            <td>{{ $run->id }}</td>
            <td>{{ $run->name }}</td>
            <td>{{ $run->status }}</td>
            <td>{{ $run->started_at }}</td>
            <td>{{ $run->finished_at }}</td>
            <td>{{ $run->output_path }}</td>
            <td><pre style="white-space:pre-wrap;">{{ json_encode($run->summary, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre></td>
        </tr>
    @endforeach
</table>
</div>

<div style="margin-top:12px;">{{ $runs->links() }}</div>
@endsection

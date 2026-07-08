@extends('rnh.layout')

@section('content')
<h1>Dashboard</h1>

<div class="grid cols-4">
    <div class="card"><div class="metric">{{ $counts['services'] }}</div><div class="muted">services</div></div>
    <div class="card"><div class="metric">{{ $counts['templates'] }}</div><div class="muted">templates</div></div>
    <div class="card"><div class="metric">{{ $counts['releases'] }}</div><div class="muted">releases</div></div>
    <div class="card"><div class="metric">{{ $counts['runs'] }}</div><div class="muted">runs</div></div>
</div>

<div class="grid" style="margin-top:12px;">
    <div class="card">
        <h2>Imported counters</h2>
        <table>
            <tr><th>Entity</th><th>Count</th></tr>
            <tr><td>Commits</td><td>{{ $counts['commits'] }}</td></tr>
            <tr><td>Changed files</td><td>{{ $counts['changedFiles'] }}</td></tr>
            <tr><td>AI rules</td><td>{{ $counts['aiRules'] }}</td></tr>
        </table>
    </div>

    <div class="card">
        <h2>Problem services</h2>
        <table>
            <tr><th>Service</th><th>Project</th><th>Status</th></tr>
            @foreach($problemServices as $service)
                <tr>
                    <td>{{ $service->name }}</td>
                    <td>{{ $service->project }}</td>
                    <td><span class="badge yellow">{{ $service->validation_status }}</span></td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="card">
        <h2>Latest imported runs</h2>
        <table>
            <tr><th>ID</th><th>Name</th><th>Status</th><th>Output</th></tr>
            @foreach($latestRuns as $run)
                <tr>
                    <td>{{ $run->id }}</td>
                    <td>{{ $run->name }}</td>
                    <td>{{ $run->status }}</td>
                    <td>{{ $run->output_path }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection

@extends('rnh.layout')

@section('content')
<h1>Конфігурація</h1>

<div class="grid">
    <div class="card">
        <h2>Container paths</h2>
        <table>
            @foreach($paths as $key => $value)
                <tr><td>{{ $key }}</td><td><code>{{ $value }}</code></td></tr>
            @endforeach
        </table>
    </div>

    <div class="card">
        <h2>Git</h2>
        <table>
            <tr><td>OK</td><td>{{ $gitVersion['ok'] ? 'yes' : 'no' }}</td></tr>
            <tr><td>stdout</td><td><pre>{{ $gitVersion['stdout'] }}</pre></td></tr>
            <tr><td>stderr</td><td><pre>{{ $gitVersion['stderr'] }}</pre></td></tr>
        </table>
    </div>

    <div class="card">
        <h2>Imported settings</h2>
        <table>
            <tr><th>Key</th><th>Value</th></tr>
            @foreach($settings as $setting)
                <tr>
                    <td>{{ $setting->key }}</td>
                    <td><pre>{{ json_encode($setting->value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre></td>
                </tr>
            @endforeach
        </table>
    </div>
</div>
@endsection

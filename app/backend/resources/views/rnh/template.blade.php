@extends('rnh.layout')

@section('content')
<h1>Шаблон релізу: {{ $template->name }}</h1>

<div class="card">
    <h2>1. Основне</h2>
    <table>
        <tr><th>Поле</th><th>Значення</th></tr>
        <tr><td>Project</td><td>{{ $template->project }}</td></tr>
        <tr><td>Description</td><td>{{ $template->description }}</td></tr>
        <tr><td>Default release name</td><td>{{ $template->default_release_name }}</td></tr>
        <tr><td>Default target branch</td><td>{{ $template->default_target_branch }}</td></tr>
        <tr><td>Base mode</td><td>{{ $template->last_comparison_base_mode }}</td></tr>
        <tr><td>Diverged diff mode</td><td>{{ $template->last_diverged_history_diff_mode }}</td></tr>
    </table>
</div>

<div class="card" style="margin-top:12px;">
    <h2>2. Сервіси</h2>
    <table>
        <tr>
            <th>✓</th>
            <th>Service</th>
            <th>Status</th>
            <th>Base ref</th>
            <th>Base SHA</th>
            <th>Target refs</th>
            <th>Ask</th>
            <th>Version</th>
            <th>Git URL</th>
            <th>Note</th>
        </tr>
        @foreach($services as $item)
            <tr>
                <td>{{ $item->included ? '✓' : '' }}</td>
                <td>{{ $item->service_name }}</td>
                <td><span class="badge {{ $item->validation_status === 'Valid' ? 'green' : 'yellow' }}">{{ $item->validation_status }}</span></td>
                <td>{{ $item->baseline_ref }}</td>
                <td style="font-family:Consolas, monospace;">{{ $item->baseline_sha }}</td>
                <td>{{ $item->target_branch }}</td>
                <td>{{ $item->ask_if_changed ? 'yes' : 'no' }}</td>
                <td>{{ $item->baseline_version }}</td>
                <td style="max-width:320px; word-break:break-all;">{{ $item->git_url }}</td>
                <td>{{ $item->notes }}</td>
            </tr>
        @endforeach
    </table>
</div>
@endsection

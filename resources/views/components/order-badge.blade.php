@props(['status'])
<span class="status-badge status-{{ $status->value }}"><span class="status-dot" aria-hidden="true"></span>{{ $status->label() }}</span>

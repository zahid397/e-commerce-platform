@props(['action', 'name', 'message' => null])
<form method="post" action="{{ $action }}" data-confirm="{{ $message ?? 'Delete “' . $name . '”? This cannot be undone.' }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="icon-button danger-button" aria-label="Delete {{ $name }}" title="Delete {{ $name }}"><x-icon name="trash" :size="17" /></button>
</form>

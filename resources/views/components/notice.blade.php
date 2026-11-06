@if(session('success'))
    <div class="notice notice-success" role="status"><x-icon name="check" /><span>{{ session('success') }}</span></div>
@endif
@if($errors->any())
    <div class="notice notice-error" role="alert" tabindex="-1" data-error-summary>
        <x-icon name="alert" />
        <div>
            <strong>{{ $errors->count() === 1 ? 'Please review the following' : 'Please correct these items' }}</strong>
            <ul>
                @foreach($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

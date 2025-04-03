@if ($errors->has($name))
    <div>
        @foreach ($errors->get($name) as $error)
            <small class="help-block text-red mb-0 mt-1">{{ $error }}</small>
        @endforeach
    </div>
@endif

<div class="container mt-3">
    @foreach (['success', 'warning', 'error'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show" role="alert">
                {{ session($type) }}
                <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
    @endforeach

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Please check the following:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

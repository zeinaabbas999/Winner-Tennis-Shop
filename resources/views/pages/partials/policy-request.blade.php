<aside class="customer-form-panel policy-request" aria-labelledby="{{ $type }}-request-title">
    <p class="eyebrow">{{ ucfirst($type) }} request</p>
    <h3 id="{{ $type }}-request-title">Request a {{ $type }}</h3>
    @guest
        <p>Log in to select an eligible delivered order item. Guest customers can use the secure order link from their confirmation email for order support.</p>
        <a class="btn btn-dark" href="{{ route('login') }}">Log in</a>
    @else
        @if($items->isEmpty())
            <p>No eligible delivered items are currently available for a new {{ $type }} request.</p>
        @else
            <form class="customer-form" method="POST" action="{{ route('policy-requests.store', $type) }}">
                @csrf
                <div>
                    <label class="form-label" for="{{ $type }}-order-item">Eligible order item</label>
                    <select class="form-select" id="{{ $type }}-order-item" name="order_item_id" required>
                        <option value="">Choose an item</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}">{{ $item->product_name }} · {{ $item->order->order_number }}</option>
                        @endforeach
                    </select>
                    @error('order_item_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label" for="{{ $type }}-reason">Reason</label>
                    <textarea class="form-control" id="{{ $type }}-reason" name="reason" rows="4" maxlength="1500" required></textarea>
                    @error('reason')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
                <button class="btn btn-dark" type="submit">Submit request</button>
            </form>
        @endif
    @endguest
</aside>

@props(['quote', 'checkout' => false])
<aside class="panel cart-summary">
    <header class="panel-heading"><div><h2>Order summary</h2><p>Prices in Bangladesh taka.</p></div><x-icon name="orders" :size="21" /></header>
    <div class="panel-body">
        @if($checkout)
            <ul class="order-lines">
                @foreach($quote['lines'] as $line)
                    <li><div><strong>{{ $line['product']->name }}</strong><small>{{ $line['quantity'] }} × {{ \App\Support\Money::display($line['product']->new_price) }}</small></div><strong class="nowrap">{{ \App\Support\Money::display($line['subtotal']) }}</strong></li>
                @endforeach
            </ul>
        @endif
        <div class="summary-total"><span>Total</span><strong>{{ \App\Support\Money::display($quote['total']) }}</strong></div>
        <p class="summary-note"><x-icon name="shield" :size="18" /> Demo order. No online payment is collected.</p>
        @if(! $checkout && $quote['lines'])
            <a class="button button-primary button-wide" href="{{ route('checkout.show') }}">Continue to checkout <x-icon name="arrow-right" :size="17" /></a>
        @endif
    </div>
</aside>

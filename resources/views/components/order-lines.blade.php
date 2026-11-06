@props(['order'])
<ul class="order-lines">
    @foreach($order->items as $item)
        <li>
            <div><strong>{{ $item->product_name }}</strong><small>{{ $item->quantity }} × {{ \App\Support\Money::display($item->unit_price) }}</small></div>
            <strong class="nowrap">{{ \App\Support\Money::display(\App\Support\Money::decimal(\App\Support\Money::minor($item->unit_price) * $item->quantity)) }}</strong>
        </li>
    @endforeach
</ul>
<div class="summary-total"><span>Total</span><strong>{{ \App\Support\Money::display($order->total_amount) }}</strong></div>

@extends('layouts.admin')
@section('title', 'Order details')
@section('content')
<x-page-heading title="Order details" :description="$order->reference" eyebrow="ORDER WORKSPACE">
    <a class="button button-secondary" href="{{ route('admin.orders.index') }}"><x-icon name="arrow-left" :size="17" /> Orders</a>
</x-page-heading>
<div class="dashboard-grid">
    <section class="panel">
        <header class="panel-heading"><div><h2>Order items</h2><p>Placed {{ $order->created_at->timezone('Asia/Dhaka')->format('d M Y, H:i') }} Dhaka time</p></div><x-order-badge :status="$order->status" /></header>
        <div class="panel-body"><x-order-lines :order="$order" /></div>
    </section>
    <div class="dashboard-side">
        <section class="panel">
            <header class="panel-heading"><h2>Delivery details</h2><x-icon name="user" :size="20" /></header>
            <div class="panel-body"><dl class="detail-facts"><div><dt>Name</dt><dd>{{ $order->name }}</dd></div><div><dt>Email</dt><dd>{{ $order->email }}</dd></div><div><dt>Phone</dt><dd>{{ $order->phone }}</dd></div><div><dt>Address</dt><dd class="long-text">{{ $order->address }}</dd></div></dl></div>
        </section>
        <section class="panel">
            <header class="panel-heading"><div><h2>Order status</h2><p>No payment is collected in this demo.</p></div></header>
            <div class="panel-body">
                @if($order->status->next())
                    <form method="post" action="{{ route('admin.orders.update', $order) }}" class="form-stack" data-busy>
                        @csrf
                        @method('PATCH')
                        <x-select-field name="status" label="Change status" :required="true">
                            <option value="">Select next status</option>
                            @foreach($order->status->next() as $next)<option value="{{ $next->value }}">{{ $next->label() }}</option>@endforeach
                        </x-select-field>
                        <button class="button button-primary" type="submit" data-busy-label="Updating…">Update status</button>
                    </form>
                @else
                    <p class="muted">This order is {{ strtolower($order->status->label()) }}. No further status changes are available.</p>
                @endif
            </div>
        </section>
    </div>
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Orders')
@section('content')
<x-page-heading title="Orders" description="Review placed orders and update their status." eyebrow="ORDER WORKSPACE" />
<form method="get" action="{{ route('admin.orders.index') }}" class="panel filter-bar">
    <div class="filter-search"><label class="field-label" for="order-search">Search orders</label><input class="input" name="q" id="order-search" value="{{ $q }}" maxlength="100" placeholder="Reference or customer name"></div>
    <div><label class="field-label" for="order-status">Status</label><select class="input select" name="status" id="order-status"><option value="">All statuses</option>
        @foreach(\App\Support\OrderStatus::cases() as $item)<option value="{{ $item->value }}" @selected($item === $status)>{{ $item->label() }}</option>@endforeach
    </select></div>
    <div class="filter-actions"><button class="button button-primary" type="submit">Apply</button><a class="button button-secondary" href="{{ route('admin.orders.index') }}">Clear</a></div>
</form>
<section class="panel">
    <header class="panel-heading"><div><h2>Order list</h2><p>{{ $orders->total() }} {{ \Illuminate\Support\Str::plural('order', $orders->total()) }}</p></div></header>
    @if($orders->isEmpty())
        <x-empty-state icon="orders" title="No orders found" description="New storefront orders will appear here." />
    @else
        <div class="table-wrap">
            <table class="data-table" role="table">
                <caption class="sr-only">Orders</caption>
                <thead role="rowgroup"><tr role="row"><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
                <tbody role="rowgroup">
                @foreach($orders as $order)
                    <tr role="row">
                        <td class="table-main" role="cell" data-label="Order"><a class="table-label" href="{{ route('admin.orders.show', $order) }}"><strong class="order-reference">{{ $order->reference }}</strong><small>{{ $order->created_at->timezone('Asia/Dhaka')->format('d M Y, H:i') }} · {{ $order->items_count }} {{ \Illuminate\Support\Str::plural('line', $order->items_count) }}</small></a></td>
                        <td role="cell" data-label="Customer">{{ $order->name }}</td>
                        <td role="cell" data-label="Total" class="nowrap">{{ \App\Support\Money::display($order->total_amount) }}</td>
                        <td role="cell" data-label="Status"><x-order-badge :status="$order->status" /></td>
                        <td role="cell" data-label="Actions"><a class="button button-secondary button-sm" href="{{ route('admin.orders.show', $order) }}" aria-label="View order {{ $order->reference }}">View <x-icon name="arrow-right" :size="15" /></a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <x-pagination :paginator="$orders" />
    @endif
</section>
@endsection

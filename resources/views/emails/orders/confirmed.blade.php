<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Order {{ $order->order_number }}</title>
</head>
<body style="margin:0;background:#f3f0e8;color:#171b18;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3f0e8;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:680px;background:#fffdf8;">
                    <tr>
                        <td style="padding:28px 32px;background:#153f2e;color:#ffffff;">
                            <div style="font-size:26px;font-weight:800;letter-spacing:1px;">WINNER.</div>
                            <div style="margin-top:8px;color:#d7ff35;font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;">Order received</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 32px 20px;">
                            <h1 style="margin:0 0 14px;font-size:28px;line-height:1.2;">Thank you, {{ $order->customer_name }}</h1>
                            <p style="margin:0;color:#59615c;line-height:1.6;">Your order has been created successfully and is currently <strong>{{ ucfirst($order->status) }}</strong>. Payment has not been marked as completed.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#ecefe8;">
                                <tr>
                                    <td style="padding:14px 16px;"><strong>Order</strong><br><span style="color:#59615c;">{{ $order->order_number }}</span></td>
                                    <td style="padding:14px 16px;text-align:right;"><strong>Date</strong><br><span style="color:#59615c;">{{ $order->created_at->format('d M Y') }}</span></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 12px;">
                            <h2 style="margin:0;font-size:18px;">Order details</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                                @foreach($order->items as $item)
                                    <tr>
                                        <td style="padding:14px 0;border-bottom:1px solid #d8d5cc;">
                                            <strong>{{ $item->product_name }}</strong><br>
                                            @if($item->variant_name)<span style="color:#59615c;font-size:13px;">{{ $item->variant_name }}</span><br>@endif
                                            <span style="color:#59615c;font-size:13px;">{{ $item->quantity }} × {{ number_format((float) $item->price, 2) }} EGP</span>
                                        </td>
                                        <td style="padding:14px 0;border-bottom:1px solid #d8d5cc;text-align:right;font-weight:700;">{{ number_format((float) $item->subtotal, 2) }} EGP</td>
                                    </tr>
                                @endforeach
                                <tr><td style="padding:16px 0 6px;">Subtotal</td><td style="padding:16px 0 6px;text-align:right;">{{ number_format((float) $order->subtotal, 2) }} EGP</td></tr>
                                @if((float)$order->coupon_discount > 0)<tr><td style="padding:6px 0;">Coupon {{ $order->coupon_code }}</td><td style="padding:6px 0;text-align:right;">-{{ number_format((float) $order->coupon_discount, 2) }} EGP</td></tr>@endif
                                @if($order->first_order_offer_label)<tr><td style="padding:6px 0;">{{ $order->first_order_offer_label }}</td><td style="padding:6px 0;text-align:right;">@if((float)$order->first_order_discount > 0)-{{ number_format((float)$order->first_order_discount, 2) }} EGP@else Applied @endif</td></tr>@endif
                                @if($order->points_redeemed > 0)<tr><td style="padding:6px 0;">Rewards ({{ number_format($order->points_redeemed) }} points)</td><td style="padding:6px 0;text-align:right;">-{{ number_format((float) $order->points_discount, 2) }} EGP</td></tr>@endif
                                <tr><td style="padding:6px 0;">Shipping</td><td style="padding:6px 0;text-align:right;">{{ number_format((float) $order->shipping_fee, 2) }} EGP</td></tr>
                                <tr>
                                    <td style="padding:6px 0;font-size:18px;font-weight:800;">Total</td>
                                    <td style="padding:6px 0;text-align:right;font-size:18px;font-weight:800;">{{ number_format((float) $order->total, 2) }} EGP</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;background:#ecefe8;">
                            <h2 style="margin:0 0 10px;font-size:18px;">Delivery information</h2>
                            <p style="margin:0;color:#59615c;line-height:1.6;white-space:pre-line;">{{ $order->address }}</p>
                            <p style="margin:8px 0 0;color:#59615c;line-height:1.6;">{{ $order->phone }}</p>
                            <p style="margin:14px 0 0;color:#59615c;">Current status: <strong>{{ ucfirst($order->status) }}</strong></p>
                            @if($guestAccessToken)<p style="margin:18px 0 0"><a href="{{ route('guest.orders.show', [$order, $guestAccessToken]) }}" style="display:inline-block;background:#153f2e;color:#fff;padding:12px 18px;text-decoration:none">View your secure order page</a></p><p style="font-size:12px;color:#59615c">Keep this private link. It provides access to this guest order.</p>@endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;color:#6b726e;font-size:12px;line-height:1.5;">
                            This message confirms that WINNER received your order. Keep the order number for future reference.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

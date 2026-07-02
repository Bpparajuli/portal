@php
    $companyName = \App\Models\Setting::getValue('site_name', 'Idea Consultancy');
    $companyLogo = \App\Models\Setting::getValue('site.logo', '');
    $companyEmail = \App\Models\Setting::getValue('site_email', '');
    $companyPhone = \App\Models\Setting::getValue('site_phone', '');
    $companyAddress = \App\Models\Setting::getValue('address', '');
    $logoUrl = $companyLogo ? \App\Models\Setting::resolveImageUrl($companyLogo) : null;
    $amountInWords = function ($num) {
        $num = (int) floor($num);
        if ($num == 0) {
            return 'Zero';
        }
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
                 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];
        $sub100 = function ($n) use ($ones, $tens) {
            if ($n < 20) return $ones[$n];
            return trim($tens[floor($n / 10)] . ' ' . $ones[$n % 10]);
        };
        $words = '';
        if ($num >= 10000000) { $words .= $sub100(floor($num / 10000000)) . ' Crore '; $num %= 10000000; }
        if ($num >= 100000) { $words .= $sub100(floor($num / 100000)) . ' Lakh '; $num %= 100000; }
        if ($num >= 1000) { $words .= $sub100(floor($num / 1000)) . ' Thousand '; $num %= 1000; }
        if ($num >= 100) { $words .= $sub100(floor($num / 100)) . ' Hundred '; $num %= 100; }
        if ($num > 0) { $words .= $sub100($num); }
        return trim($words) . ' Only';
    };
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $revenue->id }} - {{ $student->full_name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            color: #1e293b;
            line-height: 1.6;
            padding: 40px 20px;
            background: #f1f5f9;
        }

        .receipt {
            max-width: 750px;
            margin: 0 auto;
            background: #fff;
            border-radius: 16px;
            padding: 40px 45px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .06);
        }

        .header {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            padding-bottom: 24px;
            border-bottom: 2px solid #f1f5f9;
            margin-bottom: 28px;
        }

        .header-logo {
            flex-shrink: 0;
        }

        .header-logo img {
            height: 100px;
            width: 100px;
            object-fit: contain;
            border-radius: 12px;
        }

        .header-logo-placeholder {
            height: 100px;
            width: 100px;
            background: linear-gradient(135deg, #1a0262, #3b1d8e);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
        }

        .header-info {
            flex: 1;
        }

        .header-info h1 {
            font-size: 20px;
            color: #0f172a;
            font-weight: 700;
            letter-spacing: .3px;
            margin-bottom: 2px;
        }

        .header-info .company {
            font-size: 25px;
            font-weight: 600;
            color: #1a0262;
            margin-bottom: 6px;
        }

        .header-info .contact {
            font-size: 12px;
            color: #64748b;
            line-height: 1.7;
        }

        .header-info .contact span {
            display: inline-block;
            margin-right: 18px;
        }

        .receipt-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
            padding: 14px 18px;
            background: #f8fafc;
            border-radius: 10px;
            font-size: 13px;
        }

        .receipt-meta .label {
            color: #94a3b8;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .receipt-meta .value {
            font-weight: 700;
            color: #0f172a;
        }

        .parties {
            display: flex;
            gap: 30px;
            margin-bottom: 28px;
        }

        .parties>div {
            flex: 1;
        }

        .parties h3 {
            font-size: 11px;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: .6px;
            margin-bottom: 6px;
        }

        .parties .name {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
        }

        .parties .detail {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table.items thead th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: .6px;
            padding: 10px 14px;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        table.items thead th:last-child {
            text-align: right;
        }

        table.items tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            color: #334155;
        }

        table.items tbody td:last-child {
            text-align: right;
            font-weight: 600;
        }

        table.items tbody tr:last-child td {
            border-bottom: none;
        }

        table.items tfoot td {
            padding: 12px 14px;
            font-size: 13px;
        }

        table.items tfoot .total-label {
            text-align: right;
            font-weight: 700;
            color: #0f172a;
            font-size: 15px;
            padding-right: 8px;
        }

        table.items tfoot .total-value {
            text-align: right;
            font-weight: 800;
            color: #1a0262;
            font-size: 20px;
            padding-left: 8px;
            border-top: 2px solid #1a0262;
        }

        .amount-words {
            text-align: right;
            font-size: 12px;
            color: #64748b;
            font-style: italic;
            margin-bottom: 20px;
            padding: 0 14px;
        }

        .balance-info {
            display: flex;
            justify-content: space-between;
            padding: 16px 18px;
            background: #f8fafc;
            border-radius: 10px;
            margin-top: 8px;
        }

        .balance-info .item {
            text-align: center;
            flex: 1;
        }

        .balance-info .item:not(:last-child) {
            border-right: 1px solid #e2e8f0;
        }

        .balance-info .label {
            font-size: 11px;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: .5px;
        }

        .balance-info .value {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 2px;
        }

        .footer-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-top: 32px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .footer-left {
            font-size: 11px;
            color: #94a3b8;
            line-height: 1.8;
        }

        .footer-left .company-name-foot {
            font-weight: 600;
            color: #64748b;
        }

        .footer-right {
            text-align: right;
            font-size: 11px;
            color: #94a3b8;
        }

        .footer-right .processed-label {
            text-transform: uppercase;
            letter-spacing: .4px;
            font-size: 10px;
        }

        .footer-right .processed-name {
            font-weight: 600;
            color: #64748b;
        }

        .actions {
            text-align: center;
            margin-top: 28px;
        }

        .actions button {
            display: inline-block;
            padding: 11px 28px;
            margin: 0 6px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: opacity .15s;
        }

        .actions button:hover {
            opacity: .85;
        }

        .btn-print {
            background: #1a0262;
            color: #fff;
        }

        .btn-download {
            background: #059669;
            color: #fff;
        }

        .btn-close {
            background: #e2e8f0;
            color: #475569;
        }

        @media print {
            body {
                padding: 0;
                background: #fff;
            }

            .receipt {
                box-shadow: none;
                border: none;
                padding: 30px;
                max-width: 100%;
                border-radius: 0;
            }

            .actions {
                display: none;
            }

            .no-print {
                display: none;
            }
        }

        @media (max-width: 600px) {
            .receipt {
                padding: 20px;
            }

            .header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .parties {
                flex-direction: column;
            }

            .balance-info {
                flex-direction: column;
                gap: 12px;
            }

            .balance-info .item:not(:last-child) {
                border-right: none;
                border-bottom: 1px solid #e2e8f0;
                padding-bottom: 12px;
            }

            .footer-row {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="receipt">
        {{-- HEADER --}}
        <div class="header">
            <div class="header-logo">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $companyName }}">
                @else
                    <div class="header-logo-placeholder">{{ substr($companyName, 0, 2) }}</div>
                @endif
            </div>
            <div class="header-info">
                <div class="company">{{ $companyName }}</div>
                <h1>Payment Receipt</h1>
                <div class="contact">
                    @if ($companyEmail)
                        <span>&#9993; {{ $companyEmail }}</span>
                    @endif
                    @if ($companyPhone)
                        <span>&#9742; {{ $companyPhone }}</span>
                    @endif
                    @if ($companyAddress)
                        <span>&#9906; {{ $companyAddress }}</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- META --}}
        <div class="receipt-meta">
            <div>
                <div class="label">Receipt #</div>
                <div class="value">{{ str_pad($revenue->id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>
            <div style="text-align:center;">
                <div class="label">Date</div>
                <div class="value">{{ $revenue->transaction_date->format('d M Y') }}</div>
            </div>
            <div style="text-align:right;">
                <div class="label">Payment Method</div>
                <div class="value">{{ ucfirst(str_replace('_', ' ', $revenue->method)) }}</div>
                @if ($revenue->reference_number)
                    <div style="font-size:11px;color:#94a3b8;margin-top:2px;">Ref: {{ $revenue->reference_number }}
                    </div>
                @endif
            </div>
        </div>

        {{-- PARTIES --}}
        <div class="parties">
            <div>
                <h3>Received From</h3>
                <div class="name">{{ $student->full_name }}</div>
                @if ($student->email)
                    <div class="detail">&#9993; {{ $student->email }}</div>
                @endif
                @if ($student->phone_number)
                    <div class="detail">&#9742; {{ $student->phone_number }}</div>
                @endif
            </div>
            <div style="text-align:right;">
                <h3>Payment Method</h3>
                <div class="name">{{ ucfirst(str_replace('_', ' ', $revenue->method)) }}</div>
                @if ($revenue->reference_number)
                    <div class="detail">Ref: {{ $revenue->reference_number }}</div>
                @endif
            </div>
        </div>

        {{-- ITEMS TABLE --}}
        <table class="items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="width:140px;">Amount (NPR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $revenue->description ?: 'Payment received' }}</td>
                    <td>NPR {{ number_format($revenue->amount, 2) }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td class="total-label">Total Paid</td>
                    <td class="total-value">NPR {{ number_format($revenue->amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="amount-words">
            Amount in words: {{ $amountInWords($revenue->amount) }}
        </div>

        {{-- BALANCE --}}
        <div class="balance-info">
            <div class="item">
                <div class="label">Total Receivable</div>
                <div class="value">NPR {{ number_format($student->expected_revenue ?? 0, 2) }}</div>
            </div>
            <div class="item">
                <div class="label">Total Received</div>
                <div class="value">NPR {{ number_format($student->received_revenue ?? 0, 2) }}</div>
            </div>
            <div class="item">
                <div class="label">Remaining Balance</div>
                <div class="value">NPR
                    {{ number_format(max(0, ($student->expected_revenue ?? 0) - ($student->received_revenue ?? 0)), 2) }}
                </div>
            </div>
        </div>

        {{-- FOOTER --}}
        <div class="footer-row">
            <div class="footer-left">
                <span class="company-name-foot">{{ $companyName }}</span><br>
                This is a computer-generated receipt. No signature required.
            </div>
            <div class="footer-right">
                <div class="processed-label">Processed By</div>
                <div class="processed-name">{{ $revenue->creator?->name ?? '—' }}</div>
                @if ($revenue->created_at)
                    <div>{{ $revenue->created_at->format('d M Y, h:i A') }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="actions no-print">
        <button class="btn-print" onclick="window.print()">&#128424; Print / Save PDF</button>
        <button class="btn-close" onclick="window.close()">&#10005; Close</button>
    </div>
</body>

</html>

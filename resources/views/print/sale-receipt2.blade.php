<!DOCTYPE html>
<html>
<head>
    <title>Sale Receipt</title>
    <style>
        body {
            font-family: monospace;
            width: 80mm;
            margin: 0 auto;
            text-align: center;
        }

        .logo {
            max-width: 120px;
            max-height:100px;
            margin: 0 auto 5px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        table {
            width: 100%;
            font-size: 12px;
        }

        td {
            padding: 2px 0;
        }

        .left { text-align: left; }
        .right { text-align: right; }

        .bold { font-weight: bold; }

        .small { font-size: 11px; }

        @media print {
            body {
                margin: auto;
                align-items:center;
            }
        }
    </style>
</head>
<body>
    {{-- LOGO --}}
    @if($organisation && $organisation['logo'])
        <img src="{{ asset('storage/' . $organisation['logo']) }}" class="logo">
    @endif

    {{-- ORGANIZATION INFO --}}
    <div class="bold">{{ $organisation['name'] ?? '' }}</div>
    <div class="small">{{ $organisation['street'] ?? '' }},{{ $organisation['city'] ?? '' }},{{ $organisation['country'] ?? '' }}</div>
    <div class="small">{{ $organisation['phone1'] ?? '' }}/{{ $organisation['phone2'] ?? '' }}</div>
    <div class="small">{{ $organisation['email'] ?? '' }}</div>

    <div class="divider"></div>

    {{-- SALE INFO --}}
    <div class="small">Receipt #: {{ str_pad($sale['id'],5,'0',STR_PAD_LEFT) }}</div>
    <div class="small">Date: {{ $sale['created_at']->format('d/m/Y H:i') }}</div>
    <div class="small">
        Served by:
        {{ $sale['servedBy'] }}
    </div>

    <div class="divider"></div>

    {{-- ITEMS --}}
    <table>
        @foreach($sale['items'] as $item)
            <tr>
                <td colspan="2" class="left">
                    {{ $item['name'] }}
                </td>
            </tr>
            <tr>
                <td class="left small">
                    {{ $item['quantity'] }} x {{ number_format($item['selling_price']) }}
                </td>
                <td class="right small">
                    {{ number_format($item['quantity'] * $item['selling_price']) }}
                </td>
            </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    {{-- TOTALS --}}
    <table>
        <tr>
            <td class="left bold">TOTAL</td>
            <td class="right bold">{{ number_format($sale['total_amount']) }}</td>
        </tr>

        @foreach($sale['payments'] as $payment)
            <tr>
                <td class="left small">
                    {{ $payment['method']['name'] }}
                </td>
                <td class="right small">
                    {{ number_format($payment['amount']) }}
                </td>
            </tr>
        @endforeach
        <tr>
            <td class="left bold">PENDING</td>
            <td class="right bold">{{number_format($sale['pending'])}}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- FOOTER --}}
    <div class="small">Thank you!</div>
    <div class="small">Powered by MTOMAWE ERP</div>

</body>
</html>

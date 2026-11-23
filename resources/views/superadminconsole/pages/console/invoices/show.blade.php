<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="robots" content="noindex, nofollow">

    <title>{{ $inv->invoice_no }}</title>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link href="{{ asset('assets/css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/components.css') }}" rel="stylesheet">

    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #fff;
            }

            .sheet {
                border: 0 !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body>

<div class="container my-4" style="max-width:820px">

    <div class="d-flex justify-content-between mb-3 no-print">

        <a
            href="{{ route('console.invoices.index') }}"
            class="btn btn-light border"
        >
            &larr; Back
        </a>

        <button
            type="button"
            class="btn btn-primary js-print"
        >
            Print / Save as PDF
        </button>

    </div>

    <div class="mp-card sheet p-4 p-md-5">

        <div class="d-flex justify-content-between flex-wrap gap-3 mb-4">

            <div>
                <div class="fw-bold fs-4">
                    {{ config('app.name', 'MovePro CRM') }}
                </div>

                <div class="caption">
                    Packers &amp; Movers Management System<br>
                </div>
            </div>

            <div class="text-md-end">

                <div class="text-uppercase caption fw-semibold">
                    Tax invoice
                </div>

                <div class="fs-5 fw-bold">
                    {{ $inv->invoice_no }}
                </div>

                <div class="caption">
                    Date: {{ $inv->created_at->format('d M Y') }}
                </div>

                <span class="status tone-{{ $inv->status === 'paid' ? 'success' : 'warning' }} mt-1">
                    {{ ucfirst($inv->status) }}
                </span>

            </div>

        </div>

        <div class="mb-4">

            <div class="caption">
                Billed to
            </div>

            <div class="fw-semibold">
                {{ $inv->company_name }}
            </div>

            <div class="caption">
                {{ $inv->company_address }}

                @if ($inv->company_gst)
                    <br>
                    GSTIN: {{ $inv->company_gst }}
                @endif
            </div>

        </div>

        <table class="table mp-table mb-4">

            <thead>
                <tr>
                    <th>Description</th>
                    <th>Period</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>

            <tbody>
                <tr>

                    <td>
                        {{ $inv->description }}
                    </td>

                    <td class="text-nowrap">
                        {{ $inv->period_start->format('d M Y') }}
                        –
                        {{ $inv->period_end->format('d M Y') }}
                    </td>

                    <td class="text-end">
                        ₹{{ number_format($inv->subtotal) }}
                    </td>

                </tr>
            </tbody>

        </table>

        <div class="ms-auto" style="max-width:300px">

            <div class="d-flex justify-content-between py-1">
                <span>Subtotal</span>
                <span>₹{{ number_format($inv->subtotal) }}</span>
            </div>

            <div class="d-flex justify-content-between py-1">
                <span>
                    GST ({{ $inv->gst_percent }}%)
                </span>

                <span>
                    ₹{{ number_format($inv->gst_amount) }}
                </span>
            </div>

            <div class="d-flex justify-content-between py-2 border-top fw-bold fs-5">

                <span>Total</span>

                <span>
                    ₹{{ number_format($inv->total) }}
                </span>

            </div>

            @if ($inv->status === 'paid')

                <div class="caption text-end">
                    Paid {{ $inv->paid_at->format('d M Y') }}
                    via {{ $inv->payment_mode }}
                </div>

            @endif

        </div>

        <hr class="my-4">

        <div class="caption">
            This is a computer-generated invoice.
            Payment is due on receipt.
        </div>

    </div>

</div>

<script>
document.querySelector('.js-print').addEventListener('click', function () {
    window.print();
});
</script>

</body>
</html>
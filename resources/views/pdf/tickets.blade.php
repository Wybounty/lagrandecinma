<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 12mm 10mm 14mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #111827;
            background: #ffffff;
        }

        .ticket-page {
            width: 100%;
            padding: 0;
        }

        .ticket-page--break {
            page-break-after: always;
        }

        .ticket-shell {
            width: 100%;
            border: 1px solid #f3d3d3;
            border-radius: 18px;
            overflow: hidden;
            background: #ffffff;
        }

        .ticket-topbar {
            height: 12px;
            background: #780000;
        }

        .ticket-inner {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .ticket-header {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .ticket-header td {
            padding: 18px 18px 14px 18px;
            vertical-align: top;
            border-bottom: 1px solid #ebebeb;
        }

        .brand {
            margin: 0;
            color: #780000;
            font-size: 26px;
            font-weight: 700;
            line-height: 1.1;
            letter-spacing: -0.03em;
        }

        .badge {
            display: inline-block;
            margin-top: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            background: #fef2f2;
            color: #b91c1c;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
        }

        .reservation-label {
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            margin-bottom: 4px;
        }

        .reservation-number {
            font-size: 18px;
            font-weight: 700;
            color: #111827;
        }

        .main-row {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .main-row td {
            vertical-align: top;
            padding: 18px;
        }

        .left-col {
            width: 62%;
            padding-right: 12px;
        }

        .right-col {
            width: 38%;
            padding-left: 12px;
            text-align: center;
            border-left: 1px dashed #d1d5db;
        }

        .movie-title {
            margin: 0;
            font-size: 28px;
            line-height: 1.15;
            font-weight: 800;
            color: #111827;
            word-wrap: break-word;
        }

        .meta-list {
            margin-top: 14px;
            font-size: 12px;
            line-height: 1.75;
            color: #4b5563;
        }

        .meta-list strong {
            color: #111827;
        }

        .info-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 10px 10px;
            margin-top: 18px;
            table-layout: fixed;
        }

        .info-table td {
            width: 50%;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            background: #fafafa;
            padding: 12px 14px;
            vertical-align: top;
        }

        .detail-label {
            display: block;
            margin-bottom: 5px;
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.14em;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            line-height: 1.35;
            word-wrap: break-word;
        }

        .mono {
            font-family: "DejaVu Sans Mono", monospace;
        }

        .qr-wrap {
            display: inline-block;
            width: 150px;
            max-width: 150px;
            padding: 10px;
            margin: 0 auto;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: #ffffff;
        }

        .qr-wrap img {
            display: block;
            width: 130px;
            height: 130px;
            max-width: 130px;
            max-height: 130px;
        }

        .qr-title {
            margin-top: 10px;
            font-size: 10px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.14em;
        }

        .ticket-number {
            margin-top: 10px;
            font-size: 13px;
            font-weight: 700;
            color: #780000;
            word-wrap: break-word;
        }

        .qr-note {
            margin-top: 8px;
            font-size: 11px;
            color: #6b7280;
            line-height: 1.5;
        }

        .footer-row {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-row td {
            padding: 0 18px 18px 18px;
            vertical-align: top;
        }

        .footer-box {
            border-top: 1px solid #ebebeb;
            padding-top: 14px;
        }

        .footer-box table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .footer-box td {
            padding: 0 0 8px 0;
            vertical-align: top;
            font-size: 12px;
            color: #4b5563;
        }

        .footer-box strong {
            color: #111827;
        }
    </style>
</head>
<body>
    @foreach ($tickets as $ticket)
        <section class="ticket-page @if (! $loop->last) ticket-page--break @endif">
            <div class="ticket-shell">
                <div class="ticket-topbar"></div>

                <table class="ticket-header">
                    <tr>
                        <td width="62%">
                            <p class="brand">{{ $ticket['cinema_name'] }}</p>
                            <span class="badge">Billet de cinema</span>
                        </td>
                        <td width="38%" style="text-align: right;">
                            <div class="reservation-label">Reservation</div>
                            <div class="reservation-number">#{{ $ticket['reservation_number'] }}</div>
                        </td>
                    </tr>
                </table>

                <table class="main-row">
                    <tr>
                        <td class="left-col">
                            <h1 class="movie-title">{{ $ticket['movie_title'] }}</h1>

                            <div class="meta-list">
                                <div><strong>Date:</strong> {{ $ticket['session_date'] }}</div>
                                <div><strong>Heure:</strong> {{ $ticket['session_time'] }}</div>
                                <div><strong>Salle:</strong> {{ $ticket['room_name'] }}</div>
                                <div><strong>Client:</strong> {{ $ticket['customer_name'] }}</div>
                                <div><strong>Email:</strong> {{ $ticket['customer_email'] }}</div>
                            </div>

                            <table class="info-table">
                                <tr>
                                    <td>
                                        <span class="detail-label">Numero du billet</span>
                                        <div class="detail-value mono">{{ $ticket['ticket_number'] }}</div>
                                    </td>
                                    <td>
                                        <span class="detail-label">Numero de reservation</span>
                                        <div class="detail-value mono">#{{ $ticket['reservation_number'] }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="detail-label">Cinema</span>
                                        <div class="detail-value">{{ $ticket['cinema_name'] }}</div>
                                    </td>
                                    <td>
                                        <span class="detail-label">Ticket</span>
                                        <div class="detail-value">#{{ $ticket['ticket_index'] }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>

                        <td class="right-col">
                            <div class="qr-title">QR code</div>
                            <div class="qr-wrap">
                                <img
                                    src="{{ $ticket['qr_data_uri'] }}"
                                    alt="QR code du billet {{ $ticket['ticket_number'] }}"
                                >
                            </div>
                            <div class="ticket-number">{{ $ticket['ticket_number'] }}</div>
                            <div class="qr-note">Present this QR code at the entrance.</div>
                        </td>
                    </tr>
                </table>

                <table class="footer-row">
                    <tr>
                        <td>
                            <div class="footer-box">
                                <table>
                                    <tr>
                                        <td width="50%"><strong>La Grande Cinema</strong></td>
                                        <td width="50%" style="text-align: right;">Billet {{ $ticket['ticket_index'] }} sur {{ count($tickets) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </section>
    @endforeach
</body>
</html>

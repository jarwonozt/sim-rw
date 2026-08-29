<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $letter->letter_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1a1a1a;
        }

        .kop {
            text-align: center;
            border-bottom: 3px solid #1a1a1a;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .kop h1 {
            font-size: 16px;
            margin: 0;
            text-transform: uppercase;
        }

        .kop p {
            margin: 2px 0;
            font-size: 11px;
        }

        .title {
            text-align: center;
            margin-bottom: 24px;
        }

        .title h2 {
            font-size: 14px;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
        }

        .title p {
            margin: 2px 0;
        }

        .body {
            text-align: justify;
            line-height: 1.6;
        }

        .signature {
            margin-top: 48px;
            width: 260px;
            margin-left: auto;
            text-align: center;
        }

        .signature .space {
            height: 64px;
        }
    </style>
</head>
<body>
    <div class="kop">
        <h1>Rukun Warga {{ $rw->nomor_rw ?? '-' }}</h1>
        <p>{{ $rw->village->name ?? '' }}</p>
        @if ($rw->address ?? false)
            <p>{{ $rw->address }}</p>
        @endif
    </div>

    <div class="title">
        <h2>{{ $letter->template->name }}</h2>
        <p>Nomor: {{ $letter->letter_number }}</p>
    </div>

    <div class="body">
        {!! $body !!}
    </div>

    <div class="signature">
        <p>{{ \Illuminate\Support\Carbon::parse($letter->issued_date)->translatedFormat('d F Y') }}</p>
        <p>Ketua RW {{ $rw->nomor_rw ?? '-' }}</p>
        <div class="space"></div>
        <p><strong>{{ $rw->ketuaRw->name ?? '(............................)' }}</strong></p>
    </div>
</body>
</html>

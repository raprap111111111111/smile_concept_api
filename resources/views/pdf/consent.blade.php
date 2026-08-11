<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Patient Clinical Packet - {{ $patient->name }}</title>
    <style>
        @page {
            margin: 8mm;
            size: A4 landscape;
        }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #000;
            line-height: 1.2;
            margin: 0;
        }

        /* Two-column spread — NO fixed height, let content flow naturally */
        .spread {
            display: table;
            width: 100%;
            table-layout: fixed;
            page-break-inside: avoid;
            page-break-after: always;
        }
        .spread:last-child { page-break-after: auto; }

        .col {
            display: table-cell;
            vertical-align: top;
            padding: 0 6px;
        }
        .col-left  { width: 50%; border-right: 1.5px solid #000; }
        .col-right { width: 50%; }

        /* Titles */
        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            border: 2px double #000;
            padding: 2px 12px;
            display: inline-block;
            letter-spacing: 1px;
        }
        .title-wrap { text-align: center; margin: 3px 0 5px; }

        /* Fields */
        .field-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 100px;
            padding: 0 3px;
        }
        h3.section-header {
            font-size: 9.5px;
            font-weight: bold;
            margin: 5px 0 3px;
            text-transform: uppercase;
        }

        /* Info tables */
        table.info-table { width: 100%; border-collapse: collapse; }
        table.info-table td { padding: 1px 3px; vertical-align: bottom; font-size: 8px; }

        /* Treatment table */
        table.tx-table { width: 100%; border-collapse: collapse; font-size: 7.5px; }
        table.tx-table th, table.tx-table td {
            border: 1px solid #000;
            padding: 1px 3px;
            height: 13px;
        }
        table.tx-table th { background: #eee; font-weight: bold; height: 15px; }

        /* Consent */
        .consent-body { text-align: justify; font-size: 8.5px; line-height: 1.35; }
        .consent-body p { margin: 4px 0; }

        /* Signatures */
        .signature-block {
            margin-top: 12px;
            display: table;
            width: 100%;
        }
        .signature-cell {
            display: table-cell;
            text-align: center;
            width: 33%;
            padding: 0 6px;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 35px;
            padding-top: 2px;
            font-size: 7.5px;
        }
        .signature-img { max-height: 38px; max-width: 140px; }

        /* Dental Chart — compact */
        .tooth-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 1px 0;
        }
        .tooth-grid td {
            border: 1px solid #000;
            width: 6.25%;
            height: 20px;
            text-align: center;
            font-size: 7.5px;
        }
        .tooth-number { border: none !important; font-size: 6.5px; height: 9px !important; }
        .tooth-symbol { font-size: 11px; }
        .teeth-label {
            text-align: center;
            font-size: 7px;
            font-weight: bold;
            margin: 1px 0 4px;
        }
        .status-label { font-size: 7px; font-weight: bold; margin: 2px 0; overflow: hidden; }
        .status-label .right { float: left; }
        .status-label .left  { float: right; }

        /* Legend */
        .legend {
            margin-top: 5px;
            font-size: 6.5px;
            border-top: 1px solid #999;
            padding-top: 3px;
        }
        .legend table { width: 100%; }
        .legend strong { display: block; margin-bottom: 2px; font-size: 7.5px; }

        .yn { font-family: DejaVu Sans; }

        .voided-banner {
            background: #ffe5e5;
            color: #a00;
            padding: 3px;
            text-align: center;
            font-weight: bold;
            margin: 3px 0;
            border: 1px solid #a00;
            font-size: 8.5px;
        }

        .footer-meta {
            position: fixed;
            bottom: 2mm;
            left: 8mm;
            right: 8mm;
            font-size: 6px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 2px;
            text-align: center;
        }
    </style>
</head>
<body>

@if($consent->isVoided())
    <div class="voided-banner">
        ⚠️ THIS CONSENT WAS VOIDED ON {{ $consent->voided_at->format('F j, Y') }}
        @if($consent->voided_reason) — {{ $consent->voided_reason }} @endif
    </div>
@endif

{{-- ═══════════ PAGE 1: TREATMENT + PATIENT INFO ═══════════ --}}
<div class="spread">

    {{-- LEFT: Treatment Record --}}
    <div class="col col-left">
        <table class="info-table" style="margin-bottom: 4px;">
            <tr>
                <td>
                    <strong>Name:</strong>
                    <span class="field-line" style="min-width:140px">{{ $patient->name }}</span>
                </td>
                <td>
                    <strong>Age:</strong>
                    <span class="field-line" style="min-width:30px">{{ $patient->age ?? '' }}</span>
                </td>
                <td>
                    <strong>Gender:</strong>
                    <span class="field-line" style="min-width:30px">{{ $patient->gender ?? '' }}</span>
                </td>
            </tr>
        </table>

        <div class="title-wrap">
            <div class="form-title">TREATMENT RECORD</div>
        </div>

        <table class="tx-table">
            <thead>
                <tr>
                    <th style="width:12%">Date</th>
                    <th style="width:10%">Tooth No./s</th>
                    <th style="width:38%">Procedure</th>
                    <th style="width:13%">Amt. Charged</th>
                    <th style="width:13%">Amt. Paid</th>
                    <th style="width:14%">Balance</th>
                </tr>
            </thead>
            <tbody>
                @php $rowCount = 0; @endphp
                @foreach($treatments as $tx)
                    <tr>
                        <td>{{ $tx->date }}</td>
                        <td>{{ $tx->tooth_no }}</td>
                        <td>{{ $tx->procedure }}</td>
                        <td style="text-align:right;">{{ number_format($tx->amount_charged, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($tx->amount_paid, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($tx->balance, 2) }}</td>
                    </tr>
                    @php $rowCount++; @endphp
                @endforeach

                {{-- 🔥 Reduced to 22 rows — fits page without overflow --}}
                @for($i = $rowCount; $i < 22; $i++)
                    <tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    {{-- RIGHT: Patient Info + Medical History --}}
    <div class="col col-right">
        <div style="text-align:center; margin-bottom:4px;">
            <strong style="font-size:10px;">PHILIPPINE DENTAL ASSOCIATION</strong><br>
            <div class="form-title" style="font-size:9.5px; margin-top:2px;">DENTAL CHART</div>
        </div>

        <h3 class="section-header">Patient Information Record</h3>

        <table class="info-table">
            <tr>
                <td colspan="3">
                    <strong>Name:</strong>
                    <span class="field-line" style="min-width:280px">{{ $patient->name }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Birthdate:</strong>
                    <span class="field-line" style="min-width:75px">{{ $patient->birthdate ?? '' }}</span>
                </td>
                <td>
                    <strong>Age:</strong>
                    <span class="field-line" style="min-width:30px">{{ $patient->age ?? '' }}</span>
                </td>
                <td>
                    <strong>Sex:</strong>
                    <span class="field-line" style="min-width:30px">{{ $patient->gender ?? '' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <strong>Address:</strong>
                    <span class="field-line" style="min-width:260px">{{ $patient->address ?? '' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>Occupation:</strong>
                    <span class="field-line" style="min-width:150px">{{ $patient->occupation ?? '' }}</span>
                </td>
                <td>
                    <strong>Mobile:</strong>
                    <span class="field-line" style="min-width:75px">{{ $patient->phone ?? '' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <strong>Email:</strong>
                    <span class="field-line" style="min-width:250px">{{ $patient->email ?? '' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <strong>Reason for consultation:</strong>
                    <span class="field-line" style="min-width:200px">{{ $patient->consultation_reason ?? '' }}</span>
                </td>
            </tr>
        </table>

        <h3 class="section-header">Medical History</h3>

        @if($medicalProfile)
            <table class="info-table">
                <tr>
                    <td><strong>Blood Type:</strong> <span class="field-line" style="min-width:50px">{{ $medicalProfile->blood_type ?? '' }}</span></td>
                    <td><strong>Allergies:</strong> <span class="field-line" style="min-width:140px">{{ $medicalProfile->allergies ?? 'None' }}</span></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>Medical History:</strong>
                        <div style="border:1px solid #000; padding:3px; min-height:32px; margin-top:2px;">
                            {{ $medicalProfile->medical_history ?? 'None declared.' }}
                        </div>
                    </td>
                </tr>
            </table>

            <table class="info-table" style="margin-top:4px;">
                <tr>
                    <td>1. Are you pregnant?</td>
                    <td style="width:75px;">
                        {!! ($medicalProfile->is_pregnant ?? false) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
                <tr>
                    <td>2. Bleeding disorders?</td>
                    <td>
                        {!! ($medicalProfile->has_bleeding_disorders ?? false) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
                <tr>
                    <td>3. Cardiac conditions?</td>
                    <td>
                        {!! ($medicalProfile->has_cardiac_conditions ?? false) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
                <tr>
                    <td>4. Requires epinephrine-free anesthesia?</td>
                    <td>
                        {!! ($medicalProfile->requires_epinephrine_free_anesthesia ?? false) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
            </table>

            <div style="margin-top: 5px;">
                <strong>Emergency Contact:</strong>
                <span class="field-line" style="min-width:140px">{{ $medicalProfile->emergency_contact_name ?? '' }}</span>
                <span class="field-line" style="min-width:100px">{{ $medicalProfile->emergency_contact_phone ?? '' }}</span>
            </div>
        @else
            <p style="font-style:italic; color:#666;">No medical profile on file.</p>
        @endif

        <div style="margin-top: 20px; text-align:right;">
            @if(str_starts_with($signature, 'data:image'))
                <img src="{{ $signature }}" class="signature-img" alt="Signature">
            @endif
            <div style="border-top:1px solid #000; width:160px; margin-left:auto; padding-top:2px; font-size:7.5px; text-align:center;">
                Patient Signature
            </div>
        </div>
    </div>
</div>

{{-- ═══════════ PAGE 2: CONSENT + DENTAL CHART ═══════════ --}}
<div class="spread">

    {{-- LEFT: Informed Consent --}}
    <div class="col col-left">
        <div class="title-wrap">
            <div class="form-title">INFORMED CONSENT</div>
        </div>

        <div style="margin-bottom: 6px; font-size: 8.5px;">
            <strong>Patient:</strong> {{ $patient->name }}
            &nbsp;&nbsp;
            <strong>Date:</strong> {{ $consent->signed_at->format('F j, Y') }}
        </div>

        <div class="consent-body">
            {!! nl2br(e($template->body)) !!}
        </div>

        <div class="signature-block">
            <div class="signature-cell">
                @if(str_starts_with($signature, 'data:image'))
                    <img src="{{ $signature }}" class="signature-img" alt="Patient Signature">
                @endif
                <div class="signature-line">Patient/Parent/Guardian</div>
            </div>
            <div class="signature-cell">
                <div class="signature-line">
                    {{ $consent->appointment?->doctor?->user?->name ?? $consent->signedByStaff?->name ?? '' }}
                    <br>Dentist / Signature
                </div>
            </div>
            <div class="signature-cell">
                <div style="margin-top: 35px; padding-top: 2px; border-top: 1px solid #000; font-size: 7.5px;">
                    {{ $consent->signed_at->format('F j, Y') }}
                </div>
                <div style="font-size:7.5px;">Date</div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Dental Chart --}}
    <div class="col col-right">
        <div class="title-wrap">
            <div class="form-title">DENTAL RECORD CHART</div>
        </div>

        <div style="font-weight:bold; margin: 3px 0; font-size:9px;">INTRAORAL EXAMINATION</div>

        <table class="info-table">
            <tr>
                <td colspan="3">
                    <strong>Name:</strong>
                    <span class="field-line" style="min-width:220px">{{ $patient->name }}</span>
                </td>
            </tr>
            <tr>
                <td><strong>Age:</strong> <span class="field-line" style="min-width:30px">{{ $patient->age ?? '' }}</span></td>
                <td><strong>Gender:</strong> <span class="field-line" style="min-width:30px">{{ $patient->gender ?? '' }}</span></td>
                <td><strong>Date:</strong> <span class="field-line" style="min-width:70px">{{ $consent->signed_at->format('M d, Y') }}</span></td>
            </tr>
        </table>

        <div class="status-label" style="margin-top:6px;">
            <span class="right">RIGHT</span>
            <span class="left">LEFT</span>
        </div>

        <table class="tooth-grid">
            <tr>
                @foreach([55,54,53,52,51,61,62,63,64,65] as $tooth)
                    <td class="tooth-number">{{ $tooth }}</td>
                @endforeach
            </tr>
            <tr>
                @foreach([55,54,53,52,51,61,62,63,64,65] as $tooth)
                    <td class="tooth-symbol">{!! $dentalChart[$tooth] ?? '&#9711;' !!}</td>
                @endforeach
            </tr>
        </table>
        <div class="teeth-label">TEMPORARY TEETH (Upper)</div>

        <table class="tooth-grid">
            <tr>
                @foreach([18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28] as $tooth)
                    <td class="tooth-number">{{ $tooth }}</td>
                @endforeach
            </tr>
            <tr>
                @foreach([18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28] as $tooth)
                    <td class="tooth-symbol">{!! $dentalChart[$tooth] ?? '&#9711;' !!}</td>
                @endforeach
            </tr>
        </table>
        <div class="teeth-label">PERMANENT TEETH (Upper)</div>

        <table class="tooth-grid">
            <tr>
                @foreach([48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38] as $tooth)
                    <td class="tooth-symbol">{!! $dentalChart[$tooth] ?? '&#9711;' !!}</td>
                @endforeach
            </tr>
            <tr>
                @foreach([48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38] as $tooth)
                    <td class="tooth-number">{{ $tooth }}</td>
                @endforeach
            </tr>
        </table>
        <div class="teeth-label">PERMANENT TEETH (Lower)</div>

        <table class="tooth-grid">
            <tr>
                @foreach([85,84,83,82,81,71,72,73,74,75] as $tooth)
                    <td class="tooth-symbol">{!! $dentalChart[$tooth] ?? '&#9711;' !!}</td>
                @endforeach
            </tr>
            <tr>
                @foreach([85,84,83,82,81,71,72,73,74,75] as $tooth)
                    <td class="tooth-number">{{ $tooth }}</td>
                @endforeach
            </tr>
        </table>
        <div class="teeth-label">TEMPORARY TEETH (Lower)</div>

        <div class="legend">
            <table>
                <tr>
                    <td style="vertical-align:top; width:34%;">
                        <strong>Condition</strong>
                        <div>D-Decayed &nbsp; M-Missing</div>
                        <div>F-Filled &nbsp; I-For Extraction</div>
                        <div>RF-Root Fragment &nbsp; Im-Impacted</div>
                        <div>MO-Missing (Other)</div>
                    </td>
                    <td style="vertical-align:top; width:33%;">
                        <strong>Restoration</strong>
                        <div>J-Jacket &nbsp; A-Amalgam</div>
                        <div>AB-Abutment &nbsp; P-Pontic</div>
                        <div>In-Inlay &nbsp; FX-Fixed Composite</div>
                        <div>Rm-Removable Denture</div>
                    </td>
                    <td style="vertical-align:top; width:33%;">
                        <strong>Surgery</strong>
                        <div>X-Extraction (Caries)</div>
                        <div>XO-Extraction (Other)</div>
                        <div>✓-Present &nbsp; Cm-Congenital</div>
                        <div>Sp-Supernumerary</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="footer-meta">
    Document ID: #{{ $consent->id }} |
    Signed: {{ $consent->signed_at->format('M d, Y g:i A') }} |
    IP: {{ $consent->ip_address ?? 'N/A' }} |
    {{ $clinic }}
</div>

</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Patient Clinical Packet - {{ $patient->name }}</title>
    <style>
        @page { margin: 12mm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #000;
            line-height: 1.25;
        }
        .page-break { page-break-after: always; }

        /* Two-column spread */
        .spread { display: table; width: 100%; table-layout: fixed; }
        .col { display: table-cell; vertical-align: top; padding: 0 8px; }
        .col-left { width: 50%; border-right: 1.5px solid #000; }
        .col-right { width: 50%; }

        /* Titles */
        .form-title {
            text-align: center;
            font-weight: bold;
            font-size: 13px;
            border: 2px double #000;
            padding: 3px 15px;
            display: inline-block;
            letter-spacing: 1px;
        }
        .title-wrap { text-align: center; margin: 4px 0 8px; }

        /* Fields */
        .field-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 100px;
            padding: 0 3px;
        }
        .field-row { margin: 4px 0; }
        h3.section-header {
            font-size: 11px;
            font-weight: bold;
            margin: 8px 0 4px;
            text-transform: uppercase;
        }

        /* Tables */
        table.info-table { width: 100%; border-collapse: collapse; }
        table.info-table td { padding: 2px 3px; vertical-align: bottom; font-size: 9px; }

        table.tx-table { width: 100%; border-collapse: collapse; font-size: 8.5px; }
        table.tx-table th, table.tx-table td {
            border: 1px solid #000;
            padding: 2px 3px;
        }
        table.tx-table th { background: #eee; font-weight: bold; }

        /* Consent body */
        .consent-body { text-align: justify; }
        .consent-body p { margin: 5px 0; }
        .consent-body strong { text-transform: uppercase; }

        /* Signature */
        .signature-block {
            margin-top: 20px;
            display: table;
            width: 100%;
        }
        .signature-cell {
            display: table-cell;
            text-align: center;
            width: 33%;
            padding: 0 8px;
        }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 50px;
            padding-top: 2px;
            font-size: 8px;
        }
        .signature-img {
            max-height: 50px;
            max-width: 180px;
        }

        /* Dental Chart */
        .tooth-grid {
            width: 100%;
            border-collapse: collapse;
            margin: 3px 0;
        }
        .tooth-grid td {
            border: 1px solid #000;
            width: 6.25%;
            height: 26px;
            text-align: center;
            font-size: 8px;
        }
        .tooth-number {
            border: none !important;
            font-size: 7px;
            height: 10px !important;
        }
        .tooth-symbol {
            font-size: 14px;
        }
        .status-label {
            font-size: 8px;
            font-weight: bold;
            margin: 3px 0;
        }
        .status-label .right { float: left; }
        .status-label .left  { float: right; }
        .teeth-label {
            text-align: center;
            font-size: 8px;
            font-weight: bold;
            margin: 3px 0;
        }

        /* Legend */
        .legend {
            margin-top: 8px;
            font-size: 8px;
            border-top: 1px solid #999;
            padding-top: 5px;
        }
        .legend .legend-group { margin-bottom: 4px; }
        .legend strong { display: block; margin-bottom: 2px; }

        /* Yes/No boxes */
        .yn { font-family: DejaVu Sans; }
        .voided-banner {
            background: #ffe5e5;
            color: #a00;
            padding: 4px;
            text-align: center;
            font-weight: bold;
            margin: 6px 0;
            border: 1px solid #a00;
            font-size: 10px;
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

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- PAGE 1: TREATMENT RECORD + PATIENT INFO / MEDICAL HISTORY      --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="spread">

    {{-- LEFT: Treatment Record --}}
    <div class="col col-left">
        <table class="info-table" style="margin-bottom: 6px;">
            <tr>
                <td>
                    <strong>Name:</strong>
                    <span class="field-line" style="min-width:180px">{{ $patient->name }}</span>
                </td>
                <td>
                    <strong>Age:</strong>
                    <span class="field-line" style="min-width:35px">{{ $patient->age ?? '' }}</span>
                </td>
                <td>
                    <strong>Gender:</strong>
                    <span class="field-line" style="min-width:35px">{{ $patient->gender ?? '' }}</span>
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
                    <th style="width:13%">Amount Charged</th>
                    <th style="width:13%">Amount Paid</th>
                    <th style="width:14%">Balance</th>
                </tr>
            </thead>
            <tbody>
                @php $rowCount = 0; @endphp
                @forelse($treatments as $tx)
                    <tr>
                        <td>{{ $tx->date }}</td>
                        <td>{{ $tx->tooth_no }}</td>
                        <td>{{ $tx->procedure }}</td>
                        <td style="text-align:right;">{{ number_format($tx->amount_charged, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($tx->amount_paid, 2) }}</td>
                        <td style="text-align:right;">{{ number_format($tx->balance, 2) }}</td>
                    </tr>
                    @php $rowCount++; @endphp
                @empty
                @endforelse

                {{-- Fill empty rows to look like paper form (total 30 rows) --}}
                @for($i = $rowCount; $i < 30; $i++)
                    <tr>
                        <td style="height:16px;">&nbsp;</td>
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

    {{-- RIGHT: Patient Information Record + Medical History --}}
    <div class="col col-right">
        <div style="text-align:center; margin-bottom:6px;">
            <strong style="font-size:12px;">PHILIPPINE DENTAL ASSOCIATION</strong><br>
            <div class="form-title" style="font-size:11px; margin-top:2px;">DENTAL CHART</div>
        </div>

        <h3 class="section-header">Patient Information Record</h3>

        <table class="info-table">
            <tr>
                <td colspan="3">
                    <strong>Name:</strong>
                    <span class="field-line" style="min-width:340px">{{ $patient->name }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Birthdate:</strong>
                    <span class="field-line" style="min-width:90px">{{ $patient->birthdate ?? '' }}</span>
                </td>
                <td>
                    <strong>Age:</strong>
                    <span class="field-line" style="min-width:35px">{{ $patient->age ?? '' }}</span>
                </td>
                <td>
                    <strong>Sex:</strong>
                    <span class="field-line" style="min-width:35px">{{ $patient->gender ?? '' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <strong>Home Address:</strong>
                    <span class="field-line" style="min-width:300px">{{ $patient->address ?? '' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <strong>Occupation:</strong>
                    <span class="field-line" style="min-width:180px">{{ $patient->occupation ?? '' }}</span>
                </td>
                <td>
                    <strong>Mobile:</strong>
                    <span class="field-line" style="min-width:90px">{{ $patient->phone ?? '' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <strong>Email:</strong>
                    <span class="field-line" style="min-width:290px">{{ $patient->email ?? '' }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <strong>Reason for consultation:</strong>
                    <span class="field-line" style="min-width:260px">{{ $patient->consultation_reason ?? '' }}</span>
                </td>
            </tr>
        </table>

        <h3 class="section-header">Medical History</h3>

        @if($medicalProfile)
            <table class="info-table">
                <tr>
                    <td><strong>Blood Type:</strong> <span class="field-line" style="min-width:60px">{{ $medicalProfile->blood_type ?? '' }}</span></td>
                    <td><strong>Allergies:</strong> <span class="field-line" style="min-width:180px">{{ $medicalProfile->allergies ?? 'None' }}</span></td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>Medical History:</strong>
                        <div style="border:1px solid #000; padding:4px; min-height:40px; margin-top:2px;">
                            {{ $medicalProfile->medical_history ?? 'None declared.' }}
                        </div>
                    </td>
                </tr>
            </table>

            <table class="info-table" style="margin-top:6px;">
                <tr>
                    <td>1. Are you pregnant?</td>
                    <td style="width:80px;">
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

            <div style="margin-top: 6px;">
                <strong>Emergency Contact:</strong>
                <span class="field-line" style="min-width:150px">{{ $medicalProfile->emergency_contact_name ?? '' }}</span>
                <span class="field-line" style="min-width:120px">{{ $medicalProfile->emergency_contact_phone ?? '' }}</span>
            </div>
        @else
            <p style="font-style:italic; color:#666;">No medical profile on file.</p>
        @endif

        <div style="margin-top: 15px; text-align:right;">
            @if(str_starts_with($signature, 'data:image'))
                <img src="{{ $signature }}" class="signature-img" alt="Signature">
            @endif
            <div style="border-top:1px solid #000; width:200px; margin-left:auto; padding-top:2px; font-size:8px; text-align:center;">
                Signature
            </div>
        </div>
    </div>
</div>

<div class="page-break"></div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- PAGE 2: INFORMED CONSENT + DENTAL RECORD CHART (TEETH)         --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="spread">

    {{-- LEFT: Informed Consent --}}
    <div class="col col-left">
        <div class="title-wrap">
            <div class="form-title">INFORMED CONSENT</div>
        </div>

        <div class="consent-body">
            {!! nl2br(e($template->body)) !!}
        </div>

        <div class="signature-block">
            <div class="signature-cell">
                @if(str_starts_with($signature, 'data:image'))
                    <img src="{{ $signature }}" class="signature-img" alt="Patient Signature">
                @endif
                <div class="signature-line">Patient/Parent/Guardian Signature</div>
            </div>
            <div class="signature-cell">
                <div class="signature-line">
                    {{ $consent->appointment?->doctor?->user?->name ?? '' }}
                    <br>Dentist / Signature
                </div>
            </div>
            <div class="signature-cell">
                <div style="margin-top: 50px; padding-top: 2px; border-top: 1px solid #000; font-size: 8px;">
                    {{ $consent->signed_at->format('F j, Y') }}
                </div>
                <div style="font-size:8px;">Date</div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Dental Record Chart (Teeth Diagram) --}}
    <div class="col col-right">
        <div class="title-wrap">
            <div class="form-title">DENTAL RECORD CHART</div>
        </div>

        <div style="font-weight:bold; margin: 6px 0; font-size:10px;">
            INTRAORAL EXAMINATION
        </div>

        <table class="info-table">
            <tr>
                <td colspan="3">
                    <strong>Name:</strong>
                    <span class="field-line" style="min-width:280px">{{ $patient->name }}</span>
                </td>
            </tr>
            <tr>
                <td>
                    <strong>Age:</strong>
                    <span class="field-line" style="min-width:35px">{{ $patient->age ?? '' }}</span>
                </td>
                <td>
                    <strong>Gender:</strong>
                    <span class="field-line" style="min-width:35px">{{ $patient->gender ?? '' }}</span>
                </td>
                <td>
                    <strong>Date:</strong>
                    <span class="field-line" style="min-width:80px">{{ $consent->signed_at->format('M d, Y') }}</span>
                </td>
            </tr>
        </table>

        {{-- Upper Temporary Teeth (55-65) --}}
        <div class="status-label" style="margin-top:10px;">
            <span class="right">STATUS</span>
            <span class="left">LEFT</span>
            <div style="clear:both;"></div>
            <span class="right">RIGHT</span>
            <div style="clear:both;"></div>
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

        <div class="teeth-label">TEMPORARY TEETH</div>

        {{-- Upper Permanent Teeth (18-28) --}}
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

        <div class="teeth-label">PERMANENT TEETH</div>

        {{-- Lower Permanent Teeth (48-38) --}}
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

        {{-- Lower Temporary Teeth (85-75) --}}
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

        <div class="teeth-label">TEMPORARY TEETH</div>

        <div class="status-label" style="margin-top:4px;">
            <span class="right">STATUS</span>
            <span class="left">LEFT</span>
            <div style="clear:both;"></div>
            <span class="right">RIGHT</span>
        </div>

        {{-- Legend --}}
        <div class="legend">
            <table style="width:100%; font-size:7.5px;">
                <tr>
                    <td style="vertical-align:top; width:35%;">
                        <strong>Legend: Condition</strong>
                        <div>D - Decayed (Caries)</div>
                        <div>M - Missing due to Caries</div>
                        <div>F - Filled</div>
                        <div>I - Caries for Extraction</div>
                        <div>RF - Root Fragment</div>
                        <div>MO - Missing due to Other</div>
                        <div>Im - Impacted Tooth</div>
                    </td>
                    <td style="vertical-align:top; width:35%;">
                        <strong>Restoration & Prosthetics</strong>
                        <div>J - Jacket Crown</div>
                        <div>A - Amalgam Filling</div>
                        <div>AB - Abutment</div>
                        <div>P - Pontic</div>
                        <div>In - Inlay</div>
                        <div>FX - Fixed Cure Composite</div>
                        <div>Rm - Removable Denture</div>
                    </td>
                    <td style="vertical-align:top; width:30%;">
                        <strong>Surgery</strong>
                        <div>X - Extraction (Caries)</div>
                        <div>XO - Extraction (Other)</div>
                        <div>✓ - Present Teeth</div>
                        <div>Cm - Congenitally Missing</div>
                        <div>Sp - Supernumerary</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

{{-- Footer meta --}}
<div style="position:fixed; bottom:5mm; left:12mm; right:12mm; font-size:7px; color:#666; border-top:1px solid #ccc; padding-top:2px; text-align:center;">
    Document ID: #{{ $consent->id }} |
    Signed: {{ $consent->signed_at->format('M d, Y g:i A') }} |
    IP: {{ $consent->ip_address ?? 'N/A' }} |
    {{ $clinic }}
</div>

</body>
</html>
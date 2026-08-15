<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Patient Clinical Packet - {{ $patient->name }}</title>
    <style>
        @page {
            margin: 6mm;
            size: 15in 10in;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #000;
            line-height: 1.25;
        }

        .spread {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .spread.page-break { page-break-before: always; }

        .col {
            display: table-cell;
            vertical-align: top;
            padding: 0 8px;
        }
        .col-left  { width: 50%; border-right: 1.5px solid #000; }
        .col-right { width: 50%; }

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

        .field-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 100px;
            padding: 0 3px;
        }
        h3.section-header {
            font-size: 9.5px;
            font-weight: bold;
            margin: 6px 0 3px;
            text-transform: uppercase;
            border-bottom: 1px solid #000;
        }

        table.info-table { width: 100%; border-collapse: collapse; }
        table.info-table td { padding: 1.5px 3px; vertical-align: bottom; font-size: 8px; }

        table.tx-table { width: 100%; border-collapse: collapse; font-size: 7.5px; }
        table.tx-table th, table.tx-table td {
            border: 1px solid #000;
            padding: 1px 3px;
            height: 13px;
        }
        table.tx-table th { background: #eee; font-weight: bold; height: 15px; }

        .consent-body { text-align: justify; font-size: 8px; line-height: 1.4; }
        .consent-body p { margin: 4px 0; text-indent: 0; }
        .consent-body .clause-title { font-weight: bold; text-transform: uppercase; }

        /* ✅ FIXED — initial box now displays the initial text properly */
        .initial-box {
            display: inline-block;
            border: 1px solid #000;
            min-width: 40px;
            min-height: 14px;
            padding: 1px 4px;
            margin-left: 4px;
            vertical-align: middle;
            text-align: center;
            font-weight: bold;
            letter-spacing: 1.5px;
            font-size: 9px;
            line-height: 1.3;
            background: #fff;
            color: #000;
        }
        .initial-label { float: right; font-size: 7.5px; font-style: italic; }
        .consent-closing { margin-top: 6px; font-size: 8px; text-align: justify; }

        .signature-block { margin-top: 12px; display: table; width: 100%; }
        .signature-cell { display: table-cell; text-align: center; width: 33%; padding: 0 8px; }
        .signature-line {
            border-top: 1px solid #000;
            margin-top: 28px;
            padding-top: 2px;
            font-size: 7.5px;
        }
        .signature-img { max-height: 30px; max-width: 130px; }

        /* ═════════ PDA DENTAL CHART — FLAT STYLE ═════════ */
        .chart-line {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .side-lbl {
            width: 50px;
            text-align: center;
            font-weight: bold;
            font-size: 7px;
            line-height: 1.1;
            padding: 2px;
            border: none;
        }
        .sbox-temp { border: 1px solid #000; height: 18px; width: calc((100% - 100px) / 10); padding: 0; }
        .sbox-perm { border: 1px solid #000; height: 18px; width: calc((100% - 100px) / 16); padding: 0; }
        .tnum-temp { text-align: center; font-size: 7px; height: 12px; border: none; width: calc((100% - 100px) / 10); padding: 0; }
        .tnum-perm { text-align: center; font-size: 7px; height: 12px; border: none; width: calc((100% - 100px) / 16); padding: 0; }
        .tsym-temp { text-align: center; font-size: 14px; height: 20px; border: none; line-height: 1; width: calc((100% - 100px) / 10); padding: 0; }
        .tsym-perm { text-align: center; font-size: 14px; height: 20px; border: none; line-height: 1; width: calc((100% - 100px) / 16); padding: 0; }

        .legend { margin-top: 6px; font-size: 6.5px; border-top: 1px solid #999; padding-top: 3px; }
        .legend table { width: 100%; }
        .legend td { vertical-align: top; padding: 0 3px; }
        .legend strong { display: block; margin-bottom: 2px; font-size: 7.5px; }

        .exam-extras { margin-top: 5px; font-size: 7px; }
        .exam-extras table { width: 100%; }
        .exam-extras td { vertical-align: top; padding: 1px 3px; }
        .exam-extras strong { font-size: 7.5px; display: block; margin-bottom: 2px; }
        .exam-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 50px;
            height: 8px;
        }

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
            left: 6mm;
            right: 6mm;
            font-size: 6px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 2px;
            text-align: center;
        }

        .conditions-grid { font-size: 6.8px; width: 100%; border-collapse: collapse; }
        .conditions-grid td { padding: 0 2px; vertical-align: top; }
        .conditions-grid .cbx { font-family: DejaVu Sans; }
        .yn-inline { font-size: 7px; white-space: nowrap; }
    </style>
</head>
<body>

@php
    // ═══════════════════════════════════════════════════════════════════
    // Extract form data helpers — makes blade cleaner below
    // ═══════════════════════════════════════════════════════════════════
    $formData    = $consent->form_data ?? [];
    $clauses     = $formData['clauses']  ?? [];

    // Helper closure to render initial for any clause key
    $ini = fn(string $key) => e($clauses[$key]['initial'] ?? '');
@endphp

@if($consent->isVoided())
    <div class="voided-banner">
        ⚠️ THIS CONSENT WAS VOIDED ON {{ $consent->voided_at->format('F j, Y') }}
        @if($consent->voided_reason) — {{ $consent->voided_reason }} @endif
    </div>
@endif

{{-- ═══════════ PAGE 1: FRONT (Treatment + Patient Info) ═══════════ --}}
<div class="spread">

    {{-- LEFT: Treatment Record --}}
    <div class="col col-left">
        <table class="info-table" style="margin-bottom: 4px;">
            <tr>
                <td><strong>Name:</strong> <span class="field-line" style="min-width:160px">{{ $patient->name }}</span></td>
                <td><strong>Age:</strong> <span class="field-line" style="min-width:30px">{{ $patient->age ?? '' }}</span></td>
                <td><strong>Gender:</strong> <span class="field-line" style="min-width:30px">{{ $patient->gender ?? '' }}</span></td>
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
                @for($i = $rowCount; $i < 32; $i++)
                    <tr>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                        <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    {{-- RIGHT: PDA Patient Info --}}
    <div class="col col-right">
        <table style="width:100%; margin-bottom:4px; border-collapse:collapse;">
            <tr>
                <td style="width:60px; vertical-align:middle;">
                    @if(!empty($pdaLogo))
                        <img src="{{ $pdaLogo }}" alt="PDA Logo" style="width:55px; height:55px;">
                    @endif
                </td>
                <td style="text-align:center; vertical-align:middle;">
                    <strong style="font-size:12px;">PHILIPPINE DENTAL ASSOCIATION</strong><br>
                    <div class="form-title" style="font-size:10px; margin-top:2px;">DENTAL CHART</div>
                </td>
                <td style="width:60px;">&nbsp;</td>
            </tr>
        </table>

        <h3 class="section-header">Patient Information Record</h3>

        <table class="info-table">
            <tr>
                <td colspan="6"><strong>Name:</strong> <span class="field-line" style="min-width:340px">{{ $patient->name }}</span></td>
            </tr>
            <tr>
                <td colspan="6" style="text-align:center; font-size:6.5px; font-style:italic;">
                    Last &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    First &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Middle
                </td>
            </tr>
            <tr>
                <td colspan="2"><strong>Birthdate:</strong> <span class="field-line" style="min-width:90px">{{ $patient->birthdate ?? '' }}</span></td>
                <td><strong>Age:</strong> <span class="field-line" style="min-width:35px">{{ $patient->age ?? '' }}</span></td>
                <td colspan="3"><strong>Sex M/F:</strong> <span class="field-line" style="min-width:40px">{{ $patient->gender ?? '' }}</span> &nbsp; <strong>Nickname:</strong> <span class="field-line" style="min-width:80px">{{ $patient->nickname ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="3"><strong>Religion:</strong> <span class="field-line" style="min-width:100px">{{ $patient->religion ?? '' }}</span></td>
                <td colspan="3"><strong>Nationality:</strong> <span class="field-line" style="min-width:100px">{{ $patient->nationality ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="4"><strong>Home Address:</strong> <span class="field-line" style="min-width:230px">{{ $patient->address ?? '' }}</span></td>
                <td colspan="2"><strong>Home No.:</strong> <span class="field-line" style="min-width:90px">{{ $patient->home_phone ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="4"><strong>Occupation:</strong> <span class="field-line" style="min-width:210px">{{ $patient->occupation ?? '' }}</span></td>
                <td colspan="2"><strong>Office No.:</strong> <span class="field-line" style="min-width:90px">{{ $patient->office_phone ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="4"><strong>Dental Insurance:</strong> <span class="field-line" style="min-width:200px">{{ $patient->dental_insurance ?? '' }}</span></td>
                <td colspan="2"><strong>Fax No.:</strong> <span class="field-line" style="min-width:90px">{{ $patient->fax_no ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="4"><strong>Effective Date:</strong> <span class="field-line" style="min-width:200px">{{ $patient->insurance_effective_date ?? '' }}</span></td>
                <td colspan="2"><strong>Cell/Mobile:</strong> <span class="field-line" style="min-width:90px">{{ $patient->phone ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="4"><strong>For Minors — Parent/Guardian:</strong> <span class="field-line" style="min-width:200px">{{ $patient->guardian_name ?? '' }}</span></td>
                <td colspan="2"><strong>Email:</strong> <span class="field-line" style="min-width:100px">{{ $patient->email ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="6"><strong>Guardian Occupation:</strong> <span class="field-line" style="min-width:200px">{{ $patient->guardian_occupation ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="6"><strong>Whom may we thank for referring you?</strong> <span class="field-line" style="min-width:260px">{{ $patient->referred_by ?? '' }}</span></td>
            </tr>
            <tr>
                <td colspan="6"><strong>Reason for dental consultation:</strong> <span class="field-line" style="min-width:250px">{{ $patient->consultation_reason ?? '' }}</span></td>
            </tr>
        </table>

        <h3 class="section-header">Dental History</h3>
        <table class="info-table">
            <tr>
                <td><strong>Previous Dentist: Dr.</strong> <span class="field-line" style="min-width:160px">{{ $patient->previous_dentist ?? '' }}</span></td>
                <td><strong>Last Dental Visit:</strong> <span class="field-line" style="min-width:100px">{{ $patient->last_dental_visit ?? '' }}</span></td>
            </tr>
        </table>

        <h3 class="section-header">Medical History</h3>

        @if($medicalProfile)
            <table class="info-table">
                <tr>
                    <td colspan="2"><strong>Physician: Dr.</strong> <span class="field-line" style="min-width:180px">{{ $medicalProfile->physician_name ?? '' }}</span></td>
                    <td><strong>Specialty:</strong> <span class="field-line" style="min-width:120px">{{ $medicalProfile->physician_specialty ?? '' }}</span></td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Office Address:</strong> <span class="field-line" style="min-width:200px">{{ $medicalProfile->physician_address ?? '' }}</span></td>
                    <td><strong>Office No.:</strong> <span class="field-line" style="min-width:100px">{{ $medicalProfile->physician_phone ?? '' }}</span></td>
                </tr>
            </table>

            <table class="info-table" style="margin-top:3px; font-size:7px;">
                <tr>
                    <td style="width:75%;">1. Are you in good health?</td>
                    <td class="yn-inline">
                        {!! ($medicalProfile->in_good_health ?? true) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
                <tr>
                    <td>2. Are you under medical treatment now?</td>
                    <td class="yn-inline">
                        {!! ($medicalProfile->under_medical_treatment ?? false) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
                <tr><td colspan="2" style="padding-left:14px;">If so, condition: <span class="field-line" style="min-width:220px">{{ $medicalProfile->treatment_condition ?? '' }}</span></td></tr>
                <tr>
                    <td>3. Serious illness or surgery?</td>
                    <td class="yn-inline">
                        {!! ($medicalProfile->had_serious_illness ?? false) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
                <tr><td colspan="2" style="padding-left:14px;">If so, what: <span class="field-line" style="min-width:240px">{{ $medicalProfile->illness_details ?? '' }}</span></td></tr>
                <tr>
                    <td>4. Ever been hospitalized?</td>
                    <td class="yn-inline">
                        {!! ($medicalProfile->was_hospitalized ?? false) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
                <tr><td colspan="2" style="padding-left:14px;">If so, when/why: <span class="field-line" style="min-width:250px">{{ $medicalProfile->hospitalization_details ?? '' }}</span></td></tr>
                <tr>
                    <td>5. Taking prescription/non-prescription medication?</td>
                    <td class="yn-inline">
                        {!! ($medicalProfile->takes_medications ?? false) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
                <tr><td colspan="2" style="padding-left:14px;">If so, specify: <span class="field-line" style="min-width:250px">{{ $medicalProfile->medications ?? '' }}</span></td></tr>
                <tr>
                    <td>6. Do you use tobacco products?</td>
                    <td class="yn-inline">
                        {!! ($medicalProfile->uses_tobacco ?? false) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
                <tr>
                    <td>7. Alcohol, cocaine or other dangerous drugs?</td>
                    <td class="yn-inline">
                        {!! ($medicalProfile->uses_alcohol_drugs ?? false) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
                <tr>
                    <td>8. Allergic to any of the following:</td>
                    <td class="yn-inline">
                        {!! ($medicalProfile->has_allergies ?? false) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="padding-left:14px; font-size:6.8px;">
                        <span class="yn">☐</span> Local Anesthetic &nbsp;
                        <span class="yn">☐</span> Penicillin &nbsp;
                        <span class="yn">☐</span> Sulfa &nbsp;
                        <span class="yn">☐</span> Aspirin &nbsp;
                        <span class="yn">☐</span> Latex &nbsp;
                        <span class="yn">☐</span> Other: <span class="field-line" style="min-width:80px">{{ $medicalProfile->other_allergies ?? '' }}</span>
                    </td>
                </tr>
                <tr>
                    <td>9. Bleeding Time: <span class="field-line" style="min-width:80px">{{ $medicalProfile->bleeding_time ?? '' }}</span></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td>10. Women only: pregnant / nursing / birth control?</td>
                    <td class="yn-inline">
                        {!! ($medicalProfile->is_pregnant ?? false) ? '<span class="yn">☑</span> Yes &nbsp; <span class="yn">☐</span> No' : '<span class="yn">☐</span> Yes &nbsp; <span class="yn">☑</span> No' !!}
                    </td>
                </tr>
                <tr>
                    <td>11. Blood Type: <span class="field-line" style="min-width:60px">{{ $medicalProfile->blood_type ?? '' }}</span>
                        &nbsp; 12. Blood Pressure: <span class="field-line" style="min-width:80px">{{ $medicalProfile->blood_pressure ?? '' }}</span></td>
                    <td>&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2"><strong>13. Do you have or have you had any of the following?</strong></td>
                </tr>
            </table>

            @php
                $conditions = $medicalProfile->conditions ?? [];
                $isChecked = fn($key) => in_array($key, (array) $conditions) ? '☑' : '☐';
            @endphp
            <table class="conditions-grid" style="margin-top:2px;">
                <tr>
                    <td>
                        <div><span class="cbx">{{ $isChecked('high_bp') }}</span> High Blood Pressure</div>
                        <div><span class="cbx">{{ $isChecked('low_bp') }}</span> Low Blood Pressure</div>
                        <div><span class="cbx">{{ $isChecked('epilepsy') }}</span> Epilepsy / Convulsions</div>
                        <div><span class="cbx">{{ $isChecked('aids_hiv') }}</span> AIDS or HIV Infection</div>
                        <div><span class="cbx">{{ $isChecked('std') }}</span> Sexually Transmitted disease</div>
                        <div><span class="cbx">{{ $isChecked('ulcers') }}</span> Stomach Troubles / Ulcers</div>
                        <div><span class="cbx">{{ $isChecked('fainting') }}</span> Fainting Seizure</div>
                        <div><span class="cbx">{{ $isChecked('weight_loss') }}</span> Rapid Weight Loss</div>
                        <div><span class="cbx">{{ $isChecked('radiation') }}</span> Radiation Therapy</div>
                        <div><span class="cbx">{{ $isChecked('joint_replacement') }}</span> Joint Replacement / Implant</div>
                        <div><span class="cbx">{{ $isChecked('heart_surgery') }}</span> Heart Surgery</div>
                        <div><span class="cbx">{{ $isChecked('heart_attack') }}</span> Heart Attack</div>
                        <div><span class="cbx">{{ $isChecked('thyroid') }}</span> Thyroid Problem</div>
                    </td>
                    <td>
                        <div><span class="cbx">{{ $isChecked('heart_disease') }}</span> Heart Disease</div>
                        <div><span class="cbx">{{ $isChecked('heart_murmur') }}</span> Heart Murmur</div>
                        <div><span class="cbx">{{ $isChecked('hepatitis_liver') }}</span> Hepatitis / Liver Disease</div>
                        <div><span class="cbx">{{ $isChecked('rheumatic_fever') }}</span> Rheumatic Fever</div>
                        <div><span class="cbx">{{ $isChecked('hay_fever') }}</span> Hay Fever / Allergies</div>
                        <div><span class="cbx">{{ $isChecked('respiratory') }}</span> Respiratory Problems</div>
                        <div><span class="cbx">{{ $isChecked('jaundice') }}</span> Hepatitis / Jaundice</div>
                        <div><span class="cbx">{{ $isChecked('tuberculosis') }}</span> Tuberculosis</div>
                        <div><span class="cbx">{{ $isChecked('swollen_ankles') }}</span> Swollen ankles</div>
                        <div><span class="cbx">{{ $isChecked('kidney') }}</span> Kidney disease</div>
                        <div><span class="cbx">{{ $isChecked('diabetes') }}</span> Diabetes</div>
                        <div><span class="cbx">{{ $isChecked('chest_pain') }}</span> Chest pain</div>
                        <div><span class="cbx">{{ $isChecked('stroke') }}</span> Stroke</div>
                    </td>
                    <td>
                        <div><span class="cbx">{{ $isChecked('cancer') }}</span> Cancer / Tumors</div>
                        <div><span class="cbx">{{ $isChecked('anemia') }}</span> Anemia</div>
                        <div><span class="cbx">{{ $isChecked('angina') }}</span> Angina</div>
                        <div><span class="cbx">{{ $isChecked('asthma') }}</span> Asthma</div>
                        <div><span class="cbx">{{ $isChecked('emphysema') }}</span> Emphysema</div>
                        <div><span class="cbx">{{ $isChecked('bleeding_problems') }}</span> Bleeding Problems</div>
                        <div><span class="cbx">{{ $isChecked('blood_diseases') }}</span> Blood Diseases</div>
                        <div><span class="cbx">{{ $isChecked('head_injuries') }}</span> Head Injuries</div>
                        <div><span class="cbx">{{ $isChecked('arthritis') }}</span> Arthritis / Rheumatism</div>
                        <div><span class="cbx">{{ $isChecked('other') }}</span> Other: <span class="field-line" style="min-width:60px">{{ $medicalProfile->other_conditions ?? '' }}</span></div>
                        <div style="margin-top:14px; text-align:center;">
                            @if(str_starts_with($signature, 'data:image'))
                                <img src="{{ $signature }}" class="signature-img" alt="Signature">
                            @endif
                            <div style="border-top:1px solid #000; padding-top:2px; font-size:7px;">Signature</div>
                        </div>
                    </td>
                </tr>
            </table>
        @else
            <p style="font-style:italic; color:#666;">No medical profile on file.</p>
        @endif
    </div>
</div>

{{-- ═══════════ PAGE 2: BACK (Consent + Dental Chart) ═══════════ --}}
<div class="spread page-break">

    {{-- LEFT: Informed Consent — ✅ NOW WITH REAL INITIALS FROM form_data --}}
    <div class="col col-left">
        <div class="title-wrap">
            <div class="form-title">INFORMED CONSENT</div>
        </div>

        <div class="consent-body">
            <p><span class="clause-title">Treatment to be Done:</span> I understand and consent to have any treatment done by the dentist after the procedure, the risks &amp; benefits &amp; cost have been fully explained. These treatments include, but are not limited to, x-rays, cleanings, periodontal treatments, fillings, crowns, bridges, all types of extraction, root canals, &amp;/or dentures, local anesthetics &amp; surgical cases.
                <span class="initial-label">(Initial: <span class="initial-box">{{ $ini('treatment_to_be_done') }}</span>)</span>
            </p>

            <p><span class="clause-title">Drugs &amp; Medications:</span> I understand that antibiotics, analgesics &amp; other medications can cause allergic reactions like redness &amp; swelling of tissues, pain, itching, vomiting &amp;/or anaphylactic shock.
                <span class="initial-label">(Initial: <span class="initial-box">{{ $ini('drugs_medications') }}</span>)</span>
            </p>

            <p><span class="clause-title">Changes in Treatment Plan:</span> I understand that during treatment it may be necessary to change/add procedures because of conditions found while working on the teeth that was not discovered during examination. For example, root canal therapy may be needed following routine restorative procedures. I give my permission to the dentist to make any/all changes and additions as necessary w/ my responsibility to pay all the costs agreed.
                <span class="initial-label">(Initial: <span class="initial-box">{{ $ini('changes_treatment_plan') }}</span>)</span>
            </p>

            <p><span class="clause-title">Radiograph:</span> I understand that an x-ray shot or a radiograph maybe necessary as part of diagnostic aid to come up with tentative diagnostic of my Dental problem and to make a good treatment plan, but, this will not give me a 100% assurance for the accuracy of the treatment since all dental treatments are subject to unpredictable complications that later on may lead to sudden changes and additions as necessary w/ my responsibility to pay all the costs agreed.
                <span class="initial-label">(Initial: <span class="initial-box">{{ $ini('radiograph') }}</span>)</span>
            </p>

            <p><span class="clause-title">Removal of Teeth:</span> I understand that alternatives to tooth removal (root canal therapy, crown &amp; periodontal surgery, etc.) &amp; I completely understand these alternatives, including their risk &amp; benefits prior to authorizing the dentist to remove teeth &amp; any other structures necessary for reasons above. I understand that removing teeth does not always remove all the infections, if present, &amp; it may be necessary to have further treatment. I understand the risk involved in having teeth removed, such as pain, swelling, spread of infections, dry socket, fractured jaw, loss of feeling on the teeth, lips, tongue &amp; surrounding tissue that can last for an indefinite period of time. I understand that I may need further treatment under a specialist if complications arise during or following treatment.
                <span class="initial-label">(Initial: <span class="initial-box">{{ $ini('removal_of_teeth') }}</span>)</span>
            </p>

            <p><span class="clause-title">Crowns (Caps) &amp; Bridges:</span> Preparing a tooth may irritate the nerve tissue in the center of the tooth, leaving the tooth extra sensitive to heat, cold &amp; pressure. Treating such irritation may involve using special toothpastes, mouth rinses or root canal therapy. I understand that sometimes it is not possible to match the color of natural teeth exactly with artificial teeth. I further understand that I may be wearing temporary crowns, which may come off easily &amp; that I must be careful to ensure that they are kept on until the permanent crowns are delivered. It is my responsibility to return for permanent cementation within 20 days from tooth preparation, as excessive days delay may allow for tooth movement, which may necessitate a remake of the crown, bridge/cap. I understand there will be additional charges for remakes due to my delaying of permanent cementation, &amp; I realize that final opportunity to make changes in my new crown, bridges or cap (including shape, fit, size &amp; color) will be before permanent cementation.
                <span class="initial-label">(Initial: <span class="initial-box">{{ $ini('crowns_bridges') }}</span>)</span>
            </p>

            <p><span class="clause-title">Endodontics (Root Canal):</span> I understand there is no guarantee that a root canal treatment will save a tooth &amp; that complications can occur from the treatment &amp; that occasionally root canal filling material may extend through the tooth which does not necessarily affect the success of the treatment. I understand that endodontic files &amp; drills are very fine instruments &amp; stresses vented in their manufacture &amp; calcifications present in teeth can cause them to break during use. I understand that referral to the endodontist for additional treatments may be necessary following any root canal treatment &amp; I agree that I am responsible for any additional cost for treatment performed by the endodontist. I understand that a tooth may required removal in spite of all efforts to save it.
                <span class="initial-label">(Initial: <span class="initial-box">{{ $ini('endodontics') }}</span>)</span>
            </p>

            <p><span class="clause-title">Periodontal Disease:</span> I understand that periodontal disease is a serious condition causing gum &amp; bone inflammation &amp;/or loss &amp; that can lead eventually to the loss of my teeth. I understand the alternative treatment plans to correct periodontal disease, including gum surgery, tooth extractions with or without replacement. I understand that undertaking any dental procedures may have future adverse effect on my periodontal conditions.
                <span class="initial-label">(Initial: <span class="initial-box">{{ $ini('periodontal_disease') }}</span>)</span>
            </p>

            <p><span class="clause-title">Fillings:</span> I understand that care must be exercised in chewing on fillings, especially during the first 24 hours to avoid breakage. I understand that a more extensive filling or a crown may be required, as additional decay or fracture may become evident after initial excavation. I understand that significant sensitivity is a common, but usually temporary, after-effect of a newly placed filling. I further understand that filling a tooth may irritate the nerve tissue creating sensitivity &amp; treating such sensitivity could require root canal therapy or extractions.
                <span class="initial-label">(Initial: <span class="initial-box">{{ $ini('fillings') }}</span>)</span>
            </p>

            <p><span class="clause-title">Dentures:</span> I understand that wearing of dentures can be difficult. Sore spots, altered speech &amp; difficulty in eating are common problems. Immediate dentures (placement of denture immediately after extractions) may be painful. Immediate dentures may require considerable adjusting &amp; several relines. I understand that it is my responsibility to return for delivery of dentures. I understand that failure to keep my delivery appointment may result in poorly fitted dentures. If a remake is required due to my delays of more than 30 days, there will be additional charges. A permanent reline will be needed later, which is not included in the initial fee. I understand that all adjustment or alteration of any kind this initial period is subject to charges.
                <span class="initial-label">(Initial: <span class="initial-box">{{ $ini('dentures') }}</span>)</span>
            </p>

            <p class="consent-closing">I understand that dentistry is not an exact science and that no dentist can properly guarantee accurate results all the time.</p>

            <p class="consent-closing">I hereby authorize any of the doctors/dental auxiliaries to proceed with &amp; perform the dental restoration &amp; treatments as explained to me. I understand that these are subject to modification depending on undiagnosable circumstances that may arise during the course of treatment. I understand that regardless of any dental insurance coverage I may have, I am responsible for payment of dental fees. I agree to pay any attorney's fees, collection fee, or court costs that may be incurred to satisfy any obligation to this office. All treatment were properly explained to me &amp; any untoward circumstances that may arise during the procedure, the attending dentist will not be held liable since it is my free will, with full trust &amp; confidence in him/her, to undergo dental treatment under his/her care.</p>
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
                    {{ $consent->appointment?->doctor?->user?->name ?? $consent->signedByStaff?->name ?? '' }}
                    <br>Dentist / Signature
                </div>
            </div>
            <div class="signature-cell">
                <div style="margin-top: 28px; padding-top: 2px; border-top: 1px solid #000; font-size: 7.5px;">
                    {{ $consent->signed_at->format('F j, Y') }}
                </div>
                <div style="font-size:7.5px;">Date</div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Dental Record Chart (PDA Style) --}}
    <div class="col col-right">
        <div class="title-wrap">
            <div class="form-title">DENTAL RECORD CHART</div>
        </div>

        <div style="font-weight:bold; margin: 3px 0; font-size:9px;">INTRAORAL EXAMINATION</div>

        <table class="info-table">
            <tr>
                <td colspan="3"><strong>Name:</strong> <span class="field-line" style="min-width:260px">{{ $patient->name }}</span></td>
            </tr>
            <tr>
                <td><strong>Age:</strong> <span class="field-line" style="min-width:30px">{{ $patient->age ?? '' }}</span></td>
                <td><strong>Gender M/F:</strong> <span class="field-line" style="min-width:30px">{{ $patient->gender ?? '' }}</span></td>
                <td><strong>Date:</strong> <span class="field-line" style="min-width:70px">{{ $consent->signed_at->format('M d, Y') }}</span></td>
            </tr>
        </table>

        {{-- Top STATUS Row --}}
        <table class="chart-line" style="margin-top:8px;">
            <tr>
                <td class="side-lbl">STATUS<br>RIGHT</td>
                @for($i=0; $i<10; $i++)
                    <td class="sbox sbox-temp">&nbsp;</td>
                @endfor
                <td class="side-lbl">LEFT</td>
            </tr>
        </table>

        {{-- TEMPORARY UPPER --}}
        <table class="chart-line">
            <tr>
                <td class="side-lbl" rowspan="2">TEMPORARY<br>TEETH</td>
                @foreach([55,54,53,52,51,61,62,63,64,65] as $tooth)
                    <td class="tnum tnum-temp">{{ $tooth }}</td>
                @endforeach
                <td class="side-lbl" rowspan="2">&nbsp;</td>
            </tr>
            <tr>
                @foreach([55,54,53,52,51,61,62,63,64,65] as $tooth)
                    <td class="tsym tsym-temp">{!! $dentalChart[$tooth] ?? '&#9678;' !!}</td>
                @endforeach
            </tr>
        </table>

        {{-- PERMANENT UPPER --}}
        <table class="chart-line" style="margin-top:2px;">
            <tr>
                <td class="side-lbl" rowspan="3">PERMANENT<br>TEETH</td>
                @for($i=0; $i<16; $i++)
                    <td class="sbox sbox-perm">&nbsp;</td>
                @endfor
                <td class="side-lbl" rowspan="3">&nbsp;</td>
            </tr>
            <tr>
                @foreach([18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28] as $tooth)
                    <td class="tnum tnum-perm">{{ $tooth }}</td>
                @endforeach
            </tr>
            <tr>
                @foreach([18,17,16,15,14,13,12,11,21,22,23,24,25,26,27,28] as $tooth)
                    <td class="tsym tsym-perm">{!! $dentalChart[$tooth] ?? '&#9678;' !!}</td>
                @endforeach
            </tr>
        </table>

        {{-- PERMANENT LOWER --}}
        <table class="chart-line">
            <tr>
                <td class="side-lbl" rowspan="3">&nbsp;</td>
                @foreach([48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38] as $tooth)
                    <td class="tsym tsym-perm">{!! $dentalChart[$tooth] ?? '&#9678;' !!}</td>
                @endforeach
                <td class="side-lbl" rowspan="3">&nbsp;</td>
            </tr>
            <tr>
                @foreach([48,47,46,45,44,43,42,41,31,32,33,34,35,36,37,38] as $tooth)
                    <td class="tnum tnum-perm">{{ $tooth }}</td>
                @endforeach
            </tr>
            <tr>
                @for($i=0; $i<16; $i++)
                    <td class="sbox sbox-perm">&nbsp;</td>
                @endfor
            </tr>
        </table>

        {{-- TEMPORARY LOWER --}}
        <table class="chart-line" style="margin-top:2px;">
            <tr>
                <td class="side-lbl" rowspan="2">TEMPORARY<br>TEETH</td>
                @foreach([85,84,83,82,81,71,72,73,74,75] as $tooth)
                    <td class="tsym tsym-temp">{!! $dentalChart[$tooth] ?? '&#9678;' !!}</td>
                @endforeach
                <td class="side-lbl" rowspan="2">&nbsp;</td>
            </tr>
            <tr>
                @foreach([85,84,83,82,81,71,72,73,74,75] as $tooth)
                    <td class="tnum tnum-temp">{{ $tooth }}</td>
                @endforeach
            </tr>
        </table>

        {{-- Bottom STATUS Row --}}
        <table class="chart-line">
            <tr>
                <td class="side-lbl">STATUS<br>RIGHT</td>
                @for($i=0; $i<10; $i++)
                    <td class="sbox sbox-temp">&nbsp;</td>
                @endfor
                <td class="side-lbl">LEFT</td>
            </tr>
        </table>

        {{-- LEGEND --}}
        <div class="legend">
            <table>
                <tr>
                    <td style="width:34%;">
                        <strong>Legend: Condition</strong>
                        <div>D &nbsp;- Decayed (Caries Indicated for Filling)</div>
                        <div>M &nbsp;- Missing due to Caries</div>
                        <div>F &nbsp;- Filled</div>
                        <div>I &nbsp;- Caries Indicated for Extraction</div>
                        <div>RF - Root Fragment</div>
                        <div>MO - Missing due to Other</div>
                        <div>Im - Impacted Tooth</div>
                    </td>
                    <td style="width:33%;">
                        <strong>Restoration &amp; Prosthetics</strong>
                        <div>J &nbsp;- Jacket Crown</div>
                        <div>A &nbsp;- Amalgam Filling</div>
                        <div>AB - Abutment</div>
                        <div>P &nbsp;- Pontic</div>
                        <div>In - Inlay</div>
                        <div>FX - Fixed Cure Composite</div>
                        <div>Rm - Removable Denture</div>
                    </td>
                    <td style="width:33%;">
                        <strong>Surgery</strong>
                        <div>X &nbsp;- Extraction due to Caries</div>
                        <div>XO - Extraction due to Other Causes</div>
                        <div>✓ &nbsp;- Present Teeth</div>
                        <div>Cm - Congenitally Missing</div>
                        <div>Sp - Supernumerary</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="exam-extras">
            <table>
                <tr>
                    <td style="width:25%;">
                        <strong>Periodical Screening</strong>
                        <div><span class="exam-line"></span> Gingivitis</div>
                        <div><span class="exam-line"></span> Early Periodontics</div>
                        <div><span class="exam-line"></span> Moderate Periodontics</div>
                        <div><span class="exam-line"></span> Advanced Periodontics</div>
                    </td>
                    <td style="width:25%;">
                        <strong>Occlusion</strong>
                        <div><span class="exam-line"></span> Class (Molar)</div>
                        <div><span class="exam-line"></span> Overjet</div>
                        <div><span class="exam-line"></span> Overbite</div>
                        <div><span class="exam-line"></span> Midline Deviation</div>
                        <div><span class="exam-line"></span> Crossbite</div>
                    </td>
                    <td style="width:25%;">
                        <strong>Appliances</strong>
                        <div><span class="exam-line"></span> Orthodontic</div>
                        <div><span class="exam-line"></span> Stayplate</div>
                        <div><span class="exam-line"></span> Others</div>
                    </td>
                    <td style="width:25%;">
                        <strong>TMD</strong>
                        <div><span class="exam-line"></span> Clenching</div>
                        <div><span class="exam-line"></span> Clicking</div>
                        <div><span class="exam-line"></span> Trismus</div>
                        <div><span class="exam-line"></span> Muscle Spasm</div>
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
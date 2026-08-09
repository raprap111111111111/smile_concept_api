@php
    /*
    |===========================================================================
    | SMILE CONCEPT — EMAIL DESIGN FILE
    |===========================================================================
    |
    | This is the only file you need to edit to restyle every email the system
    | sends (appointment booked, confirmed, cancelled, rescheduled, reminders).
    |
    | Change a value below, save, then run:
    |
    |     php artisan view:clear
    |
    | ...and send yourself a test. That's the whole loop — no PHP to touch.
    |
    | The two colours default to whatever is set in the admin Settings screen
    | (Branding > Primary / Secondary Colour), so the clinic can rebrand without
    | a code change. The fallback after the comma is used when that setting is
    | empty. Everything else is plain CSS: edit the values, keep the class
    | names. The class names are matched against the email markup, so renaming
    | one silently removes its styling.
    |
    */

    // ─── Colours ────────────────────────────────────────────────────────────
    $brand     = setting('primary_color',   '#2563eb');  // buttons, headings, accents
    $accent    = setting('secondary_color', '#f59e0b');  // highlight panels
    $ink       = '#1f2933';                              // body text
    $heading   = '#102a43';                              // headings
    $muted     = '#829ab1';                              // footer / fine print
    $line      = '#e4e9f0';                              // hairline borders
    $pageBg    = '#eef2f7';                              // outside the card
    $cardBg    = '#ffffff';                              // the card itself
    $panelBg   = '#f7f9fc';                              // quoted panel background

    // ─── Shape & type ───────────────────────────────────────────────────────
    $radius    = '12px';
    $width     = '600px';                                // card width; keep <= 600px
    $fontStack = "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji'";
@endphp
/* Base */

body,
body *:not(html):not(style):not(br):not(tr):not(code) {
    box-sizing: border-box;
    font-family: {!! $fontStack !!};
    position: relative;
}

body {
    -webkit-text-size-adjust: none;
    background-color: {{ $pageBg }};
    color: {{ $ink }};
    height: 100%;
    line-height: 1.5;
    margin: 0;
    padding: 0;
    width: 100% !important;
}

p,
ul,
ol,
blockquote {
    line-height: 1.5;
    text-align: start;
}

a {
    color: {{ $brand }};
}

a img {
    border: none;
}

/* Typography */

h1 {
    color: {{ $heading }};
    font-size: 20px;
    font-weight: 700;
    margin-top: 0;
    text-align: start;
}

h2 {
    color: {{ $heading }};
    font-size: 16px;
    font-weight: 700;
    margin-top: 0;
    text-align: start;
}

h3 {
    color: {{ $heading }};
    font-size: 14px;
    font-weight: 700;
    margin-top: 0;
    text-align: left;
}

p {
    font-size: 16px;
    line-height: 1.6em;
    margin-top: 0;
    text-align: left;
}

p.sub {
    font-size: 12px;
}

img {
    max-width: 100%;
}

/* Layout */

.wrapper {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    background-color: {{ $pageBg }};
    margin: 0;
    padding: 0;
    width: 100%;
}

.content {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    margin: 0;
    padding: 0;
    width: 100%;
}

/* Header */

.header {
    padding: 28px 0 18px;
    text-align: center;
}

.header a {
    color: {{ $brand }};
    font-size: 22px;
    font-weight: 700;
    letter-spacing: -0.2px;
    text-decoration: none;
}

/* Logo */

.logo {
    height: 64px;
    margin-top: 10px;
    margin-bottom: 10px;
    max-height: 64px;
    width: auto;
}

/* Body */

.body {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    background-color: {{ $pageBg }};
    border-bottom: 1px solid {{ $pageBg }};
    border-top: 1px solid {{ $pageBg }};
    margin: 0;
    padding: 0;
    width: 100%;
}

.inner-body {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: {{ $width }};
    background-color: {{ $cardBg }};
    border-color: {{ $line }};
    border-radius: {{ $radius }};
    border-width: 1px;
    box-shadow: 0 1px 3px 0 rgba(16, 42, 67, 0.08), 0 1px 2px -1px rgba(16, 42, 67, 0.06);
    margin: 0 auto;
    padding: 0;
    width: {{ $width }};
}

.inner-body a {
    word-break: break-all;
}

/* Subcopy */

.subcopy {
    border-top: 1px solid {{ $line }};
    margin-top: 25px;
    padding-top: 25px;
}

.subcopy p {
    color: {{ $muted }};
    font-size: 13px;
}

/* Footer */

.footer {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: {{ $width }};
    margin: 0 auto;
    padding: 0;
    text-align: center;
    width: {{ $width }};
}

.footer p {
    color: {{ $muted }};
    font-size: 12px;
    line-height: 1.6;
    text-align: center;
}

.footer a {
    color: {{ $muted }};
    text-decoration: underline;
}

/* Tables */

.table table {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    margin: 24px auto;
    width: 100%;
}

.table th {
    border-bottom: 1px solid {{ $line }};
    margin: 0;
    padding-bottom: 8px;
}

.table td {
    border-bottom: 1px solid {{ $line }};
    color: {{ $ink }};
    font-size: 15px;
    line-height: 20px;
    margin: 0;
    padding: 12px 0;
}

.content-cell {
    max-width: 100vw;
    padding: 32px;
}

/* Buttons */

.action {
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 100%;
    margin: 30px auto;
    padding: 0;
    text-align: center;
    width: 100%;
    float: unset;
}

.button {
    -webkit-text-size-adjust: none;
    border-radius: {{ $radius }};
    color: #fff;
    display: inline-block;
    font-weight: 600;
    overflow: hidden;
    text-decoration: none;
}

.button-blue,
.button-primary {
    background-color: {{ $brand }};
    border-bottom: 8px solid {{ $brand }};
    border-left: 18px solid {{ $brand }};
    border-right: 18px solid {{ $brand }};
    border-top: 8px solid {{ $brand }};
}

.button-green,
.button-success {
    background-color: #16a34a;
    border-bottom: 8px solid #16a34a;
    border-left: 18px solid #16a34a;
    border-right: 18px solid #16a34a;
    border-top: 8px solid #16a34a;
}

.button-red,
.button-error {
    background-color: #dc2626;
    border-bottom: 8px solid #dc2626;
    border-left: 18px solid #dc2626;
    border-right: 18px solid #dc2626;
    border-top: 8px solid #dc2626;
}

/* Panels */

.panel {
    border-left: {{ $accent }} solid 4px;
    margin: 21px 0;
}

.panel-content {
    background-color: {{ $panelBg }};
    color: {{ $ink }};
    padding: 16px;
}

.panel-content p {
    color: {{ $ink }};
}

.panel-item {
    padding: 0;
}

.panel-item p:last-of-type {
    margin-bottom: 0;
    padding-bottom: 0;
}

/* Utilities */

.break-all {
    word-break: break-all;
}

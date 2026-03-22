<html>
<head>
    <title>Student Marksheet - {{ $sr->user->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/my_print.css') }}" />
</head>
<body>
<div class="container">
    <div id="print" xmlns:margin-top="http://www.w3.org/1999/xhtml">
        {{--    Logo N School Details--}}
        <table width="100%">
            <tr>
                <td><img src="{{ $s['logo'] }}" style="max-height : 100px;"></td>

                <td style="text-align: center; ">
                    <strong><span style="color: #1b0c80; font-size: 22px;">{{ strtoupper(Qs::getSetting('system_name')) }}</span></strong><br/>
                    <span style="color: #6b7280; font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase;">Progress report</span><br/>
                    <span style="color: #374151; font-size: 13px;">{{ ucwords($s['address'] ?? '') }}</span>
                </td>
                <td style="width: 100px; height: 100px; float: left;">
                    <img src="{{ $sr->user->photo }}"
                         alt="..."  width="100" height="100">
                </td>
            </tr>
        </table>
        <br/>

        {{--<!-- SHEET BEGINS HERE-->--}}
@include('pages.support_team.marks.print.sheet')

        <div style="margin-top: 40px; text-align: right; clear: both;">
            <div style="display: inline-block; min-width: 200px; border-top: 1px solid #111; padding-top: 8px; font-size: 7.5pt; letter-spacing: 0.12em; text-transform: uppercase; color: #4b5563;">Principal&rsquo;s signature</div>
        </div>

    </div>
</div>

<script>
    window.print();
</script>
</body>

</html>

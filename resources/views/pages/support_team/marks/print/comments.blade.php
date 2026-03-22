<div>
    <table class="td-left" style="border-collapse:collapse;">
        <tbody>
        <tr>
            <td><strong>NEXT TERM BEGINS:</strong></td>
            <td>{{ date('l\, jS F\, Y', strtotime($s['term_begins'])) }}</td>
        </tr>
        <tr>
            <td><strong>NEXT TERM FEES:</strong></td>
            <td>{{ Qs::getCurrency() }}{{ $s['next_term_fees_'.$class_type->id] ?? '—' }}</td>
        </tr>
        </tbody>
    </table>
</div>

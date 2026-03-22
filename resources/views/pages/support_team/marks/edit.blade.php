@php
    $slots = $mark_config->slotsForDisplay();
    $active_slot = (int) ($mark_config->active_slot ?? 0);
@endphp
<style>
    .marks-entry-table { width: 100%; border-collapse: collapse; }
    .marks-entry-table thead th { background-color: #002147; color: #fff; font-weight: 600; border: 1px solid #002147; padding: 8px 6px; white-space: nowrap; }
    .marks-entry-table thead th.col-sn { width: 45px; min-width: 45px; max-width: 45px; text-align: left; padding-left: 10px; }
    .marks-entry-table thead th.col-name { min-width: 250px; }
    .marks-entry-table thead th.col-adm { width: 150px; min-width: 150px; max-width: 150px; }
    .marks-entry-table thead th.col-ca { width: 1%; min-width: 70px; text-align: center; }
    .marks-entry-table tbody tr { border-bottom: 1px solid #eee; }
    .marks-entry-table tbody td { vertical-align: middle; padding: 4px 6px; border-left: 1px solid #eee; border-bottom: 1px solid #eee; }
    .marks-entry-table tbody td:first-child { border-left: 1px solid #eee; }
    .marks-entry-table tbody td:last-child { border-right: 1px solid #eee; }
    .marks-entry-table tbody td.col-sn { width: 45px; min-width: 45px; max-width: 45px; text-align: left; padding-left: 10px; }
    .marks-entry-table tbody td.col-name { min-width: 250px; }
    .marks-entry-table tbody td.col-adm { width: 150px; min-width: 150px; max-width: 150px; }
    .marks-entry-table tbody td.col-ca { text-align: center; }
    .marks-entry-table thead th.col-name,
    .marks-entry-table thead th.col-adm,
    .marks-entry-table tbody td.col-name,
    .marks-entry-table tbody td.col-adm { text-align: left; }

    .marks-entry-table .score-input { width: 60px; height: 35px; padding: 0 6px; text-align: center; font-weight: bold; border: 1px solid #ddd; border-radius: 2px; font-size: 0.9rem; box-sizing: border-box; display: block; margin: 0 auto; }
    .marks-entry-table .score-input:focus { outline: none; border-color: #D32F2F; box-shadow: 0 0 0 1px #D32F2F; }

    .marks-entry-table .total-cell { text-align: center; }
    .marks-entry-table .total-input {
        width: 80px;
        height: 35px;
        padding: 0 6px;
        text-align: center;
        font-weight: 800;
        border: 1px solid #d9dee6;
        border-radius: 2px;
        font-size: 0.95rem;
        box-sizing: border-box;
        background: #f8fafc;
    }
</style>
<form class="ajax-update" action="{{ route('marks.update', [$m->term, $m->my_class_id, $m->section_id, $m->subject_id]) }}" method="post">
    @csrf @method('put')
    <table id="marks-entry-table" class="table marks-entry-table">
        <thead>
        <tr>
            <th class="col-sn">S/N</th>
            <th class="col-name">Name</th>
            <th class="col-adm">ADM_No</th>
            @foreach($slots as $slot)
                <th class="col-ca export-col">
                    {{ $slot['label'] }} ({{ $slot['max'] }})
                </th>
            @endforeach
            <th class="col-ca col-total" style="text-align:center;">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($marks->sortBy('user.name') as $mk)
            <tr>
                <td class="col-sn">{{ $loop->iteration }}</td>
                <td class="col-name">{{ $mk->user->name }}</td>
                <td class="col-adm">{{ $mk->user->student_record->adm_no ?? '—' }}</td>
                @foreach($slots as $slot)
                    <td class="col-ca">
                        <input
                            title="{{ $slot['label'] }}"
                            min="0"
                            max="{{ $slot['max'] }}"
                            class="score-input"
                            name="{{ $slot['key'] }}_{{ $mk->id }}"
                            value="{{ $mk->{$slot['key']} }}"
                            type="number"
                        >
                    </td>
                @endforeach
                @php $totalKey = 'tex' . $m->term; $initTotal = $mk->{$totalKey} ?? null; @endphp
                <td class="col-ca total-cell">
                    <input
                        class="total-input"
                        type="number"
                        readonly
                        value="{{ $initTotal !== null ? (int) $initTotal : 0 }}"
                    >
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="text-right mt-3">
        <button type="submit" class="btn btn-primary" style="background-color: #D32F2F; border-color: #D32F2F; color: #fff;">Update Marks <i class="icon-paperplane ml-2" style="color: #fff;"></i></button>
    </div>
</form>

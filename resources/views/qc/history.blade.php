@extends('layouts.master')

@section('page_title', 'Riwayat QC')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Riwayat QC</h3>
    <a href="{{ route('qc.queue') }}" class="btn btn-primary">
        <i class="bi bi-clipboard-check me-2"></i>Antrian Inspeksi
    </a>
</div>

<div class="card card-flush">
    <div class="card-body py-5">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="qc_history_table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>No</th>
                        <th>Nomor PO</th>
                        <th>Customer</th>
                        <th class="text-end">Qty Pass</th>
                        <th class="text-end">Qty Rework</th>
                        <th class="text-end">Qty Reject</th>
                        <th>Tanggal QC</th>
                        <th>Status Akhir</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach(($orders ?? collect()) as $i => $order)
                        @php
                            $sum = $order->qcSummary;
                            $rework = (int)($sum->qty_rework ?? 0);
                            $final = $rework > 0 ? 'rework_sewing' : 'completed';
                            $badge = $final === 'rework_sewing' ? 'badge-light-danger' : 'badge-light-success';
                            $label = $final === 'rework_sewing' ? 'Rework Sewing' : 'Completed';
                            $notes = (string)($sum->general_notes ?? '');
                            $defects = collect($order->orderQcChecklists ?? [])->map(function($x){
                                return trim(($x->qc_category ?? '-') . ' — ' . ($x->qc_instruction ?? '-'));
                            })->values();
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-gray-800 fw-bold">{{ $order->po_number ?? '-' }}</td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td class="text-end">{{ $sum->qty_pass ?? 0 }}</td>
                            <td class="text-end">{{ $sum->qty_rework ?? 0 }}</td>
                            <td class="text-end">{{ $sum->qty_reject ?? 0 }}</td>
                            <td>{{ optional($sum->updated_at)->format('d M Y H:i') ?? '-' }}</td>
                            <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-light btn-sm btnViewNotes"
                                    data-po="{{ $order->po_number ?? '-' }}"
                                    data-notes="{{ e($notes) }}"
                                    data-defects='@json($defects)'>
                                    <i class="bi bi-file-text"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="notesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Catatan QC - <span id="nm_po">-</span></h5>
        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body">
        <div class="mb-5">
            <div class="fw-bold mb-2">General Notes</div>
            <div class="text-gray-700" id="nm_notes" style="white-space: pre-line;">-</div>
        </div>
        <div>
            <div class="fw-bold mb-2">Defect Checklist</div>
            <ul class="mb-0" id="nm_defects"></ul>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
        jQuery('#qc_history_table').DataTable({ pageLength: 10, ordering: true });
    }

    var modalEl = document.getElementById('notesModal');
    var modal = null;
    if (modalEl && window.bootstrap) {
        modal = new bootstrap.Modal(modalEl);
    }

    document.querySelectorAll('.btnViewNotes').forEach(function(btn){
        btn.addEventListener('click', function(){
            var po = btn.getAttribute('data-po') || '-';
            var notes = btn.getAttribute('data-notes') || '';
            var defects = [];
            try { defects = JSON.parse(btn.getAttribute('data-defects') || '[]'); } catch (e) { defects = []; }

            var poEl = document.getElementById('nm_po');
            var notesEl = document.getElementById('nm_notes');
            var ul = document.getElementById('nm_defects');

            if (poEl) poEl.textContent = po;
            if (notesEl) notesEl.textContent = notes ? notes : '-';
            if (ul) {
                ul.innerHTML = '';
                if (!defects.length) {
                    var li = document.createElement('li');
                    li.textContent = '-';
                    ul.appendChild(li);
                } else {
                    defects.forEach(function(d){
                        var li = document.createElement('li');
                        li.textContent = d;
                        ul.appendChild(li);
                    });
                }
            }

            if (modal) modal.show();
        });
    });
});
</script>
@endpush

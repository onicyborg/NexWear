@extends('layouts.master')

@section('page_title','QC Inspeksi - ' . ($order->po_number ?? '-'))

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">QC Inspeksi</h3>
    <div class="d-flex align-items-center gap-3">
        <a href="{{ route('qc.queue') }}" class="btn btn-light">
            <i class="bi bi-arrow-left me-2"></i>Kembali
        </a>
        <a href="{{ route('qc.dashboard') }}" class="btn btn-light">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard
        </a>
    </div>
</div>

<div class="text-muted mb-5">PO: <span class="fw-semibold">{{ $order->po_number ?? '-' }}</span> &mdash; Customer: {{ $order->customer->name ?? '-' }}</div>

<div class="row g-5">
    <div class="col-lg-8">
        <div class="card card-flush">
            <div class="card-header">
                <h4 class="card-title mb-0">Input Hasil QC</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">Total Qty PO yang harus diinspeksi: <strong id="total_qty">{{ $totalQty }}</strong></div>
                <form action="{{ route('qc.submit', $order->id) }}" method="POST" id="qc_form">
                    @csrf
                    <div class="row g-4 align-items-end mb-5">
                        <div class="col-md-4">
                            <label class="form-label">Qty Pass</label>
                            <input type="number" min="0" class="form-control qc-input" name="qty_pass" id="qty_pass" value="{{ old('qty_pass', $order->qcSummary->qty_pass ?? '') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Qty Rework</label>
                            <input type="number" min="0" class="form-control qc-input" name="qty_rework" id="qty_rework" value="{{ old('qty_rework', $order->qcSummary->qty_rework ?? '') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Qty Reject</label>
                            <input type="number" min="0" class="form-control qc-input" name="qty_reject" id="qty_reject" value="{{ old('qty_reject', $order->qcSummary->qty_reject ?? '') }}" required>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Catatan Umum</label>
                        <textarea class="form-control" rows="3" name="general_notes" placeholder="Contoh:&#10;Rework Ukuran L : 10&#10;Rework Ukuran M : 20&#10;dst">{{ old('general_notes', $order->qcSummary->general_notes ?? '') }}</textarea>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Checklist Defect (opsional)</label>
                        <div class="row g-3">
                            @php
                                $prevChecked = collect($order->orderQcChecklists ?? [])->pluck('qc_instruction','qc_instruction');
                                // Atau bisa pakai ID jika disimpan: namun saat ini checklist menyimpan category+instruction snapshot.
                            @endphp
                            @foreach(($masterKpis ?? collect()) as $kpi)
                                <div class="col-md-6">
                                    <label class="form-check form-check-custom form-check-solid">
                                        @php $checked = $prevChecked->has($kpi->instruction); @endphp
                                        <input class="form-check-input" type="checkbox" name="defect_kpis[]" value="{{ $kpi->id }}" {{ $checked ? 'checked' : '' }}>
                                        <span class="form-check-label">
                                            <span class="fw-bold">{{ $kpi->category }}</span> — {{ $kpi->instruction }}
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" id="btn_submit_qc" class="btn btn-success">Simpan Hasil QC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-flush h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Ringkasan PO</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-row-dashed align-middle">
                        <thead>
                            <tr class="text-muted text-uppercase fs-7">
                                <th>Size</th>
                                <th class="text-end">Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $sizeMap = []; @endphp
                            @foreach(($order->orderItems ?? collect()) as $it)
                                @php $sz = strtoupper($it->size); $sizeMap[$sz] = ($sizeMap[$sz] ?? 0) + (int)$it->quantity; @endphp
                            @endforeach
                            @foreach($sizeMap as $sz => $q)
                                <tr>
                                    <td class="fw-semibold">{{ $sz }}</td>
                                    <td class="text-end">{{ $q }}</td>
                                </tr>
                            @endforeach
                            <tr class="fw-bold border-top">
                                <td>Total</td>
                                <td class="text-end">{{ $totalQty }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  function checkSum(){
    var total = parseInt(document.getElementById('total_qty').textContent||'0',10);
    var a = parseInt(document.getElementById('qty_pass').value||'0',10);
    var b = parseInt(document.getElementById('qty_rework').value||'0',10);
    var c = parseInt(document.getElementById('qty_reject').value||'0',10);
    var ok = (a+b+c) === total;
    var btn = document.getElementById('btn_submit_qc');
    if (btn) btn.disabled = !ok;
  }
  document.addEventListener('DOMContentLoaded', function(){
    ['qty_pass','qty_rework','qty_reject'].forEach(function(id){
      var el = document.getElementById(id);
      if (el) el.addEventListener('input', checkSum);
    });
    checkSum();
  });
})();
</script>
@endpush

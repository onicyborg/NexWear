@extends('layouts.master')

@section('page_title', 'Riwayat Penjahitan')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Riwayat Penjahitan</h3>
    <a href="{{ route('sewing.dashboard') }}" class="btn btn-light"><i class="bi bi-arrow-left me-1"></i>Kembali ke Kanban</a>
</div>

@php
    $supplierOptions = ($historyOrders ?? collect())
        ->map(fn($o) => $o->customer->name ?? null)
        ->filter()
        ->unique()
        ->sort()
        ->values();
@endphp

<div class="card card-flush">
    <div class="card-body py-5">
        <div class="row g-3 mb-5">
            <div class="col-md-4">
                <label class="form-label">Cari Nomor PO</label>
                <input type="text" class="form-control" id="history_po_search" placeholder="Ketik nomor PO...">
            </div>
            <div class="col-md-4">
                <label class="form-label">Filter Supplier</label>
                <select class="form-select" id="history_supplier_filter" data-placeholder="Pilih Supplier">
                    <option value="">Semua Supplier</option>
                    @foreach($supplierOptions as $name)
                        <option value="{{ $name }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="sewing_history_table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>No.</th>
                        <th>Nomor PO</th>
                        <th>Customer</th>
                        <th>Export Date</th>
                        <th>Waktu Mulai</th>
                        <th>Waktu Selesai</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach(($historyOrders ?? collect()) as $i => $order)
                        @php
                            $track = ($order->productionTrackings ?? collect())->first();
                            $start = optional($track?->started_at)->format('d M Y H:i');
                            $end = optional($track?->completed_at)->format('d M Y H:i');
                            $durMs = ($track && $track->started_at && $track->completed_at)
                                ? (\Carbon\Carbon::parse($track->started_at)->diffInSeconds(\Carbon\Carbon::parse($track->completed_at)))
                                : null;
                            $dur = $durMs !== null ? sprintf('%02dj %02d mnt', intdiv($durMs, 3600), intdiv($durMs%3600,60)) : '-';
                            $itemsRaw = $order->orderItems ?? collect();
                            $grouped = [];
                            foreach ($itemsRaw as $it) {
                                $key = ($it->color_code).'|'.($it->color_name);
                                if (!isset($grouped[$key])) {
                                    $grouped[$key] = ['color_code'=>$it->color_code,'color_name'=>$it->color_name,'XS'=>0,'S'=>0,'M'=>0,'L'=>0,'XL'=>0];
                                }
                                $sz = strtoupper(trim((string)$it->size));
                                if (!in_array($sz, ['XS','S','M','L','XL'])) $sz = 'M';
                                $grouped[$key][$sz] = ($grouped[$key][$sz] ?? 0) + (int)$it->quantity;
                            }
                            $mapped = array_values(array_map(function($row){
                                $row['total'] = (int)($row['XS']+$row['S']+$row['M']+$row['L']+$row['XL']);
                                return $row;
                            }, $grouped));
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td class="text-gray-800 fw-bold">{{ $order->po_number ?? '-' }}</td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>{{ optional($order->export_date)->format('d M Y') ?? '-' }}</td>
                            <td>{{ $start ?? '-' }}</td>
                            <td>{{ $end ?? '-' }}</td>
                            <td>{{ $dur }}</td>
                            <td><span class="badge bg-success">Selesai Dijahit</span></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-light-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#sizeModal"
                                    data-order='@json(["po"=>$order->po_number, "customer"=>$order->customer->name ?? "-"] )'
                                    data-items='@json($mapped)'>
                                    <i class="bi bi-eye me-1"></i>Detail/Instruksi
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Instruksi Size (reuse pola dari kanban) -->
<div class="modal fade" id="sizeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Instruksi Size</h5>
        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body">
        <div class="mb-4">
            <div class="fw-bold">PO: <span id="sm_po">-</span></div>
            <div class="text-muted">Customer: <span id="sm_customer">-</span></div>
        </div>
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-3" id="size_table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Color</th>
                        <th class="text-end">XS</th>
                        <th class="text-end">S</th>
                        <th class="text-end">M</th>
                        <th class="text-end">L</th>
                        <th class="text-end">XL</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody id="size_table_body"></tbody>
            </table>
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
        var dt = null;
        if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
            dt = jQuery('#sewing_history_table').DataTable({
                pageLength: 10,
                ordering: true
            });
        }

        if (window.jQuery && jQuery.fn && jQuery.fn.select2) {
            jQuery('#history_supplier_filter').select2({
                width: '100%',
                allowClear: true,
                placeholder: 'Pilih Supplier'
            });
        }

        function escapeRegex(s) {
            return String(s || '').replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }

        var poInput = document.getElementById('history_po_search');
        poInput && poInput.addEventListener('keyup', function(){
            if (!dt) return;
            dt.column(1).search((poInput.value || '').trim()).draw();
        });

        var supplierSelect = document.getElementById('history_supplier_filter');
        if (supplierSelect) {
            var handler = function(){
                if (!dt) return;
                var v = (supplierSelect.value || '').trim();
                if (!v) {
                    dt.column(2).search('').draw();
                    return;
                }
                dt.column(2).search('^' + escapeRegex(v) + '$', true, false).draw();
            };
            supplierSelect.addEventListener('change', handler);
            if (window.jQuery) {
                jQuery('#history_supplier_filter').on('change', handler);
            }
        }

        var sizeModalEl = document.getElementById('sizeModal');
        sizeModalEl && sizeModalEl.addEventListener('show.bs.modal', function (event) {
            var btn = event.relatedTarget;
            if (!btn) return;
            var order = {};
            try { order = JSON.parse(btn.getAttribute('data-order') || '{}'); } catch (e) { order = {}; }
            var items = [];
            try { items = JSON.parse(btn.getAttribute('data-items') || '[]'); } catch (e) { items = []; }
            document.getElementById('sm_po').textContent = order.po || '-';
            document.getElementById('sm_customer').textContent = order.customer || '-';
            var tbody = document.getElementById('size_table_body');
            if (tbody) tbody.innerHTML = '';
            items.forEach(function(it){
                var tr = document.createElement('tr');
                tr.innerHTML = '<td class="fw-semibold">'+ (it.color_name || '-') +'</td>'+
                               '<td class="text-end">'+ (it.XS || 0) +'</td>'+
                               '<td class="text-end">'+ (it.S || 0) +'</td>'+
                               '<td class="text-end">'+ (it.M || 0) +'</td>'+
                               '<td class="text-end">'+ (it.L || 0) +'</td>'+
                               '<td class="text-end">'+ (it.XL || 0) +'</td>'+
                               '<td class="text-end fw-bold">'+ (it.total || 0) +'</td>';
                tbody && tbody.appendChild(tr);
            });
        });
    });
</script>
@endpush

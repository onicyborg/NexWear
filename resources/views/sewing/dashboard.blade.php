@extends('layouts.master')

@section('page_title', 'Dashboard Sewing')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Kanban Sewing</h3>
    <div class="text-muted">Pantau dan kelola proses penjahitan</div>
</div>

<!-- Toolbar: Search & Sort -->
<div class="row g-3 mb-5">
    <div class="col-md-3">
        <input type="text" class="form-control" id="kanban_search" placeholder="Cari Nomor PO atau Customer...">
    </div>
    <div class="col-md-3">
        <select class="form-select" id="kanban_sort">
            <option value="date">Urutkan: Export Date Terdekat</option>
            <option value="ship">Urutkan: Ship Mode (Udara/Urgent)</option>
        </select>
    </div>
</div>

<div class="row g-7">
    <div class="col-lg-6">
        <div class="card card-flush h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Menunggu Dijahit (Pending)</h4>
            </div>
            <div class="card-body">
                @if(($pendingOrders ?? collect())->isEmpty())
                    <div class="text-muted">Tidak ada order pending.</div>
                @else
                    <div id="kanban_pending" class="d-flex flex-column gap-4">
                        @foreach($pendingOrders as $order)
                            @php
                                $ship = strtoupper(trim((string)($order->ship_mode ?? '-')));
                                $isRework = (strtolower((string)$order->status) === 'rework_sewing') || ((int)($order->qcSummary->qty_rework ?? 0) > 0);
                                $reworkQty = (int)($order->qcSummary->qty_rework ?? 0);
                            @endphp
                            <div class="card border-gray-200 shadow-sm kanban-item"
                                 data-po="{{ strtolower($order->po_number ?? '') }}"
                                 data-customer="{{ strtolower($order->customer->name ?? '') }}"
                                 data-date="{{ optional($order->export_date)->toDateString() }}"
                                 data-ship="{{ $ship }}">
                                <div class="card-header d-flex justify-content-between align-items-start py-3">
                                    <div>
                                        <div class="fw-bold d-flex align-items-center gap-2">
                                            <span>PO: {{ $order->po_number ?? '-' }}</span>
                                            @if($isRework)
                                                <span class="badge bg-danger fs-6 fw-bold">⚠️ REWORK: {{ $reworkQty }} pcs</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small">Customer: {{ $order->customer->name ?? '-' }}</div>
                                    </div>
                                    <div class="text-end d-flex flex-column gap-2 align-items-end">
                                        <span class="badge {{ in_array($ship, ['AIR','NOTS']) ? 'bg-danger' : 'bg-info' }}">{{ $ship ?: '-' }}</span>
                                    </div>
                                </div>
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted">Export Date</div>
                                        <div class="fw-semibold">{{ optional($order->export_date)->format('d M Y') ?? '-' }}</div>
                                    </div>
                                    @if($isRework && !empty($order->qcSummary->general_notes))
                                        <div class="mt-3">
                                            <div class="text-muted">Catatan Rework</div>
                                            <div class="fw-semibold">{{ $order->qcSummary->general_notes }}</div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center gap-2 py-3">
                                    @php
                                        $itemsRaw = $order->orderItems ?? collect();
                                        $grouped = [];
                                        foreach ($itemsRaw as $it) {
                                            $key = $it->color_code . '|' . $it->color_name;
                                            if (!isset($grouped[$key])) {
                                                $grouped[$key] = ['color_code' => $it->color_code, 'color_name' => $it->color_name, 'XS'=>0,'S'=>0,'M'=>0,'L'=>0,'XL'=>0];
                                            }
                                            $sz = strtoupper(trim((string)$it->size));
                                            if (!in_array($sz, ['XS','S','M','L','XL'])) $sz = 'M';
                                            $grouped[$key][$sz] = ($grouped[$key][$sz] ?? 0) + (int)$it->quantity;
                                        }
                                        $mapped = array_values(array_map(function($row){
                                            $row['total'] = (int)($row['XS']+$row['S']+$row['M']+$row['L']+$row['XL']);
                                            $row['notes'] = '-';
                                            return $row;
                                        }, $grouped));
                                    @endphp
                                    <button type="button" class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#sizeModal"
                                        data-order='@json(["po"=>$order->po_number, "customer"=>$order->customer->name ?? "-"] )'
                                        data-items='@json($mapped)'>
                                        <i class="bi bi-eye me-1"></i>Instruksi Size
                                    </button>
                                    <form method="POST" action="{{ route('sewing.start', $order->id) }}" class="ms-auto form-start">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            Mulai Proses Sewing
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card card-flush h-100">
            <div class="card-header">
                <h4 class="card-title mb-0">Sedang Dijahit (In Progress)</h4>
            </div>
            <div class="card-body">
                @if(($inProgressOrders ?? collect())->isEmpty())
                    <div class="text-muted">Belum ada order yang sedang dikerjakan.</div>
                @else
                    <div id="kanban_inprogress" class="d-flex flex-column gap-4">
                        @foreach($inProgressOrders as $order)
                            @php
                                $ship = strtoupper(trim((string)($order->ship_mode ?? '-')));
                                $isRework = (strtolower((string)$order->status) === 'rework_sewing') || ((int)($order->qcSummary->qty_rework ?? 0) > 0);
                                $reworkQty = (int)($order->qcSummary->qty_rework ?? 0);
                            @endphp
                            <div class="card border-gray-200 shadow-sm kanban-item"
                                 data-po="{{ strtolower($order->po_number ?? '') }}"
                                 data-customer="{{ strtolower($order->customer->name ?? '') }}"
                                 data-date="{{ optional($order->export_date)->toDateString() }}"
                                 data-ship="{{ $ship }}">
                                <div class="card-header d-flex justify-content-between align-items-start py-3">
                                    <div>
        							<div class="fw-bold d-flex align-items-center gap-2">
                                            <span>PO: {{ $order->po_number ?? '-' }}</span>
                                            @if($isRework)
                                                <span class="badge bg-danger fs-6 fw-bold">⚠️ REWORK: {{ $reworkQty }} pcs</span>
                                            @endif
                                        </div>
                                        <div class="text-muted small">Customer: {{ $order->customer->name ?? '-' }}</div>
                                    </div>
                                    <div class="text-end d-flex flex-column gap-2 align-items-end">
                                        <span class="badge {{ in_array($ship, ['AIR','NOTS']) ? 'bg-danger' : 'bg-info' }}">{{ $ship ?: '-' }}</span>
                                    </div>
                                </div>
                                <div class="card-body py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted">Export Date</div>
                                        <div class="fw-semibold">{{ optional($order->export_date)->format('d M Y') ?? '-' }}</div>
                                    </div>
                                    @if($isRework && !empty($order->qcSummary->general_notes))
                                        <div class="mt-3">
                                            <div class="text-muted">Catatan Rework</div>
                                            <div class="fw-semibold">{{ $order->qcSummary->general_notes }}</div>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer d-flex justify-content-between align-items-center gap-2 py-3">
                                    @php
                                        $itemsRaw = $order->orderItems ?? collect();
                                        $grouped = [];
                                        foreach ($itemsRaw as $it) {
                                            $key = $it->color_code . '|' . $it->color_name;
                                            if (!isset($grouped[$key])) {
                                                $grouped[$key] = ['color_code' => $it->color_code, 'color_name' => $it->color_name, 'XS'=>0,'S'=>0,'M'=>0,'L'=>0,'XL'=>0];
                                            }
                                            $sz = strtoupper(trim((string)$it->size));
                                            if (!in_array($sz, ['XS','S','M','L','XL'])) $sz = 'M';
                                            $grouped[$key][$sz] = ($grouped[$key][$sz] ?? 0) + (int)$it->quantity;
                                        }
                                        $mapped = array_values(array_map(function($row){
                                            $row['total'] = (int)($row['XS']+$row['S']+$row['M']+$row['L']+$row['XL']);
                                            $row['notes'] = '-';
                                            return $row;
                                        }, $grouped));
                                    @endphp
                                    <button type="button" class="btn btn-light-primary" data-bs-toggle="modal" data-bs-target="#sizeModal"
                                        data-order='@json(["po"=>$order->po_number, "customer"=>$order->customer->name ?? "-"] )'
                                        data-items='@json($mapped)'>
                                        <i class="bi bi-eye me-1"></i>Instruksi Size
                                    </button>
                                    <form method="POST" action="{{ route('sewing.complete', $order->id) }}" class="ms-auto form-complete">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-complete">
                                            Selesaikan Proses Sewing
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Instruksi Size -->
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
    // Live Search & Sort
    var inputSearch = document.getElementById('kanban_search');
    var selectSort = document.getElementById('kanban_sort');
    var contPending = document.getElementById('kanban_pending');
    var contInprog = document.getElementById('kanban_inprogress');

    function iterateAllItems(cb){
        [contPending, contInprog].forEach(function(c){
            if (!c) return;
            c.querySelectorAll('.kanban-item').forEach(cb);
        });
    }

    function applySearch(){
        var q = (inputSearch?.value || '').toLowerCase().trim();
        iterateAllItems(function(card){
            var po = (card.dataset.po || '');
            var cust = (card.dataset.customer || '');
            var match = !q || po.indexOf(q) !== -1 || cust.indexOf(q) !== -1;
            card.style.display = match ? '' : 'none';
        });
    }

    function sortContainer(container, mode){
        if (!container) return;
        var items = Array.prototype.slice.call(container.querySelectorAll('.kanban-item'));
        var cmp;
        if (mode === 'ship') {
            function weight(card){
                var s = (card.dataset.ship || '').toUpperCase();
                return (s === 'AIR' || s === 'NOTS') ? 0 : 1;
            }
            cmp = function(a,b){ return weight(a) - weight(b); };
        } else { // date asc
            cmp = function(a,b){
                var da = new Date(a.dataset.date || '2100-12-31');
                var db = new Date(b.dataset.date || '2100-12-31');
                return da - db;
            };
        }
        items.sort(cmp).forEach(function(n){ container.appendChild(n); });
    }

    function applySort(){
        var mode = (selectSort?.value || 'date');
        sortContainer(contPending, mode);
        sortContainer(contInprog, mode);
    }

    inputSearch && inputSearch.addEventListener('keyup', applySearch);
    selectSort && selectSort.addEventListener('change', function(){
        applySort();
        applySearch();
    });

    applySort();
    applySearch();

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

    // Confirm complete (SweetAlert if available, else confirm)
    document.querySelectorAll('.form-complete').forEach(function(form){
        form.addEventListener('submit', function(e){
            var proceed = true;
            if (window.Swal && Swal.fire) {
                e.preventDefault();
                Swal.fire({
                    title: 'Selesaikan Jahit?',
                    text: 'Yakin jahitan sudah selesai?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Selesai',
                    cancelButtonText: 'Batal'
                }).then(function(result){
                    if (result.isConfirmed) form.submit();
                });
                proceed = false;
            } else {
                proceed = window.confirm('Yakin jahitan sudah selesai?');
                if (!proceed) e.preventDefault();
            }
        });
    });

    // Confirm start process
    document.querySelectorAll('.form-start').forEach(function(form){
        form.addEventListener('submit', function(e){
            var proceed = true;
            if (window.Swal && Swal.fire) {
                e.preventDefault();
                Swal.fire({
                    title: 'Mulai Proses Sewing?',
                    text: 'Yakin mulai menjahit order ini?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Mulai',
                    cancelButtonText: 'Batal'
                }).then(function(result){
                    if (result.isConfirmed) form.submit();
                });
                proceed = false;
            } else {
                proceed = window.confirm('Yakin mulai menjahit order ini?');
                if (!proceed) e.preventDefault();
            }
        });
    });
});
</script>
@endpush

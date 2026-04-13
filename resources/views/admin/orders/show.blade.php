@extends('layouts.master')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">PO Detail</h3>
    <a href="{{ route('orders.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<div class="row g-5">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#po_info_collapse">
                <h3 class="card-title">Info PO</h3>
                <div class="card-toolbar rotate-180">
                    <i class="ki-duotone ki-down fs-1"></i>
                </div>
            </div>
            <div id="po_info_collapse" class="collapse show">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><strong>Order No</strong><div>{{ $order->order_no }}</div></div>
                    <div class="col-md-3"><strong>PO Number</strong><div>{{ $order->po_number }}</div></div>
                    <div class="col-md-3"><strong>Customer</strong><div>{{ $order->customer?->name ?? '-' }}</div></div>
                    <div class="col-md-3"><strong>Export Date</strong><div>{{ $order->export_date ? \Carbon\Carbon::parse($order->export_date)->format('d M Y') : '-' }}</div></div>
                    <div class="col-md-3"><strong>Destination</strong><div>{{ $order->destination_country ?? '-' }}</div></div>
                    <div class="col-md-3"><strong>Ship Mode</strong><div>{{ $order->ship_mode ?? '-' }}</div></div>
                    <div class="col-md-3"><strong>Status</strong>
                        @php
                            $status = strtolower($order->status ?? 'pending');
                            $map = [
                                'pending' => 'secondary',
                                'cutting' => 'info',
                                'on_process_cutting' => 'info',
                                'sewing' => 'primary',
                                'qc' => 'warning',
                                'completed' => 'success',
                            ];
                            $cls = $map[$status] ?? 'secondary';
                        @endphp
                        <div><span class="badge bg-{{ $cls }}">{{ ucfirst($status) }}</span></div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#po_items_collapse">
                <h3 class="card-title">Detail Items</h3>
                <div class="card-toolbar rotate-180">
                    <i class="ki-duotone ki-down fs-1"></i>
                </div>
            </div>
            <div id="po_items_collapse" class="collapse">
            <div class="card-body">
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <tr>
                            <th>Color Code</th>
                            <th>Color Name</th>
                            <th>Size</th>
                            <th class="text-end">Quantity</th>
                        </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                        @forelse($order->orderItems as $it)
                            <tr>
                                <td>{{ $it->color_code ?? '-' }}</td>
                                <td>{{ $it->color_name ?? '-' }}</td>
                                <td>{{ $it->size ?? '-' }}</td>
                                <td class="text-end">{{ number_format($it->quantity ?? 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada item</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header collapsible cursor-pointer rotate" data-bs-toggle="collapse" data-bs-target="#po_tracking_collapse">
                <h3 class="card-title">Tracking Progress</h3>
                <div class="card-toolbar rotate-180">
                    <i class="ki-duotone ki-down fs-1"></i>
                </div>
            </div>
            <div id="po_tracking_collapse" class="collapse">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="timeline">
                            @foreach($stepsStatus as $s)
                                @php
                                    $state = $s['state'];
                                    $trk = $s['track'];
                                    $icon = $state === 'done' ? 'bi-check-circle-fill text-success' : ($state === 'active' ? 'bi-circle-fill text-primary' : 'bi-dot text-secondary');
                                    $titleCls = $state === 'done' ? 'text-success' : ($state === 'active' ? 'text-primary' : 'text-muted');
                                @endphp
                                <div class="timeline-item">
                                    <div class="timeline-line"></div>
                                    <div class="timeline-icon"><i class="bi {{ $icon }} fs-2"></i></div>
                                    <div class="timeline-content d-flex">
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="fw-bold {{ $titleCls }}">{{ $s['label'] }}</span>
                                                @if($trk?->completed_at)
                                                    <span class="badge bg-success ms-2">Selesai</span>
                                                @elseif($state === 'active')
                                                    <span class="badge bg-primary ms-2">Berjalan</span>
                                                @else
                                                    <span class="badge bg-secondary ms-2">Belum Mulai</span>
                                                @endif
                                            </div>
                                            @if($trk)
                                                <div class="text-muted">
                                                    <div>Diproses oleh: {{ $trk->processedBy?->name ?? '-' }}</div>
                                                    <div>Mulai: {{ $trk->started_at ? \Carbon\Carbon::parse($trk->started_at)->format('d M Y H:i') : '-' }}</div>
                                                    <div>Selesai: {{ $trk->completed_at ? \Carbon\Carbon::parse($trk->completed_at)->format('d M Y H:i') : '-' }}</div>
                                                    @if($trk->notes)
                                                        <div>Catatan: {{ $trk->notes }}</div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="text-muted">Belum ada data tracking.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>
@endsection

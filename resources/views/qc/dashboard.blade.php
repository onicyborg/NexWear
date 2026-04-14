@extends('layouts.master')

@section('page_title', 'Dashboard Quality Control')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Dashboard Quality Control</h3>
    <a href="{{ route('qc.queue') }}" class="btn btn-primary">
        <i class="bi bi-clipboard-check me-2"></i>Antrian Inspeksi
    </a>
</div>

<div class="row g-5 mb-5">
    <div class="col-md-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center">
                    <span class="symbol symbol-40px me-3">
                        <span class="symbol-label bg-light-info text-info">
                            <i class="bi bi-hourglass-split fs-2"></i>
                        </span>
                    </span>
                    <div class="flex-grow-1">
                        <div class="text-muted">Pending QC</div>
                        <div class="fs-2 fw-bold">{{ $pendingCount ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center">
                    <span class="symbol symbol-40px me-3">
                        <span class="symbol-label bg-light-success text-success">
                            <i class="bi bi-check2-circle fs-2"></i>
                        </span>
                    </span>
                    <div class="flex-grow-1">
                        <div class="text-muted">Completed Today</div>
                        <div class="fs-2 fw-bold">{{ $completedToday ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center">
                    <span class="symbol symbol-40px me-3">
                        <span class="symbol-label bg-light-warning text-warning">
                            <i class="bi bi-exclamation-triangle fs-2"></i>
                        </span>
                    </span>
                    <div class="flex-grow-1">
                        <div class="text-muted">Rework Today</div>
                        <div class="fs-2 fw-bold">{{ $totalReworkToday ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3">
        <div class="card card-flush h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex align-items-center">
                    <span class="symbol symbol-40px me-3">
                        <span class="symbol-label bg-light-danger text-danger">
                            <i class="bi bi-x-circle fs-2"></i>
                        </span>
                    </span>
                    <div class="flex-grow-1">
                        <div class="text-muted">Reject Today</div>
                        <div class="fs-2 fw-bold">{{ $totalRejectToday ?? 0 }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-flush">
    <div class="card-header">
        <h4 class="card-title mb-0">Recent QC Activity</h4>
        <div class="card-toolbar">
            <a href="{{ route('qc.history') }}" class="btn btn-light">Lihat Riwayat</a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-3">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Nomor PO</th>
                        <th>Customer</th>
                        <th>Tanggal QC</th>
                        <th>Status Akhir</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach(($recentOrders ?? collect()) as $order)
                        @php
                            $final = ((int)($order->qcSummary->qty_rework ?? 0) > 0) ? 'rework_sewing' : 'completed';
                            $badge = $final === 'rework_sewing' ? 'badge-light-danger' : 'badge-light-success';
                            $label = $final === 'rework_sewing' ? 'Rework Sewing' : 'Completed';
                        @endphp
                        <tr>
                            <td class="text-gray-800 fw-bold">{{ $order->po_number ?? '-' }}</td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>{{ optional($order->qcSummary->updated_at)->format('d M Y H:i') ?? '-' }}</td>
                            <td><span class="badge {{ $badge }}">{{ $label }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

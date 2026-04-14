@extends('layouts.master')

@section('page_title','Antrian Inspeksi QC')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Antrian Inspeksi QC</h3>
    <a href="{{ route('qc.dashboard') }}" class="btn btn-light">
        <i class="bi bi-speedometer2 me-2"></i>Dashboard
    </a>
</div>

<div class="card card-flush">
    <div class="card-body py-5">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="qc_orders_table">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Nomor PO</th>
                        <th>Customer</th>
                        <th>Export Date</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach(($orders ?? collect()) as $order)
                        <tr>
                            <td class="text-gray-800 fw-bold">{{ $order->po_number }}</td>
                            <td>{{ $order->customer->name ?? '-' }}</td>
                            <td>{{ optional($order->export_date)->format('d M Y') ?? '-' }}</td>
                            <td class="text-end">
                                <a href="{{ route('qc.inspect', $order->id) }}" class="btn btn-primary">
                                    Mulai Inspeksi
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function(){
        if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
            jQuery('#qc_orders_table').DataTable({ pageLength: 10, ordering: true });
        }
    });
</script>
@endpush

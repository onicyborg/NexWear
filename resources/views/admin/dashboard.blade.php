@extends('layouts.master')

@section('page_title', 'Dashboard Admin')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">Dashboard Admin</h3>
    <div class="d-flex gap-2">
        <a href="{{ route('orders.index') }}" class="btn btn-primary"><i class="bi bi-list"></i> Lihat Orders</a>
    </div>
  </div>

  <div class="row g-5 mb-5">
    <div class="col-md-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="text-muted">Total PO Aktif</div>
          <div class="fs-1 fw-bold">{{ number_format($stats['totalActivePo'] ?? 0) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="text-muted">PO H-3</div>
          <div class="fs-1 fw-bold">{{ number_format($stats['deadlineSoonPo'] ?? 0) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="text-muted">Item Selesai Hari Ini</div>
          <div class="fs-1 fw-bold">{{ number_format($stats['itemsDoneToday'] ?? 0) }}</div>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="text-muted">Total Customer</div>
          <div class="fs-1 fw-bold">{{ number_format($stats['totalCustomers'] ?? 0) }}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-5 mb-5">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header">
          <h3 class="card-title">Distribusi Status PO</h3>
        </div>
        <div class="card-body">
          <div id="po_status_chart" style="height: 320px"></div>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header">
          <h3 class="card-title">PO Urgent (Export Date Terdekat)</h3>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
              <thead>
                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                  <th>PO</th>
                  <th>Customer</th>
                  <th>Export Date</th>
                  <th>Progress</th>
                </tr>
              </thead>
              <tbody class="fw-semibold text-gray-600">
                @forelse(($urgentOrders ?? []) as $o)
                <tr>
                  <td>{{ $o->po_number }}</td>
                  <td>{{ $o->customer?->name ?? '-' }}</td>
                  <td>{{ $o->export_date ? \Carbon\Carbon::parse($o->export_date)->format('d M Y') : '-' }}</td>
                  <td style="min-width: 160px;">
                    <div class="d-flex align-items-center">
                      <div class="progress w-100" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" style="width: {{ (int)($o->progress_percent ?? 0) }}%" aria-valuenow="{{ (int)($o->progress_percent ?? 0) }}" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                      <span class="ms-3 fw-bold">{{ (int)($o->progress_percent ?? 0) }}%</span>
                    </div>
                  </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">Tidak ada data</td></tr>
                @endforelse
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
document.addEventListener('DOMContentLoaded', function(){
  // ApexCharts donut for status distribution
  try {
    const labels = @json(($chart['labels'] ?? []));
    const series = @json(($chart['series'] ?? []));
    if (window.ApexCharts) {
      const el = document.querySelector('#po_status_chart');
      const opt = {
        chart: { type: 'donut', height: 320 },
        labels: labels,
        series: series,
        dataLabels: { enabled: false },
        legend: { position: 'bottom' },
        theme: { mode: document.documentElement.hasAttribute('data-bs-theme') ? (document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark':'light') : 'light' }
      };
      const ch = new window.ApexCharts(el, opt);
      ch.render();
    }
  } catch (e) { console.warn('chart init failed', e); }
});
</script>
@endpush

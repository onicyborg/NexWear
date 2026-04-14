@extends('layouts.master')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
    <h3 class="fw-bold mb-0">System Logs</h3>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table id="system_logs_table" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <th>Time</th>
                        <th>User</th>
                        <th>Method</th>
                        <th>URL</th>
                        <th>Action</th>
                        <th>Table</th>
                        <th>IP</th>
                        <th class="text-end">Detail</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->created_at?->format('d M Y H:i:s') }}</td>
                            <td>{{ $log->user?->name ?? '-' }}</td>
                            <td>
                                @php
                                    $m = strtoupper($log->method ?? '-');
                                    $map = [
                                        'GET' => 'secondary',
                                        'POST' => 'primary',
                                        'PUT' => 'info',
                                        'PATCH' => 'warning',
                                        'DELETE' => 'danger',
                                    ];
                                    $cls = $map[$m] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $cls }}">{{ $m }}</span>
                            </td>
                            <td style="max-width: 360px;" title="{{ $log->url }}">
                                <div class="text-truncate">{{ $log->url }}</div>
                            </td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->table_name }}</td>
                            <td>{{ $log->ip_address ?? '-' }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-light btn-sm btnLogDetail"
                                    data-id="{{ $log->id }}"
                                    data-time="{{ $log->created_at?->format('d M Y H:i:s') }}"
                                    data-user="{{ $log->user?->name ?? '-' }}"
                                    data-method="{{ $log->method }}"
                                    data-url="{{ $log->url }}"
                                    data-action="{{ $log->action }}"
                                    data-table="{{ $log->table_name }}"
                                    data-ip="{{ $log->ip_address }}"
                                    data-request='@json($log->request_payload)'
                                    data-old='@json($log->old_values)'
                                    data-new='@json($log->new_values)'>
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="logDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Log Detail</h5>
        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <div class="modal-body">
        <div class="row g-3 mb-3">
          <div class="col-md-4"><strong>Time</strong><div id="dlg_time">-</div></div>
          <div class="col-md-4"><strong>User</strong><div id="dlg_user">-</div></div>
          <div class="col-md-4"><strong>IP</strong><div id="dlg_ip">-</div></div>
          <div class="col-md-4"><strong>Method</strong><div id="dlg_method">-</div></div>
          <div class="col-md-8"><strong>URL</strong><div id="dlg_url" class="text-break">-</div></div>
          <div class="col-md-4"><strong>Action</strong><div id="dlg_action">-</div></div>
          <div class="col-md-8"><strong>Table</strong><div id="dlg_table">-</div></div>
        </div>
        <div class="mb-3">
          <strong>Request Payload</strong>
          <pre class="bg-light p-3 rounded" id="dlg_request" style="max-height: 240px; overflow:auto;">-</pre>
        </div>
        <div class="mb-3">
          <strong>Old Values</strong>
          <pre class="bg-light p-3 rounded" id="dlg_old" style="max-height: 240px; overflow:auto;">-</pre>
        </div>
        <div class="mb-0">
          <strong>New Values</strong>
          <pre class="bg-light p-3 rounded" id="dlg_new" style="max-height: 240px; overflow:auto;">-</pre>
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
    function pretty(data){
      try { return JSON.stringify(data, null, 2); } catch(e) { return String(data ?? '-'); }
    }

    if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
      const $ = window.jQuery;
      $('#system_logs_table').DataTable({ pageLength: 10, ordering: true, order: [[0, 'desc']] });

      $('#system_logs_table').on('click', '.btnLogDetail', function(){
        const $btn = $(this);
        const modalEl = document.getElementById('logDetailModal');
        document.getElementById('dlg_time').textContent = $btn.attr('data-time') || '-';
        document.getElementById('dlg_user').textContent = $btn.attr('data-user') || '-';
        document.getElementById('dlg_ip').textContent = $btn.attr('data-ip') || '-';
        (function(){
          const m = ($btn.attr('data-method') || '-').toUpperCase();
          const map = { GET:'secondary', POST:'primary', PUT:'info', PATCH:'warning', DELETE:'danger' };
          const cls = map[m] || 'secondary';
          document.getElementById('dlg_method').innerHTML = `<span class="badge bg-${cls}">${m}</span>`;
        })();
        document.getElementById('dlg_url').textContent = $btn.attr('data-url') || '-';
        document.getElementById('dlg_action').textContent = $btn.attr('data-action') || '-';
        document.getElementById('dlg_table').textContent = $btn.attr('data-table') || '-';

        let req = $btn.attr('data-request');
        let oldv = $btn.attr('data-old');
        let newv = $btn.attr('data-new');
        try { req = JSON.parse(req); } catch(e) {}
        try { oldv = JSON.parse(oldv); } catch(e) {}
        try { newv = JSON.parse(newv); } catch(e) {}

        document.getElementById('dlg_request').textContent = pretty(req);
        document.getElementById('dlg_old').textContent = pretty(oldv);
        document.getElementById('dlg_new').textContent = pretty(newv);

        const modal = new bootstrap.Modal(modalEl);
        modal.show();
      });
    } else {
      // Fallback without jQuery/DataTables
      document.querySelectorAll('.btnLogDetail').forEach(btn => {
        btn.addEventListener('click', function(){
          const modalEl = document.getElementById('logDetailModal');
          document.getElementById('dlg_time').textContent = this.getAttribute('data-time') || '-';
          document.getElementById('dlg_user').textContent = this.getAttribute('data-user') || '-';
          document.getElementById('dlg_ip').textContent = this.getAttribute('data-ip') || '-';
          (function(){
            const m = (this.getAttribute('data-method') || '-').toUpperCase();
            const map = { GET:'secondary', POST:'primary', PUT:'info', PATCH:'warning', DELETE:'danger' };
            const cls = map[m] || 'secondary';
            document.getElementById('dlg_method').innerHTML = `<span class=\"badge bg-${cls}\">${m}</span>`;
          }).call(this);
          document.getElementById('dlg_url').textContent = this.getAttribute('data-url') || '-';
          document.getElementById('dlg_action').textContent = this.getAttribute('data-action') || '-';
          document.getElementById('dlg_table').textContent = this.getAttribute('data-table') || '-';

          let req = this.getAttribute('data-request');
          let oldv = this.getAttribute('data-old');
          let newv = this.getAttribute('data-new');
          try { req = JSON.parse(req); } catch(e) {}
          try { oldv = JSON.parse(oldv); } catch(e) {}
          try { newv = JSON.parse(newv); } catch(e) {}

          document.getElementById('dlg_request').textContent = pretty(req);
          document.getElementById('dlg_old').textContent = pretty(oldv);
          document.getElementById('dlg_new').textContent = pretty(newv);

          const modal = new bootstrap.Modal(modalEl);
          modal.show();
        });
      });
    }
  });
</script>
@endpush

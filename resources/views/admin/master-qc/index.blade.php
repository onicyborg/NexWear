@extends('layouts.master')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Master QC KPI</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kpiModal" id="btnAddKpi">
            <i class="bi bi-plus-lg me-2"></i>Tambah KPI
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3 mb-5 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Filter Category</label>
                    <select id="dt_category" class="form-select">
                        <option value="">Semua</option>
                        @php
                            $cats = $kpis->pluck('category')->filter()->unique()->values();
                        @endphp
                        @foreach($cats as $cat)
                            <option value="{{ $cat }}">{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pencarian (Instruction)</label>
                    <input type="text" id="dt_search" class="form-control" placeholder="Cari instruction...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kpi_table">
                    <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <tr>
                            <th>Category</th>
                            <th>Instruction</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach ($kpis as $k)
                            <tr>
                                <td>{{ $k->category }}</td>
                                <td>{{ $k->instruction }}</td>
                                <td>
                                    @if($k->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-light btn-active-light-primary btn-sm me-2 btnEditKpi"
                                        data-id="{{ $k->id }}"
                                        data-category="{{ $k->category }}"
                                        data-instruction="{{ $k->instruction }}"
                                        data-active="{{ $k->is_active ? 1 : 0 }}">
                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btnDeleteKpi" data-id="{{ $k->id }}" data-name="{{ $k->instruction }}">
                                        <i class="bi bi-trash me-1"></i>Hapus
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="kpiModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="kpiForm" method="POST" action="{{ route('master-qc.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="kpiFormMethod" value="POST">
                    <input type="hidden" name="id" id="kpi_id" value="{{ old('id') }}">
                    <input type="hidden" name="form_mode" id="form_mode" value="{{ old('form_mode','create') }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="kpiModalTitle">Tambah KPI</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-5">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <input type="text" name="category" id="kpi_category" value="{{ old('category') }}" class="form-control @error('category') is-invalid @enderror">
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-5">
                            <label class="form-label">Instruction <span class="text-danger">*</span></label>
                            <textarea name="instruction" id="kpi_instruction" class="form-control @error('instruction') is-invalid @enderror" rows="3">{{ old('instruction') }}</textarea>
                            @error('instruction')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="kpi_is_active" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                            <label class="form-check-label" for="kpi_is_active">Aktif</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveKpi">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="deleteKpiForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let table = null;
            if (window.jQuery && $.fn.DataTable) {
                table = $('#kpi_table').DataTable({ pageLength: 10, ordering: true });
            }

            // Filters
            if (table) {
                $.fn.dataTable.ext.search.push(function (settings, data) {
                    if (settings.nTable.id !== 'kpi_table') return true;
                    const catVal = document.getElementById('dt_category')?.value || '';
                    const rowCat = (data[0] || '').toString();
                    return !catVal || rowCat === catVal;
                });
                document.getElementById('dt_category')?.addEventListener('change', () => table.draw());
                document.getElementById('dt_search')?.addEventListener('input', function(){
                    table?.search(this.value || '').draw();
                });
            }

            // Add
            document.getElementById('btnAddKpi')?.addEventListener('click', function(){
                const f = document.getElementById('kpiForm');
                f.action = @json(route('master-qc.store'));
                document.getElementById('kpiFormMethod').value = 'POST';
                document.getElementById('form_mode').value = 'create';
                document.getElementById('kpiModalTitle').textContent = 'Tambah KPI';
                f.reset();
                document.getElementById('kpi_is_active').checked = true;
            });

            // Edit
            document.querySelectorAll('.btnEditKpi').forEach(btn => {
                btn.addEventListener('click', function(){
                    const id = this.getAttribute('data-id');
                    const cat = this.getAttribute('data-category') || '';
                    const ins = this.getAttribute('data-instruction') || '';
                    const act = this.getAttribute('data-active') === '1';

                    const f = document.getElementById('kpiForm');
                    f.action = @json(route('master-qc.update', '__ID__')).replace('__ID__', id);
                    document.getElementById('kpiFormMethod').value = 'PUT';
                    document.getElementById('form_mode').value = 'edit';
                    document.getElementById('kpiModalTitle').textContent = 'Edit KPI';
                    document.getElementById('kpi_id').value = id;
                    document.getElementById('kpi_category').value = cat;
                    document.getElementById('kpi_instruction').value = ins;
                    document.getElementById('kpi_is_active').checked = act;
                    const modal = new bootstrap.Modal(document.getElementById('kpiModal'));
                    modal.show();
                });
            });

            // Delete
            document.querySelectorAll('.btnDeleteKpi').forEach(btn => {
                btn.addEventListener('click', function(){
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    Swal.fire({
                        title: 'Hapus KPI?',
                        text: `Yakin ingin menghapus ${name}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((res) => {
                        if (res.isConfirmed) {
                            const form = document.getElementById('deleteKpiForm');
                            form.action = @json(route('master-qc.destroy', '__ID__')).replace('__ID__', id);
                            form.submit();
                        }
                    });
                });
            });

            // Flash notifications
            @if (session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 1800, showConfirmButton: false });
            @endif
            @if (session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')), timer: 2000, showConfirmButton: false });
            @endif

            // Auto-open modal on validation errors
            @if ($errors->any())
                const mode = @json(old('form_mode','create'));
                const modalEl = document.getElementById('kpiModal');
                const modal = new bootstrap.Modal(modalEl);
                document.getElementById('kpiModalTitle').textContent = mode === 'edit' ? 'Edit KPI' : 'Tambah KPI';
                modal.show();
            @endif
        });
    </script>
@endpush

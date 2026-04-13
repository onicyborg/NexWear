@extends('layouts.master')

@section('page_title', 'Master Customers')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Data Customers</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#customerModal" id="btnAddCustomer">
            <i class="bi bi-plus-lg me-2"></i>Tambah Customer
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row g-3 mb-5 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Pencarian (Kode/Nama)</label>
                    <input type="text" id="dt_search" class="form-control" placeholder="Cari kode atau nama...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select id="dt_status" class="form-select">
                        <option value="">Semua</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="customers_table">
                    <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Alamat</th>
                            <th>Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach ($customers as $c)
                            <tr>
                                <td>{{ $c->customer_code }}</td>
                                <td>{{ $c->name }}</td>
                                <td>{{ $c->email ?? '-' }}</td>
                                <td>{{ $c->phone ?? '-' }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($c->address ?? '-', 50) }}</td>
                                <td>
                                    @if($c->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-light btn-active-light-primary btn-sm me-2 btnEditCustomer"
                                        data-id="{{ $c->id }}"
                                        data-code="{{ $c->customer_code }}"
                                        data-name="{{ $c->name }}"
                                        data-email="{{ $c->email }}"
                                        data-phone="{{ $c->phone }}"
                                        data-address="{{ $c->address }}"
                                        data-active="{{ $c->is_active ? 1 : 0 }}">
                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btnDeleteCustomer" data-id="{{ $c->id }}" data-name="{{ $c->name }}">
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
    <div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="customerForm" method="POST" action="{{ route('customers.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="customerFormMethod" value="POST">
                    <input type="hidden" name="id" id="customer_id" value="{{ old('id') }}">
                    <input type="hidden" name="form_mode" id="form_mode" value="{{ old('form_mode','create') }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="customerModalTitle">Tambah Customer</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-5">
                            <label class="form-label">Kode <span class="text-danger">*</span></label>
                            <input type="text" name="customer_code" id="customer_code" value="{{ old('customer_code') }}" class="form-control @error('customer_code') is-invalid @enderror">
                            @error('customer_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-5">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="customer_name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="customer_email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Telepon</label>
                                <input type="text" name="phone" id="customer_phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mt-5">
                            <label class="form-label">Alamat</label>
                            <textarea name="address" id="customer_address" class="form-control @error('address') is-invalid @enderror" rows="3">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check form-switch mt-5">
                            <input class="form-check-input" type="checkbox" value="1" id="customer_active" name="is_active" {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label" for="customer_active">Aktif</label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveCustomer">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Hidden delete form -->
    <form id="deleteCustomerForm" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // DataTable init
            let table = null;
            if (window.jQuery && $.fn.DataTable) {
                table = $('#customers_table').DataTable({ pageLength: 10, ordering: true });
            }

            const customerModal = document.getElementById('customerModal');
            const customerForm = document.getElementById('customerForm');
            const formMethod = document.getElementById('customerFormMethod');
            const formMode = document.getElementById('form_mode');
            const title = document.getElementById('customerModalTitle');
            const idInput = document.getElementById('customer_id');
            const codeInput = document.getElementById('customer_code');
            const nameInput = document.getElementById('customer_name');
            const emailInput = document.getElementById('customer_email');
            const phoneInput = document.getElementById('customer_phone');
            const addressInput = document.getElementById('customer_address');
            const activeInput = document.getElementById('customer_active');

            // Reset to create mode
            function setCreateMode() {
                customerForm.action = @json(route('customers.store'));
                formMethod.value = 'POST';
                formMode.value = 'create';
                title.textContent = 'Tambah Customer';
                idInput.value = '';
                if (!@json(old('customer_code'))) codeInput.value = '';
                if (!@json(old('name'))) nameInput.value = '';
                if (!@json(old('email'))) emailInput.value = '';
                if (!@json(old('phone'))) phoneInput.value = '';
                if (!@json(old('address'))) addressInput.value = '';
                activeInput.checked = true;
            }

            // Switch to edit mode with data
            function setEditMode(row) {
                const id = row.getAttribute('data-id');
                const code = row.getAttribute('data-code') || '';
                const name = row.getAttribute('data-name') || '';
                const email = row.getAttribute('data-email') || '';
                const phone = row.getAttribute('data-phone') || '';
                const address = row.getAttribute('data-address') || '';
                const active = row.getAttribute('data-active') === '1';

                customerForm.action = @json(route('customers.update', '__ID__')).replace('__ID__', id);
                formMethod.value = 'PUT';
                formMode.value = 'edit';
                title.textContent = 'Edit Customer';
                idInput.value = id;
                codeInput.value = code;
                nameInput.value = name;
                emailInput.value = email;
                phoneInput.value = phone;
                addressInput.value = address;
                activeInput.checked = active;
            }

            // Add button
            document.getElementById('btnAddCustomer')?.addEventListener('click', function(){ setCreateMode(); });

            // Edit buttons
            document.querySelectorAll('.btnEditCustomer').forEach(btn => {
                btn.addEventListener('click', function(){
                    setEditMode(this);
                    const modal = bootstrap.Modal.getOrCreateInstance(customerModal);
                    modal.show();
                });
            });

            // Delete
            document.querySelectorAll('.btnDeleteCustomer').forEach(btn => {
                btn.addEventListener('click', function(){
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    Swal.fire({
                        title: 'Hapus Customer?',
                        text: `Yakin ingin menghapus ${name}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then((res) => {
                        if (res.isConfirmed) {
                            const form = document.getElementById('deleteCustomerForm');
                            form.action = @json(route('customers.destroy', '__ID__')).replace('__ID__', id);
                            form.submit();
                        }
                    });
                });
            });

            if (table) {
                $.fn.dataTable.ext.search.push(function (settings, data) {
                    if (settings.nTable.id !== 'customers_table') return true;
                    const q = (document.getElementById('dt_search')?.value || '').toLowerCase();
                    const statusVal = document.getElementById('dt_status')?.value || '';
                    const code = (data[0] || '').toLowerCase();
                    const name = (data[1] || '').toLowerCase();
                    const statusText = (data[5] || '').toLowerCase();
                    const rowIsActive = statusText.includes('inactive') ? '0' : (statusText.includes('active') ? '1' : '');
                    const matchesSearch = !q || code.includes(q) || name.includes(q);
                    const matchesStatus = !statusVal || rowIsActive === statusVal;
                    return matchesSearch && matchesStatus;
                });

                document.getElementById('dt_search')?.addEventListener('input', () => table.draw());
                document.getElementById('dt_status')?.addEventListener('change', () => table.draw());
            }

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
                const modal = bootstrap.Modal.getOrCreateInstance(customerModal);
                if (mode === 'edit' && @json(old('id'))) {
                    // keep form pointing to update with old id
                    customerForm.action = @json(route('customers.update', '__ID__')).replace('__ID__', @json(old('id')));
                    formMethod.value = 'PUT';
                    formMode.value = 'edit';
                    title.textContent = 'Edit Customer';
                } else {
                    setCreateMode();
                }
                modal.show();
            @endif
        });
    </script>
@endpush

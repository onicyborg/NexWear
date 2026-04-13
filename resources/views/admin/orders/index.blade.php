@extends('layouts.master')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">PO List</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#orderModal" id="btnAddOrder">
            <i class="bi bi-plus-lg me-2"></i>Tambah Order
        </button>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="orders_table">
                    <thead class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                        <tr>
                            <th>Order No</th>
                            <th>PO Number</th>
                            <th>Customer</th>
                            <th>Export Date</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach ($orders as $o)
                            <tr>
                                <td>{{ $o->order_no }}</td>
                                <td>{{ $o->po_number }}</td>
                                <td>{{ $o->customer?->name ?? '-' }}</td>
                                <td>{{ $o->export_date ? \Carbon\Carbon::parse($o->export_date)->format('d M Y') : '-' }}</td>
                                <td>
                                    @php
                                        $status = strtolower($o->status ?? 'pending');
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
                                    <span class="badge bg-{{ $cls }}">{{ ucfirst($status) }}</span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('orders.show', $o->id) }}" class="btn btn-light btn-active-light-primary btn-sm me-2">
                                        <i class="bi bi-eye me-1"></i>Detail
                                    </a>
                                    <button type="button" class="btn btn-light btn-active-light-primary btn-sm me-2 btnEditOrder"
                                        data-id="{{ $o->id }}"
                                        data-customer_id="{{ $o->customer_id }}"
                                        data-export_date="{{ $o->export_date ? $o->export_date->format('Y-m-d') : '' }}"
                                        data-destination_country="{{ $o->destination_country }}"
                                        data-ship_mode="{{ $o->ship_mode }}"
                                        data-items="{{ $o->orderItems->map(function($it){ return [
                                            'color_code' => $it->color_code,
                                            'color_name' => $it->color_name,
                                            'size' => $it->size,
                                            'quantity' => $it->quantity,
                                        ]; })->values()->toJson() }}">
                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm btnDeleteOrder" data-id="{{ $o->id }}" data-no="{{ $o->order_no }}">
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

    <form id="deleteOrderForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- Modal Create/Edit -->
    <div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="orderForm" method="POST" action="{{ route('orders.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="orderFormMethod" value="POST">
                    <input type="hidden" name="id" id="order_id" value="{{ old('id') }}">
                    <input type="hidden" name="form_mode" id="form_mode" value="{{ old('form_mode','create') }}">

                    <div class="modal-header">
                        <h5 class="modal-title" id="orderModalTitle">Tambah Order</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label">Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror">
                                    <option value="">Pilih Customer</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->customer_code }})</option>
                                    @endforeach
                                </select>
                                @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Export Date</label>
                                <input type="date" name="export_date" id="export_date" value="{{ old('export_date') }}" class="form-control @error('export_date') is-invalid @enderror">
                                @error('export_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Destination</label>
                                <input type="text" name="destination_country" id="destination_country" value="{{ old('destination_country') }}" class="form-control @error('destination_country') is-invalid @enderror">
                                @error('destination_country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Ship Mode</label>
                                <input type="text" name="ship_mode" id="ship_mode" value="{{ old('ship_mode') }}" class="form-control @error('ship_mode') is-invalid @enderror">
                                @error('ship_mode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label d-flex justify-content-between align-items-center">Detail Ukuran/Item
                                    <button type="button" class="btn btn-sm btn-light-primary" id="btnAddItem"><i class="bi bi-plus-lg me-1"></i>Tambah Baris</button>
                                </label>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle" id="order_items_table">
                                        <thead class="text-muted">
                                            <tr>
                                                <th style="width:18%">Color Code</th>
                                                <th style="width:24%">Color Name</th>
                                                <th style="width:18%">Size</th>
                                                <th style="width:18%">Quantity</th>
                                                <th style="width:12%" class="text-end">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="order_items_tbody"></tbody>
                                    </table>
                                </div>
                                @error('item_size')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveOrder">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        if (window.jQuery && $.fn.DataTable) {
            $('#orders_table').DataTable({ pageLength: 10, ordering: true });
        }

        const orderModalEl = document.getElementById('orderModal');
        const orderForm = document.getElementById('orderForm');
        const orderFormMethod = document.getElementById('orderFormMethod');
        const orderId = document.getElementById('order_id');
        const formMode = document.getElementById('form_mode');
        const orderModalTitle = document.getElementById('orderModalTitle');

        const fCustomer = document.getElementById('customer_id');
        const fExportDate = document.getElementById('export_date');
        const fDest = document.getElementById('destination_country');
        const fShip = document.getElementById('ship_mode');

        function clearValidation(scope){
            (scope || document).querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            (scope || document).querySelectorAll('.invalid-feedback').forEach(el => el.textContent = el.textContent);
        }

        // Items dynamic rows
        const itemsTbody = document.getElementById('order_items_tbody');
        const btnAddItem = document.getElementById('btnAddItem');
        function addItemRow(data){
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><input type="text" name="item_color_code[]" class="form-control form-control-sm" value="${data?.color_code ?? ''}"></td>
                <td><input type="text" name="item_color_name[]" class="form-control form-control-sm" value="${data?.color_name ?? ''}"></td>
                <td><input type="text" name="item_size[]" class="form-control form-control-sm" value="${data?.size ?? ''}" required></td>
                <td><input type="number" min="0" name="item_quantity[]" class="form-control form-control-sm" value="${data?.quantity ?? ''}" required></td>
                <td class="text-end">
                    <button type="button" class="btn btn-sm btn-icon btn-light-danger btnRemoveItem" title="Hapus"><i class="bi bi-x-lg"></i></button>
                </td>
            `;
            itemsTbody.appendChild(tr);
            tr.querySelector('.btnRemoveItem').addEventListener('click', () => tr.remove());
        }
        btnAddItem?.addEventListener('click', () => addItemRow({}));

        document.getElementById('btnAddOrder')?.addEventListener('click', function(){
            clearValidation(orderForm);
            orderForm.action = @json(route('orders.store'));
            orderFormMethod.value = 'POST';
            formMode.value = 'create';
            orderModalTitle.textContent = 'Tambah Order';
            orderId.value = '';
            fCustomer.value = '';
            fExportDate.value = '';
            fDest.value = '';
            fShip.value = '';
            itemsTbody.innerHTML = '';
            addItemRow({});
        });

        document.querySelectorAll('.btnEditOrder').forEach(btn => {
            btn.addEventListener('click', function(){
                clearValidation(orderForm);
                const id = this.getAttribute('data-id');
                orderForm.action = @json(route('orders.update', '__ID__')).replace('__ID__', id);
                orderFormMethod.value = 'PUT';
                formMode.value = 'edit';
                orderModalTitle.textContent = 'Edit Order';
                orderId.value = id;
                fCustomer.value = this.getAttribute('data-customer_id') || '';
                fExportDate.value = this.getAttribute('data-export_date') || '';
                fDest.value = this.getAttribute('data-destination_country') || '';
                fShip.value = this.getAttribute('data-ship_mode') || '';
                // populate items
                itemsTbody.innerHTML = '';
                try {
                    const items = JSON.parse(this.getAttribute('data-items') || '[]');
                    if (Array.isArray(items) && items.length) {
                        items.forEach(it => addItemRow(it));
                    } else {
                        addItemRow({});
                    }
                } catch(e){ addItemRow({}); }
                const modal = new bootstrap.Modal(orderModalEl);
                modal.show();
            });
        });

        document.querySelectorAll('.btnDeleteOrder').forEach(btn => {
            btn.addEventListener('click', function(){
                const id = this.getAttribute('data-id');
                const no = this.getAttribute('data-no');
                Swal.fire({
                    title: 'Hapus Order?',
                    text: `Yakin ingin menghapus ${no}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((res) => {
                    if (res.isConfirmed) {
                        const form = document.getElementById('deleteOrderForm');
                        form.action = @json(route('orders.destroy', '__ID__')).replace('__ID__', id);
                        form.submit();
                    }
                });
            });
        });

        @if (session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil', text: @json(session('success')), timer: 1800, showConfirmButton: false });
        @endif
        @if (session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal', text: @json(session('error')), timer: 2000, showConfirmButton: false });
        @endif

        @if ($errors->any())
            // Auto-open modal with correct mode after validation errors
            try {
                const mode = @json(old('form_mode','create'));
                const id = @json(old('id'));
                if (mode === 'edit' && id) {
                    orderForm.action = @json(route('orders.update', '__ID__')).replace('__ID__', id);
                    orderFormMethod.value = 'PUT';
                    formMode.value = 'edit';
                    orderModalTitle.textContent = 'Edit Order';
                    orderId.value = id;
                } else {
                    orderForm.action = @json(route('orders.store'));
                    orderFormMethod.value = 'POST';
                    formMode.value = 'create';
                    orderModalTitle.textContent = 'Tambah Order';
                    orderId.value = '';
                }
                // Repopulate items from old input
                itemsTbody.innerHTML = '';
                const oldSizes = @json(old('item_size', []));
                const oldQtys = @json(old('item_quantity', []));
                const oldCodes = @json(old('item_color_code', []));
                const oldNames = @json(old('item_color_name', []));
                if (oldSizes.length) {
                    oldSizes.forEach((sz, i) => addItemRow({
                        size: sz || '',
                        quantity: oldQtys?.[i] || '',
                        color_code: oldCodes?.[i] || '',
                        color_name: oldNames?.[i] || '',
                    }));
                } else {
                    addItemRow({});
                }
                const modal = new bootstrap.Modal(orderModalEl);
                modal.show();
            } catch (e) {}
        @endif
    });
</script>
@endpush

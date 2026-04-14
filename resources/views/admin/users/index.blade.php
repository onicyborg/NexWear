@extends('layouts.master')

@section('page_title', 'Manage Users')

@push('styles')
@endpush

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-5">
        <h3 class="fw-bold mb-0">Manage Users</h3>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" id="btnAddUser">
            <i class="bi bi-plus-lg me-2"></i>Add New
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-flush">
        <div class="card-body py-5">
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5" id="users_table">
                    <thead>
                        <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-gray-600">
                        @foreach($users as $user)
                            @php
                                $roleName = is_object($user->role ?? null) ? ($user->role->name ?? (string)($user->role)) : (string)($user->role ?? '');
                                $roleMap = ['Admin' => 'primary', 'Cutting' => 'info', 'Sewing' => 'warning', 'QC' => 'success'];
                                $roleColor = $roleMap[$roleName] ?? 'secondary';
                            @endphp
                            <tr>
                                <td class="text-gray-800 fw-bold">{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge badge-light-{{ $roleColor }}">{{ $roleName ?: '-' }}</span></td>
                                <td class="text-end">
                                    <button type="button"
                                        class="btn btn-light btn-active-light-primary btn-sm me-2 btnEditUser"
                                        data-bs-toggle="modal" data-bs-target="#userModal"
                                        data-mode="edit"
                                        data-update_url="{{ route('users.update', $user->id) }}"
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-role="{{ $roleName }}">
                                        <i class="bi bi-pencil-square me-1"></i>Edit
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#confirmDeleteModal"
                                        data-delete_url="{{ route('users.destroy', $user->id) }}"
                                        data-name="{{ $user->name }}">
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

    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="userForm" method="POST" action="{{ route('users.store') }}">
                    @csrf
                    <input type="hidden" name="_method" id="userFormMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalTitle">Tambah User</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-5">
                            <div class="col-md-6">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control" name="name" id="user_name" value="{{ old('name') }}" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="user_email" value="{{ old('email') }}" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="user_password" />
                                <small class="text-muted">Isi jika ingin mengganti/membuat password. Kosongkan saat edit jika tidak ingin diganti.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select class="form-select" name="role" id="user_role" required>
                                    @foreach(\App\Enums\UserRole::cases() as $r)
                                        @php $val = $r->value ?? $r->name; @endphp
                                        <option value="{{ $val }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="btnSaveUser">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title">Hapus User</h5>
                        <button type="button" class="btn btn-sm btn-icon" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Yakin ingin menghapus user <strong id="delete_name">-</strong>?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if(session('success'))
        <script>
            (function(){
                var msg = @json(session('success'));
                if (window.toastr && toastr.success) { toastr.success(msg); }
                else { console.log('SUCCESS:', msg); }
            })();
        </script>
    @endif

    @if(session('error'))
        <script>
            (function(){
                var msg = @json(session('error'));
                if (window.toastr && toastr.error) { toastr.error(msg); }
                else { console.error('ERROR:', msg); }
            })();
        </script>
    @endif

    @if($errors && $errors->any())
        <script>
            (function(){
                var errs = @json($errors->all());
                var msg = errs.join('\n');
                if (window.toastr && toastr.error) { toastr.error(msg); }
                else { console.error('ERRORS:', msg); }
            })();
        </script>
    @endif
@endsection

@push('scripts')
    <script>
        (function(){
            document.addEventListener('DOMContentLoaded', function () {
                // Init DataTable
                if (window.jQuery && jQuery.fn && jQuery.fn.DataTable) {
                    jQuery('#users_table').DataTable({ pageLength: 10, ordering: true });
                }

                var userModalEl = document.getElementById('userModal');
                var userForm = document.getElementById('userForm');
                var userFormMethod = document.getElementById('userFormMethod');
                var userModalTitle = document.getElementById('userModalTitle');

                var fName = document.getElementById('user_name');
                var fEmail = document.getElementById('user_email');
                var fPassword = document.getElementById('user_password');
                var fRole = document.getElementById('user_role');
                var fActive = document.getElementById('user_active');

                // Add New
                document.getElementById('btnAddUser')?.addEventListener('click', function(){
                    if (!userForm) return;
                    userForm.action = @json(route('users.store'));
                    userFormMethod.value = 'POST';
                    userModalTitle.textContent = 'Tambah User';
                    fName && (fName.value = '');
                    fEmail && (fEmail.value = '');
                    fPassword && (fPassword.value = '');
                    fRole && (fRole.selectedIndex = 0);
                    fActive && (fActive.value = '1');
                });

                // Edit (delegated for DataTables)
                if (window.jQuery) {
                    jQuery('#users_table').on('click', '.btnEditUser', function(){
                        if (!userForm) return;
                        userForm.action = this.getAttribute('data-update_url') || userForm.action;
                        userFormMethod.value = 'PUT';
                        userModalTitle.textContent = 'Edit User';
                        fName && (fName.value = this.getAttribute('data-name') || '');
                        fEmail && (fEmail.value = this.getAttribute('data-email') || '');
                        fPassword && (fPassword.value = '');
                        var roleVal = this.getAttribute('data-role') || '';
                        if (fRole) {
                            Array.from(fRole.options).forEach(function(opt){ opt.selected = (opt.value == roleVal || opt.text == roleVal); });
                        }
                        fActive && (fActive.value = this.getAttribute('data-active') === '0' ? '0' : '1');
                    });
                } else {
                    document.querySelectorAll('.btnEditUser').forEach(function(btn){
                        btn.addEventListener('click', function(){
                            if (!userForm) return;
                            userForm.action = this.getAttribute('data-update_url') || userForm.action;
                            userFormMethod.value = 'PUT';
                            userModalTitle.textContent = 'Edit User';
                            fName && (fName.value = this.getAttribute('data-name') || '');
                            fEmail && (fEmail.value = this.getAttribute('data-email') || '');
                            fPassword && (fPassword.value = '');
                            var roleVal = this.getAttribute('data-role') || '';
                            if (fRole) {
                                Array.from(fRole.options).forEach(function(opt){ opt.selected = (opt.value == roleVal || opt.text == roleVal); });
                            }
                            fActive && (fActive.value = this.getAttribute('data-active') === '0' ? '0' : '1');
                        });
                    });
                }

                // Delete modal
                var deleteModalEl = document.getElementById('confirmDeleteModal');
                deleteModalEl && deleteModalEl.addEventListener('show.bs.modal', function (event) {
                    var btn = event.relatedTarget;
                    var url = btn && btn.getAttribute('data-delete_url');
                    var name = btn && btn.getAttribute('data-name');
                    var deleteForm = document.getElementById('deleteForm');
                    var deleteName = document.getElementById('delete_name');
                    deleteForm && url && (deleteForm.action = url);
                    deleteName && (deleteName.textContent = name || '-');
                });
            });
        })();
    </script>
@endpush

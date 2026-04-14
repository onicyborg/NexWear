@php use App\Enums\UserRole; @endphp

<div class="app-sidebar-menu overflow-hidden flex-column-fluid">
    <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
        <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true" data-kt-scroll-activate="true"
            data-kt-scroll-height="auto" data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-save-state="true">

            <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6" id="#kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">

                @php $role = auth()->user()?->role; @endphp

                @if ($role === UserRole::Admin)
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                            <span class="menu-icon"><i class="bi bi-speedometer2 fs-2"></i></span>
                            <span class="menu-title">Dashboard Admin</span>
                        </a>
                    </div>

                    <div class="menu-content">
                        <div class="separator my-3"></div>
                        <span class="menu-heading text-uppercase text-muted fs-8">Master Data</span>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('admin/users*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <span class="menu-icon"><i class="bi bi-person-gear fs-2"></i></span>
                            <span class="menu-title">Users</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('admin/customers*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                            <span class="menu-icon"><i class="bi bi-people fs-2"></i></span>
                            <span class="menu-title">Customers</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('admin/master-qc*') ? 'active' : '' }}" href="{{ route('master-qc.index') }}">
                            <span class="menu-icon"><i class="bi bi-ui-checks-grid fs-2"></i></span>
                            <span class="menu-title">Master QC KPI</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('admin/orders*') ? 'active' : '' }}" href="{{ route('orders.index') }}">
                            <span class="menu-icon"><i class="bi bi-card-list fs-2"></i></span>
                            <span class="menu-title">PO List</span>
                        </a>
                    </div>

                    <div class="menu-content">
                        <div class="separator my-3"></div>
                        <span class="menu-heading text-uppercase text-muted fs-8">System</span>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->is('admin/system-logs*') ? 'active' : '' }}" href="{{ route('system-logs.index') }}">
                            <span class="menu-icon"><i class="bi bi-activity fs-2"></i></span>
                            <span class="menu-title">System Logs</span>
                        </a>
                    </div>

                @elseif ($role === UserRole::Cutting)
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('cutting.dashboard') ? 'active' : '' }}" href="{{ route('cutting.dashboard') }}">
                            <span class="menu-icon"><i class="bi bi-scissors fs-2"></i></span>
                            <span class="menu-title">Antrian Cutting</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('cutting.history') ? 'active' : '' }}" href="{{ route('cutting.history') }}">
                            <span class="menu-icon"><i class="bi bi-clock-history fs-2"></i></span>
                            <span class="menu-title">Riwayat Cutting</span>
                        </a>
                    </div>

                @elseif ($role === UserRole::Sewing)
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('sewing.dashboard') ? 'active' : '' }}" href="{{ route('sewing.dashboard') }}">
                            <span class="menu-icon"><i class="bi bi-tools fs-2"></i></span>
                            <span class="menu-title">Antrian Sewing</span>
                        </a>
                    </div>

                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('sewing.history') ? 'active' : '' }}" href="{{ route('sewing.history') }}">
                            <span class="menu-icon"><i class="bi bi-clock-history fs-2"></i></span>
                            <span class="menu-title">Riwayat Sewing</span>
                        </a>
                    </div>

                @elseif ($role === UserRole::QC)
                    <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('qc.dashboard') ? 'active' : '' }}" href="{{ route('qc.dashboard') }}">
                            <span class="menu-icon"><i class="bi bi-clipboard-check fs-2"></i></span>
                            <span class="menu-title">Checklist QC</span>
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

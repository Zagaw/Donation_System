@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.certificates.index') }}" class="text-decoration-none">
                            <i class="fas fa-certificate me-1"></i>Certificates
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $certificate->certificate_number }}
                    </li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0">
                <i class="fas fa-certificate me-2 text-primary"></i>
                Certificate Details
            </h2>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.certificates.download', $certificate) }}" 
               class="btn btn-success">
                <i class="fas fa-download me-2"></i>Download PDF
            </a>
            @if($certificate->status != 'revoked')
            <a href="{{ route('admin.certificates.regenerate', $certificate) }}" 
               class="btn btn-warning"
               onclick="return confirm('Are you sure you want to regenerate this certificate?')">
                <i class="fas fa-sync-alt me-2"></i>Regenerate
            </a>
            <form action="{{ route('admin.certificates.revoke', $certificate) }}" 
                  method="POST" 
                  class="d-inline"
                  onsubmit="return confirm('Are you sure you want to revoke this certificate? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-ban me-2"></i>Revoke
                </button>
            </form>
            @endif
            <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    @if($certificate->status == 'revoked')
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>This certificate has been revoked!</strong> It is no longer valid and cannot be downloaded.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Main Content Row -->
    <div class="row">
        <!-- Certificate Information Card -->
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        Certificate Information
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%" class="text-muted">Certificate #:</td>
                            <td class="fw-bold text-primary">{{ $certificate->certificate_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                @if($certificate->status == 'generated')
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i>Generated
                                    </span>
                                @elseif($certificate->status == 'pending')
                                    <span class="badge bg-warning px-3 py-2">
                                        <i class="fas fa-clock me-1"></i>Pending
                                    </span>
                                @elseif($certificate->status == 'sent')
                                    <span class="badge bg-info px-3 py-2">
                                        <i class="fas fa-envelope me-1"></i>Sent
                                    </span>
                                @elseif($certificate->status == 'revoked')
                                    <span class="badge bg-danger px-3 py-2">
                                        <i class="fas fa-ban me-1"></i>Revoked
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Issue Date:</td>
                            <td class="fw-semibold">
                                <i class="far fa-calendar-alt me-1 text-muted"></i>
                                {{ $certificate->issue_date->format('F d, Y') }}
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created At:</td>
                            <td>
                                <small class="text-muted">
                                    {{ $certificate->created_at->format('M d, Y h:i A') }}
                                </small>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Updated:</td>
                            <td>
                                <small class="text-muted">
                                    {{ $certificate->updated_at->format('M d, Y h:i A') }}
                                </small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Recipient Information Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">
                        <i class="fas fa-user me-2 text-success"></i>
                        Recipient Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-lg bg-light rounded-circle me-3">
                            <i class="fas fa-user-circle fa-3x text-secondary"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1">{{ $certificate->recipient_name }}</h4>
                            <p class="text-muted mb-0">
                                <i class="fas fa-id-card me-1"></i>User ID: {{ $certificate->user_id }}
                            </p>
                        </div>
                    </div>
                    @if($certificate->donor_name)
                    <hr>
                    <div class="mt-3">
                        <small class="text-muted d-block mb-1">Donor Name:</small>
                        <span class="fw-semibold">{{ $certificate->donor_name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Donation Details Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">
                        <i class="fas fa-box me-2 text-warning"></i>
                        Donation Details
                    </h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td width="40%" class="text-muted">Donation ID:</td>
                            <td class="fw-semibold">#{{ $certificate->donation_id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Item Name:</td>
                            <td class="fw-bold">{{ $certificate->item_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Quantity:</td>
                            <td>
                                <span class="badge bg-info bg-opacity-10 text-dark px-3 py-2">
                                    {{ $certificate->quantity }} pieces
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Category:</td>
                            <td>
                                @php
                                    $categoryColors = [
                                        'Food' => 'success',
                                        'Clothing' => 'primary',
                                        'Medical' => 'danger',
                                        'Education' => 'warning',
                                        'Other' => 'secondary'
                                    ];
                                    $color = $categoryColors[$certificate->category] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} px-3 py-2">
                                    <i class="fas fa-tag me-1"></i>{{ $certificate->category }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Certificate Preview Column -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title fw-bold mb-0">
                        <i class="fas fa-file-pdf me-2 text-danger"></i>
                        Certificate Preview
                    </h5>
                    @if($certificate->file_path && $certificate->status != 'revoked')
                    <a href="{{ route('admin.certificates.download', $certificate) }}" 
                       class="btn btn-sm btn-danger">
                        <i class="fas fa-download me-1"></i>Download PDF
                    </a>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($certificate->file_path && Storage::disk('public')->exists($certificate->file_path))
                        <div class="certificate-preview">
                            <iframe src="{{ asset('storage/' . $certificate->file_path) }}#toolbar=0" 
                                    style="width: 100%; height: 700px;" 
                                    frameborder="0"
                                    class="w-100">
                            </iframe>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-file-pdf fa-5x text-muted mb-3"></i>
                                <h5 class="text-muted">Certificate PDF Not Available</h5>
                                <p class="text-muted mb-3">
                                    @if($certificate->status == 'revoked')
                                        This certificate has been revoked.
                                    @else
                                        The PDF file has not been generated yet.
                                    @endif
                                </p>
                                @if($certificate->status != 'revoked')
                                <a href="{{ route('admin.certificates.regenerate', $certificate) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-sync-alt me-2"></i>Generate PDF Now
                                </a>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .avatar-lg {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .table-borderless tr td {
        padding: 0.75rem 0;
        border: none;
    }
    .certificate-preview {
        background: #f8f9fa;
        border-radius: 0 0 8px 8px;
        overflow: hidden;
    }
    .empty-state {
        padding: 60px 20px;
    }
    .btn-group .btn {
        margin-left: 5px;
    }
    .card {
        border: none;
        border-radius: 12px;
    }
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,.05);
        border-radius: 12px 12px 0 0 !important;
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-resize iframe
    function resizeIframe(obj) {
        obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 'px';
    }
</script>
@endpush
@endsection
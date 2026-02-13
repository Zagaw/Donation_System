@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">
                <i class="fas fa-certificate me-2 text-primary"></i>
                Certificate Details
            </h2>
            <p class="text-muted mb-0">
                Certificate #: <span class="fw-semibold">{{ $certificate->certificate_number }}</span>
            </p>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.certificates.download', $certificate) }}" 
               class="btn btn-success">
                <i class="fas fa-download me-2"></i>Download PDF
            </a>
            <a href="{{ route('admin.certificates.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    @if($certificate->status == 'revoked')
    <div class="alert alert-danger mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>This certificate has been revoked!</strong> It is no longer valid.
    </div>
    @endif

    <div class="row">
        <!-- Certificate Information -->
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
                            <td width="45%" class="text-muted">Certificate #:</td>
                            <td class="fw-bold">{{ $certificate->certificate_number }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                @if($certificate->status == 'generated')
                                    <span class="badge bg-success">Generated</span>
                                @elseif($certificate->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($certificate->status == 'sent')
                                    <span class="badge bg-info">Sent</span>
                                @elseif($certificate->status == 'revoked')
                                    <span class="badge bg-danger">Revoked</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Issue Date:</td>
                            <td>{{ $certificate->issue_date->format('F d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created At:</td>
                            <td>{{ $certificate->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Recipient Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">
                        <i class="fas fa-user me-2 text-success"></i>
                        Recipient Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light rounded-circle p-3 me-3">
                            <i class="fas fa-user-circle fa-2x text-secondary"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1">{{ $certificate->recipient_name }}</h5>
                            <p class="text-muted mb-0">User ID: {{ $certificate->user_id }}</p>
                        </div>
                    </div>
                    @if($certificate->donor_name)
                    <hr>
                    <div>
                        <small class="text-muted d-block">Donor Name:</small>
                        <span class="fw-semibold">{{ $certificate->donor_name }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Donation Details -->
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
                            <td width="45%" class="text-muted">Donation ID:</td>
                            <td>#{{ $certificate->donation_id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Item Name:</td>
                            <td class="fw-bold">{{ $certificate->item_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Quantity:</td>
                            <td>{{ $certificate->quantity }} pieces</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Category:</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ $certificate->category }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Actions Card -->
            @if($certificate->status != 'revoked')
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold mb-0">
                        <i class="fas fa-cog me-2 text-secondary"></i>
                        Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.certificates.regenerate', $certificate) }}" 
                           class="btn btn-warning"
                           onclick="return confirm('Are you sure you want to regenerate this certificate?')">
                            <i class="fas fa-sync-alt me-2"></i>Regenerate Certificate
                        </a>
                        <form action="{{ route('admin.certificates.revoke', $certificate) }}" 
                              method="POST" 
                              onsubmit="return confirm('Are you sure you want to revoke this certificate? This action cannot be undone.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-ban me-2"></i>Revoke Certificate
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Certificate Preview -->
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
                <div class="card-body">
                    @if($certificate->file_path && Storage::disk('public')->exists($certificate->file_path))
                        <div class="text-center">
                            <iframe src="{{ asset('storage/' . $certificate->file_path) }}" 
                                    style="width: 100%; height: 600px; border: 1px solid #dee2e6; border-radius: 4px;">
                            </iframe>
                        </div>
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                File: {{ basename($certificate->file_path) }}
                            </small>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-pdf fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">PDF Preview Not Available</h5>
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
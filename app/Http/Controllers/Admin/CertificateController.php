<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Donation;
use App\Services\CertificateService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    protected $certificateService;

    public function __construct(CertificateService $certificateService)
    {
        $this->certificateService = $certificateService;
    }

    public function index()
    {
        $certificates = Certificate::with('donation')->latest()->paginate(15);
        return view('admin.certificates.index', compact('certificates'));
    }

    public function show(Certificate $certificate)
    {
        return view('admin.certificates.show', compact('certificate'));
    }

    public function generate(Donation $donation)
    {
        try {
            if ($donation->status !== 'completed') {
                return back()->with('error', 'Certificate can only be generated for completed donations.');
            }

            $certificate = $this->certificateService->generateCertificate($donation);
            
            return redirect()
                ->route('admin.certificates.show', $certificate)
                ->with('success', 'Certificate generated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to generate certificate: ' . $e->getMessage());
        }
    }

    public function download(Certificate $certificate)
    {
        try {
            return $this->certificateService->downloadCertificate($certificate);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to download certificate.');
        }
    }

    public function regenerate(Certificate $certificate)
    {
        try {
            $this->certificateService->regenerateCertificate($certificate);
            return back()->with('success', 'Certificate regenerated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to regenerate certificate.');
        }
    }

    public function revoke(Certificate $certificate)
    {
        try {
            $this->certificateService->revokeCertificate($certificate);
            return redirect()
                ->route('admin.certificates.index')
                ->with('success', 'Certificate revoked successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to revoke certificate.');
        }
    }
}
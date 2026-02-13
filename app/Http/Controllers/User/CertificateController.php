// app/Http/Controllers/User/CertificateController.php
<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
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
        $certificates = Certificate::where('user_id', auth()->id())
            ->with('donation')
            ->latest()
            ->paginate(10);
            
        return view('user.certificates.index', compact('certificates'));
    }

    public function show(Certificate $certificate)
    {
        if ($certificate->user_id !== auth()->id()) {
            abort(403);
        }
        return view('user.certificates.show', compact('certificate'));
    }

    public function download(Certificate $certificate)
    {
        if ($certificate->user_id !== auth()->id()) {
            abort(403);
        }
        
        try {
            return $this->certificateService->downloadCertificate($certificate);
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to download certificate.');
        }
    }
}
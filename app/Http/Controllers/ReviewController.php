<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TravelRequest;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function index()
    {
        // Menampilkan SPPD yang statusnya "in_review" (dalam proses review)
        $travelRequests = TravelRequest::with(['user'])
            ->where('status', 'in_review')
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('review.list', compact('travelRequests'));
    }

    public function ajaxListReview(Request $request)
    {
        $query = TravelRequest::with(['user']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('tujuan', 'ILIKE', "%$search%")
                  ->orWhere('keperluan', 'ILIKE', "%$search%")
                  ->orWhere('kode_sppd', 'ILIKE', "%$search%") ;
            });
        }
        $travelRequests = $query->orderByDesc('created_at')->paginate(10);
        return view('review.partials.review_table', compact('travelRequests'))->render();
    }
}

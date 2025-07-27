@props(['travelRequest'])

@php
    $sequence = collect($travelRequest->getApprovalFlow())->map(function($role) {
        return [
            'role' => $role,
                            'name' => $role
        ];
    })->values()->all();
    $approvals = $travelRequest->approvals->keyBy('role');
    $currentStep = 0;
    if ($travelRequest->status === 'approved') {
        $currentStep = count($sequence);
    } elseif ($travelRequest->status === 'in_review' && $travelRequest->current_approver_role) {
        $currentIndex = collect($sequence)->search(fn($step) => $step['role'] === $travelRequest->current_approver_role);
        $currentStep = $currentIndex !== false ? $currentIndex : 0;
    }
@endphp

<div class="glass-card p-6 fade-in">
    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
        <i class="fas fa-route mr-2 text-indigo-500"></i>Progress Approval
    </h3>

    <!-- Progress Visualization -->
    <div class="relative">
        <!-- Progress Line -->
        <div class="absolute top-4 left-4 w-full h-0.5 bg-gray-200"></div>
        <div class="absolute top-4 left-4 h-0.5 bg-indigo-500 transition-all duration-500"
             style="width: {{ $travelRequest->status === 'approved' ? '100%' : ($currentStep > 0 ? (($currentStep / count($sequence)) * 100) . '%' : '0%') }}"></div>

        <!-- Steps -->
        <div class="relative flex justify-between">
            @foreach($sequence as $index => $step)
                @php
                    $approval = $approvals->get($step['role']);
                    $isCompleted = $approval && $approval->status === 'approved';
                    $isCurrent = !$isCompleted && $travelRequest->current_approver_role === $step['role'] && $travelRequest->status === 'in_review';
                    $isRejected = $approval && $approval->status === 'rejected';
                @endphp

                <div class="flex flex-col items-center" style="flex: 1;">
                    <!-- Step Circle -->
                    <div class="relative z-10 w-8 h-8 rounded-full flex items-center justify-center border-2 transition-all duration-300
                        @if($isCompleted)
                            bg-green-500 border-green-500 text-white
                        @elseif($isRejected)
                            bg-red-500 border-red-500 text-white
                        @elseif($isCurrent)
                            bg-indigo-500 border-indigo-500 text-white animate-pulse
                        @else
                            bg-white border-gray-300 text-gray-400
                        @endif">
                        @if($isCompleted)
                            <i class="fas fa-check text-xs"></i>
                        @elseif($isRejected)
                            <i class="fas fa-times text-xs"></i>
                        @elseif($isCurrent)
                            <i class="fas fa-clock text-xs"></i>
                        @else
                            <span class="text-xs font-medium">{{ $index + 1 }}</span>
                        @endif
                    </div>

                    <!-- Step Info -->
                    <div class="mt-2 text-center">
                        <p class="text-sm font-medium
                            @if($isCompleted) text-green-600
                            @elseif($isRejected) text-red-600
                            @elseif($isCurrent) text-indigo-600
                            @else text-gray-500 @endif">
                            {{ $step['name'] }}
                        </p>

                        @if($approval)
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $approval->approver->name ?? '-' }}
                            </p>
                            @if($approval->approved_at)
                                <p class="text-xs text-gray-400">
                                    {{ \Carbon\Carbon::parse($approval->approved_at)->format('d/m H:i') }}
                                </p>
                            @endif
                        @endif

                        <!-- Status Badge -->
                        @if($isCompleted)
                            <span class="inline-block mt-1 px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                Disetujui
                            </span>
                        @elseif($isRejected)
                            <span class="inline-block mt-1 px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                Ditolak
                            </span>
                        @elseif($isCurrent)
                            <span class="inline-block mt-1 px-2 py-1 text-xs rounded-full bg-indigo-100 text-indigo-800">
                                Menunggu
                            </span>
                        @else
                            <span class="inline-block mt-1 px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-500">
                                Pending
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Additional Info -->
    <div class="mt-6 p-4 bg-gray-50 rounded-lg">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                @if($travelRequest->status === 'approved')
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-check text-white text-sm"></i>
                    </div>
                @elseif($travelRequest->status === 'rejected')
                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-times text-white text-sm"></i>
                    </div>
                @elseif($travelRequest->status === 'in_review')
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-clock text-white text-sm"></i>
                    </div>
                @else
                    <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                        <i class="fas fa-paper-plane text-white text-sm"></i>
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-medium text-gray-900">
                    Status: {{ ucfirst(str_replace('_', ' ', $travelRequest->status)) }}
                </h4>
                <p class="text-sm text-gray-600 mt-1">
                    @if($travelRequest->status === 'approved')
                        SPPD telah disetujui oleh semua pihak yang berwenang.
                    @elseif($travelRequest->status === 'rejected')
                        SPPD ditolak. Silakan periksa alasan penolakan.
                    @elseif($travelRequest->status === 'in_review')
                        @if($travelRequest->current_approver_role)
                            Menunggu persetujuan dari {{ $travelRequest->current_approver_role }}.
                        @else
                            Sedang dalam proses review.
                        @endif
                    @else
                        SPPD telah diajukan dan menunggu review.
                    @endif
                </p>
                @if($requesterRole !== 'staff')
                    <p class="text-xs text-gray-500 mt-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        Alur approval khusus untuk {{ $requesterRole }}:
                        {{ implode(' → ', array_column($sequence, 'name')) }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

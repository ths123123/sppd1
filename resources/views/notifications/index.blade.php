@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-bell mr-3 text-red-800"></i> Notifikasi Saya
                </h2>
                
                <div class="space-y-4">
                    @if($notifications->isEmpty())
                        <div class="text-center py-8">
                            <div class="text-gray-400 mb-3">
                                <i class="fas fa-bell-slash text-5xl"></i>
                            </div>
                            <p class="text-gray-500 text-lg">Tidak ada notifikasi untuk ditampilkan</p>
                        </div>
                    @else
                        @foreach($notifications as $notification)
                            <div class="border-l-4 {{ $notification->is_read ? 'border-gray-300' : 'border-red-500' }} bg-white p-4 shadow-sm hover:shadow-md transition-all duration-200 rounded-r-lg">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 mr-3">
                                        @if($notification->type == 'approval')
                                            <div class="bg-blue-100 p-2 rounded-full">
                                                <i class="fas fa-check-circle text-blue-500"></i>
                                            </div>
                                        @elseif($notification->type == 'rejection')
                                            <div class="bg-red-100 p-2 rounded-full">
                                                <i class="fas fa-times-circle text-red-500"></i>
                                            </div>
                                        @elseif($notification->type == 'revision')
                                            <div class="bg-yellow-100 p-2 rounded-full">
                                                <i class="fas fa-exclamation-circle text-yellow-500"></i>
                                            </div>
                                        @else
                                            <div class="bg-gray-100 p-2 rounded-full">
                                                <i class="fas fa-info-circle text-gray-500"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <h3 class="text-lg font-semibold text-gray-800">{{ $notification->title }}</h3>
                                            <span class="text-xs text-gray-500">{{ $notification->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-gray-600 mt-1">{{ $notification->message }}</p>
                                        @if($notification->travel_request_id)
                                            <div class="mt-3">
                                                <a href="{{ route('travel-requests.show', $notification->travel_request_id) }}" class="inline-flex items-center px-3 py-1 bg-red-700 text-white text-sm rounded-md hover:bg-red-800 transition-colors duration-200">
                                                    <i class="fas fa-eye mr-1"></i> Lihat Detail SPPD
                                                </a>
                                            </div>
                                        @elseif($notification->action_url)
                                            <div class="mt-3">
                                                <a href="{{ $notification->action_url }}" class="inline-flex items-center px-3 py-1 bg-red-700 text-white text-sm rounded-md hover:bg-red-800 transition-colors duration-200">
                                                    <i class="fas fa-external-link-alt mr-1"></i> {{ $notification->action_text ?: 'Lihat Detail' }}
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
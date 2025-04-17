@extends('layouts.app')

@section('title', 'Audit Log - Dashboard Admin')

@section('content')
<div class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200">
    <div class="mb-1 w-full">
        <div class="mb-4">
            <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl">Audit Log</h1>
        </div>
        <div class="sm:flex">
            <div class="flex items-center space-x-2 sm:space-x-3 ml-auto">
                <button type="button" data-modal-toggle="filter-modal" class="w-full sm:w-auto flex items-center justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-gray-600 hover:bg-gray-700 focus:ring-4 focus:ring-gray-300">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    Filter Log
                </button>
                <a href="{{ route('admin.audit.export') }}" class="w-full sm:w-auto flex items-center justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-green-600 hover:bg-green-700 focus:ring-4 focus:ring-green-300">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export CSV
                </a>
                <button type="button" data-modal-target="clear-logs-modal" data-modal-toggle="clear-logs-modal" class="w-full sm:w-auto flex items-center justify-center px-4 py-2 text-sm font-medium text-white rounded-lg bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Clear Logs
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Search and filter form -->
<form method="GET" action="{{ route('admin.audit.index') }}" class="bg-white p-4 mb-4 border-b border-gray-200">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
            <label for="search" class="block mb-2 text-sm font-medium text-gray-900">Search</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Search description, IP...">
        </div>
        <div>
            <label for="action" class="block mb-2 text-sm font-medium text-gray-900">Action</label>
            <select id="action" name="action" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $action)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="date_from" class="block mb-2 text-sm font-medium text-gray-900">From Date</label>
            <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
        </div>
        <div>
            <label for="date_to" class="block mb-2 text-sm font-medium text-gray-900">To Date</label>
            <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5">
        </div>
    </div>
        <div class="mt-4 flex justify-end">
        <a href="{{ route('admin.audit.index') }}" class="px-4 py-2 mr-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:ring-4 focus:ring-gray-200">
            Reset
        </a>
        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:ring-4 focus:ring-green-300">
            Apply Filters
        </button>
    </div>
</form>

<!-- Table -->
<div class="flex flex-col">
    <div class="overflow-x-auto">
        <div class="align-middle inline-block min-w-full">
            <div class="shadow overflow-hidden">
                <table class="table-fixed min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th scope="col" class="p-4 text-left text-xs font-medium text-gray-500 uppercase">
                                Time
                            </th>
                            <th scope="col" class="p-4 text-left text-xs font-medium text-gray-500 uppercase">
                                User
                            </th>
                            <th scope="col" class="p-4 text-left text-xs font-medium text-gray-500 uppercase">
                                Action
                            </th>
                            <th scope="col" class="p-4 text-left text-xs font-medium text-gray-500 uppercase">
                                Description
                            </th>
                            <th scope="col" class="p-4 text-left text-xs font-medium text-gray-500 uppercase">
                                IP Address
                            </th>
                            <th scope="col" class="p-4 text-left text-xs font-medium text-gray-500 uppercase">
                                Details
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($logs as $log)
                            <tr class="hover:bg-gray-100">
                                <td class="p-4 text-sm font-normal text-gray-500 whitespace-nowrap">
                                    {{ $log->created_at->format('M d, Y H:i:s') }}
                                </td>
                                <td class="p-4 text-sm font-normal text-gray-500">
                                    @if($log->user_type && $log->user_id)
                                        @if($log->user_type === 'App\\Models\\User')
                                            Admin: {{ optional(\App\Models\User::find($log->user_id))->name ?? 'Unknown' }}
                                        @elseif($log->user_type === 'App\\Models\\Guru')
                                            Guru: {{ optional(\App\Models\Guru::find($log->user_id))->nama ?? 'Unknown' }}
                                        @else
                                            {{ class_basename($log->user_type) }} #{{ $log->user_id }}
                                        @endif
                                    @else
                                        System
                                    @endif
                                </td>
                                <td class="p-4 text-sm font-semibold text-gray-900">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if(in_array($log->action, ['login_success', 'created']))
                                        bg-green-100 text-green-800 
                                    @elseif(in_array($log->action, ['login_failed', 'deleted']))
                                        bg-red-100 text-red-800
                                    @elseif($log->action === 'updated')
                                        bg-yellow-100 text-yellow-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                                </td>
                                <td class="p-4 text-sm text-gray-900 max-w-xs truncate">
                                    {{ $log->description ?? 'No description' }}
                                </td>
                                <td class="p-4 text-sm text-gray-500 whitespace-nowrap">
                                    {{ $log->ip_address ?? 'N/A' }}
                                </td>
                                <td class="p-4 whitespace-nowrap">
                                <a href="{{ route('admin.audit.show', $log->id) }}" class="text-green-600 hover:underline font-medium">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        
                        @if($logs->isEmpty())
                            <tr>
                                <td colspan="6" class="p-4 text-sm text-center text-gray-500">
                                    No audit logs found
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Pagination -->
<div class="mt-4">
    {{ $logs->withQueryString()->links() }}
</div>

<!-- Clear Logs Modal -->
<div id="clear-logs-modal" tabindex="-1" aria-hidden="true" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="relative p-4 w-full max-w-md max-h-full">
        <div class="relative bg-white rounded-lg shadow">
            <div class="p-4 md:p-5 text-center">
                <h3 class="mb-5 text-lg font-normal text-gray-500">Are you sure you want to clear audit logs?</h3>
                <form action="{{ route('admin.audit.clear') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="period" class="block mb-2 text-sm font-medium text-gray-900">Select period to clear:</label>
                        <select id="period" name="period" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                            <option value="1month">Older than 1 month</option>
                            <option value="3months">Older than 3 months</option>
                            <option value="6months">Older than 6 months</option>
                            <option value="1year">Older than 1 year</option>
                            <option value="all">All logs</option>
                        </select>
                    </div>
                    <button type="submit" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                        Yes, I'm sure
                    </button>
                    <button data-modal-hide="clear-logs-modal" type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10">
                        No, cancel
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
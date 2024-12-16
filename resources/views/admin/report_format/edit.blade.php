@extends('layouts.app')

@section('title', 'Edit Format Rapor')

@section('content')
<div class="p-4 bg-white mt-14 rounded-lg shadow">
    <div id="report-editor-root"></div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // React component initialization will go here later
    console.log('Edit page loaded');
});
</script>
@endpush
@endsection
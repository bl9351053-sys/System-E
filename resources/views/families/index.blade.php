@extends('layouts.app')

@section('title', 'Families in Evacuation')

@section('content')
<div class="card">
    <div class="card-header">👨‍👩‍👧‍👦 Families Currently in Evacuation Areas</div>

    @if($families->count() > 0)
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Family Name</th>
                        <th>Members</th>
                        <th>Contact Number</th>
                        <th>Evacuation Area</th>
                        <th>Special Needs</th>
                        <th>Checked In</th>
                       
                    </tr>
                </thead>
                <tbody>
                    @foreach($families as $family)
                        <tr>
                            <td><strong>{{ $family->family_head_name }}</strong></td>
                            <td>{{ $family->total_members }}</td>
                            <td>{{ $family->contact_number }}</td>
                            <td>
                                <a href="{{ route('evacuation-areas.show', $family->evacuationArea) }}" class="text-primary" style="color: #667eea; text-decoration: none;">
                                    {{ $family->evacuationArea->name }}
                                </a>
                            </td>
                            <td>{{ $family->special_needs ?? 'None' }}</td>

                            <td>
                                @if($family->checked_in_at)
                                    {{ $family->checked_in_at->tz('Asia/Manila')->format('M d, Y g:i A') }}
                                @else
                                    {{ now()->tz('Asia/Manila')->format('M d, Y g:i A') }}
                                @endif
                            </td>

                            
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-15">
            {{ $families->links() }}
        </div>
    @else
        <p class="text-muted p-1">No families currently in evacuation areas.</p>
    @endif
</div>
@endsection

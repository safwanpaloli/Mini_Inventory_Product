@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <h2>Users</h2>
        <a href="#" class="btn btn-primary mb-3">Add User</a>
        <table class="table table-bordered bg-white shadow-sm">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge bg-secondary">{{ $user->role }}</span></td>
                    <td>
                        @if($user->active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                        @if($user->locked_until && \Carbon\Carbon::parse($user->locked_until)->isFuture())
                            <span class="badge bg-warning text-dark">Locked</span>
                        @endif
                    </td>
                    <td>
                        <a href="#" class="btn btn-sm btn-info">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

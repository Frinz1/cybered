@extends('layouts.admin')

@section('title', 'CyberEd - Manage Users')

@section('content')
<header class="admin-header">
    <h1>Manage Users</h1>
    <div class="header-actions">
        <button id="add-user-btn" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <line x1="19" y1="8" x2="19" y2="14"></line>
                <line x1="22" y1="11" x2="16" y2="11"></line>
            </svg>
            Add User
        </button>
        <button id="toggle-admin-sidebar-btn" class="btn btn-icon" title="Toggle Sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                <line x1="9" x2="9" y1="3" y2="21"></line>
            </svg>
        </button>
    </div>
</header>

<div class="users-container">
    <div class="search-filter">
        <div class="search-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="user-search" placeholder="Search users...">
        </div>
    </div>

    <div class="users-table-container">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Joined</th>
                    <th>Sessions</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="users-table-body">
                @foreach($users as $user)
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar">{{ substr($user->name, 0, 2) }}</div>
                            <span>{{ $user->name }}</span>
                        </div>
                    </td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="progress-bar">
                            <div class="progress" style="width: {{ min($user->chat_sessions_count * 10, 100) }}%"></div>
                        </div>
                        <span class="progress-text">{{ $user->chat_sessions_count }} sessions</span>
                    </td>
                    <td>
                        <div class="table-actions">
                            <button class="btn btn-icon edit-user" title="Edit User" data-id="{{ $user->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M12 20h9"></path>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                </svg>
                            </button>
                            <button class="btn btn-icon delete-user" title="Delete User" data-id="{{ $user->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M3 6h18"></path>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                                    <line x1="10" x2="10" y1="11" y2="17"></line>
                                    <line x1="14" x2="14" y1="11" y2="17"></line>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $users->links() }}
    </div>
</div>

<!-- User Modal -->
<div class="modal" id="user-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="user-modal-title">Add User</h2>
            <button class="close-modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="user-form">
                <input type="hidden" id="user-id">
                <div class="form-group">
                    <label for="user-name">Name</label>
                    <input type="text" id="user-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="user-email">Email</label>
                    <input type="email" id="user-email" name="email" required>
                </div>
                <div class="form-group" id="password-group">
                    <label for="user-password">Password</label>
                    <input type="password" id="user-password" name="password" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary cancel-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal" id="confirm-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Confirm Delete</h2>
            <button class="close-modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this user? This action cannot be undone.</p>
            <div class="modal-actions">
                <button class="btn btn-secondary cancel-modal">Cancel</button>
                <button class="btn btn-danger confirm-delete">Delete</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    document.getElementById('toggle-admin-sidebar-btn').addEventListener('click', function() {
        document.querySelector('.admin-sidebar').classList.toggle('collapsed');
    });


    document.getElementById('user-search').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#users-table-body tr');

        rows.forEach(row => {
            const name = row.querySelector('.user-cell span').textContent.toLowerCase();
            const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();

            if (name.includes(searchTerm) || email.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });


    const userModal = document.getElementById('user-modal');
    const confirmModal = document.getElementById('confirm-modal');
    let currentUserId = null;


    document.getElementById('add-user-btn').addEventListener('click', function() {
        document.getElementById('user-modal-title').textContent = 'Add User';
        document.getElementById('user-form').reset();
        document.getElementById('user-id').value = '';
        document.getElementById('password-group').style.display = 'block';
        document.getElementById('user-password').required = true;
        userModal.classList.add('show');
    });


    document.querySelectorAll('.close-modal, .cancel-modal').forEach(button => {
        button.addEventListener('click', function() {
            userModal.classList.remove('show');
            confirmModal.classList.remove('show');
        });
    });

    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.id;
            currentUserId = userId;


            fetch(`/admin/users/${userId}/edit`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch user data');
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('user-modal-title').textContent = 'Edit User';
                    document.getElementById('user-id').value = data.id;
                    document.getElementById('user-name').value = data.name;
                    document.getElementById('user-email').value = data.email;
                    document.getElementById('password-group').style.display = 'none';
                    document.getElementById('user-password').required = false;
                    document.getElementById('user-password').value = '';

                    userModal.classList.add('show');
                })
                .catch(error => {
                    console.error('Error fetching user:', error);
                    alert('Failed to load user data. Please try again.');
                });
        });
    });


    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            currentUserId = this.dataset.id;
            confirmModal.classList.add('show');
        });
    });

    document.querySelector('.confirm-delete').addEventListener('click', function() {
        if (currentUserId) {
            fetch(`/admin/users/${currentUserId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to delete user');
                    }
                    return response.json();
                })
                .then(data => {
                    confirmModal.classList.remove('show');
                    window.location.reload();
                })
                .catch(error => {
                    console.error('Error deleting user:', error);
                    alert('Failed to delete user. Please try again.');
                });
        }
    });


    document.getElementById('user-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const userId = document.getElementById('user-id').value;
        const formData = new FormData(this);

        const url = userId ?
            `/admin/users/${userId}` :
            '/admin/users';

        const method = userId ? 'PUT' : 'POST';

        fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content')
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Failed to save user');
                }
                return response.json();
            })
            .then(data => {
                userModal.classList.remove('show');
                window.location.reload();
            })
            .catch(error => {
                console.error('Error saving user:', error);
                alert('Failed to save user. Please try again.');
            });
    });
});
</script>
@endpush
@endsection
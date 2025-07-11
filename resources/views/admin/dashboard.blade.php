@extends('layouts.admin')

@section('title', 'CyberEd - Admin Dashboard')

@section('content')
<header class="admin-header">
    <h1>Dashboard</h1>
    <div class="header-actions">
        <button id="toggle-admin-sidebar-btn" class="btn btn-icon" title="Toggle Sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                <line x1="9" x2="9" y1="3" y2="21"></line>
            </svg>
        </button>
    </div>
</header>

<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Total Users</h3>
            <p class="stat-value">{{ $stats['total_users'] }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Chat Sessions</h3>
            <p class="stat-value">{{ $stats['chat_sessions'] }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Scenarios</h3>
            <p class="stat-value">{{ $stats['scenarios'] }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
            </svg>
        </div>
        <div class="stat-content">
            <h3>Completion Rate</h3>
            <p class="stat-value">{{ $stats['completion_rate'] }}%</p>
        </div>
    </div>
</div>

<div class="dashboard-charts">
    <div class="chart-container">
        <h3>User Activity</h3>
        <div class="chart">
            <div class="chart-bar-container">
                <div class="chart-labels">
                    <span>Mon</span>
                    <span>Tue</span>
                    <span>Wed</span>
                    <span>Thu</span>
                    <span>Fri</span>
                    <span>Sat</span>
                    <span>Sun</span>
                </div>
                <div class="chart-bars">
                    <div class="chart-bar" style="height: 40%"></div>
                    <div class="chart-bar" style="height: 65%"></div>
                    <div class="chart-bar" style="height: 85%"></div>
                    <div class="chart-bar" style="height: 70%"></div>
                    <div class="chart-bar" style="height: 55%"></div>
                    <div class="chart-bar" style="height: 20%"></div>
                    <div class="chart-bar" style="height: 15%"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="chart-container">
        <h3>Topic Distribution</h3>
        <div class="chart">
            <div class="pie-chart"></div>
            <div class="pie-legend">
                <div class="legend-item">
                    <span class="legend-color" style="background-color: var(--color-primary);"></span>
                    <span>Phishing (60%)</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background-color: var(--color-secondary);"></span>
                    <span>Malware (40%)</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="recent-activity">
    <h3>Recent Activity</h3>
    <div class="activity-list">
        @foreach($recentActivity as $activity)
        <div class="activity-item">
            <div class="activity-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="activity-content">
                <p>{{ $activity['description'] }}</p>
                <span class="activity-time">{{ $activity['time'] }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection

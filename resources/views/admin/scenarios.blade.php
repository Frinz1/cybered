@extends('layouts.admin')

@section('title', 'CyberEd - Manage Scenarios')

@section('content')
<header class="admin-header">
    <h1>Manage Scenarios</h1>
    <div class="header-actions">
        <button id="add-scenario-btn" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 5v14M5 12h14"></path>
            </svg>
            Add Scenario
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

<div class="scenarios-container">
    <div class="scenario-filters">
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">All</button>
            <button class="filter-tab" data-filter="phishing">Phishing</button>
            <button class="filter-tab" data-filter="malware">Malware</button>
        </div>
        <div class="search-box">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="scenario-search" placeholder="Search scenarios...">
        </div>
    </div>

    <div class="scenarios-grid" id="scenarios-grid">
        @foreach($scenarios as $scenario)
        <div class="scenario-card" data-type="{{ $scenario->type }}">
            <div class="scenario-header">
                <div class="scenario-type {{ $scenario->type }}">{{ ucfirst($scenario->type) }}</div>
                <div class="scenario-actions">
                    <button class="btn btn-icon edit-scenario" title="Edit Scenario" data-id="{{ $scenario->id }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 20h9"></path>
                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                        </svg>
                    </button>
                    <button class="btn btn-icon delete-scenario" title="Delete Scenario" data-id="{{ $scenario->id }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                            <line x1="10" x2="10" y1="11" y2="17"></line>
                            <line x1="14" x2="14" y1="11" y2="17"></line>
                        </svg>
                    </button>
                </div>
            </div>
            <div class="scenario-content">
                <h3>{{ $scenario->title }}</h3>
                <p>{{ $scenario->description }}</p>
                <div class="scenario-meta">
                    <span class="severity {{ $scenario->severity }}">{{ ucfirst($scenario->severity) }} Severity</span>
                    <span class="usage">Used {{ $scenario->usage_count }} times</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>


<div class="modal" id="scenario-modal">
    <div class="modal-content large">
        <div class="modal-header">
            <h2 id="scenario-modal-title">Add Scenario</h2>
            <button class="close-modal">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="scenario-form">
                <input type="hidden" id="scenario-id">
                <div class="form-row">
                    <div class="form-group">
                        <label for="scenario-title">Title</label>
                        <input type="text" id="scenario-title" name="title" required>
                    </div>
                    <div class="form-group">
                        <label for="scenario-type">Type</label>
                        <select id="scenario-type" name="type" required>
                            <option value="">Select type</option>
                            <option value="phishing">Phishing</option>
                            <option value="malware">Malware</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="scenario-description">Description</label>
                    <textarea id="scenario-description" name="description" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="scenario-severity">Severity</label>
                    <select id="scenario-severity" name="severity" required>
                        <option value="">Select severity</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="scenario-explanation">Explanation</label>
                    <textarea id="scenario-explanation" name="explanation" rows="4"
                        placeholder="Explain why this is a threat and what users should do..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="scenario-mitigation">Mitigation Steps</label>
                    <textarea id="scenario-mitigation" name="mitigation" rows="4"
                        placeholder="List the steps users should take to mitigate this threat..." required></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary cancel-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Scenario</button>
                </div>
            </form>
        </div>
    </div>
</div>


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
            <p>Are you sure you want to delete this scenario? This action cannot be undone.</p>
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


    const filterTabs = document.querySelectorAll('.filter-tab');
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            const cards = document.querySelectorAll('.scenario-card');

            cards.forEach(card => {
                if (filter === 'all' || card.dataset.type === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });


    document.getElementById('scenario-search').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const cards = document.querySelectorAll('.scenario-card');

        cards.forEach(card => {
            const title = card.querySelector('h3').textContent.toLowerCase();
            const description = card.querySelector('p').textContent.toLowerCase();

            if (title.includes(searchTerm) || description.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });

    const scenarioModal = document.getElementById('scenario-modal');
    const confirmModal = document.getElementById('confirm-modal');
    let currentScenarioId = null;


    document.getElementById('add-scenario-btn').addEventListener('click', function() {
        document.getElementById('scenario-modal-title').textContent = 'Add Scenario';
        document.getElementById('scenario-form').reset();
        document.getElementById('scenario-id').value = '';
        scenarioModal.classList.add('show');
    });

    document.querySelectorAll('.close-modal, .cancel-modal').forEach(button => {
        button.addEventListener('click', function() {
            scenarioModal.classList.remove('show');
            confirmModal.classList.remove('show');
        });
    });


    document.querySelectorAll('.edit-scenario').forEach(button => {
        button.addEventListener('click', function() {
            const scenarioId = this.dataset.id;
            currentScenarioId = scenarioId;


            fetch(`/admin/scenarios/${scenarioId}/edit`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const scenario = data.scenario;
                        document.getElementById('scenario-modal-title').textContent =
                            'Edit Scenario';
                        document.getElementById('scenario-id').value = scenario.id;
                        document.getElementById('scenario-title').value = scenario.title;
                        document.getElementById('scenario-type').value = scenario.type;
                        document.getElementById('scenario-description').value = scenario
                            .description;
                        document.getElementById('scenario-severity').value = scenario
                            .severity;
                        document.getElementById('scenario-explanation').value = scenario
                            .explanation;


                        if (Array.isArray(scenario.mitigation_steps)) {
                            document.getElementById('scenario-mitigation').value = scenario
                                .mitigation_steps.join('\n');
                        } else if (scenario.solution) {
                            document.getElementById('scenario-mitigation').value = scenario
                                .solution;
                        } else {
                            document.getElementById('scenario-mitigation').value = '';
                        }

                        scenarioModal.classList.add('show');
                    } else {
                        alert(data.error || 'Failed to load scenario data');
                    }
                })
                .catch(error => {
                    console.error('Error fetching scenario:', error);
                    alert('Failed to load scenario data. Please try again.');
                });
        });
    });


    document.querySelectorAll('.delete-scenario').forEach(button => {
        button.addEventListener('click', function() {
            currentScenarioId = this.dataset.id;
            confirmModal.classList.add('show');
        });
    });


    document.querySelector('.confirm-delete').addEventListener('click', function() {
        if (currentScenarioId) {
            fetch(`/admin/scenarios/${currentScenarioId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        confirmModal.classList.remove('show');
                        window.location.reload();
                    } else {
                        alert(data.error || 'Failed to delete scenario');
                    }
                })
                .catch(error => {
                    console.error('Error deleting scenario:', error);
                    alert('Failed to delete scenario. Please try again.');
                });
        }
    });


    document.getElementById('scenario-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const scenarioId = document.getElementById('scenario-id').value;
        const formData = new FormData(this);

        const url = scenarioId ?
            `/admin/scenarios/${scenarioId}` :
            '/admin/scenarios';

        const method = scenarioId ? 'PUT' : 'POST';

        fetch(url, {
                method: method,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                        'content')
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    scenarioModal.classList.remove('show');
                    window.location.reload();
                } else {
                    alert(data.error || 'Failed to save scenario');
                }
            })
            .catch(error => {
                console.error('Error saving scenario:', error);
                alert('Failed to save scenario. Please try again.');
            });
    });
});
</script>
@endpush
@endsection
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CyberEd - Municipal Cybersecurity Training</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
    .welcome-container {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 2rem;
        background: linear-gradient(135deg, var(--color-bg-primary) 0%, var(--color-bg-secondary) 100%);
    }

    .welcome-logo {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        margin-bottom: 2rem;
    }

    .welcome-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--color-text-primary);
    }

    .welcome-subtitle {
        font-size: 1.25rem;
        color: var(--color-text-secondary);
        margin-bottom: 2rem;
        max-width: 600px;
    }

    .welcome-actions {
        display: flex;
        gap: 1rem;
    }

    .welcome-features {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 2rem;
        margin-top: 4rem;
        max-width: 1000px;
    }

    .feature-card {
        background-color: var(--color-bg-card);
        border: 1px solid var(--color-border);
        border-radius: var(--border-radius-lg);
        padding: 1.5rem;
        width: 300px;
        text-align: left;
    }

    .feature-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--border-radius);
        background-color: rgba(59, 130, 246, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-primary);
        margin-bottom: 1rem;
    }

    .feature-title {
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: var(--color-text-primary);
    }

    .feature-description {
        color: var(--color-text-secondary);
        font-size: 0.875rem;
        line-height: 1.5;
    }
    </style>
</head>

<body>
    <div class="welcome-container">
        <img src="{{ asset('placeholder.svg?height=120&width=120&text=CyberEd') }}" alt="CyberEd Logo"
            class="welcome-logo">
        <h1 class="welcome-title">CyberEd</h1>
        <p class="welcome-subtitle">Interactive cybersecurity training for municipal employees</p>

        <div class="welcome-actions">
            <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
            <a href="{{ route('register') }}" class="btn btn-outline">Register</a>
        </div>

        <div class="welcome-features">
            <div class="feature-card">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Interactive Chatbot</h3>
                <p class="feature-description">Learn about cybersecurity threats through interactive conversations with
                    our specialized chatbot.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Threat Scenarios</h3>
                <p class="feature-description">Access a database of 100+ real-world cybersecurity scenarios with
                    practical solutions.</p>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <h3 class="feature-title">Municipal Focus</h3>
                <p class="feature-description">Content specifically designed for municipal employees and the unique
                    security challenges they face.</p>
            </div>
        </div>
    </div>
</body>

</html>
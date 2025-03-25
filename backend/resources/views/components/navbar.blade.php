<nav class="navbar">
    <div class="navbar-brand">Loan Management System</div>
    <div class="user-info">
        <div class="user-profile" id="user-initial">
            {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
        </div>
        <span id="username-display">{{ Auth::user()->username }}</span>
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
</nav>
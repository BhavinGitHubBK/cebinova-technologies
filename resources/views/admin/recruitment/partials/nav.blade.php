<nav class="analytics-nav" aria-label="Recruitment">
    <a class="{{ request()->routeIs('admin.recruitment.jobs.*') ? 'is-active' : '' }}" href="{{ route('admin.recruitment.jobs.index') }}">Jobs</a>
    <a class="{{ request()->routeIs('admin.recruitment.applications.*') ? 'is-active' : '' }}" href="{{ route('admin.recruitment.applications.index') }}">Applications</a>
    <a class="{{ request()->routeIs('admin.recruitment.departments.*') ? 'is-active' : '' }}" href="{{ route('admin.recruitment.departments.index') }}">Departments</a>
</nav>

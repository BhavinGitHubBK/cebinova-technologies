<h1>New career application</h1>
<p><strong>{{ $application->name }}</strong> applied for <strong>{{ $application->job->title }}</strong>.</p>
<p>Email: {{ $application->email }}<br>Phone: {{ $application->phone }}<br>Experience: {{ $application->experience }}</p>
<p><a href="{{ route('admin.recruitment.applications.show', $application) }}">Review application securely in admin</a></p>

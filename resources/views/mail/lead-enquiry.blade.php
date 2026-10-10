@php
    $isAdmin = $audience === 'admin';
@endphp
<div style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    @if ($isAdmin)
        <p>A new enquiry was submitted on the website.</p>
    @else
        <p>Hello {{ $lead->name }},</p>
        <p>Thank you for contacting CEBINOVA Technologies. We have your enquiry and will reply during working hours, Monday to Saturday, 10 AM to 7 PM.</p>
    @endif

    <p>
        <strong>Name:</strong> {{ $lead->name }}<br>
        @if ($lead->business_name)<strong>Business:</strong> {{ $lead->business_name }}<br>@endif
        <strong>Phone:</strong> {{ $lead->phone }}<br>
        @if ($lead->email)<strong>Email:</strong> {{ $lead->email }}<br>@endif
        @if ($lead->service)<strong>Service:</strong> {{ $lead->service }}<br>@endif
        @if ($lead->message)<strong>Message:</strong> {{ $lead->message }}@endif
    </p>

    @if ($isAdmin)
        <p><a href="{{ route('admin.leads.show', $lead) }}">Open this lead</a></p>
    @else
        <p>This is a confirmation only. It is not a quote or a contract.</p>
    @endif
</div>

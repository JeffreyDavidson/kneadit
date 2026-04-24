<!DOCTYPE html>
<html>
<body style="font-family: sans-serif;">
    <h2>🚨 Free-forever grant integrity alert</h2>

    <p>
        The following tenant(s) are marked <code>free_forever = true</code> but have no active grant recorded in
        <code>free_forever_grants</code>. Someone may have bypassed the admin UI (direct database write, compromised
        account, or overlooked migration). Investigate before dismissing.
    </p>

    <table style="border-collapse: collapse; margin-top: 16px;">
        <thead>
            <tr>
                <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px 12px;">Tenant ID</th>
                <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px 12px;">Name</th>
                <th style="text-align: left; border-bottom: 1px solid #ddd; padding: 8px 12px;">Email</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($unapproved as $tenant)
                <tr>
                    <td style="padding: 8px 12px; font-family: monospace;">{{ $tenant['id'] }}</td>
                    <td style="padding: 8px 12px;">{{ $tenant['name'] }}</td>
                    <td style="padding: 8px 12px;">{{ $tenant['email'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 24px; color: #555; font-size: 0.9em;">
        Next step: open the central admin → Tenants → check the activity log for each unexpected tenant. If legitimate,
        use the Grant Free Forever bulk action (which writes an approved grant row) to retroactively approve it.
    </p>
</body>
</html>

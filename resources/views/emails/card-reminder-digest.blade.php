<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Pengingat Tugas projeku</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #F8FAFC; color: #1E293B; margin: 0; padding: 24px; line-height: 1.5;">
    <div style="max-width: 560px; margin: 0 auto; background-color: #FFFFFF; border-radius: 8px; border: 1px solid #E2E8F0; padding: 28px;">
        <div style="margin-bottom: 20px; border-bottom: 2px solid #8B7CF6; padding-bottom: 12px;">
            <h2 style="font-size: 18px; font-weight: 700; color: #0F172A; margin: 0;">projeku — Pengingat Tenggat Waktu</h2>
            <p style="font-size: 13px; color: #64748B; margin: 4px 0 0 0;">Halo {{ $user->name }}, berikut daftar kartu tugas Anda yang mendekati atau telah jatuh tempo:</p>
        </div>

        <div style="margin-bottom: 24px;">
            <table style="width: 100%; border-collapse: collapse;">
                @foreach($cards as $card)
                    <tr style="border-bottom: 1px solid #F1F5F9;">
                        <td style="padding: 12px 0;">
                            <div style="font-size: 14px; font-weight: 600; color: #0F172A;">
                                {{ $card->title }}
                            </div>
                            <div style="font-size: 12px; color: #64748B; margin-top: 2px;">
                                Projek: <strong>{{ $card->project ? $card->project->name : '-' }}</strong> • 
                                Tenggat: <span style="color: #DC2626; font-weight: 500;">{{ $card->due_at ? $card->due_at->setTimezone('Asia/Jakarta')->format('d M Y, H:i') . ' WIB' : '-' }}</span>
                            </div>
                        </td>
                        <td style="text-align: right; padding: 12px 0; vertical-align: middle;">
                            <a href="{{ url('/projects/' . $card->project_id . '/board') }}" style="display: inline-block; background-color: #8B7CF6; color: #FFFFFF; font-size: 12px; font-weight: 500; text-decoration: none; padding: 6px 12px; border-radius: 6px;">
                                Buka
                            </a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>

        <div style="font-size: 11px; color: #94A3B8; border-top: 1px solid #E2E8F0; padding-top: 14px; text-align: center;">
            <p style="margin: 0;">Email ini dikirim secara otomatis oleh <strong>projeku</strong>.</p>
            <p style="margin: 4px 0 0 0;">Anda dapat menonaktifkan pengingat email melalui pengaturan profil akun Anda.</p>
        </div>
    </div>
</body>
</html>


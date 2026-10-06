<?php

declare(strict_types=1);

namespace App\Support;

use App\Mail\Platform\ContactFormMail;
use App\Mail\Platform\HealthAlertMail;
use App\Mail\Platform\NewSubscriberNotificationMail;
use App\Mail\Platform\PaymentFailedAlertMail;
use App\Mail\Platform\PaymentFailedMail;
use App\Mail\Platform\PlatformCampaignMail;
use App\Mail\Platform\ScheduledCheckinMail;
use App\Mail\Platform\TrialExpiredMail;
use App\Mail\Platform\TrialReminderMail;
use App\Mail\Platform\UnapprovedFreeForeverAlertMail;
use App\Mail\Platform\WelcomeBakerMail;
use App\Mail\PlatformMail;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Notifications\Platform\OwnerVerifyEmailNotification;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailer;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Sample versions of KneadIt's own emails for the local preview page.
 *
 * Every email is built from fake but realistic values. Models are made in
 * memory and never saved, so previewing writes nothing and sends nothing.
 * The staff invitation and weekly digest still read a bakery's settings, so
 * they join this list once they move onto the platform layout.
 */
final class PlatformMailPreviews
{
    private const string STORE_NAME = "Jane's Bakery";

    private const string ADMIN_URL = 'https://janes-bakery.kneadit.test/admin';

    /**
     * @return array<string, string>
     */
    public static function titles(): array
    {
        return [
            'verify-email' => 'Verify email',
            'welcome-baker' => 'Welcome baker',
            'trial-reminder' => 'Trial reminder',
            'trial-expired' => 'Trial expired',
            'payment-failed' => 'Payment failed',
            'platform-campaign' => 'Platform campaign',
            'scheduled-checkin' => 'Scheduled check-in',
            'health-alert' => 'Health alert (admin)',
            'payment-failed-alert' => 'Payment failed alert (admin)',
            'unapproved-free-forever-alert' => 'Unapproved free-forever alert (admin)',
            'new-subscriber-notification' => 'New subscriber notification (admin)',
            'contact-form' => 'Contact form (admin)',
        ];
    }

    public static function make(string $slug): ?Renderable
    {
        return match ($slug) {
            'verify-email' => (new OwnerVerifyEmailNotification)->toMail(self::owner()),
            'welcome-baker' => new WelcomeBakerMail('Jane', self::STORE_NAME, self::ADMIN_URL, 'starter', now()->addDays(14)->toFormattedDateString()),
            'trial-reminder' => new TrialReminderMail(self::owner(), self::STORE_NAME, 3),
            'trial-expired' => new TrialExpiredMail(self::owner(), self::ADMIN_URL),
            'payment-failed' => new PaymentFailedMail(self::owner()),
            'platform-campaign' => new PlatformCampaignMail('New: order reminders', "Your customers can now get a reminder the day before pickup.\n\nTurn it on under Settings → Notifications."),
            'scheduled-checkin' => new ScheduledCheckinMail("It's been a week since you opened Jane's Bakery. How is it going?\n\nReply to this email if anything is getting in your way.", 'How is your first week going?', 'Jane', self::ADMIN_URL, 'https://kneadit.test/resources'),
            'health-alert' => new HealthAlertMail('Database backups have not run for 26 hours.'),
            'payment-failed-alert' => new PaymentFailedAlertMail(self::owner(), self::tenant(), 19.0),
            'unapproved-free-forever-alert' => new UnapprovedFreeForeverAlertMail([['id' => 'janes-bakery', 'name' => self::STORE_NAME, 'email' => 'jane@example.com']]),
            'new-subscriber-notification' => new NewSubscriberNotificationMail('Jane', 'jane@example.com', self::STORE_NAME, 'janes-bakery.kneadit.test', 'starter', 'https://kneadit.test/admin'),
            'contact-form' => new ContactFormMail('Sam', 'sam@example.com', "Hi! Does KneadIt work for a bakery that only sells at farmers' markets?"),
            default => null,
        };
    }

    /**
     * Some emails are plain text only. They are shown as text, not as a page.
     */
    public static function hasHtml(Renderable $preview): bool
    {
        if (! $preview instanceof PlatformMail) {
            return true;
        }

        $content = $preview->content();

        return $content->view !== null || $content->html !== null || $content->markdown !== null || $content->htmlString !== null;
    }

    /**
     * The plain-text version of a preview, rendered from the same template the
     * mailer uses for the email's text part.
     */
    public static function text(Renderable $preview): string
    {
        [$textView, $data] = match (true) {
            $preview instanceof MailMessage => [is_array($preview->view) ? ($preview->view['text'] ?? $preview->view[1] ?? null) : null, $preview->data()],
            $preview instanceof PlatformMail => [$preview->content()->text, self::viewData($preview)],
            default => [null, []],
        };

        if ($textView === null) {
            return 'This email has no plain-text version.';
        }

        return resolve(Mailer::class)->render(['text' => $textView], $data);
    }

    /**
     * The data a mailable passes to its templates, including what content()
     * adds with `with:`, which Laravel only merges in while rendering.
     *
     * @return array<mixed>
     */
    private static function viewData(Mailable $mail): array
    {
        $mail->render();

        return $mail->buildViewData();
    }

    private static function owner(): User
    {
        return User::factory()->owner()->make(['id' => 1, 'name' => 'Jane Baker', 'email' => 'jane@example.com']);
    }

    private static function tenant(): Tenant
    {
        return (new Tenant)->forceFill(['id' => 'janes-bakery', 'store_name' => self::STORE_NAME]);
    }
}

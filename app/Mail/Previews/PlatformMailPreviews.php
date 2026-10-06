<?php

declare(strict_types=1);

namespace App\Mail\Previews;

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
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use App\Notifications\Platform\OwnerVerifyEmailNotification;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailer;
use Illuminate\Mail\Markdown;
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
        if (! $preview instanceof Mailable) {
            return true;
        }

        $preview->render();

        return $preview->view !== null || $preview->markdown !== null;
    }

    /**
     * The plain-text version of a preview. render() already returns the text
     * when an email has no HTML version, so only emails with both need the
     * text template rendered on its own.
     */
    public static function text(Renderable $preview): string
    {
        if ($preview instanceof MailMessage && $preview->markdown !== null) {
            return resolve(Markdown::class)->renderText($preview->markdown, $preview->data())->toHtml();
        }

        $rendered = (string) $preview->render();

        if (! $preview instanceof Mailable || $preview->view === null || $preview->textView === null) {
            return $rendered;
        }

        return resolve(Mailer::class)->render(['text' => $preview->textView], $preview->buildViewData());
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

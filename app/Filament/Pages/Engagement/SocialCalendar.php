<?php

namespace App\Filament\Pages\Engagement;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Content\SocialPost;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;
use Laravel\Pennant\Feature;
use Livewire\Attributes\Computed;

class SocialCalendar extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    #[\Override]
    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('pro-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Pro;
    }

    #[\Override]
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    #[\Override]
    protected static ?string $navigationLabel = 'Social Calendar';

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    #[\Override]
    protected static ?int $navigationSort = 8;

    #[\Override]
    protected string $view = 'filament.pages.engagement.social-calendar';

    public int $year;

    public int $month;

    public ?string $selectedDate = null;

    /** @var array<string, list<array<string, mixed>>> */
    public array $posts = [];

    /** @var list<array<string, mixed>> */
    public array $selectedDayPosts = [];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;
        $this->loadPosts();
    }

    public function loadPosts(): void
    {
        $start = Date::create($this->year, $this->month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $posts = SocialPost::query()
            ->whereBetween('scheduled_for', [$start, $end])
            ->with('product')
            ->orderBy('scheduled_for')
            ->get();

        $this->posts = [];
        foreach ($posts as $post) {
            $scheduledFor = $post->scheduled_for;
            if ($scheduledFor === null) {
                continue;
            }
            $day = $scheduledFor->format('Y-m-d');
            $this->posts[$day][] = [
                'id' => $post->id,
                'platform' => $post->platform,
                'caption' => Str::limit($post->caption, 60),
                'status' => $post->status,
                'time' => $scheduledFor->format('g:i A'),
                'product' => $post->product?->name,
            ];
        }
    }

    public function previousMonth(): void
    {
        $date = Date::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->selectedDate = null;
        $this->selectedDayPosts = [];
        $this->loadPosts();
    }

    public function nextMonth(): void
    {
        $date = Date::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->selectedDate = null;
        $this->selectedDayPosts = [];
        $this->loadPosts();
    }

    public function selectDay(string $date): void
    {
        $this->selectedDate = $date;
        $this->selectedDayPosts = $this->posts[$date] ?? [];
    }

    /** @return array<int, mixed> */
    #[Computed]
    public function calendarDays(): array
    {
        $start = Date::create($this->year, $this->month, 1);
        $daysInMonth = $start->daysInMonth;
        $startDayOfWeek = $start->dayOfWeek; // 0 = Sunday

        $days = [];

        // Padding for days before the 1st
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $days[] = null;
        }

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Date::create($this->year, $this->month, $d)->format('Y-m-d');
            $days[] = [
                'day' => $d,
                'date' => $date,
                'posts' => $this->posts[$date] ?? [],
                'isToday' => $date === now()->format('Y-m-d'),
            ];
        }

        return $days;
    }

    #[Computed]
    public function monthLabel(): string
    {
        return Date::create($this->year, $this->month, 1)->format('F Y');
    }
}

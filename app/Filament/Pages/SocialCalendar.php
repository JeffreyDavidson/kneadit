<?php

namespace App\Filament\Pages;

use App\Filament\Traits\RequiresRole;
use App\Models\SocialPost;
use App\Traits\HasPlanGating;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class SocialCalendar extends Page
{
    use HasPlanGating, RequiresRole;

    protected static function getRequiredRole(): string
    {
        return 'manager';
    }

    protected static string $requiredPlan = 'pro';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Social Calendar';

    protected static string|\UnitEnum|null $navigationGroup = 'Communication';

    protected static ?int $navigationSort = 8;

    protected string $view = 'filament.pages.social-calendar';

    public int $year;

    public int $month;

    public ?string $selectedDate = null;

    public array $posts = [];

    public array $selectedDayPosts = [];

    public function mount(): void
    {
        $this->year = now()->year;
        $this->month = now()->month;
        $this->loadPosts();
    }

    public function loadPosts(): void
    {
        $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $posts = SocialPost::query()
            ->whereBetween('scheduled_for', [$start, $end])
            ->with('product')
            ->orderBy('scheduled_for')
            ->get();

        $this->posts = [];
        foreach ($posts as $post) {
            $day = $post->scheduled_for->format('Y-m-d');
            $this->posts[$day][] = [
                'id' => $post->id,
                'platform' => $post->platform,
                'caption' => \Illuminate\Support\Str::limit($post->caption, 60),
                'status' => $post->status,
                'time' => $post->scheduled_for->format('g:i A'),
                'product' => $post->product?->name,
            ];
        }
    }

    public function previousMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->selectedDate = null;
        $this->selectedDayPosts = [];
        $this->loadPosts();
    }

    public function nextMonth(): void
    {
        $date = Carbon::create($this->year, $this->month, 1)->addMonth();
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

    public function getCalendarDaysProperty(): array
    {
        $start = Carbon::create($this->year, $this->month, 1);
        $daysInMonth = $start->daysInMonth;
        $startDayOfWeek = $start->dayOfWeek; // 0 = Sunday

        $days = [];

        // Padding for days before the 1st
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $days[] = null;
        }

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::create($this->year, $this->month, $d)->format('Y-m-d');
            $days[] = [
                'day' => $d,
                'date' => $date,
                'posts' => $this->posts[$date] ?? [],
                'isToday' => $date === now()->format('Y-m-d'),
            ];
        }

        return $days;
    }

    public function getMonthLabelProperty(): string
    {
        return Carbon::create($this->year, $this->month, 1)->format('F Y');
    }
}

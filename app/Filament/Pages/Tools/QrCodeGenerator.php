<?php

namespace App\Filament\Pages\Tools;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Services\Content\QrCodeService;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Response;
use Laravel\Pennant\Feature;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property-read Schema $form
 */
class QrCodeGenerator extends Page
{
    use RequiresManagerRole;
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess() && Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedQrCode;

    protected static ?string $navigationLabel = 'QR Code';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 15;

    protected string $view = 'filament.pages.tools.qr-code-generator';

    /** @var array<string, mixed> */
    public ?array $data = [];

    public string $qrCodeSvg = '';

    public string $currentUrl = '';

    public function mount(): void
    {
        $this->form->fill([
            'page' => '',
            'size' => '300',
            'color' => '#3E2723',
            'format' => 'svg',
        ]);

        $this->generateQrCode();
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([
                EmbeddedSchema::make('form'),
            ]),
        ]);
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->statePath('data')
            ->components([
                Select::make('page')
                    ->label('Page')
                    ->options([
                        '' => 'Home / Storefront',
                        'menu' => 'Menu',
                        'order' => 'Order Page',
                        'contact' => 'Contact',
                    ])
                    ->default('')
                    ->live()
                    ->afterStateUpdated(fn () => $this->generateQrCode()),
                Select::make('size')
                    ->label('Size')
                    ->options([
                        '200' => 'Small (200px)',
                        '300' => 'Medium (300px)',
                        '500' => 'Large (500px)',
                    ])
                    ->default('300')
                    ->live()
                    ->afterStateUpdated(fn () => $this->generateQrCode()),
                ColorPicker::make('color')
                    ->label('QR Code Color')
                    ->default('#3E2723')
                    ->live()
                    ->afterStateUpdated(fn () => $this->generateQrCode()),
                Select::make('format')
                    ->label('Format')
                    ->options([
                        'svg' => 'SVG',
                        'png' => 'PNG',
                    ])
                    ->default('svg')
                    ->live()
                    ->afterStateUpdated(fn () => $this->generateQrCode()),
            ]);
    }

    public function generateQrCode(): void
    {
        $page = $this->data['page'] ?? '';
        $size = (int) ($this->data['size'] ?? 300);
        $color = $this->data['color'] ?? '#3E2723';
        $format = $this->data['format'] ?? 'svg';

        $this->currentUrl = $this->buildUrl($page);

        $service = resolve(QrCodeService::class);

        $this->qrCodeSvg = $format === 'png'
            ? base64_encode($service->generatePng($this->currentUrl, $size, $color))
            : $service->generateSvg($this->currentUrl, $size, $color);
    }

    public function downloadQrCode(): StreamedResponse
    {
        $page = $this->data['page'] ?? '';
        $size = (int) ($this->data['size'] ?? 300);
        $color = $this->data['color'] ?? '#3E2723';
        $format = $this->data['format'] ?? 'svg';

        $url = $this->buildUrl($page);

        $service = resolve(QrCodeService::class);

        if ($format === 'png') {
            $content = $service->generatePng($url, $size, $color);
            $filename = 'qr-code.' . ($page ?: 'home') . '.png';

            return Response::streamDownload(fn () => print ($content), $filename, [
                'Content-Type' => 'image/png',
            ]);
        }

        $content = $service->generateSvg($url, $size, $color);
        $filename = 'qr-code.' . ($page ?: 'home') . '.svg';

        return Response::streamDownload(fn () => print ($content), $filename, [
            'Content-Type' => 'image/svg+xml',
        ]);
    }

    private function buildUrl(string $page): string
    {
        $baseUrl = 'http://' . tenant()->domains->first()->domain;

        return $baseUrl . ($page ? "/{$page}" : '');
    }
}

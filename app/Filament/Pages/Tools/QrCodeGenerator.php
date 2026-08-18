<?php

namespace App\Filament\Pages\Tools;

use App\Enums\Platform\SubscriptionTier;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use App\Models\Platform\Tenant;
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
        $options = $this->options();

        $this->currentUrl = $this->buildUrl($options['page']);

        $service = resolve(QrCodeService::class);

        $this->qrCodeSvg = $options['format'] === 'png'
            ? base64_encode($service->generatePng($this->currentUrl, $options['size'], $options['color']))
            : $service->generateSvg($this->currentUrl, $options['size'], $options['color']);
    }

    public function downloadQrCode(): StreamedResponse
    {
        $options = $this->options();

        $url = $this->buildUrl($options['page']);

        $service = resolve(QrCodeService::class);

        if ($options['format'] === 'png') {
            $content = $service->generatePng($url, $options['size'], $options['color']);
            $filename = 'qr-code.' . ($options['page'] ?: 'home') . '.png';

            return Response::streamDownload(fn () => print ($content), $filename, [
                'Content-Type' => 'image/png',
            ]);
        }

        $content = $service->generateSvg($url, $options['size'], $options['color']);
        $filename = 'qr-code.' . ($options['page'] ?: 'home') . '.svg';

        return Response::streamDownload(fn () => print ($content), $filename, [
            'Content-Type' => 'image/svg+xml',
        ]);
    }

    private function buildUrl(string $page): string
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            throw new \LogicException('A tenant must be initialized to generate a QR code.');
        }

        $domain = $tenant->domains->first();

        if ($domain === null) {
            throw new \LogicException('The tenant must have a domain to generate a QR code.');
        }

        $baseUrl = 'http://' . $domain->domain;

        return $baseUrl . ($page ? "/{$page}" : '');
    }

    /** @return array{page: string, size: int, color: string, format: 'png'|'svg'} */
    private function options(): array
    {
        $data = $this->form->getState();
        $page = $data['page'] ?? null;
        $size = filter_var($data['size'] ?? null, FILTER_VALIDATE_INT);
        $color = $data['color'] ?? null;
        $format = $data['format'] ?? null;

        return [
            'page' => is_string($page) && in_array($page, ['', 'menu', 'order', 'contact'], true) ? $page : '',
            'size' => is_int($size) && in_array($size, [200, 300, 500], true) ? $size : 300,
            'color' => is_string($color) && preg_match('/^#[0-9a-f]{6}$/i', $color) === 1 ? $color : '#3E2723',
            'format' => $format === 'png' ? 'png' : 'svg',
        ];
    }
}

<?php

namespace App\Filament\Pages;

use App\Enums\SubscriptionTier;
use App\Enums\UserRole;
use App\Filament\Concerns\ShowsUpgradeBadge;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\HtmlString;
use Laravel\Pennant\Feature;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QrCodeGenerator extends Page
{
    use ShowsUpgradeBadge;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (! $user || ! $user->hasMinRole(UserRole::Manager)) {
            return false;
        }

        return Feature::active('growth-features');
    }

    protected static function requiredTier(): SubscriptionTier
    {
        return SubscriptionTier::Growth;
    }

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'QR Code';

    protected static string|\UnitEnum|null $navigationGroup = 'Tools';

    protected static ?int $navigationSort = 15;

    protected string $view = 'filament.pages.qr-code-generator';

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
        $baseUrl = 'http://' . tenant()->domains->first()->domain;
        $page = $this->data['page'] ?? '';
        $size = (int) ($this->data['size'] ?? 300);
        $color = $this->data['color'] ?? '#3E2723';
        $format = $this->data['format'] ?? 'svg';

        $this->currentUrl = $baseUrl . ($page ? '/' . $page : '');

        // Parse hex color to RGB
        $hex = ltrim($color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $qr = QrCode::size($size)->color((int) $r, (int) $g, (int) $b)->margin(1);

        if ($format === 'png') {
            $qr = $qr->format('png');
            /** @var string $pngData */
            $pngData = $qr->generate($this->currentUrl);
            $this->qrCodeSvg = base64_encode($pngData);
        } else {
            /** @var HtmlString $svg */
            $svg = $qr->generate($this->currentUrl);
            $this->qrCodeSvg = $svg->toHtml();
        }
    }

    public function downloadQrCode(): StreamedResponse
    {
        $baseUrl = 'http://' . tenant()->domains->first()->domain;
        $page = $this->data['page'] ?? '';
        $size = (int) ($this->data['size'] ?? 300);
        $color = $this->data['color'] ?? '#3E2723';
        $format = $this->data['format'] ?? 'svg';

        $url = $baseUrl . ($page ? '/' . $page : '');

        $hex = ltrim($color, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $qr = QrCode::size($size)->color((int) $r, (int) $g, (int) $b)->margin(1);

        if ($format === 'png') {
            /** @var string $content */
            $content = $qr->format('png')->generate($url);
            $filename = 'qr-code.' . ($page ?: 'home') . '.png';

            return Response::streamDownload(fn () => print ((string) $content), $filename, [
                'Content-Type' => 'image/png',
            ]);
        } else {
            /** @var string $content */
            $content = $qr->generate($url);
            $filename = 'qr-code.' . ($page ?: 'home') . '.svg';

            return Response::streamDownload(fn () => print ((string) $content), $filename, [
                'Content-Type' => 'image/svg+xml',
            ]);
        }
    }
}

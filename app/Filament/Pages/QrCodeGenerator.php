<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Response;

use App\Traits\HasPlanGating;
class QrCodeGenerator extends Page
{
    use HasPlanGating;


    protected static string $requiredPlan = 'growth';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';
    protected static ?string $navigationLabel = 'QR Code Generator';
    protected static string|\UnitEnum|null $navigationGroup = 'Tools';
    protected static ?int $navigationSort = 12;
    protected string $view = 'filament.pages.qr-code-generator';

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

        $qr = QrCode::size($size)->color($r, $g, $b)->margin(1);

        if ($format === 'png') {
            $qr = $qr->format('png');
            $this->qrCodeSvg = base64_encode($qr->generate($this->currentUrl));
        } else {
            $this->qrCodeSvg = $qr->generate($this->currentUrl)->toHtml();
        }
    }

    public function downloadQrCode()
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

        $qr = QrCode::size($size)->color($r, $g, $b)->margin(1);

        if ($format === 'png') {
            $content = $qr->format('png')->generate($url);
            $filename = 'qr-code.' . ($page ?: 'home') . '.png';
            return Response::streamDownload(fn () => print($content), $filename, [
                'Content-Type' => 'image/png',
            ]);
        } else {
            $content = $qr->generate($url);
            $filename = 'qr-code.' . ($page ?: 'home') . '.svg';
            return Response::streamDownload(fn () => print($content), $filename, [
                'Content-Type' => 'image/svg+xml',
            ]);
        }
    }
}

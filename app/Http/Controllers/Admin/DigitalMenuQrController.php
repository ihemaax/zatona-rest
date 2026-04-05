<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DigitalMenuSetting;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Symfony\Component\HttpFoundation\Response;

class DigitalMenuQrController extends Controller
{
    public function index()
    {
        $setting = DigitalMenuSetting::firstOrCreate(
            ['id' => 1],
            [
                'title' => 'Digital Menu',
                'slug' => 'main-menu',
                'is_active' => true,
                'show_prices' => true,
                'show_descriptions' => true,
            ]
        );

        $menuUrl = route('digital.menu.show', $setting->slug);

        return view('admin.digital-menu.qr', compact('setting', 'menuUrl'));
    }

    public function image()
    {
        $setting = DigitalMenuSetting::firstOrCreate(
            ['id' => 1],
            ['title' => 'Digital Menu', 'slug' => 'main-menu']
        );

        $menuUrl = route('digital.menu.show', $setting->slug);

        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $menuUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 420,
            margin: 18,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $result = $builder->build();

        return new Response(
            $result->getString(),
            200,
            ['Content-Type' => $result->getMimeType()]
        );
    }

    public function download()
    {
        $setting = DigitalMenuSetting::firstOrCreate(
            ['id' => 1],
            ['title' => 'Digital Menu', 'slug' => 'main-menu']
        );

        $menuUrl = route('digital.menu.show', $setting->slug);

        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $menuUrl,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 900,
            margin: 24,
            roundBlockSizeMode: RoundBlockSizeMode::Margin
        );

        $result = $builder->build();

        return new Response(
            $result->getString(),
            200,
            [
                'Content-Type' => $result->getMimeType(),
                'Content-Disposition' => 'attachment; filename="digital-menu-qr.png"',
            ]
        );
    }

    public function print()
    {
        $setting = DigitalMenuSetting::firstOrCreate(
            ['id' => 1],
            ['title' => 'Digital Menu', 'slug' => 'main-menu']
        );

        $menuUrl = route('digital.menu.show', $setting->slug);

        return view('admin.digital-menu.print-qr', compact('setting', 'menuUrl'));
    }
}
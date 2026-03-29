<?php

namespace App\Http\Controllers\Qr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DownloadController extends Controller
{
    public function qrCode()
    {
        $url = url('download-app');

        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
                                                                                           'size' => '300x300',
                                                                                           'data' => $url,
                                                                                           'format' => 'png',
                                                                                           'margin' => 2,
                                                                                           'ecc' => 'H'
                                                                                       ]);

        try {
            $image = file_get_contents($qrCodeUrl);

            if ($image === false) {
                throw new \Exception('Could not fetch QR code');
            }

            if (strpos($image, "\x89PNG\x0d\x0a\x1a\x0a") !== 0) {
                throw new \Exception('Invalid PNG data received');
            }

            return response($image)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'inline; filename="qr-code.png"')
                ->header('Cache-Control', 'public, max-age=86400'); // Кэшируем на 1 день

        } catch (\Exception $e) {
            return $this->qrCodeHtmlPage($url);
        }
    }

    private function qrCodeHtmlPage($url)
    {
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?' . http_build_query([
                                                                                           'size' => '300x300',
                                                                                           'data' => $url,
                                                                                           'format' => 'png'
                                                                                       ]);
        return response('<html>
            <head>
                <title>QR Code for App Download</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                        padding: 50px;
                        background: #f5f5f5;
                    }
                    .container {
                        background: white;
                        padding: 40px;
                        border-radius: 10px;
                        box-shadow: 0 0 20px rgba(0,0,0,0.1);
                        display: inline-block;
                    }
                    h1 {
                        color: #333;
                        margin-bottom: 30px;
                    }
                    .qr-code {
                        border: 15px solid white;
                        box-shadow: 0 0 10px rgba(0,0,0,0.1);
                        margin: 20px 0;
                    }
                    .download-btn {
                        display: inline-block;
                        background: #4CAF50;
                        color: white;
                        padding: 15px 30px;
                        text-decoration: none;
                        border-radius: 5px;
                        font-size: 18px;
                        margin-top: 20px;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1>Scan QR Code to Download App</h1>

                    <img src="' . $qrCodeUrl . '" alt="QR Code" class="qr-code" width="300" height="300">

                    <p style="margin: 20px 0; color: #666;">
                        Scan this QR code with your smartphone camera
                    </p>

                    <a href="' . $url . '" class="download-btn">
                        Or Click Here to Download
                    </a>
                </div>
            </body>
        </html>')->header('Content-Type', 'text/html');
    }

    public function download(Request $request){
        $userAgent = $request->header('User-Agent');
        Log::info('user-agent '. $userAgent);
        $iosLink = "https://apps.apple.com/app/swapmap/id1629974791";
        $androidLink = "https://play.google.com/store/apps/details?id=app.swapmap";
        $ru_store = "https://www.rustore.ru/catalog/app/app.swapmap";
        $huw = "https://appgallery.cloud.huawei.com/ag/n/app/C106912995";


        if (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
            return redirect()->away($iosLink);
        }

        if (preg_match('/HMSCore/i', $userAgent)) {
            return redirect()->away($huw);
        }
        if (preg_match('/Android/i', $userAgent)) {
            return redirect()->away($androidLink);
        }

        return redirect()->away($ru_store);
    }
}

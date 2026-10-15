<?php 

class ManifestCommand implements Asatru\Commands\Command  {
    public function handle($args)
    {
        try {
            $enabled = env('APP_ENABLEPWA', false);
            if (!$enabled) {
                throw new \Exception('PWA feature is not enabled. Please enable it first.');
            }

            $manifest = [
                'name' => env('APP_SERVERNAME'),
                'short_name' => env('APP_SERVERNAME'),
                'icons' => [
                    [
                        'src' => '/img/icons/logo.png',
                        'sizes' => '256x256',
                        'type' => 'image/png'
                    ]
                ],
                'start_url' => '/',
                'display' => 'standalone',
                'background_color' => '#323232',
                'theme_color' => '#323232'
            ];

            file_put_contents(public_path() . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
        } catch (\Exception $e) {
            echo "\033[31m{$e->getMessage()}\033[39m\n";
            return;
        }

        echo "\033[32mSuccessfully created manifest file\033[39m\n";
    }
}
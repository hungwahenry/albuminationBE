<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class WellKnownController extends Controller
{
    /**
     * Apple App Site Association for iOS Universal Links.
     * Replace TEAMID with your Apple Team ID from developer.apple.com/account.
     */
    public function appleAppSiteAssociation(): Response
    {
        $data = [
            'applinks' => [
                'apps' => [],
                'details' => [
                    [
                        'appIDs' => [
                            'TEAMID.com.albumination.app',
                        ],
                        'components' => [
                            // Album pages
                            ['/' => '/album/*'],
                            // Rotation pages
                            ['/' => '/rotation/*'],
                            // User profiles
                            ['/' => '/user/*'],
                        ],
                    ],
                ],
            ],
        ];

        return response(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), 200)
            ->header('Content-Type', 'application/json');
    }

    /**
     * Android Asset Links for App Links.
     * Replace SHA256_CERT_FINGERPRINT with your signing certificate fingerprint.
     * Get it by running: eas credentials or keytool -list -v -keystore your.keystore
     */
    public function assetLinks(): JsonResponse
    {
        $data = [
            [
                'relation'  => ['delegate_permission/common.handle_all_urls'],
                'target'    => [
                    'namespace'              => 'android_app',
                    'package_name'           => 'com.albumination.app',
                    'sha256_cert_fingerprints' => [
                        'SHA256_CERT_FINGERPRINT',
                    ],
                ],
            ],
        ];

        return response()->json($data);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;

class ViteAssetsTest extends TestCase
{
    /**
     * Test that Vite assets are correctly configured.
     *
     * @return void
     */
    public function test_vite_manifest_exists()
    {
        $manifestPath = public_path('build/manifest.json');
        $this->assertFileExists($manifestPath);

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($manifest);
        $this->assertArrayHasKey('resources/css/app.css', $manifest);
        $this->assertArrayHasKey('resources/js/app.js', $manifest);
    }

    /**
     * Test that a page using the app layout loads with Vite assets.
     *
     * @return void
     */
    public function test_app_layout_uses_vite()
    {
        // Check that the manifest files exist
        $cssFiles = glob(public_path('build/assets/app-*.css'));
        $jsFiles = glob(public_path('build/assets/app-*.js'));

        $this->assertNotEmpty($cssFiles, 'CSS build file should exist');
        $this->assertNotEmpty($jsFiles, 'JS build file should exist');

        // Check that files are not empty
        $this->assertGreaterThan(0, filesize($cssFiles[0]), 'CSS file should not be empty');
        $this->assertGreaterThan(0, filesize($jsFiles[0]), 'JS file should not be empty');
    }
}

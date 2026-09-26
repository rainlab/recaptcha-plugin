<?php namespace RainLab\Recaptcha\Tests;

use Db;
use PluginTestCase;
use RainLab\Recaptcha\Models\Settings;

class SettingsTest extends PluginTestCase
{
    /**
     * setUp test case
     */
    public function setUp(): void
    {
        parent::setUp();

        (new Settings)->resetDefault();
        Settings::clearInternalCache();
    }

    public function testDefaultValues()
    {
        $this->assertTrue((bool) Settings::get('enabled'));
        $this->assertEquals(0.5, Settings::get('min_score'));
        $this->assertNull(Settings::get('site_key'));
    }

    public function testSetAndGetRoundTrip()
    {
        Settings::set([
            'site_key' => 'test_site',
            'secret_key' => 'test_secret',
            'lang' => 'en'
        ]);

        $this->assertEquals('test_site', Settings::get('site_key'));
        $this->assertEquals('test_secret', Settings::get('secret_key'));
        $this->assertEquals('en', Settings::get('lang'));
    }

    public function testRecordStoredUnderSettingsCode()
    {
        Settings::set('site_key', 'test_site');

        $record = Db::table('system_settings')
            ->where('item', 'rainlab_recaptcha_settings')
            ->first();

        $this->assertNotNull($record);
        $this->assertEquals('test_site', array_get(json_decode($record->value, true), 'site_key'));
    }
}

<?php

use WebberZone\Better_Search\Admin\Settings;
use WebberZone\Better_Search\Feature_Manager;

class BetterSearchFeatureManagerTest extends WP_UnitTestCase {

	public function test_api_features_have_settings_toggles_and_safe_defaults() {
		$features = Feature_Manager::get_features();
		$settings = Settings::settings_features();
		$defaults = Settings::get_defaults();

		$this->assertSame( 'enable_abilities_api', $features['abilities_api']['setting'] );
		$this->assertTrue( $features['abilities_api']['default'] );
		$this->assertSame( 'checkbox', $settings['enable_abilities_api']['type'] );
		$this->assertSame( 1, $defaults['enable_abilities_api'] );

		$this->assertSame( 'enable_did_you_mean', $features['did_you_mean']['setting'] );
		$this->assertFalse( $features['did_you_mean']['default'] );
		$this->assertFalse( $settings['enable_did_you_mean']['default'] );
		$this->assertSame( 0, $defaults['enable_did_you_mean'] );

		$this->assertArrayHasKey( 'enable_rest_api', $settings );
		$this->assertArrayNotHasKey( 'enable_rest_api', Settings::settings_general() );
		$this->assertSame( 0, $defaults['enable_rest_api'] );
	}
}

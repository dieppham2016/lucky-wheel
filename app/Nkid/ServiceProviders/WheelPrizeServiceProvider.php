<?php

namespace App\Nkid\ServiceProviders;

use App;
use Illuminate\Support\ServiceProvider;

class WheelPrizeServiceProvider extends ServiceProvider {
	/**
	 * Register services.
	 *
	 * @return void
	 */
	public function register() {
		App::bind(
			'wheel-prize', function () {
			return new App\Nkid\ModelLogic\Wheel\PrizeModelLogic;
		});

		App::bind(
			'wheel-pattern', function () {
			return new App\Nkid\ModelLogic\Wheel\PatternModelLogic;
		});

		App::bind(
			'wheel-change-log', function () {
			return new App\Nkid\ModelLogic\Wheel\ChangeLogModelLogic;
		});

		App::bind(
			'wheel-config', function () {
			return new App\Nkid\ModelLogic\Wheel\ConfigModelLogic;
		});

		App::bind(
			'wheel-store', function () {
			return new App\Nkid\ModelLogic\Wheel\StoreModelLogic;
		});

	}

	/**
	 * Bootstrap services.
	 *
	 * @return void
	 */
	public function boot() {
		//
	}
}
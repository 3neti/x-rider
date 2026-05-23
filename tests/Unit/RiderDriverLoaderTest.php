<?php

use Illuminate\Filesystem\Filesystem;
use LBHurtado\XRider\Exceptions\RiderDriverNotFound;
use LBHurtado\XRider\Support\RiderDriverLoader;

it('loads the package fallback default driver', function () {
    config()->set('x-rider.drivers_path', base_path('missing-x-rider-drivers'));
    config()->set('x-rider.package_drivers_path', __DIR__.'/../../resources/rider-drivers');

    $loader = new RiderDriverLoader(new Filesystem);

    $driver = $loader->load('default');

    expect($driver)
        ->toHaveKey('name', 'default')
        ->and(data_get($driver, 'rider.success.content'))
        ->toContain('Thank you');
});

it('prefers the published driver path over the package fallback', function () {
    $publishedPath = sys_get_temp_dir().'/x-rider-drivers-'.uniqid();

    mkdir($publishedPath, 0777, true);

    file_put_contents($publishedPath.'/default.yaml', <<<YAML
name: default
version: 9.9.9
rider:
  success:
    enabled: true
    type: markdown
    content: Published override.
YAML);

    config()->set('x-rider.drivers_path', $publishedPath);
    config()->set('x-rider.package_drivers_path', __DIR__.'/../../resources/rider-drivers');

    $loader = new RiderDriverLoader(new Filesystem);

    $driver = $loader->load('default');

    expect(data_get($driver, 'version'))->toBe('9.9.9')
        ->and(data_get($driver, 'rider.success.content'))->toBe('Published override.');
});

it('throws when a driver does not exist', function () {
    config()->set('x-rider.drivers_path', base_path('missing-x-rider-drivers'));
    config()->set('x-rider.package_drivers_path', base_path('missing-package-rider-drivers'));

    $loader = new RiderDriverLoader(new Filesystem);

    $loader->load('missing');
})->throws(RiderDriverNotFound::class);

it('loads the package demo driver', function () {
    config()->set('x-rider.package_drivers_path', __DIR__.'/../../resources/rider-drivers');

    $loader = new \LBHurtado\XRider\Support\RiderDriverLoader(
        new \Illuminate\Filesystem\Filesystem
    );

    $driver = $loader->load('demo');

    expect($driver['name'])->toBe('demo')
        ->and(data_get($driver, 'rider.stages'))->toHaveCount(3)
        ->and(data_get($driver, 'rider.stages.0.key'))->toBe('demo-pre-claim')
        ->and(data_get($driver, 'rider.stages.1.key'))->toBe('demo-success-message')
        ->and(data_get($driver, 'rider.stages.2.key'))->toBe('demo-redirect');
});
<?php

use LeafyTech\Core\Application;
use LeafyTech\Core\Database;
use LeafyTech\Core\Config;
use LeafyTech\Core\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

// Define dummy model and repository
class DummyItem extends Model {
    protected $table = 'dummy_items';
    protected $fillable = ['name', 'description'];
    public $timestamps = false;
}

class DummyRepository extends BaseRepository {
    protected string $classModel = DummyItem::class;
}

beforeEach(function () {
    $_ENV['APP_ID'] = 'test-id';
    $_ENV['APP_KEY'] = 'test-key';
    $_ENV['APP_NAME'] = 'Test App';
    $_ENV['APP_URL'] = 'http://localhost';
    $_ENV['APP_DEBUG'] = 'true';
    $_ENV['DB_DRIVER'] = 'mysql';
    $_ENV['DB_HOST'] = '127.0.0.1';
    $_ENV['DB_PORT'] = '3306';
    $_ENV['DB_DATABASE'] = 'test_db';
    $_ENV['DB_USERNAME'] = 'root';
    $_ENV['DB_PASSWORD'] = 'password';
    $_ENV['DB_CHARSET'] = 'utf8mb4';
    $_ENV['DB_PREFIX'] = 'tbl_';

    // Mock configuration with SQLite memory connection
    $config = new Config($_ENV);
    $config->set('connections', [
        'default' => [
            'driver'   => 'sqlite',
            'host'     => '',
            'port'     => '',
            'database' => ':memory:',
            'username' => '',
            'password' => '',
            'charset'  => '',
            'prefix'   => '',
        ]
    ]);

    // Instantiate Database connection
    new Database($config);

    // Create schema
    Capsule::schema()->create('dummy_items', function (Blueprint $table) {
        $table->increments('id');
        $table->string('name');
        $table->string('description')->nullable();
    });
});

afterEach(function () {
    Capsule::schema()->dropIfExists('dummy_items');
});

test('it inserts, updates, finds and deletes models in database', function () {
    $repo = new DummyRepository();

    // Test insert
    $item = $repo->insert(['name' => 'Item 1', 'description' => 'Desc 1']);
    expect($item)->toBeInstanceOf(DummyItem::class)
        ->and($item->id)->toBe(1)
        ->and($item->name)->toBe('Item 1');

    // Test find
    $found = $repo->find(1);
    expect($found)->not->toBeNull()
        ->and($found->name)->toBe('Item 1');

    // Test all
    $all = $repo->all();
    expect($all)->toHaveCount(1);

    // Test update
    $updated = $repo->update(1, ['name' => 'Updated Item']);
    expect($updated)->toBeTrue()
        ->and($repo->find(1)->name)->toBe('Updated Item');

    // Test delete
    $repo->delete(1);
    expect($repo->find(1))->toBeNull();
});

<?php

namespace Tests\App\Infrastructure\Controller;

use App\Application\DataSource\CryptoDataSource;
use Illuminate\Http\Response;
use Tests\TestCase;
use Exception;
use Mockery;

class GetCoinControllerTest extends TestCase
{
    private CryptoDataSource $coinLoreCryptoDataManager;

    /**
     * @setUp
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->coinLoreCryptoDataManager = Mockery::mock(CryptoDataSource::class);
        $this->app->bind(CryptoDataSource::class, fn () => $this->coinLoreCryptoDataManager);
    }

    /**
     * @test
     */
    public function coinExists()
    {
        $this->coinLoreCryptoDataManager
            ->expects('getCoin')
            ->with('90')
            ->once()
            ->andReturn(json_encode(array(['id' => '90',
                'name' => '1',
                'symbol' => '1',
                'nameid' => '1',
                'price_usd' => '1',
                'rank' => 1])));

        $response = $this->get('/api/coin/status/90');

        $response->assertStatus(Response::HTTP_OK)->assertExactJson(['{"coin_id":"90","name":"1","symbol":"1","name_id":"1","rank":1,"price_usd":"1"}']);
    }

    /**
     * @test
     */
    public function coinNotExists()
    {
        $this->coinLoreCryptoDataManager
            ->expects('getCoin')
            ->with('90')
            ->once()
            ->andThrow(new Exception('A coin with specified ID was not found.',404));

        $response = $this->get('/api/coin/status/90');

        $response->assertStatus(Response::HTTP_NOT_FOUND)->assertExactJson(['error' => 'A coin with specified ID was not found.']);
    }
}
